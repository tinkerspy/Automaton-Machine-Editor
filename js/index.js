

$(document).ready(function() {

    $(".action_check").change(function() {
       var p =  $(this).attr("name").split(":");
       $.get( "ajax/action.php", { value: $(this).is(':checked') ? 1 : 0, state:  p[0], action: p[1] } );
    });

    $('.link_state').on('change', function() {
      var p = this.name.split( ":" );
      $.get( "ajax/link.php", {  state: p[0], 'event':  p[1], 'new': this.value } );
    });

    $("#btn_modify").click(function() {
       $("#sub_modify").toggle( "fast" );
       $("#sub_rename").hide( "fast" );
       $("#sub_export").hide( "fast" );
    });
    $("#btn_rename").click(function() {
       if ( !$( this ).hasClass( "disabled" ) ) {
         $("#sub_rename").toggle( "fast" );
         $("#sub_modify").hide( "fast" );
         $("#sub_export").hide( "fast" );
       }
    });
    $("#btn_export").click(function() {
       if ( !$( this ).hasClass( "disabled" ) ) {
         $("#sub_export").toggle( "fast" );
         $("#sub_rename").hide( "fast" );
         $("#sub_modify").hide( "fast" );
       }
    });
    $("#btn_dorename").click(function() {
       $("#frm_rename").submit();
    });
    $("#btn_doupload").click(function() {
       $("#frm_upload").submit();
    });
    $("#btn_create").click(function() {
       $("#frm_create").submit();
    });
    $('#hfile').change(function(){ 
      $('#hfile_ok').show();
    });
    $('#cppfile').change(function(){ 
      $('#cppfile_ok').show();
    });
    $(".btn-editor").click( function() {
      $("#cmd").val( this.name );
      $("#frm_editor").submit();
    });

    $(".cb_autostore").change(function() {
       $.get( "ajax/conn_autostore.php", { 'value': $(this).is(':checked') ? 1 : 0, 'conn':  $(this).attr("name") } );
    });
    $(".cb_broadcast").change(function() {
       $.get( "ajax/conn_broadcast.php", { 'value': $(this).is(':checked') ? 1 : 0, 'conn':  $(this).attr("name") } );
    });

    $('.sb_slots').on('change', function() {
      $.get( "ajax/conn_slots.php", { 'conn': this.name, 'value': this.value } );
    });

    hljs.initHighlightingOnLoad();

});

