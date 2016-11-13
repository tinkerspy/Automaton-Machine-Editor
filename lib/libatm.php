<?php

/* ATM_Collection & ATM_Machine
 *
 * Classes for manipulating (collections of) Automaton State Machine definitions
 * Input and output as .atml XML files.
 *
 */

define( "ATM_INDEX", "0" );
define( "ATM_SLEEP", "1" );
define( "ATM_ON_ENTER", "2" );
define( "ATM_ON_LOOP", "3" );
define( "ATM_ON_EXIT", "4" );
define( "ATM_EVT_OFFSET", "5" );
define( "ATM_UP_LEFT", "1" );
define( "ATM_DOWN_RIGHT", "0" );
define( "ATM_ACCESS_MIXED", "0" );
define( "ATM_ACCESS_PUBLIC", "1" );
define( "ATM_ACCESS_PRIVATE", "2" );

// Represents a collection of machine definitions ( XML => manipulate => XML )

class ATM_Collection {
  var $collection = Array();
  var $changed = false;
  var $reserved_states = Array( // Uppercase reserved words that may clash with state names
    'D0', 'D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7', 'D8', 'D9', 'D10', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17', 'D18', 'D19',
    'A0', 'A1', 'A2', 'A3', 'A4', 'A5',
    'BIN', 'OCT', 'DEC', 'HEX', 
    'ATM_SLEEP_FLAG', 'ATM_CYCLE_FLAG', 'ATM_USR1_FLAG', 'ATM_USR2_FLAG', 'ATM_USR3_FLAG', 'ATM_USR4_FLAG', 'ATM_USR_FLAGS', 'ATM_BROADCAST',
    'ATM_UP', 'ATM_DOWN', 'ATM_NOP', 'ATM_ON_SWITCH', 'ATM_ON_ENTER', 'ATM_ON_LOOP', 'ATM_ON_EXIT', 'ATM_TIMER_OFF', 'ATM_COUNTER_OFF',  
  );
  var $reserved_events = Array( // Uppercase reserved words that may clash with event names (custom methods)
    'BEGIN', 'EVENT', 'ACTION', 'TRACE', 'STATE', 'SLEEP', 'TRIGGER', 'CYCLE',
  );


  function __construct( $xml = null ) {
    
    if ( $xml ) $this->from_xml( $xml );
    return $this;
  }

  function find( $name ) {

    if ( isset( $name ) ) { 
      foreach ( $this->collection as $idx => $machine ) {
        if ( $machine->name == $name ) {
          return $idx;
        }
      }
    }
    return -1;   
  }

  function reserved_state( $label ) {

    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( $label && !in_array( $label, $this->reserved_states ) && !preg_match( '/^(\d|EVT_|ENT_|LP_|EXT_|ON_)/', $label ) ) {
      return null;
    } else {
      return $label;
    }
  }

  function reserved_event( $label ) {

    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( $label && !in_array( $label, $this->reserved_events ) && !preg_match( '/^(\d)/', $label ) ) {
      return null;
    } else {
      return $label;
    }
  }

  function machine( $name = null ) {

    $idx = $this->find( $name );
    if ( $idx != -1 ) {
        return $this->collection[$idx];
    }
    $this->changed = true;
    $machine = new ATM_Machine( $name );
    array_push( $this->collection, $machine );
    return $machine;
  }

  function first() {

    return array_shift( array_values( $this->collection ) );
  }

  function delete( $name ) {

    $idx = $this->find( $name );
    if ( $idx != -1 ) {
      unset( $this->collection[$idx] );
      $this->changed = true;
    }
    return $this;
  }

  function clear() {

    $this->collection = Array();
    return $this;
  }

  function from_xml( $xml ) {

    $idx = 0;
    do {
      $machine = new ATM_Machine();
      $machine->from_xml( $xml, $idx );
      if ( $machine->name ) {
        $this->collection[] = $machine;
      }
      $idx++;
    } while ( $machine->name );
    $this->changed = false;
    return $this;
  }

