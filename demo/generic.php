<?php

function main() {
	return [
		"headers" => getallheaders(),
		"body" => file_get_contents('php://input'),
		"_SERVER" => $_SERVER
	];
}

header('Content-type:application/json;charset=utf-8');
echo json_encode(main(), JSON_PRETTY_PRINT);
?>