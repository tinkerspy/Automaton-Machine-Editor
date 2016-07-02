<?php
include_once "./html/header.html";
include_once "./lib/libatm.php";
include_once "./lib/libcontrols.php";

session_start();

$coll = new ATM_Collection( $_SESSION["ATM_COLLECTION"] );

if ( $_FILES ) {
  $coll->clear();
  foreach ( $_FILES as $v ) {
    if ( $v['name'] ) {
      $xml = file_get_contents( $v["tmp_name"] );
      if ( preg_match( '/Automaton::(ATML|SMDL)::begin/', $xml ) ) {
        $xml = preg_replace( '/^.*Automaton::(ATML|SMDL)::begin.*?[\n\r]+/s', '', $xml );
        $xml = preg_replace( '/Automaton::(ATML|SMDL)::end.*[\n\r]+/s', '', $xml );
        $coll->from_xml( $xml );  
      }    
    }
  }
  if ( count( $coll->machines() ) ) {
    $sm = $coll->first();
    @mkdir( "machines/". session_id() );
    @unlink( "machines/". session_id(). "/MODIFY" );
    $_SESSION['MODIFY'] = 0;
    $_SESSION['HPPMODE'] = 0;
    foreach ( $_FILES as $v ) {
      if ( $v['tmp_name'] ) {
        $txt = file_get_contents( $v["tmp_name"] );
        $txt = preg_replace( '/\/\*[\n\r]+Automaton::ATML::begin.*/s', '', $txt );
        file_put_contents( 
          sprintf( "machines/%s/Machine.%s", session_id(),
            preg_match( '/\.hp?p?$/i', $v['name'] ) ? 'h' : 'cpp' 
          ), $txt
        );
        if ( preg_match( '/\.hpp$/i', $v['name'] ) ) {
          $_SESSION['HPPMODE'] = 1;
        }
        if ( preg_match( '/\.cpp$/i', $v['name'] ) ) {
          file_put_contents( "machines/". session_id(). "/MODIFY", 'x' );
          $_SESSION['MODIFY'] = 1;
        }
      }
    }
    file_put_contents( "machines/". session_id(). "/original.atml", $sm->as_xml() );
  }
  $_SESSION['HASH'] = 0;
  $coll->changed( true );
}

if ( $_POST['create'] ) {
  @mkdir( "machines/". session_id() );
  $sm = $coll->clear()->machine( 'Atm_machine' );
  @unlink( "machines/". session_id(). "/MODIFY" );
  $_SESSION['HASH'] = 0;
  $_SESSION['MODIFY'] = 0;
  $_SESSION['HPPMODE'] = 0;
}

if ( $_POST['txt_rename'] ) {
  $sm = $coll->first()->name( $_POST['txt_rename'] );
}

include_once "./navigation.php"; 

if ( $_FILES && !count( $coll->machines() ) ) { ?>
  <div class="alert alert-danger">
   <strong>Warning</strong> No State Machine definition (ATML) was found in the uploaded file(s)
  </div>
<?php }

$disabled = is_object( $sm ) ? '' : 'disabled';
$modify = $_SESSION['MODIFY'];
$hdr_ext = $_SESSION['HPPMODE'] ? 'hpp' : 'h';
?>

<form method='POST' id='frm_create'>
  <button type='button' class='btn btn-primary btn-block' id='btn_create'>
    <span class='glyphicon glyphicon-cog'></span> 
      Create new blank state machine (Atm_machine)
  </button>
  <input type='hidden' name='create' value='1'>
  <br>
