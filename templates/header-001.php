<?php

include_once "lib/libatm.php";

if ( getenv( 'ATML' ) ) {
  $coll = new ATM_Collection( getenv( "ATML" ) );
  $sm = $coll->first();
}

?>
#pragma once

#include <Automaton.h>

class <?php echo $sm->name() ?>: public Machine {

 public:
  enum { <?php echo implode( ", ", $sm->states() ); ?> }; // STATES
  enum { <?php echo implode( ", ", $sm->events() ); ?> }; // EVENTS
  <?php echo $sm->name() ?>( void ) : Machine() {};
  <?php echo $sm->name() ?>& begin( void );
  <?php echo $sm->name() ?>& trace( Stream & stream );
  <?php echo $sm->name() ?>& trigger( int event );
  int state( void );
<?php
  foreach ( $sm->get_connectors() as $key => $conn ) {
    $fname = 'on'. ucfirst( strtolower( $key ) );
    printf( "  %s& %s( Machine& machine, int event = 0 );\n", $sm->name(), $fname ); 
    printf( "  %s& %s( atm_cb_push_t callback, int idx = 0 );\n", $sm->name(), $fname );
    if ( $conn['slots'] > 1 && $conn['autostore'] == 0 ) {
      printf( "  %s& %s( int sub, Machine& machine, int event = 0 );\n", $sm->name(), $fname );
      printf( "  %s& %s( int sub, atm_cb_push_t callback, int idx = 0 );\n", $sm->name(), $fname );
    }
  }
?>

 private:
  enum { <?php echo implode( ", ", $sm->actions() ); ?> }; // ACTIONS
<?php if ( $sm->get_connectors() ) {
  $r = Array();
  $cnt = 0;
  $subcnt = 0;
  foreach ( $sm->get_connectors() as $key => $conn ) {
    if ( $conn['dir'] == 'PUSH' ) {
      if ( $subcnt > $cnt ) {
        $r[] = "ON_$key = $subcnt";
        $cnt = $subcnt;
      } else {
        $r[] = "ON_$key";
      }
      for ( $i = 1; $i < $conn['slots']; $i++ ) {
        $subcnt++;
      }
      $subcnt++;
      $cnt++;
    }
  }
  printf( "  enum { %s, CONN_MAX ". ( $subcnt > $cnt ? "= $subcnt " : "" ) ."}; // CONNECTORS\n", implode( ", ", $r ) );
  printf( "  atm_connector connectors[CONN_MAX];\n" );
} 
?>
  int event( int id ); 
  void action( int id ); 

};

