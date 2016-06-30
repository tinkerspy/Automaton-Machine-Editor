<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$coll->first()->access( $_GET['event'], $_GET['mode'] );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();

?>

