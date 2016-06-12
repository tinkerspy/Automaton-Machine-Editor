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

echo htmlentities( file_get_contents( "machines/". session_id(). "/work/Machine.h" ) );

?>
/* 
Automaton::ATML::begin - Automaton Markup Language

<?php echo htmlentities( $sm->as_xml() ) ?>

Automaton::ATML::end 
*/

</code></pre>
<?php include_once "footer.php" ?>
