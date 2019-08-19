<?php
require 'binance.php';
$binance = new Binance_history();

$response = $binance->update_price(['BTCUSDT','ETHUSDT','XRPUSDT','BCHABCUSDT','LTCUSDT','BNBUSDT','EOSUSDT','XMRUSDT','XLMUSDT','TRXUSDT','ADAUSDT','DASHUSDT','LINKUSDT','NEOUSDT','IOTAUSDT','ETCUSDT']);

echo '<pre>';
print_r($response);
echo '</pre>';