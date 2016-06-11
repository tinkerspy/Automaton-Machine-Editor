<?php

$xml = '
#pragma once

#include <Automaton.h>

class Atm_player: public Machine {

 public:
  enum { IDLE, SOUND, QUIET, NEXT, REPEAT }; // STATES
  enum { EVT_START, EVT_TIMERS, EVT_TIMERQ, EVT_EOPAT, EVT_REPCNT, ELSE }; // EVENTS
  Atm_player( void ) : Machine() {};
  Atm_player& begin( void );
  Atm_player& trace( Stream & stream );

 private:
  enum { ENT_IDLE, ENT_SOUND, ENT_QUIET, ENT_NEXT, ENT_REPEAT }; // ACTIONS
  int event( int id ); 
  void action( int id ); 

};

/* 
Automaton::SMDL::begin - State Machine Definition Language

<?xml version="1.0" encoding="UTF-8"?>
<machines>
  <machine name="Atm_player">
    <states>
      <IDLE index="0" sleep="1" on_enter="ENT_IDLE">
        <EVT_START>SOUND</EVT_START>
      </IDLE>
      <SOUND index="1" on_enter="ENT_SOUND">
        <EVT_TIMERS>QUIET</EVT_TIMERS>
      </SOUND>
      <QUIET index="2" on_enter="ENT_QUIET">
        <EVT_TIMERQ>NEXT</EVT_TIMERQ>
      </QUIET>
      <NEXT index="3" on_enter="ENT_NEXT">
        <EVT_EOPAT>REPEAT</EVT_EOPAT>
      </NEXT>
      <REPEAT index="4" on_enter="ENT_REPEAT">
        <EVT_REPCNT>IDLE</EVT_REPCNT>
        <ELSE>SOUND</ELSE>
      </REPEAT>
    </states>
    <events>
      <EVT_START index="0"/>
      <EVT_TIMERS index="1"/>
      <EVT_TIMERQ index="2"/>
      <EVT_EOPAT index="3"/>
      <EVT_REPCNT index="4"/>
    </events>
    <connectors>
    </connectors>
    <methods>
    </methods>
  </machine>
</machines>

Automaton::SMDL::end 
*/
';

    $xml = preg_replace( '/^.*Automaton::SMDL::begin.*?[\n\r]+/s', '', $xml );
    $xml = preg_replace( '/Automaton::SMDL::end.*[\n\r]+/s', '', $xml );
#    $xml = preg_replace( '/^[\s\r\n]+/', '', $xml );


echo "[$xml]\n";

