<?php

require_once 'includes/db_connection.php';
require_once 'includes/data_service.php';
require_once 'includes/layout.php';

$conn = getDbConnection();
if($conn){
    echo json_encode(["status"=>"success","message" => "front end connected"]);
} else{
    echo json_encode(["status" => "error","message"=>"connection failed"]);
}

?>
