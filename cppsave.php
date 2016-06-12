<?php


include_once "./lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );
$sm = $coll->first();

header( "Content-Type: application/octet-stream" );
header('Content-Disposition: attachment; filename="'.$sm->name().'.cpp"');

$xml = $sm->as_xml();
file_put_contents( "machines/". session_id(). "/new.atml", $xml );

if ( $sm->hash() !== $_SESSION['CHECKSUM'] ) { 
  $r = shell_exec( "./update.sh machines ". session_id() );
  $_SESSION['CHECKSUM'] = $sm->hash(); 
}

echo preg_replace( '/\n/s', "\r\n", file_get_contents( "machines/". session_id(). "/work/Machine.cpp" ) );

