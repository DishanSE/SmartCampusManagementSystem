<?php

DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD','');
DEFINE('DB_HOST','localhost:3307');
DEFINE('DB_NAME','event');

$dbc = @mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die (mysqli_connect_error().'Cannot connect to Database ');

?>