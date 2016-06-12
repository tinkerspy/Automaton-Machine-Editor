<?php

include_once "../lib/libatm.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );
$sm = $coll->first();

header( "Content-Type: application/octet-stream" );
header('Content-Disposition: attachment; filename="'.$sm->name(). ( $_SESSION['HPPMODE'] ? '.hpp' : '.h' ). '"');

if ( $sm->hash() !== $_SESSION['HASH'] ) { 
  file_put_contents( "../machines/". session_id(). "/new.atml", $sm->as_xml() );
  $r = shell_exec( "../scripts/update.sh ../machines ". session_id() );
  $_SESSION['HASH'] = $sm->hash(); 
}

echo preg_replace( '/\n/s', "\r\n", file_get_contents( "../machines/". session_id(). "/Template.h" ) );

?>
/*
Automaton::ATML::begin - Automaton Markup Language

<?php echo $sm->as_xml() ?>

Automaton::ATML::end
*/

