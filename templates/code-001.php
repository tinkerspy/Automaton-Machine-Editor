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

/* Add C++ code for each internally handled event (input) 
 * The code must return 1 to trigger the event
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
 * Control how your machine processes triggers
 */

<?php echo $sm->name() ?>& <?php echo $sm->name() ?>::trigger( int event ) {
  Machine::trigger( event );
  return *this;
}

/* Optionally override the default state() method
 * Control what the machine returns when another process requests its state
 */

int <?php echo $sm->name() ?>::state( void ) {
  return Machine::state();
}

/* Nothing customizable below this line                          
 ************************************************************************************************
*/

<?php
  foreach ( $sm->get_connectors() as $key => $conn ) {
    if ( $conn['dir'] == 'PUSH' ) {
      $fname = 'on'. ucfirst( strtolower( $key ) );

      printf( "/* %s() push connector variants ( slots %d, autostore %d, broadcast %d )\n *\n", 
        $fname, $conn['slots'], $conn['autostore'], $conn['broadcast']  );
      printf( " * Usage in action() handler: push( connectors, ON_%s, %s, v, up );\n", 
        $key, ( $conn['slots'] > 1 && $conn['broadcast'] == 0 ) ? 'sub' : 0 );
      printf( " */\n\n" );

      printf( "%s& %s::%s( Machine& machine, int event ) {", $sm->name(), $sm->name(), $fname ); // connectors, id, index, slots, fill, broadcast
      printf( " onPush( connectors, ON_%s, %d, %d, 1, %d, machine, event );", $key, ( $conn['autostore'] > 0 ? -1 : 0 ), $conn['slots'], $conn['broadcast'] );
      printf( " return *this; " );
      printf( "}\n" );

      printf( "%s& %s::%s( atm_cb_push_t callback, int idx ) {", $sm->name(), $sm->name(), $fname );
      printf( " onPush( connectors, ON_%s, %d, %d, 1, %d, callback, idx );", $key, ( $conn['autostore'] > 0 ? -1 : 0 ), $conn['slots'], $conn['broadcast'] );
      printf( " return *this; " );
      printf( "}\n" );

      if ( $conn['slots'] > 1 && $conn['autostore'] == 0 ) {
        printf( "%s& %s::%s( int sub, Machine& machine, int event ) {", $sm->name(), $sm->name(), $fname );
        printf( " onPush( connectors, ON_%s, sub, %d, 0, %d, machine, event );", $key, $conn['slots'], $conn['broadcast'] );
        printf( " return *this; " );
        printf( "}\n" );
  
        printf( "%s& %s::%s( int sub, atm_cb_push_t callback, int idx ) {", $sm->name(), $sm->name(), $fname );
        printf( " onPush( connectors, ON_%s, sub, %d, 0, %d, callback, idx );", $key, $conn['slots'], $conn['broadcast'] );
        printf( " return *this; " );
        printf( "}\n" );
      }
      echo "\n";
    }
  }
?>
/* State trace method
 * Sets the symbol table and the default logging method for serial monitoring
 */

<?php echo $sm->name() ?>& <?php echo $sm->name() ?>::trace( Stream & stream ) {
  Machine::setTrace( &stream, atm_serial_debug::trace,
    "<?php echo $sm->as_symbols() ?>" );
  return *this;
}


