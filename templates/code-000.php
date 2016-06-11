<?php

include_once "lib/libatm.php";

if ( getenv( 'ATML' ) ) {
  $coll = new ATM_Collection( getenv( "ATML" ) );
  $sm = $coll->first();
}

?>
#include "<?php echo $sm->name() ?>.h"

/* Add optional parameters for the state machine to begin()
 * Add extra initialization code
 */

<?php echo $sm->name() ?>& <?php echo $sm->name() ?>::begin() {
  // clang-format off
<?php echo  $sm->as_table() ?>
  // clang-format on
  Machine::begin( state_table, ELSE );
  return *this;          
}

/* Add C++ code for each event (input)
 * The code must return 1 if the event should be triggered
 */

int <?php echo $sm->name() ?>::event( int id ) {
  switch ( id ) {
<?php
  foreach ( $sm->events() as $event ) {
    if ( $event != 'ELSE' ) {
      printf( "    case %s:\n      return 0;\n", $event );
    }
  }
?>
  }
  return 0;
}

/* Add C++ code for each action
 * This generates the 'output' for the state machine
 */

void <?php echo $sm->name() ?>::action( int id ) {
  switch ( id ) {
<?php
  foreach ( $sm->actions() as $action ) {
    printf( "    case %s:\n      return;\n", $action );
  }
?>
  }
}

/* Optionally override the default trigger() method
 * Control what triggers your machine can and cannot process
 */

<?php echo $sm->name() ?>& <?php echo $sm->name() ?>::trigger( int event ) {
  Machine::trigger( event );
  return *this;
}

/* Optionally override the default state() method
 * Control what the machine returns when another process requests its state()
 */

int <?php echo $sm->name() ?>::state( void ) {
  return Machine::state();
}

/* State trace method
 * Sets the symbol table and the default logging method for serial monitoring
 */

<?php echo $sm->name() ?>& <?php echo $sm->name() ?>::trace( Stream & stream ) {
  Machine::setTrace( &stream, atm_serial_debug::trace,
    "<?php echo $sm->as_symbols() ?>" );
  return *this;
}


