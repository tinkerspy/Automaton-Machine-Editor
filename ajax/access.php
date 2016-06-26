<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$sm = $coll->first();

$sm->access( $_GET['event'], $_GET['mode'] );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();

?>

