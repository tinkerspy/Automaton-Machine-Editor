<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$sm = $coll->first();

$sm->set_connector( $_GET['conn'], 'broadcast', $_GET['value'] * 1 );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();
