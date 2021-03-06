<?php
/**
* Simple proxy for enabling public user pages for all IITD EE students.
* Forward requests to actual user page at IITD internal-web.
*
* See : http://www.cc.iitd.ernet.in/CSC/index.php?option=com_content&view=article&id=107&Itemid=146
*
* @author Abhishek Kedia (kedia.abhishek10 [at] gmail.com)
*/

require __DIR__."/vendor/autoload.php";

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

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function get_upstream_response($userId, $userPath) {
	$url = "http://privateweb.iitd.ac.in/~".$userId."/ees_home".$userPath;
	$method = $_SERVER["REQUEST_METHOD"];
	$request_headers = getallheaders();
	$request_headers["Connection"] = "close"; //keep-alive connections take too long (~ 5s) to respond. Also see - https://github.com/guzzle/guzzle/issues/1348
	$request_headers["X-Forwarded-For"] = get_client_ip(); //Send client IP upstream

	$client = new GuzzleHttp\Client();
	return $client->request($method, $url, [
		"headers" => $request_headers,
		"body" => fopen("php://input", "r"),
		"http_errors" => false,
		"stream" => true
	]);
}

function set_headers($headers) {
	unset($headers["Connection"]); //use default value (supplied by incoming request) for connection header
	foreach ($headers as $name => $values) {
		header($name . ': ' . implode(', ', $values));
	}
}

function main() {
	$request_path = get_request_path();
	$userId = $request_path["userId"];
	$userPath = $request_path["userPath"];
	
	$response = get_upstream_response($userId, $userPath);
	http_response_code($response->getStatusCode());
	set_headers($response->getHeaders());

	// echo $response->getBody();
	// Stream response instead
	$bodyStream = $response->getBody();
	while (!$bodyStream->eof()) {
		echo $bodyStream->read(1024);
	}
}

main();
?>
