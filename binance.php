<?php
require 'database.php';
require './php-binance-api-master/vendor/autoload.php';

/**
 * 
 */
class Binance_history
{
    public $db;
    public $api;
    
    function __construct()
    {
        $this->db = new Database('cc_markets', 'localhost', 'root', '');
        // $this->db = new Database('cc_market', 'localhost', 'usman_ak', '!Scitilop!1');
        $this->api = new Binance\API("<api key>","<secret>");

        $this->api->useServerTime();
    }

    private function fetchNextHistory($symbol, $interval, $limit)
    {
        // $data = $this->db->get_where('candlesticks', ['pair' => 'BTCUSDT', 'time_interval' => '1d'])->result_array();
        $latestCandle = $this->db->order_by('open_time', 'desc')
                        ->limit(1)
                        ->get_where('candlesticks', ['pair' => $symbol, 'time_interval' => $interval])
                        ->row_array();

        // Get Kline/candlestick data for a symbol
        // Periods: 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
        // return $this->api->candlesticks($symbol, $interval, $limit, strtotime('2017-01-01 00:00:00') * 1000);
        return $this->api->candlesticks($symbol, $interval, $limit, $latestCandle['open_time'] + 1);
    }

    private function createQueryString($history, $symbol, $interval)
    {
        $query = 'INSERT INTO `candlesticks` (`pair`, `time_interval`, `open`, `high`, `low`, `close`, `volume`, `open_time`, `close_time`, `asset_volume`, `base_volume`, `trades`, `asset_buy_volume`, `taker_buy_volume`, `ignored`) VALUES ';

        foreach ($history as $key => $value) {

            $query .= '
            (' . 
                '"' . $symbol . '", ' . 
                '"' . $interval . '", ' . 
                $value['open'] . ', ' . 
                $value['high'] . ', ' . 
                $value['low'] . ', ' . 
                $value['close'] . ', ' . 
                $value['volume'] . ', ' . 
                $value['openTime'] . ', ' . 
                $value['closeTime'] . ', ' . 
                $value['assetVolume'] . ', ' . 
                $value['baseVolume'] . ', ' . 
                $value['trades'] . ', ' . 
                $value['assetBuyVolume'] . ', ' . 
                $value['takerBuyVolume'] . ', ' . 
                $value['ignored'] . 
            '), ';
        }

        return rtrim($query, ', ') . ';';
    }

    public function insert_history(string $symbol = 'BTCUSDT', string $interval = "1d")
    {
        $history = $this->fetchNextHistory($symbol, $interval, $limit = 200);

        if ($history) {
            $query = $this->createQueryString($history, $symbol, $interval);
            return $this->db->queryRun($query);
        } else {
            return 'History fully updated.';
        }
    }

    private function createLatestPricesQueryString($data, $symbol)
    {
        $existingData = $this->db->order_by('id', 'desc')
                        ->limit(1)
                        ->get_where('latest_prices', ['pair' => $symbol])
                        ->row_array();

        if ($existingData) {
            $query = 'UPDATE `latest_prices` SET 
                `pair` = "' . $symbol . '", 
                `last_price` = ' . $data['lastPrice'] . ', 
                `volume` = ' . $data['volume'] . ', 
                `price_change_percent` = ' . $data['priceChangePercent'] . ' 
                WHERE `id` = ' . $existingData['id'] . '
            ';
        } else {

            $query = 'INSERT INTO `latest_prices` (`pair`, `last_price`, `volume`, `price_change_percent`) VALUES ';

            $query .= '
            (' . 
                '"' . $symbol . '", ' . 
                $data['lastPrice'] . ', ' . 
                $data['volume'] . ', ' . 
                $data['priceChangePercent'] . 
            ');';
        }

        return $query;
    }

    public function update_price(array $symbols = ['BTCUSDT'])
    {        
        $prevDayData = $this->api->prevDay();

        if ($prevDayData) {
            foreach ($symbols as $symbol) {
                if ( $index = array_search($symbol, array_column($prevDayData, 'symbol')) ) {
                    $newData = $prevDayData[$index];

                    $query = $this->createLatestPricesQueryString($newData, $symbol);
                    $this->db->queryRun($query);
                }
            }
            return 'Data has been updated.';
        } else {
            return 'Data is not being fetched';
        }
    }
}