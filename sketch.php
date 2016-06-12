<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";

session_start();

include_once "./navigation.php";

$sm = $coll->first();

$name = $sm->name();

$short = preg_replace( '/^Atm_/', '', $name );

echo "<pre><code class='cpp'>\n";
?>
#include &lt;Automaton.h&gt;
#include "<?php echo $name ?>.h"

// Basic Arduino sketch - instantiates the state machine and nothing else

<?php echo $name ?> <?php echo $short ?>;

void setup() {

  // Serial.begin( 9600 );
  // <?php echo $short ?>.trace( Serial );

  <?php echo $short ?>.begin() 

}

void loop() {
  automaton.run();
}

</code></pre>;
<?php include_once "./html/footer.html" ?>
