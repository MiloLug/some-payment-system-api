<?php
define("API_PATH", get_template_directory() . "/API/");

define("API_SLOT_LIFETIME", 7); //(int)
define("API_POLL_LIFETIME", 10); //(int)
define("API_POLL_SPEED", 2); //tests per second (int)

define("API_CLEANING_CRON_INTERVAL", 60); //(int)
define("API_CLEANING_INTERVAL", 2); //every N seconds (int), API_CLEANING_CRON_INTERVAL % N == 0

define("API_CHANNEL_DEFAULT", "global");

include_once API_PATH . "core.php";
include_once API_PATH . "Event.php";

include_once API_PATH . "Order.php";
include_once API_PATH . "Manager.php";

function API_global_deathCleaner(){
	API_event_cleanEvents();
	API_order_cleanOrders();
	API_manager_cleanManagers();
}

function API_open_global_deathCleaner(){
	$startTime = time();
	$k = API_CLEANING_CRON_INTERVAL/API_CLEANING_INTERVAL;

	for($i = 0; $i < $k; $i++){
		API_global_deathCleaner();
		sleep(API_CLEANING_INTERVAL);
		if((time() - $startTime) > (API_CLEANING_CRON_INTERVAL - API_CLEANING_INTERVAL/4))
			break;
	}
}


function API_main_call(){
	echo json_encode(call_user_func(
		"API_open_" . API_arg("space") . "_" . API_arg("method")
	));
	wp_die();
}
add_action('wp_ajax_API_main_call', 'API_main_call');
add_action('wp_ajax_nopriv_API_main_call', 'API_main_call');
?>