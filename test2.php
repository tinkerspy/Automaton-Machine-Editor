#!/usr/bin/php -n
<?php

include_once "./lib/libatm.php";

$xml = <<<HERE
<machines>
  <machine name="Atm_solenoids">
    <states>
      <IDLE index="0" sleep="1" on_enter="ENT_IDLE">
        <EVT_START>LEAD</EVT_START>
      </IDLE>
      <LEAD index="1" on_enter="ENT_LEAD">
        <EVT_GAPTIMER>MAIN</EVT_GAPTIMER>
      </LEAD>
      <MAIN index="2" on_enter="ENT_MAIN">
        <EVT_TIMER>TRAIL</EVT_TIMER>
      </MAIN>
      <TRAIL index="3" on_enter="ENT_TRAIL">
        <EVT_GAPTIMER>DONE</EVT_GAPTIMER>
      </TRAIL>
      <DONE index="4" on_enter="ENT_DONE" on_loop="LP_DONE" on_exit="EXT_DONE">
        <ELSE>IDLE</ELSE>
      </DONE>
      <TST1 index="5">
      </TST1>
    </states>
    <events>
      <EVT_START index="0"/>
      <EVT_TIMER index="1"/>
      <EVT_GAPTIMER index="2"/>
    </events>
    <connectors>
      <ONCHANGE autostore="0" dir="PUSH" max="1" min="0"/>
      <ONFINISH autostore="0" dir="PUSH" max="0" min="0"/>
    </connectors>
    <methods>
    </methods>
  </machine>
</machines>
HERE;

$coll = new ATM_Collection();
$machine = $coll->machine( "Atm_solenoids" );

$machine->from_xml( $xml );

echo $machine->as_xml();


