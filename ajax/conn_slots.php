<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$sm = $coll->first();

echo $_GET['value'] * 1,"\n";

$sm->set_connector( $_GET['conn'], 'slots', $_GET['value'] * 1 );

$_SESSION["ATM_COLLECTION"] = $coll->as_xml();

?>

