<?php
include_once API_PATH . "Manager.php";

function API_order_getChannelCode($managerUID, $orderID){
	return sha1($managerUID . $orderID);
}

function API_order_getOrder($id){
	global $wpdb;
	
	return $wpdb->get_row($wpdb->prepare(
"SELECT * FROM active_orders WHERE id = %s",
		$id
	));
}

function API_order_cleanOrders(){
	global $wpdb;
	$wpdb->query(
"INSERT INTO events (id, slotid, name, data)
SELECT NULL, lim.id, 'orderShouldDisconnect', CONCAT('{\"orderId\":\"',ao.id,'\"}')
FROM
	active_managers am
	INNER JOIN active_orders ao
		ON ao.manageruid = am.manageruid
	INNER JOIN listeners lim
		ON lim.channel = am.manageruid
	LEFT OUTER JOIN listeners li
		ON li.id = ao.slotid
WHERE li.id IS NULL"
	);
	$wpdb->query(
"UPDATE 
	active_orders ao
	LEFT OUTER JOIN listeners li
		ON li.id = ao.slotid
SET 
	ao.free=1,
	ao.manageruid=NULL,
	ao.slotid=NULL	
WHERE 
	li.id IS NULL 
AND ao.slotid IS NOT NULL"
	);
}

function API_order_removeOrder($id){
	global $wpdb;
	
	return $wpdb->get_row($wpdb->prepare(
"DELETE FROM active_orders WHERE id = %s",
		$id
	));
}

function API_order_endTask($orderId, $managerUID, $state, $redirect){
	global $wpdb;

	$orderChannel = API_order_getChannelCode($managerUID, $orderId);

	if($orderId == "" || $managerUID == ""){
		return [
			"ok" => false
		];
	}

	$order = API_order_getOrder($orderId);
	if($order->manageruid != $managerUID){
		return [
			"ok" => false
		];
	}

	API_order_removeOrder($orderId);

	
	if($order->hook != ""){
		file_get_contents(API_addUrlParameters($order->hook, [
			"privateKey" => $order->privatekey,
			"outId" => $order->outid,
			"state" => $state
		]));
	}

	API_event_emit("endTask", json_encode([
		"redirect" => $order->{"redirect".$redirect},
		"orderId" => $orderId
	]), $orderChannel);

	return [
		"ok" => true
	];
}

function API_open_order_testAccess(){
	global $wpdb;
	$orderId = API_arg("orderId");

	return [
		"ok" => $wpdb->get_var($wpdb->prepare(
"SELECT id FROM active_orders WHERE end = 0 AND id = %s AND free = 1",
			$orderId
		)) !== NULL
	];
}

function API_open_order_findNewTaskChannel(){
	global $wpdb;
	$orderId = API_arg("orderId");

	$count = 0;
	$muid = API_manager_takeManager();
	$channel = API_order_getChannelCode($muid, $orderId);
	$slotId = API_event_createSlot($channel, 20);

	if($orderId != ""){
		$count = $wpdb->query($wpdb->prepare(
"UPDATE active_orders SET manageruid = %s, slotid = %s WHERE id = %s AND end = 0",
			$muid,
			$slotId,
			$orderId
		));
	}

	if($count === 1){
		API_event_emit("manager_addOrder", json_encode([
			"channel" => $channel,
			"orderData" => $wpdb->get_row($wpdb->prepare(
"SELECT source, incomingtime, price, id, currency FROM active_orders WHERE id = %s",
				$orderId
			))
		]), $muid);
		return [
			"ok" => true,
			"slotId" => $slotId,
			"channel" => $channel
		];
	}else{
		return [
			"ok" => false
		];
	}
}

function API_open_order_channelConnectTimeout(){
	global $wpdb;
	$orderId = API_arg("orderId");

	$count = 0;

	if($orderId != ""){
		$count = $wpdb->query($wpdb->prepare(
"UPDATE active_orders SET manageruid = NULL, slotid = NULL WHERE id = %s AND free = 1",
			$orderId
		));
	}

	if($count === 1){
		return API_open_order_findNewTaskChannel();
	}else{
		return [
			"ok" => false
		];
	}
}

function API_open_order_addOrder(){
	global $wpdb;
	try{
		$outId = API_arg("outId", NULL);
		$price = API_arg("price");
		$hook = API_arg("hook", NULL);
		$redirectOK = API_arg("redirectOK", NULL);
		$redirectFail = API_arg("redirectFail", NULL);
		$privateKey = API_arg("privateKey", NULL);
		$currency = API_arg("currency", NULL);

		$wpdb->query($wpdb->prepare(
"INSERT INTO active_orders (price, hook, outid, redirectok, redirectfail, privatekey, currency)
	VALUES (%s, %s, %s, %s, %s, %s, %s)",
			$price,
			$hook,
			$outId,
			$redirectOK,
			$redirectFail,
			$privateKey,
			$currency
		));

		return [
			"ok" => true,
			"id" => $wpdb->insert_id
		];
	}catch(Exception $e){
		return [
			"ok" => false
		];
	}
}

function API_open_order_takeOrder(){
	global $wpdb;
	try{
		$orderId = API_arg("orderId");
		$managerUID = API_arg("managerUID");

		$count = 0;

		$count = $wpdb->query($wpdb->prepare(
"UPDATE active_orders SET free = 0 WHERE id = %s AND free = 1 AND manageruid = %s",
			$orderId,
			$managerUID
		));

		if($count === 1){
			$channel = API_order_getChannelCode($managerUID, $orderId);
			$slotId = API_event_createSlot($channel, 20);
			API_event_emit("updateOrderStage", json_encode([
				"stage" => 'cardData'
			]), $channel);
			return [
				"ok" => true,
				"slotId" => $slotId,
				"channel" => $channel
			];
		}else{
			return [
				"ok" => false
			];
		}
	}catch(Exception $e){
		return [
			"ok" => false
		];
	}
}

function API_open_order_makePayed(){
	$orderId = API_arg("orderId");
	$managerUID = API_arg("managerUID");

	return API_order_endTask($orderId, $managerUID, "ok", "ok");
}

function API_open_order_makeDeclined(){
$orderId = API_arg("orderId");
	$managerUID = API_arg("managerUID");

	return API_order_endTask($orderId, $managerUID, "fail", "fail");
}
?>