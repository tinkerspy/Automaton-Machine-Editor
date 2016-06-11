<?php

function checkbox($id,$flag,$extra='') {

  $result="<input $extra type=checkbox name=\"$id\"";
  if ($flag) $result.=" CHECKED";
  $result.= ">";
  return($result);
}




# Uitbreidingen: extra data voor select tag + classes per option (table = array of ( option =>, class =>))

function selectbox($id,$table,$selected=0,$usevalue=0,$extra='') {

  $result="\n<select name=\"$id\" $extra>\n";
  reset($table);
  foreach ($table as $key => $val) {
    if ($usevalue) $key=$val;
    if (is_array($val)) {
      $result.="  <option class='$val[class]' value=\"$key\"";
      $val=$val[option];
    } else {
      $result.="  <option value=\"$key\"";
    }
    if ($key === $selected || (is_array($selected) && in_array($key,$selected))) { # 2011/11 changed == to === (type must be same, to avoid 2011.10 = 2011.1
        $result.=" SELECTED";
    }
    $result.=">".htmlentities($val)."</option>\n";
  }
  return("$result</select>\n");
}


# Dumpt variabelen, als argument(s) de labels opgeven, dus bijv. echo print_v('HTTP_POST_VARS');
# Voordelen t.o.v. kale print_r:
# - Zet <pre></pre> tags om output heen
# - Drukt variabelenaam af
# - Doet zelf geen echo maar wordt in een 'echo' gebruikt
# - Kan meerdere variabelen tegelijkertijd afdrukken: echo print_v('test','id_user','list');

function print_v() {

  $result='<pre>';
  foreach (func_get_args() as $key => $label) {
    $result.= "$label: ". print_r($GLOBALS[$label],1). "\n";
  }
  $result.="</pre>\n";
  return $result;
}


