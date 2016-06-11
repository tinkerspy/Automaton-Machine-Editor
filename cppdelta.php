<?php
include_once "./header.php";

include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

include_once "./navigation.php";

echo "<pre><code class='cpp'>\n";

echo "  /* State Transition Table */\n";
echo htmlentities( $sm->as_table() ), "\n";

echo "  /* Enum definitions for ". $sm->name(). ".h file public section*/\n";
echo "  enum { ",implode( ', ', $sm->states() ), " }; // STATES\n";
echo "  enum { ",implode( ', ', $sm->events() ), " }; // EVENTS\n\n";
echo "  /* Enum definitions for ". $sm->name(). ".h file private section*/\n";
echo "  enum { ",implode( ', ', $sm->actions() ), " }; // ACTIONS\n\n\n";

echo "  /* Machine::setTrace() symbol lookup table */\n";
echo '  "', $sm->as_symbols(), '"', "\n\n";

?>
/*
Automaton::ATML::begin - State Machine Definition Language

<?php echo htmlentities( $sm->as_xml() ) ?>

Automaton::ATML::end
*/
<?php
echo "</code></pre>\n";
include_once "footer.php";
?>
