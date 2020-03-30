<?php
header('Content-Type: application/json; charset=utf-8');

/*
file_put_contents(__DIR__ . '/../data/add.txt', print_r($_POST, true), FILE_APPEND );
Array
(
    [group] => tGroup
    [token] => TToken
    [client] => TName
    [data] => [{"begin":1585075742,"end":1585076102,"name":"Test","category":"Huii"}, ...]
)
*/
?>
["ok"]