  function machines() {

    $r = Array();
    foreach ( $this->collection as $machine ) {
      $r[] = $machine->name;
    }
    return $r;
  }

  function as_xml() {

     $r = '';
     $r = sprintf( '<%sxml version="1.0" encoding="UTF-8"%s>', '?', '?' );
     $r .= "\n<machines>\n";
     foreach ( $this->collection as $machine ) {
       $r .= $machine->as_xml( false );
     }
     $r .= "</machines>\n";
     return $r;
  }

  function changed( $v = null) {
 
    if ( isset( $v ) ) {
      $this->changed = $v;
      return $this;
    }
    if ( $this->changed ) return true; 
    foreach ( $this->collection as $machine ) {
      if ( $machine->changed() ) {
        return true;
      }
    }
    return false;
  } 

}


// Represents a single machine definition ( XML => manipulate => XML )

class ATM_Machine {
  var $event_labels = Array( 'ELSE' );
  var $event_access = Array(); // Mixed/Private/Public
  var $state_labels = Array( "STATE", "INDEX", "SLEEP", "ON_ENTER", "ON_LOOP", "ON_EXIT" );
  var $access_labels = Array( 'MIXED', 'PUBLIC', 'PRIVATE' );
  var $connectors = Array();
  var $states = Array();
  var $changed = false;
  var $name = '';

  function __construct( $name = '' ) {

    $this->name = $name;
    return $this;
  }

  function add_state() {

    $arg_list = func_get_args();
    $label = array_shift( $arg_list );
    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( $label && !isset($this->states[$label]) && !preg_match( '/^(\d|EVT_|ENT_|LP_|EXT_|ON_)/', $label ) ) {
      $this->changed = true;
      $state_row = Array( count( $this->states ), 0, 0, 0, 0 );
      foreach ( $this->event_labels as $v ) {
        array_push( $state_row, "" );
      }
      $this->states[$label] = $state_row;
      foreach ( $arg_list as $arg ) {
        $this->action( $label, $arg, 1 );
      }
    }
    uasort( $this->states, array('ATM_Machine','machinecmp') );
    return $this;
  }

  function add_event( $label ) {

    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( $label && !preg_match( '/^EVT_.*/', $label ) ) $label = "EVT_". $label;
    if ( $label && !in_array( $label, $this->event_labels ) ) {
      $this->changed = true;
      array_unshift( $this->event_labels, $label );
      foreach ( $this->states as $k => $v ) {
        array_splice( $this->states[$k], ATM_EVT_OFFSET, 0, "" );
      }
    }
    return $this;
  }

  function add_connector( $name, $dir, $slots, $autostore, $broadcast ) {

    $name = strtoupper( preg_replace( '/[^a-z0-9]+/i', '', $name ) );
    $this->connectors[$name] = Array( 'dir' => strtoupper( $dir ), 'slots' => $slots * 1, 'autostore' => ( $autostore ? 1 : 0 ), 'broadcast' => ( $broadcast ? 1 : 0 ) );
    ksort( $this->connectors );
    $this->changed = true;
    return $this;
  }

  function del_connector( $name ) {

    $name = strtoupper( preg_replace( '/[^a-z0-9]+/i', '', $name ) );
    unset( $this->connectors[$name] );
    ksort( $this->connectors );
    $this->changed = true;
    return $this;
  }

  function set_connector( $name, $attr, $value ) {

    $attr = strtolower( $attr );
    $name = strtoupper( preg_replace( '/[^a-z0-9]+/i', '', $name ) );
    if ( $this->connectors[$name] && isset( $this->connectors[$name][$attr] ) ) {
      $this->connectors[$name][$attr] = strtoupper( $value );
    }
    $this->changed = true;
    return $this;
  }

  function get_connectors() {

    return $this->connectors;
  }  

  function clear_connectors() {
  
    $this->connectors = Array();
    $this->changed = true;
  }

