<?php
define("API_MANAGER_UID_ADDITION", "jkasdhs(*^ASAD7eg483hd23p32(*&^%$##$%^&2oai");


function API_manager_getUID($login, $pass){
	return sha1($pass . $login . API_MANAGER_UID_ADDITION);
}

function API_manager_testUID($managerId, $uid){
	return $uid === API_manager_getUID(get_field('login', $managerId), get_field('password', $managerId));
}

function API_manager_takeManager(){
	global $wpdb;
	$uid = $wpdb->get_var(
"SELECT manageruid 
	FROM active_managers 
	ORDER BY clientscount ASC LIMIT 1");

	if($uid != NULL){
		$wpdb->query($wpdb->prepare(
"UPDATE active_managers SET clientscount = clientscount+1 WHERE manageruid = %s",
			$uid
		));
	}
	return $uid;
}

function API_manager_cleanManagers(){
	global $wpdb;
	$wpdb->query(
"INSERT INTO events (id, slotid, name, data)
SELECT NULL, ao.slotid, 'managerShouldDisconnect', '\"\"'
FROM 
	active_orders ao 
	INNER JOIN active_managers am 
		ON ao.manageruid = am.manageruid
	LEFT OUTER JOIN listeners li
		ON li.channel = am.manageruid
WHERE li.channel IS NULL"
	);
	$wpdb->query(
"DELETE am FROM 
	active_managers am
	LEFT OUTER JOIN listeners li
		ON am.manageruid = li.channel
	WHERE li.channel IS NULL"
	);
}

function API_open_manager_getManagerByUID(){
	$managerUID = API_arg("managerUID");
	$managerId = API_arg("managerId");

	if(API_manager_testUID($managerId, $managerUID)){
		return [
			"ok" => true,
			"name" => get_the_title($managerId)
		];
	}else{
		return [
			"ok" => false
		];
	}
}

function API_open_manager_getManagerByAccesses(){
	$pass = API_arg("pass");
	$login = API_arg("login");

	$args = array(
		'post_type' => 'managers',
		'posts_per_page' => 1,
		'post_status' => 'publish',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'login',
				'value' => $login
			),
			array(
				'key' => 'password',
				'value' => $pass
			)
		)
	);
	$query = new WP_Query($args);

	if(count($query->posts) === 1){
		return [
			"ok" => true,
			"uid" => API_manager_getUID($login, $pass),
			"name" => get_the_title($query->posts[0]->ID),
			"id" => $query->posts[0]->ID
		];
	}else{
		return [
			"ok" => false
		];
	}
}

function API_open_manager_activate(){
	global $wpdb;

	$managerUID = API_arg("managerUID");
	$managerId = API_arg("managerId");

	if(API_manager_testUID($managerId, $managerUID)){
		
		$res = @$wpdb->insert('active_managers', [
			"manageruid" => $managerUID
		], ["%s", "%d"]);
		
		if($res){
			return [
				"ok" => true,
				"slotId" => API_event_createSlot($managerUID, 10)
			];
		}

		return [
			"ok" => false
		];
	}
	return [
		"ok" => false
	];
}

function API_open_manager_deactivate(){
	global $wpdb;
	
	$managerUID = API_arg("managerUID");
	$managerId = API_arg("managerId");

	if(API_manager_testUID($managerId, $managerUID)){
	
		$wpdb->query($wpdb->prepare(
"DELETE FROM active_managers WHERE manageruid = %s",
			$managerUID
		));

		return [
			"ok" => true
		];
	}
	return [
		"ok" => false
	];
}

// function API_manager_freeManager($uid){
// 	global $wpdb;
// 	if($uid){
// 		$wpdb->query($wpdb->prepare(
// "UPDATE active_managers SET free = 1 WHERE manageruid = %s",
// 			$uid
// 		));
// 	}
// }

?>