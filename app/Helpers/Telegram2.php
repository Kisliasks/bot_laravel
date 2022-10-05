<?php 
 $update = json_decode(file_get_contents('php://input'));
 
file_put_contents(__DIR__.'/logs2.txt', print_r($update, 1), FILE_APPEND);


?>