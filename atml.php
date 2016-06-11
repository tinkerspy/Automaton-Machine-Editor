<?php
include_once "./header.php";

include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

include_once "./navigation.php";

$sm = $coll->first();

echo "<pre><code class='xml'>\n";
echo htmlentities( $sm->as_xml() ), "\n";
echo "</code>\n</pre>\n";

include_once "footer.php";
