<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$coll->first()->set_connector( $_GET['conn'], 'autostore', (int) $_GET['value'] );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();

?>

