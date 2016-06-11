<?php


include_once "./lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );
$sm = $coll->first();

header( "Content-Type: application/octet-stream" );
header('Content-Disposition: attachment; filename="'.$sm->name(). ( $_SESSION['HPPMODE'] ? '.hpp' : '.h' ). '"');

$xml = $sm->as_xml();
file_put_contents( "machines/". session_id(). "/new.atml", $xml );

if ( md5( $xml ) !== $_SESSION['CHECKSUM'] ) { 
  $r = shell_exec( "./update.sh machines ". session_id() );
  $_SESSION['CHECKSUM'] = md5( $xml ); 
}

$txt = file_get_contents( "machines/". session_id(). "/Template.h" );
echo preg_replace( '/\n/s', "\r\n", $txt );

?>
/*
Automaton::ATML::begin - Automaton Markup Language

<?php echo $sm->as_xml() ?>

Automaton::ATML::end
*/

