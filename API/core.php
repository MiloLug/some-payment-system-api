<?php
$API_locals = [];
function API_arg($name, $alt = ""){
	global $_locals;
	$_name = "API_argument_" . $name;
	return (isset($_POST[$_name]) ? $_POST[$_name]: (isset($_GET[$_name]) ? urldecode($_GET[$_name]) : (isset($API_locals[$name]) ? $API_locals[$name] : $alt)));
}


function API_addUrlParameters($url, $add){
	$url_parts = parse_url($url);
	$params = [];

	if (isset($url_parts['query'])) {
		parse_str($url_parts['query'], $params);
	}

	$params = array_merge($params, $add);
	
	$url_parts['query'] = http_build_query($params);

	return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];
}
?>