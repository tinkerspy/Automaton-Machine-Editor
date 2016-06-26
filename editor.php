<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

$error_msg = '';
if ( !$_SESSION["ATM_COLLECTION"] ) return( header( "Location: index.php" ) );

// Idee: rename acties bewaren en dan direct op de source code uitvoeren alvorens te mergen...

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$sm = $coll->first();

if ( $_POST ) {
  if ( $_POST['cmd'] == 'add_state' && $_POST['add_text'] ) {
    foreach ( preg_split( '/\s+/', $_POST['add_text'] ) as $p ){
      if ( $label = $coll->reserved( $p ) ) {
        $error_msg = "$label is a reserved word";
      } else {
        $sm->add_state( $p );
      }
    }
  }
  if ( $_POST['cmd'] == 'add_event' && $_POST['add_text'] ) {
    foreach ( array_reverse( preg_split( '/\s+/', $_POST['add_text'] ) ) as $p ){
      $sm->add_event( $p );
    }
  }
  if ( $_POST['cmd'] == 'rename' && $_POST['add_text'] && $_POST['state_event'] ) $sm->rename( $_POST['state_event'], $_POST['add_text'] );
  if ( $_POST['cmd'] == 'delete' && $_POST['state_event'] ) $sm->delete( $_POST['state_event'] );
  if ( $_POST['cmd'] == 'up_left' && $_POST['state_event'] ) $sm->move( $_POST['state_event'], ATM_UP_LEFT );
  if ( $_POST['cmd'] == 'down_right' && $_POST['state_event'] ) $sm->move( $_POST['state_event'], ATM_DOWN_RIGHT );
  if ( $_POST['cmd'] == 'clear' ) $sm->clear();
}

include_once "./navigation.php";

if ( $error_msg ) {
?>
  <div class="alert alert-danger">
   <strong>Warning</strong> <?php echo $error_msg; ?>
  </div>
<?php
}

$col_events = Array();
foreach ( $sm->events() as $event ) {
  $col_events[] = $event == 'ELSE' ? $event : 
    sprintf( "<input type='radio' name='state_event' id='radio_%s' value='%s' %s> <label for='radio_%s'>%s</label>",
               $event, $event, ( $event == $_POST['state_event'] ? "checked='checked'" : '' ), $event, $event );
}

$col_labels = array_merge( Array( 'Sleep', 'ENTER', 'LOOP', 'EXIT' ), $col_events );

$col_events2 = Array();
foreach ( $sm->events() as $event ) {
  $col_events2[] = $event == 'ELSE' ? $event : 
    sprintf( "<div class='radio'> <label><input type='radio' name='state_event' id='radio_%s' value='%s' %s><b>%s</b></label></div>",
               $event, $event, ( $event == $_POST['state_event'] ? "checked='checked'" : '' ), preg_replace( '/^EVT_/', '', $event ));
}

$col_labels2 = array_merge( Array( 'Sleep', 'ENTER', 'LOOP', 'EXIT' ), $col_events2 );  

echo "<form name='frm_editor' id='frm_editor' method='POST'>\n";
echo "<table class='table table-condensed'>\n";
echo "  <thead>\n";
echo "    <tr>\n"; 
echo "      <th class='info'></th>\n";
foreach ( $col_labels2 as $k => $v ) {
  printf( "    <th class='info text-center' style='vertical-align: middle;'>%s</th>\n", $v );
}
echo "    </tr>\n";
echo "  </thead>\n";
echo "  <tbody>\n";

foreach ( $sm->table_dump() as $state_label => $state_row ) {
printf( "  <tr>\n    <th class='info'><input type=radio name='state_event' id='radio_%s' value='%s' %s>\n      <label for='radio_%s'>%s</label>\n    </th>\n",
$state_label, $state_label, ( $state_label == $_POST['state_event'] ? "checked='checked'" : '' ), $state_label, $state_label );
  for ( $i = 0; $i < 4; $i++ ) {
    $class = $i > 0 ? 'success' : 'danger';
    printf( "    <td class='text-center %s'><label class='checkbox-inline'>".
             "<input type='checkbox' class='action_check' name='%s' %s></label></td>\n", 
             $class, "$state_label:". ($i + 1), $state_row[$i] ? 'checked' : '' );  
  }
  foreach ( $sm->events() as $idx => $event ) {
    $class = $event == 'ELSE' ? 'danger' : 'warning';
    printf( "  <td class='$class text-center'>%s  </td>\n", selectbox( "${state_label}:${event}", 
             array_merge( Array( "-" ), $sm->states() ), 
             $state_row[$idx + 4] ? $state_row[$idx + 4] : '-', 1, "class='link_state'" ) );
  }
  echo "  </tr>\n";
} 
echo "  <tr class='info'>\n";
echo "    <td></td><td></td><td></td><td></td><th class='text-center'>Event:</th>\n";
$access_labels = Array();
foreach( $sm->access_labels as $key => $label ) {
  $access_label[$key] = ucfirst( strtolower( $label ) );
}
foreach ( $sm->events() as $idx => $event ) {
  if ( $event != 'ELSE' ) {
    printf( "    <td class='text-center'>%s</td>\n", 
      selectbox( "${event}", $access_label, $sm->access( $event ), 0, "class='event_access'" ) );
  }
}
echo "    <td></td>\n";
echo "  </tr>\n";

echo "  </tbody>\n";
echo "</table>\n";

?>
 <br>
 <input type="text" class="form-control" id='add_text' name='add_text'>
 <br>
 <input type="hidden" id='cmd' name='cmd' value=''>
 <div class="btn-group">
  <button type="button" class="btn btn-primary btn-editor" name='add_state'>
    <span class='glyphicon glyphicon-ok'></span>
    Add State
  </button>
  <button type="button" class="btn btn-primary btn-editor" name='add_event'>
    <span class='glyphicon glyphicon-flash'></span>
    Add Event
  </button>
</div>
 <div class="btn-group">
  <button type="button" class="btn btn-primary btn-editor" name='rename'>
    <span class='glyphicon glyphicon-text-size'></span>
    Rename
  </button>
  <button type="button" class="btn btn-primary btn-editor" name='delete'>
    <span class='glyphicon glyphicon-remove'></span>
    Delete
  </button>
  <button type="button" class="btn btn-primary btn-editor" name='clear'>
    <span class='glyphicon glyphicon-fire'></span>
    Clear all
  </button>
</div>
 <div class="btn-group pull-right">
  <button type="button" class="btn btn-primary btn-editor" name='up_left'>
    <span class='glyphicon glyphicon-arrow-up btn-editor' id='icn_upleft'></span>
    <span class='glyphicon glyphicon-arrow-left btn-editor' id='icn_upleft'></span>
  </button>
  <button type="button" class="btn btn-primary btn-editor" name='down_right'>
    <span class='glyphicon glyphicon-arrow-right btn-editor' id='icn_downright'></span>
    <span class='glyphicon glyphicon-arrow-down btn-editor' id='icn_downright'></span>
  </button>
</div>
</form>
<br>
<?php

if ( $coll->changed() ) {
  $_SESSION['ATM_COLLECTION'] = $coll->as_xml();
}

include_once "./html/footer.html";

?>
