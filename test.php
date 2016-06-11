#!/usr/bin/php -n
<?php

include_once "./lib/libatm.php";

$coll = new ATM_Collection();
$machine = $coll->machine( "Atm_solenoids" );

$machine
  ->add_state( 'IDLE', ATM_ON_ENTER, ATM_SLEEP )
  ->add_state( 'LEAD', ATM_ON_ENTER )
  ->add_state( 'MAIN', ATM_ON_ENTER )
  ->add_state( 'TRAIL', ATM_ON_ENTER )
  ->add_state( 'DONE', ATM_ON_ENTER, ATM_ON_EXIT, ATM_ON_LOOP )
  ->add_event( 'GAPTIMER' ) 
  ->add_event( 'TIMER' )
  ->add_event( 'START' )
  ->link(  'IDLE',    'EVT_START',  'LEAD' )
  ->link(  'LEAD', 'EVT_GAPTIMER',  'MAIN' )
  ->link(  'MAIN',    'EVT_TIMER', 'TRAIL' )
  ->link( 'TRAIL', 'EVT_GAPTIMER',  'DONE' )
  ->link(  'DONE',         'ELSE',  'IDLE' );

$machine->add_state( 'TST1', 'TST2' );

$machine->add_connector( 'finish', 'push', 'simple', 0, 0, 0 );
$machine->add_connector( 'change', 'push', 'boolean', 'false', 'true', 0 );

$machine->set_connector( 'onfinish', 'max', 7 );

echo $machine->as_xml();


