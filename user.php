<?php
/**
* Simple proxy for enabling public user pages for all IITD EE students.
* Forward requests to actual user page at IITD internal-web.
*
* See : http://www.cc.iitd.ernet.in/CSC/index.php?option=com_content&view=article&id=107&Itemid=146
*
* @author Abhishek Kedia (kedia.abhishek10 [at] gmail.com)
*/

require __DIR__.'/httpful.phar';

function get_path_prefix() {
	$script_name = basename(__FILE__, ".php");
	$script_path = $_SERVER["SCRIPT_NAME"];
	$prefix_regex = "/^(.*)\/".$script_name."\.php$/";

	$is_matched = preg_match($prefix_regex, $script_path, $matches);
	if (!$is_matched) {
		// http_response_code(500);
		header("HTTP/1.0 500 Internal Server Error");
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
		// http_response_code(400);
		header("HTTP/1.0 400 Bad Request");
		die("Invalid URL format");
	}

	return array(
		"serverBase" => $matches[1],
		"userId" => $matches[2],
		"userPath" => $matches[3]
	);
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
	unset($request_headers["Host"]);
	$request_headers["Connection"] = "close"; //keep-alive connections take too long (~ 5s) to respond. Also see - https://github.com/guzzle/guzzle/issues/1348
	$request_headers["X-Forwarded-For"] = get_client_ip(); //Send client IP upstream

	$response = \Httpful\Request::init($method)
					->uri($url)
					->body(file_get_contents('php://input'))
					->addHeaders($request_headers)
					->followRedirects(true)
					->send();
	return $response;
}

function set_reponse_code($response) {
	$headers = $response->raw_headers;
	$end = strpos($headers, "\r\n");
	if ($end === false)
		$end = strlen($headers);
	header(substr($headers, 0, $end));
}

function set_headers($headers) {
	unset($headers["connection"]); //use default value (supplied by incoming request) for connection header
	foreach ($headers as $name => $value) {
		header($name.": ".$value);
	}
}

function main() {
	$request_path = get_request_path();
	$userId = $request_path["userId"];
	$userPath = $request_path["userPath"];
	
	$response = get_upstream_response($userId, $userPath);
	// http_response_code($response->code);
	set_reponse_code($response);
	set_headers($response->headers->toArray());

	echo $response->raw_body;
}

main();
?>