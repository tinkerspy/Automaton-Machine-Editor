<?php

include_once "libatm.php";

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

 private:
  enum { <?php echo implode( ", ", $sm->actions() ); ?> }; // ACTIONS
  int event( int id ); 
  void action( int id ); 

};

