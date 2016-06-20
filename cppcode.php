<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";

session_start();

if ( !$_SESSION["ATM_COLLECTION"] ) return( header( "Location: index.php" ) );

include_once "./navigation.php";

if ( $sm->hash() !== $_SESSION['HASH'] ) { 
  file_put_contents( "machines/". session_id(). "/new.atml", $sm->as_xml() );
  $r = shell_exec( "./scripts/update.sh machines ". session_id() );
  $_SESSION['HASH'] = $sm->hash(); 
}

echo "<pre><code class='cpp'>\n";

echo htmlentities( file_get_contents( "machines/". session_id(). "/work/Machine.cpp" )) ;

echo "</code>\n</pre>\n";

include_once "./html/footer.html";