  function delete( $label ) { // Delete state or event (auto-detect)

    if ( preg_match( '/^EVT_/', $label ) ) {
      foreach ( $this->event_labels as $idx => $event ) {
        if ( $event == $label ) { // Found the event
          $this->changed = true;
          unset( $this->event_labels[$idx] );
          // Now remove column $idx from every state row (splice?)
          foreach ( $this->states as $k => $state ) {
            array_splice(  $this->states[$k], ATM_EVT_OFFSET + $idx, 1 ); 
          }
        }
      }
      $this->event_labels = array_values( $this->event_labels );
    } else {
      if ( $this->states[$label] ) {
        $this->changed = true;
        $my_index = $this->states[$label][ATM_INDEX];
        unset( $this->states[$label] ); 
        // Renumber indexes!
        foreach ( $this->states as $k => $v ) {
          if ( $v[ATM_INDEX] > $my_index ) $this->states[$k][ATM_INDEX]--;
        }
        foreach ( $this->states as $k => $v ) {
          for ( $e = 0; $e < count( $this->event_labels ); $e++ ) {
            if ( $v[ATM_EVT_OFFSET + $e] == $label ) $this->states[$k][ATM_EVT_OFFSET + $e] = ''; 
          }
        }
      }
    }
    uasort( $this->states, array('ATM_Machine','machinecmp') );
    return $this;
  }

  function access( $label, $v = null ) {
    
    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( $label && !preg_match( '/^EVT_.*/', $label ) ) $label = "EVT_". $label;
    if ( $label && in_array( $label, $this->event_labels ) ) {
      if ( isset( $v ) ) {
        $this->event_access[$label] = (int) $v;
      } else {
        return isset( $this->event_access[$label] ) ? $this->event_access[$label] : 0;
      }
    }
    return $this;
  }

  function clear() {
  
    $this->states = Array();
    $this->event_labels = Array( 'ELSE' );
    $this->connectors = Array();
    $this->changed = true;
    return $this;
  }

  function move( $label, $up ) { // Move state or event (auto-detect)

    if ( preg_match( '/^EVT_/', $label ) ) {
      foreach ( $this->event_labels as $idx => $event ) {
        if ( $event == $label ) { // Found the event
          if ( $up ) {
            $dst = $idx > 0 ? $idx - 1 : null; // Room to move to the left?
          } else {
            $dst = $idx < count( $this->event_labels ) - 2 ? $idx + 1 : null; // Room to move to the right?
          }
          if ( isset( $dst ) ) { 
            // Move event in labels
            $this->event_labels[$idx] = $this->event_labels[$dst];
            $this->event_labels[$dst] = $label;
            // Move event in state column ( $idx -> $dst )
            foreach ( $this->states as $key => $state ) {
              $this->states[$key][ATM_EVT_OFFSET + $idx] = $state[ATM_EVT_OFFSET + $dst];
              $this->states[$key][ATM_EVT_OFFSET + $dst] = $state[ATM_EVT_OFFSET + $idx];
            }
          }
        }
      }
    } else {
      if ( $this->states[$label] ) {
        $idx = $this->states[$label][ATM_INDEX];
        foreach ( $this->states as $key => $state ) {
          if ( $state[ATM_INDEX] == ( $up ? $idx - 1 : $idx + 1 ) ) {
            $this->states[$label][ATM_INDEX] = $state[ATM_INDEX];
            $this->states[$key][ATM_INDEX] = $idx; 
          }
        }
      }
    }
    uasort( $this->states, array('ATM_Machine','machinecmp') );
    $this->changed = true;
    return $this;
  }

