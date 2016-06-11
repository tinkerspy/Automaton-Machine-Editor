<?php

session_start();

include_once "./lib/libatm.php";

$coll = new ATM_Collection( $_SESSION['ATM_COLLECTION'] );

echo "<pre>", htmlentities( $coll->as_xml() ), "</pre>\n";


