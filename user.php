<?php
// Forward requests to actual user page at IITD internal-web.

function get_path_prefix() {
	$script_name = basename(__FILE__, ".php");
	$script_path = $_SERVER["SCRIPT_NAME"];
	$prefix_regex = "/^(.*)\/".$script_name."\.php$/";

	$is_matched = preg_match($prefix_regex, $script_path, $matches);
	if (!$is_matched) {
		http_response_code(500);
		die("Cannot find path prefix");
	}
	return $matches[1];
}

function get_request_path() {
	$PREFIX = get_path_prefix();
	// $PATH_REGEX = "^(\/~ee1130431\/(?:user.php\/|u\/|~))(?:((?:ee|EE)\w\d{6})((?:\/?|\/.*)))?$";
	$PATH_REGEX = "/^(".preg_quote($PREFIX, "/")."\/(?:".basename(__FILE__, ".php").".php\/|u\/|~))((?:ee|EE)\w\d{6})((?:\/?|\/.*))$/";

	$uri = $_SERVER["REQUEST_URI"];
	$is_matched = preg_match($PATH_REGEX, $uri, $matches);
	if (!$is_matched) {
		http_response_code(400);
		die("Invalid URL format");
	}

	return [
		"serverBase" => $matches[1],
		"userId" => $matches[2],
		"userPath" => $matches[3]
	];
}

function main() {
	$request_path = get_request_path();
	echo json_encode($request_path);
}

header('Content-type:application/json;charset=utf-8');
main();
?>