<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";

session_start();

include_once "./navigation.php";

$sm = $coll->first();

echo "<pre><code class='cpp'>\n";
?>
#include &lt;Automaton.h&gt;
#include "<?php echo $sm->name() ?>.h"

// Basic Arduino sketch - instantiates the state machine and nothing else

<?php echo $sm->name() ?> <?php echo $sm->short() ?>;

void setup() {

  // Serial.begin( 9600 );
  // <?php echo $sm->short() ?>.trace( Serial );

  <?php echo $sm->short() ?>.begin() 

}

void loop() {
  automaton.run();
}

</code></pre>;
<?php include_once "./html/footer.html" ?>
