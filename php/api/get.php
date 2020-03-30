<?php
header('Content-Type: application/json; charset=utf-8');

/*
file_put_contents(__DIR__ . '/../data/get.txt', print_r($_POST, true), FILE_APPEND );
Array
(
    [group] => tGroup
    [token] => TToken
    [client] => TName
    [data] => {"file":"2020-03-12.json","device":"Test"}
)
*/

$query = json_decode($_POST['data'], true);

$data = array(
	'2020-03-12.json' => '[{
		"begin": 1585172769,
		"end": 1585173369,
		"name": "2020-03-12",
		"category": "Huii"
	  },
	  {
		"begin": 1585173613,
		"end": 1585174213,
		"name": "2020-03-12",
		"category": "Huii"
	  },
	  {
		"begin": 1585174217,
		"end": 1585174517,
		"name": "2020-03-12",
		"category": "Huii"
	  }]',
	'2020-03-22.json' => '[{
		"begin": 1585172769,
		"end": 1585173369,
		"name": "2020-03-22",
		"category": "Huii"
	  },
	  {
		"begin": 1585173613,
		"end": 1585174213,
		"name": "2020-03-22",
		"category": "Huii"
	  },
	  {
		"begin": 1585174217,
		"end": 1585174517,
		"name": "2020-03-22",
		"category": "Huii"
	  }]'
	);

if( isset( $data[$query['file']] ) ){
	echo $data[$query['file']];
}
?>