<?php
include_once "./header.php";

include_once "./lib/libatm.php";

session_start();

include_once "./navigation.php";

if ( $sm->hash() !== $_SESSION['HASH'] ) { 
  file_put_contents( "machines/". session_id(). "/new.atml", $sm->as_xml() );
  $r = shell_exec( "./update.sh machines ". session_id() );
  $_SESSION['HASH'] = $sm->hash(); 
}

echo "<pre><code class='cpp'>\n";

echo htmlentities( file_get_contents( "machines/". session_id(). "/work/Machine.cpp" )) ;

echo "</code>\n</pre>\n";

include_once "footer.php";

