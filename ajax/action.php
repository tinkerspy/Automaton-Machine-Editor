<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$coll->first()->action( $_GET['state'], $_GET['action'], $_GET['value'] );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();

?>

