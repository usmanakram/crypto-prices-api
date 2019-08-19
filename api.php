<?php
/*if ( isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], ['http://localhost:3000','http://localhost:5000']) ) {
	// header('Access-Control-Allow-Origin: http://localhost:3000');
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	// header('Content-type: application/xml');
	header('Content-type: application/json');
}*/

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require 'database.php';

$db = new Database('cc_markets', 'localhost', 'root', '');
// $db = new Database('cc_market', 'localhost', 'usman_ak', '!Scitilop!1');

$pairs = ['BTCUSDT','ETHUSDT','XRPUSDT','BCHABCUSDT','LTCUSDT','BNBUSDT','EOSUSDT','XMRUSDT','XLMUSDT','TRXUSDT','ADAUSDT','DASHUSDT','LINKUSDT','NEOUSDT','IOTAUSDT','ETCUSDT'];

if (isset($_GET['coins_list']) && $_GET['coins_list'] === 'all') {
	$response = $db->order_by('id', 'asc')
				->get('latest_prices')
				->result_array();
		
	echo json_encode($response);

} elseif ( isset($_GET['coin']) ) {

	$response = $db->order_by('open_time', 'desc')
					->get_where('candlesticks', ['pair' => strtoupper($_GET['coin']) . 'USDT', 'time_interval' => '1d'])
					->result_array();

	echo json_encode($response);
}
?>