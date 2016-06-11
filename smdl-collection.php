<?php
include_once "./lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );


if ( $_GET['machine'] ) {
  header('Content-type: text/xml');
  header("Content-Disposition: attachment; filename=\"$_GET[machine].smdl\"");
  echo preg_replace( '/[\n]/', "\r\n", $coll->machine( $_GET['machine'] )->as_xml() );
} else {
  header('Content-type: text/xml');
  header('Content-Disposition: attachment; filename="collection.smdl"');
  echo preg_replace( '/[\n]/', "\r\n", $coll->as_xml() );
}