  function rename( $label, $to ) { // Rename state or event (auto-detect)

    $to = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $to ) ) ); // Clean up $to label
    if ( preg_match( '/^EVT_/', $label ) ) {
      if ( !preg_match( '/^EVT_.*/', $to ) ) $to = "EVT_". $to; // EVT_ prefix
      if ( !in_array( $to, $this->event_labels ) ) { // $to not already taken?
        foreach ( $this->event_labels as $idx => $event ) {
          if ( $event == $label ) {
             $this->event_labels[$idx] = $to; 
          }
        }
      }
    } else {
      if ( !$this->states[$to] && !preg_match( '/^(\d|EVT_|ENT_|LP_|EXT_|ON_)/', $to ) ) { // Legal state name?
        foreach ( $this->states as $key => $state ) {
          if ( $key == $label ) {
            $this->states[$to] = array_values( $state );
            unset( $this->states[$label] );
          }
        }
        foreach ( $this->states as $key => $state ) {
          foreach ( $state as $k => $v ) {
            if ( $k >= ATM_EVT_OFFSET ) {
              if ( $v == $label ) $this->states[$key][$k] = $to;
            }
          }
        }
        uasort( $this->states, array('ATM_Machine','machinecmp') );
      }
    }
    $this->changed = true;
    return $this;
  }

  function name( $name = null ) { // Rename machine

    if ( isset( $name ) ) {
      $this->name = $name;
      $this->changed = true;
    }
    return $this->name;
  }

  function short() {

    return preg_replace( '/^Atm_/', '', $this->name() );
  }

  function print_states() {

    foreach ( $this->states as $k => $v ) {
      echo "print_states: $k => $v[0]\n";
    }
  }

  function states() { 

    return array_keys( $this->states );
  }

  function events() { 
 
    return $this->event_labels;
  }

  function actions() { 

    $r = Array();
    foreach ( $this->states as $k => $v ) {
      if ( $v[ATM_ON_ENTER] ) $r[] = "ENT_$k";
      if ( $v[ATM_ON_LOOP ] ) $r[] = "LP_$k";
      if ( $v[ATM_ON_EXIT ] ) $r[] = "EXT_$k";
    }
    return $r;
  }

  function link( $state_label, $event_label, $to_label ) {

    $event_idx = array_search( $event_label, $this->event_labels );
    if ( $event_idx !== FALSE && isset( $this->states[$state_label] ) ) {
      if ( isset( $this->states[$to_label] ) ) {
        $this->states[$state_label][$event_idx + ATM_EVT_OFFSET] = $to_label; 
      } else {
        $this->states[$state_label][$event_idx + ATM_EVT_OFFSET] = ''; 
      }
      $this->changed = true;
    }
    return $this;
  }

  function action( $label, $action, $v ) {

    $label = preg_replace( '/[^a-z0-9_]/i', '', strtoupper( trim( $label ) ) );
    if ( isset( $v ) && $this->states[$label] ) {
      $this->changed = true;
      $this->states[$label][$action] = $v ? 1 : 0;
      return $this;
    } else {
      return  $this->states[$label][$action];
    }
  }

  function attribs( array $attr ) {

    $r = Array();
    foreach( $attr as $k => $v ) {
      array_push( $r, "$k=\"$v\"" );
    }
    return implode( " ", $r );
  }

  function as_xml( $add_envelope = true ) {

    $r = '';
    if ( $add_envelope ) {
      $r = sprintf( '<%sxml version="1.0" encoding="UTF-8"%s>', '?', '?' );
      $r .= "\n<machines>\n";
    }
    $r .= "  <machine name=\"". $this->name. "\">\n";
    $r .= "    <states>\n";
    foreach( $this->states as $state_label => $state_row ) {
      $attr = Array( 'index' => $state_row[ATM_INDEX] );
      if ( $state_row[ATM_SLEEP   ] ) $attr['sleep'   ] = 1;
      if ( $state_row[ATM_ON_ENTER] ) $attr['on_enter'] = "ENT_$state_label";
      if ( $state_row[ATM_ON_LOOP ] ) $attr['on_loop' ] = "LP_$state_label";
      if ( $state_row[ATM_ON_EXIT ] ) $attr['on_exit' ] = "EXT_$state_label";
      
      $r .= sprintf( "      <%s %s>\n", $state_label, $this->attribs( $attr ) );
      for ( $e = 0; $e < count( $this->event_labels ); $e++ ) {
        if ( $state_row[$e + ATM_EVT_OFFSET] != "" ) { 
          $r .= sprintf( "        <%s>%s</%s>\n", $this->event_labels[$e], $state_row[$e + ATM_EVT_OFFSET], $this->event_labels[$e] );          
        } 
      }
      $r .= "      </$state_label>\n";
    }
    $r .= "    </states>\n";
    $r .= "    <events>\n";
    for ( $e = 0; $e < count( $this->event_labels ) - 1; $e++ ) {
      $attr = Array( 'access' => ( strtoupper( $this->access_labels[$this->access( $this->event_labels[$e] )] ) ) );
      $r .= sprintf( "      <%s index=\"%s\" %s/>\n", $this->event_labels[$e], $e, $this->attribs( $attr ) );
    }
    $r .= "    </events>\n";
    $r .= "    <connectors>\n";
    foreach ( $this->connectors as $conn_name => $conn_row ) {
      ksort( $conn_row );
      $r .= sprintf( "      <%s %s/>\n", $conn_name, $this->attribs( $conn_row ) );
    }
    $r .= "    </connectors>\n";
    $r .= "    <methods>\n";
    $r .= "    </methods>\n";
    $r .= "  </machine>\n";
    if ( $add_envelope ) {
      $r .= "</machines>\n";
    }
    return $r; 
  }

  function from_xml( $xml, $idx = 0 ) {

    $data = simplexml_load_string( preg_match( '/\<machines\>/', $xml ) ? $xml : "<machines>$xml</machines>" );
    if ($data === false) {
       echo "Failed loading XML: ";
       foreach(libxml_get_errors() as $error) {
         die( $error->message );
       }
    } else {
      // Extract the machine name
      $this->name = (string) $data->machine[$idx]['name'];
      if ( !$this->name ) return $this;
      // Extract the state names
      $r = Array();
      foreach ( $data->machine[$idx]->{'states'}->children() as $child ) {
        $r[$child->getName()] = (string) $child['index'];
      }
      asort( $r );
      // Add the states in order
      foreach ( array_keys( $r ) as $v ) {
        $this->add_state( $v );
      } 
      // Set the state actions (incl ATM_SLEEP)
      foreach ( $data->machine[$idx]->{'states'}->children() as $child ) {
        foreach ( $child->attributes() as $k => $v ) {
          switch ( $k ) {
            case 'sleep':
              $this->action( $child->getName(), ATM_SLEEP, $v );
              break;
            case 'on_enter':
              $this->action( $child->getName(), ATM_ON_ENTER, $v );
              break;
            case 'on_loop':
              $this->action( $child->getName(), ATM_ON_LOOP, $v );
              break;
            case 'on_exit':
              $this->action( $child->getName(), ATM_ON_EXIT, $v );
              break;
          }
        }
      }
      // Extract the event names
      $r = Array();
      foreach ( $data->machine[$idx]->{'events'}->children() as $child ) {
          $r[$child->getName()] = (string) $child['index'];
      } 
      arsort( $r );
      // Add the events in reverse order
      foreach ( array_keys( $r ) as $v ) {
        $this->add_event( $v );
      }
      // Extract the event access modes
      foreach ( $data->machine[$idx]->{'events'}->children() as $child ) {
        $this->access( $child->getName(), array_search( (string) $child['access'], $this->access_labels ) );  
      }
      // Now we can create the event -> state links
      foreach ( $data->machine[$idx]->{'states'}->children() as $child ) {
        foreach( $child as $grandchild ) {
          $this->link( $child->getName(), $grandchild->getName(), (string) $grandchild );
        }
      } 
      foreach ( $data->machine[$idx]->{'connectors'}->children() as $child ) {
        $this->add_connector( $child->getName(), $child['dir'], $child['slots'], (int)$child['autostore'], (int)$child['broadcast'] );
      }
    }
    $this->changed = false;
    return $this;
  }

  function as_table() {

    $cols = Array( 5, 8, 7, 7 );
    for ( $e = 0; $e < count( $this->event_labels ); $e++ ) {
      $cols[$e + 4] = strlen( $this->event_labels[$e] );
      foreach ( $this->states as $state => $v ) {
        if ( strlen( $state ) > $cols[0] ) $cols[0] =  strlen( $state );
        if ( $v[ATM_ON_ENTER] && strlen( $state ) + 4 > $cols[1] ) $cols[1] = strlen( $state ) + 4;
        if ( $v[ATM_ON_LOOP ] && strlen( $state ) + 3 > $cols[2] ) $cols[2] = strlen( $state ) + 3;
        if ( $v[ATM_ON_EXIT ] && strlen( $state ) + 4 > $cols[3] ) $cols[3] = strlen( $state ) + 4;
        if ( $v[ATM_SLEEP] && $cols[2] < 9 ) $cols[2] = 9;
        if ( strlen( $v[ATM_EVT_OFFSET + $e] ) > $cols[$e + 4] ) {
          $cols[$e + 4] = strlen( $v[ATM_EVT_OFFSET + $e] );
        }
      }
    }
    $format0 = "    /* %$cols[0]s    %$cols[1]s  %$cols[2]s  %$cols[3]s ";
    $format1 = "    /* %$cols[0]s */ %$cols[1]s, %$cols[2]s, %$cols[3]s,";
    $r = "  const static state_t state_table[] PROGMEM = {\n";
    for ( $e = 0; $e < count( $this->event_labels ); $e++ ) {
      $format0 .= " %". $cols[$e + 4]. "s ";
      $format1 .= " %". $cols[$e + 4]. "s,"; 
    }
    $a = array_merge( Array( "", $this->state_labels[3], $this->state_labels[4],  $this->state_labels[5] ), $this->event_labels );
    $r .= vsprintf( $format0. "*/\n", $a );
    foreach ( $this->states as $state => $row ) {
      $row[4] = $row[4] ? "EXT_$state" : '-1';
      $row[3] = $row[1] ? 'ATM_SLEEP' : ( $row[3] ? "LP_$state" : '-1' );
      $row[2] = $row[2] ? "ENT_$state" : '-1';
      $row[1] = $state;
      foreach ( $row as $k => $v ) {
        $row[$k] = $v == '' ? '-1' : $v;
      }
      unset( $row[0] );
      $r .= vsprintf( $format1. "\n", $row );
    }
    return "$r  };\n";
  }

  function machinecmp( $a, $b ) {

    return $a[0] - $b[0]; // strcmp( $a[0], $b[0] );
  }

  function table_dump() {

    $states = $this->states;
    foreach ( $states as $st => $state_row ) {
      unset( $states[$st][0] );
      $states[$st] = array_values( $states[$st] );
    }
    return $states;
  }

  function as_symbols() {

    return strtoupper( preg_replace( '/^ATM_/i', '', $this->name ) ). 
             '\0'. implode( '\0', $this->events() ). 
             '\0'. implode( '\0', $this->states() );
  }

  function dump( $element_width = 14 ) {

    $r = '';
    $labels = $this->state_labels;
    array_splice( $labels, count( $labels ), 0, $this->event_labels );
    foreach( $labels as $label ) {
      $r .= str_pad( $label, $element_width, " ", STR_PAD_LEFT );
    }
    $r .= "\n";
    foreach( $this->states as $state_label => $state_row ) {
	$r .= str_pad( $state_label, $element_width, " ", STR_PAD_LEFT );
        foreach ( $state_row as $entry ) {
		$r .= str_pad( $entry, $element_width, " ", STR_PAD_LEFT );
	}
	$r .= "\n";
    }
    return $r;
  }

  function changed() {
    
    return $this->changed;
  }

  function hash() {
    
    return md5( $this->as_xml() );
  }

}



