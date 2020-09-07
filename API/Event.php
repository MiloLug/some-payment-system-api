<?php
function API_event_cleanEvents(){
	global $wpdb;
	$wpdb->query(
"DELETE listeners, events
	FROM listeners
		LEFT JOIN events
		ON events.slotid = listeners.id
	WHERE listeners.removedate < CURRENT_TIMESTAMP()");
}

function API_event_resurrectSlot($id){
	global $wpdb;
	$wpdb->query($wpdb->prepare(
"UPDATE listeners 
	SET removedate = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL %d second) 
	WHERE id = %s",
		API_SLOT_LIFETIME,
		$id
	));
}

function API_event_createSlot($channel = API_CHANNEL_DEFAULT, $start_lifetime = API_SLOT_LIFETIME){
	global $wpdb;
	$wpdb->query($wpdb->prepare(
"INSERT INTO listeners (id, channel, removedate) 
	VALUES(NULL, %s, DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL %d second))",
		$channel,
		$start_lifetime
	));
	return $wpdb->insert_id;
}

function API_event_emit($name, $data, $channel = API_CHANNEL_DEFAULT, $slotId = "", $exclude_slot = -1){
	global $wpdb;
	if($slotId != ""){
		$wpdb->query($wpdb->prepare(
"INSERT INTO events (id, slotid, name, data) VALUES(NULL, %s, %s, %s)",
			$slotId,
			$name,
			stripslashes($data)
		));
	}else{
		$wpdb->query($wpdb->prepare(
"INSERT INTO events (id, slotid, name, data)
SELECT NULL, sel.id, %s, %s
FROM listeners AS sel
WHERE sel.channel = %s
	AND sel.id <> %s",
			$name,
			stripslashes($data),
			$channel,
			$exclude_slot
		));
	}
}

function API_open_event_createSlot(){
	try{
		$channel = API_arg("channel", API_CHANNEL_DEFAULT);
		
		$id = API_event_createSlot($channel);

		return [
			"ok" => true,
			"id" => $id
		];
	}catch(Exception $e){
		return [
			"ok" => false
		];
	}
}

function API_open_event_emit(){
	try{
		$data = API_arg("data", '""');
		$name = API_arg("name");
		$channel = API_arg("channel", API_CHANNEL_DEFAULT);
		$emitSelf = !!API_arg("emitSelf");
		$thisSlot = API_arg("thisSlot");
		$slotId = API_arg("slotId");
		
		API_event_cleanEvents();

		API_event_emit($name, $data, $channel, $slotId, ($emitSelf ? "" : $thisSlot));

		return [
			"ok" => true
		];
	}catch(Exception $e){
		return [
			"ok" => false
		];
	}
}

function API_open_event_on(){
	global $wpdb;
	try{
		$slotId = API_arg("slotId");
		if($slotId == ""){
			return [
				"ok" => false,
			];
		}

		API_event_resurrectSlot($slotId);
		ignore_user_abort(false);
		flush();
		ob_flush();

		for($sec = 0; $sec < (API_POLL_LIFETIME*API_POLL_SPEED); $sec++){
			$res = $wpdb->get_results($wpdb->prepare(
				"SELECT data, name FROM events WHERE slotid = %s",
				$slotId
			));
			if((int)($sec/API_POLL_SPEED) % 2 == 0){
				echo "\n\r";
				flush();
				ob_flush();
				if(connection_aborted())
					break;
			}

			API_event_resurrectSlot($slotId);
			if(count($res) != 0){
				$wpdb->query($wpdb->prepare(
					"DELETE FROM events WHERE slotid = %s",
					$slotId
				));
				return [
					"ok" => true,
					"events" => $res
				];
			}
			usleep(1000000/API_POLL_SPEED);
		}
		return [
			"ok" => true,
			"events" => []
		];
	}catch(Exception $e){
		return [
			"ok" => false
		];
	}
}
?>