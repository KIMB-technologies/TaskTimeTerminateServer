<?php
header('Content-Type: application/json; charset=utf-8');

/*
file_put_contents(__DIR__ . '/../data/list.txt', print_r($_POST, true), FILE_APPEND );
Array
(
    [group] => tGroup
    [token] => TToken
    [client] => TName
    [data] => []
)
*/

echo json_encode(array(
	array(
		'file' => '2020-03-12.json',
		'timestamp' => strtotime('2020-03-12'),
		'device' => 'Test'
	),
	array(
		'file' => '2020-03-22.json',
		'timestamp' => strtotime('2020-03-22'),
		'device' => 'Test'
	)
));
?>