<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

if ( !$_SESSION["ATM_COLLECTION"] ) return( header( "Location: index.php" ) );

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

$sm = $coll->first();

if ( $_POST ) {
  if ( $_POST['cmd'] == 'add_connector' ) {
    foreach ( array_reverse( preg_split( '/\s+/', $_POST['add_text'] ) ) as $p ){
      if ( preg_match( '/\w/', $p ) ) $sm->add_connector( $p, 'PUSH', '1', '0', '0' );
    }
  }
  if ( $_POST['cmd'] == 'delete' &&  $_POST['conn_radio'] ) {
   $sm->del_connector( $_POST['conn_radio'] );
  }
  if ( $_POST['cmd'] == 'clear' ) {
    $sm->clear_connectors();
  }
}

include_once "./navigation.php";

?>
<form name="frm_editor" id="frm_editor" method='POST'>
<table class='table'>
  <thead>
    <tr class='info'>
      <th>Name</th>
      <th>Direction</th>
      <th>Slots</th>
      <th>Auto store</th>
      <th>Broadcast</th>
    </tr>
  </thead>
  <tbody>
<?php
  foreach ( $sm->get_connectors() as $conn_name => $conn_row ) {
    printf( "    <tr class='warning'>\n      <td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n<td>%s</td>\n    </tr>\n", 
      sprintf( "<input type='radio' name='conn_radio' id='radio_%s' value='%s'> <label for='radio_%s'>%s</label>",
        $conn_name, $conn_name, $conn_name, $conn_name ), 
      $conn_row['dir'], 
      selectbox( "${conn_name}", range( 1, 32 ), (int)$conn_row['slots'], 1, "class='sb_slots'" ),
      checkbox( "${conn_name}", (int)$conn_row['autostore'], "class='cb_autostore'" ),
      checkbox( "${conn_name}", (int)$conn_row['broadcast'], "class='cb_broadcast'" ) );
  }
?>
  </tbody>
</table>

<div>
 <br>
 <input type="text" class="form-control" id='add_text' name='add_text'>
 <br>
 <input type="hidden" id='cmd' name='cmd' value=''>
 <div class="btn-group">
  <button type="button" class="btn btn-primary btn-editor" name='add_connector'>
    <span class='glyphicon glyphicon-link'></span>
    Add Connector
  </button>
 </div>
 <div class="btn-group">
  <button type="button" class="btn btn-primary btn-editor" name='delete'>
    <span class='glyphicon glyphicon-remove'></span>
    Delete
  </button>
  <button type="button" class="btn btn-primary btn-editor" name='clear'>
    <span class='glyphicon glyphicon-fire'></span>
    Clear all connectors
  </button>
</div>
</form>
<br>
<br>
<?php

if ( $coll->changed() ) {
  $_SESSION['ATM_COLLECTION'] = $coll->as_xml();
}

include_once "./html/footer.html";

?>
