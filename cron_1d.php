<?php
require 'binance.php';
$binance = new Binance_history();

$pairs = ['BTCUSDT','ETHUSDT','XRPUSDT','BCHABCUSDT','LTCUSDT','BNBUSDT','EOSUSDT','XMRUSDT','XLMUSDT','TRXUSDT','ADAUSDT','DASHUSDT','LINKUSDT','NEOUSDT','IOTAUSDT','ETCUSDT'];

foreach ($pairs as $pair) {
	$response = $binance->insert_history($pair);
}

echo '<pre>';
print_r($response);
echo '</pre>';