</form>
<form method='POST' id='frm_upload' enctype="multipart/form-data">
  <button type='button' class='btn btn-primary btn-block' id='btn_modify'>
    <span class='glyphicon glyphicon-random'></span> 
      Modify existing state machine
  </button>
  <br>
  <div id='sub_modify' style='display: none'>
    Upload the source files you want to modify...<br><br>
    <label class="btn btn-default btn-file">
        <span class='glyphicon glyphicon-upload'></span> Upload Atm_*.h file 
        <input type="file" name='hfile' id='hfile' style="display: none;">
    </label> 
    <span id='hfile_ok' class='glyphicon glyphicon-ok' style='display: none'></span>
    <br>
    <br>
    <label class="btn btn-default btn-file">
        <span class='glyphicon glyphicon-upload'></span> Upload Atm_*.cpp file 
        <input type="file" name='cppfile' id='cppfile' style="display: none;">
    </label> 
    <span id='cppfile_ok' class='glyphicon glyphicon-ok' style='display: none'></span>
    <br>
    <br>
    <button type='button' id='btn_doupload' class='btn btn-success'><span class='glyphicon glyphicon-floppy-open'></span> Upload</button><br><br> 
  </div>
</form>
<button type='button' class='btn btn-primary btn-block <?php echo $disabled ?>' id='btn_rename'>
  <span class='glyphicon glyphicon-text-size'></span> 
    Rename state machine
</button>
<br> 
<form method='POST' id='frm_rename'>
<div id='sub_rename' style='display: none'>
<div class="form-group">
  <label for="txt_rename">New machine name:</label>
  <input type="text" class="form-control" id='txt_rename' name='txt_rename' value="<?php echo is_object( $sm ) ? $sm->name() : '' ?>">
</div> 
<button type='button' id='btn_dorename' class='btn btn-success'><span class='glyphicon glyphicon-cog'></span> Rename</button><br><br> 
</div>
</form>
<button type='button' class='btn btn-primary btn-block <?php echo $disabled ?>' id='btn_export'>
  <span class='glyphicon glyphicon-floppy-disk'></span> 
    Export C++ code
</button>
<br>
<table class='table table-condensed' style='display: none' id='sub_export'>
  <thead>
    <tr> 
      <th>Template</th>
      <th><?php if ( $modify ) echo "Modified code" ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="text-align: left">
        <a href='export/hsave-template.php'>
          <span class='glyphicon glyphicon-floppy-disk'></span>
            <?php echo is_object( $sm ) ? $sm->name(). '.'. $hdr_ext : '' ?>
        </a>
      </td>
      <td style="text-align: left">
        <?php if ( $modify ) { ?>
        <a href='export/hsave.php'>
          <span class='glyphicon glyphicon-floppy-disk'></span>
            <?php echo is_object( $sm ) ? $sm->name(). '.'. $hdr_ext : '' ?>
        </a>
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td style="text-align: left">
        <a href='export/cppsave-template.php'>
          <span class='glyphicon glyphicon-floppy-disk'></span>
            <?php echo is_object( $sm ) ? $sm->name(). '.cpp' : '' ?>
        </a>
      </td>
      <td style="text-align: left">
        <?php if ( $modify ) { ?>
        <a href='export/cppsave.php'>
          <span class='glyphicon glyphicon-floppy-disk'></span>
            <?php echo is_object( $sm ) ? $sm->name(). '.cpp' : '' ?>
        </a>
        <?php } ?>
      </td>
    </tr>
    <tr>
      <td style="text-align: left">
        <a href='export/sketch-template.php'>
          <span class='glyphicon glyphicon-floppy-disk'></span>
            <?php echo is_object( $sm ) ? $sm->short(). '.ino' : '' ?>
        </a>
      </td>
      <td style="text-align: left">
      </td>
    </tr>
  </tbody>
</table>
<a href='https://github.com/tinkerspy/Automaton/wiki/Machine-building-tutorial-2' class='btn btn-primary btn-block' id='btn_tutorial'>
  <span class='glyphicon glyphicon-book'></span> 
    Tutorial
</a>
<br>

<?php

if ( $coll->changed() ) {
  #echo "Saving collection<br>\n";
  $_SESSION['ATM_COLLECTION'] = $coll->as_xml();
}

include_once "./html/footer.html";

?>

