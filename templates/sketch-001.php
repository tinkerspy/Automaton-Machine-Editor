<?php

include_once "libatm.php";

if ( getenv( 'ATML' ) ) {
  $coll = new ATM_Collection( getenv( "ATML" ) );
  $sm = $coll->first();
}

?>
#include <Automaton.h>
#include "<?php echo $sm->name() ?>.h"

// Basic Arduino sketch - instantiates the state machine and nothing else

<?php echo $sm->name() ?> <?php echo $sm->short() ?>;

void setup() {

  // Serial.begin( 9600 );
  // <?php echo $sm->short() ?>.trace( Serial );

  <?php echo $sm->short() ?>.begin();

}

void loop() {
  automaton.run();
}

