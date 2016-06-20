<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

if ( !$_SESSION["ATM_COLLECTION"] ) return( header( "Location: index.php" ) );

include_once "./navigation.php";

$sm = $coll->first();

echo "<pre><code class='xml'>\n";
echo htmlentities( $sm->as_xml() ), "\n";
echo "</code>\n</pre>\n";

include_once "./html/footer.html";

