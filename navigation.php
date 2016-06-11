<?php

if ( !is_dir( "machines" ) ) {
  die( "<br>FATAL: The 'machines' subdirectory does not exist<br>" );
}

if ( !is_writable( "machines" ) ) {
  die( "<br>FATAL: The 'machines' subdirectory is not writable<br>" );
}

if ( !is_object( $coll ) ) {
  $coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );
}
$sm = $coll->first();

?>
<nav class="navbar navbar-inverse">
  <div class="container-fluid"> 
    <div class="navbar-header">
      <a class="navbar-brand" href="#">
        <span class='glyphicon glyphicon-cog'></span> 
        Automaton
      </a>
    </div>
    <ul class="nav navbar-nav">
      <?php echo menu_item( 'index.php', 'File' ) ?> 
      <?php echo menu_item( 'editor.php', 'Editor', is_object( $sm ) ) ?> 
      <?php echo menu_item( 'connectors.php', 'Connectors', is_object( $sm ) ) ?> 
      <?php echo menu_item( 'cppheader.php', $_SESSION['HPPMODE'] ? '.hpp' : '.h', is_object( $sm ) ) ?> 
      <?php echo menu_item( 'cppcode.php', '.cpp', is_object( $sm ) ) ?> 
      <?php echo menu_item( 'atml.php', '.atml', is_object( $sm ) ) ?> 
    </ul> 
  </div>  
</nav>
<?php

if ( is_object( $sm ) ) {
  printf( "<div class='well'><span class='glyphicon glyphicon-%s'></span> State Machine: %s <span class='badge' title='Machine contains %d states and %d events'>%d:%d</span></div>", 
    $_SESSION['MODIFY'] ? 'random' : 'cog', $sm->name(), 
    count( $sm->states() ), count( $sm->events() ) - 1, 
    count( $sm->states() ), count( $sm->events() ) - 1 );  
} else {
  printf( "<div class='well'><span class='glyphicon glyphicon-warning-sign'></span> No machine active</div>" );
}

function menu_item( $url, $label, $enable = true ) {
 
  return sprintf( "<li class='%s'><a href='%s'>%s</a></li>\n",
    preg_match( '/'.$url.'$/', $_SERVER['SCRIPT_NAME'] ) 
    ? 'active' : ( $enable ? '' : 'disabled' ),
    $url, $label );
}
