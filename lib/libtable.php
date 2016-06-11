<%

/*

22/7/2008 PL

Ik heb de libtable.php class op de Epicurus aangepast.

Het verschil met de oude versie is dat het nu mogelijk is om - naast de automatische toewijzing van classes en id's - ook handmatig de attributen van de verschillende velden aan te passen. Dat is alleen mogelijk als je gebruikt maakt van AddHeader en AddRow (dus niet met HtmlHeader en HtmlRow). De attributen class, id, colspan en style kunnen worden aangepast per cell of per row met de SetAttr() method.

Aanpassen van een row: $table->SetAttr( 1, null, 'class', 'myrow' ); # Past de class van de 2e row aan
Aanpassen van een cell: $table->SetAttr( 1, 0,  'style', 'background-color: red' ); # Past de achtergrondkleur van de 1e cel van de 2e row aan

Voorbeeld:

include_once 'libtable.php';

$table = new Table('myclass',1,1);
$table->AddRow('Regel 1',Array(a => 1, b => 2, c => 3, d => 4));
$table->AddRow('Regel 2',Array(a => 5, b => 6, c => 7, d => 8));
$table->SetAttr( 1, 0, 'style', 'background-color: red' );
echo $table->Close();

Resultaat: http://www.sin-online.nl/test/table_new.html

De SetAttr() method moet voor de Close() method, maar na de AddHeader/AddRow methods die de data in de table zetten worden aangeroepen.

SetAttrRange( $row, $col, $row2, $col2, $attr, $value) doet hetzelfde maar dan voor een range cellen of rows ( als $row = null )

Zowel SettAttr als SetAttrRange kunnen meerdere attributen zetten, vb: SetAttr( 1, 1, 'style', 'background-color: red', 'class', 'testclass' )

Nieuwe methods:

SetAttr( $row, $col, $attr, $value );
SetAttrRange( $row, $col, $row2, $col2, $attr, $value );
MaxRow();
MaxCol();
HideCol( $col );


======= Voorbeelden =======

 Voorbeeld 1: 

 Vanuit een simpel array een tabel opbouwen met autom column headers

 $table=new Table('myclass'0,1);
 echo $table->Html(Array(a => 1, b => 2, c => 3, d => 4));

 Voorbeeld 2: 

 Met de in-memory functies een tabel opbouwen met handmatig ingestelde
 column headers. Vervolgens het resulaat van Close() via echo naar de 
 browser sturen. 

 $table=new Table('myclass',1);
 $table->AddHeader(Array( 'a', 'b', 'c', 'd' ));
 $table->AddRow("Regel 1',Array( 1, 2, 3, 4 ));
 echo $table->Close();

 Voorbeeld 3:

 Als voorbeeld 2 maar nu wordt het resultaat per row direct naar de browser
 afgedrukt (zuiniger met geheugen).

 $table=new Table('myclass',1);
 echo $table->HtmlHeader(Array( 'a', 'b', 'c', 'd' ));
 echo $table->HtmlRow("Regel 1',Array( 1, 2, 3, 4 ));
 echo $table->Close();


 Voorbeeld 4:

 Afdrukken tabel met 2 rows en 4 kolommen, Bovendien voorzien van row en 
 (autom) column headers

 $table=new Table('myclass',1,1);
 echo $table->HtmlRow('Regel 1',Array(a => 1, b => 2, c => 3, d => 4));
 echo $table->HtmlRow('Regel 2',Array(a => 5, b => 6, c => 7, d => 8));
 echo $table->Close();

*/

class Table {
	var $s = '';
	var $rowcnt = 0;
	var $maxcolcnt = 0;
	var $hdrcnt = 0;
	var $class = 'table';
	var $colheader = 0;
	var $rowheader = 0;
	var $th = 'th';
	var $td = Array( );
	var $tr = Array( );

	function Table($class='table', $rowheader=0, $colheader=0, $id = '', $style = '') {
	
		$this->class=$class;
		$this->colheader=$colheader;
		$this->rowheader=$rowheader;
		if ( $id == '' ) $id = $class;
		$this->s="\n<table class='$class' id='$id' style='$style'>\n";
	}

	# Headers moeten eigenlijk ook genummerd worden...

	function AddHeader($row,$colspan=NULL) {

		$colcnt=0;
		$this->tr[$this->rowcnt] = Array (
					'class' => $this->class."_row_top",
					id => $this->class."_row_h".$this->rowcnt
		);
                if ($this->rowheader) {
			$this->td[$this->rowcnt][$colcnt] = Array (
				tag => $this->th,
				'class' => $this->class."_colh",
				style => '',
				id => $this->class."_h".$this->hdrcnt."h${colcnt}",
				colspan => ( is_array($colspan) && $colspan[$colcnt] ? $colspan[$colcnt] : 1 ),
				content => '&nbsp;' );
			$colcnt++;
                }
		foreach ($row as $key => $value) {
			$this->td[$this->rowcnt][$colcnt] = Array (
				tag => $this->th,
				'class' => $this->class."_col${colcnt}",
				style => '',
				id => $this->class."_h".$this->hdrcnt."c${colcnt}",
				colspan => ( is_array($colspan) && $colspan[$colcnt] ? $colspan[$colcnt] : 1 ),
				content => $value );
				$colcnt++;
		}
		if ( $colcnt > $this->maxcolcnt ) $this->maxcolcnt = $colcnt;
		$this->hdrcnt++;
		$this->rowcnt++;
	}

	function AddRow($label,$row,$colspan=NULL,$flush=0) {

		if ( !is_array( $row ) ) $row = Array( $row );
                if ($this->colheader == 1 && $this->rowcnt == 0 && is_array($row)) {
                        $this->AddHeader(array_keys($row),NULL);
			if ( $flush ) $this->s .= $this->output_row( $this->rowcnt - 1 );
                }
                $colcnt=0;
		if ( !is_array( $row ) ) $row = Array( $row );
		$this->tr[$this->rowcnt] = Array (
                              'class' => $this->class."_row_".($this->rowcnt % 2 ? 'even' : 'odd'),
                              id => $this->class."_row_r".($this->rowcnt - 1)
                );
		if (isset($label) && $this->rowheader) {
			$this->td[$this->rowcnt][$colcnt] = Array (
				tag => $this->th,
				'class' => $this->class."_colh",
				style => '',
				id => $this->class."_r".($this->rowcnt - 1)."h0",
				colspan => (is_array($colspan) && $colspan[$colcnt] ? $colspan[$colcnt] : 1 ),
				content => $label 
			);
			$colcnt++;
		}
		foreach ($row as $key => $value) {
			$this->td[$this->rowcnt][$colcnt] = Array (
				tag => 'td',
				'class' => $this->class."_col${colcnt}",
				style => '',
				id => $this->class."_r".($this->rowcnt - 1)."c${colcnt}",
				colspan => ( is_array($colspan) && $colspan[$colcnt] ? $colspan[$colcnt] : 1 ),
				content => $value
			);
			$colcnt++;
		}
		if ( $colcnt > $this->maxcolcnt ) $this->maxcolcnt = $colcnt;
		$this->rowcnt++;
	}


	function MaxCol() {

		return $this->maxcolcnt - 1;
	}


	function MaxRow() {

		return $this->rowcnt - 1;
	}


        function HtmlHeader($row,$colspan=NULL) {

                $this->AddHeader($row,$colspan);
                $s=$this->s . $this->output_row( $this->rowcnt - 1 );
                $this->s='';
                return $s;
        }


	function HtmlRow($label,$row,$colspan=NULL) {

		$this->AddRow($label,$row,$colspan,1);
		$s=$this->s . $this->output_row( $this->rowcnt - 1 );
		$this->s='';
		return $s;
	}


	function as_string() {

		return print_r( $this->td, 1 ) . print_r( $this->tr, 1 );
	}


	function SetAttr( $row, $col, $attr ) {

		$args = is_array( $attr ) ? $attr  : array_slice( func_get_args(), 2 );
		if ( !isset( $row ) ) $row=$this->MaxRow();
		for ( $i = 0; $i < count( $args ); $i = $i + 2 ) {
			$attr = $args[$i];
               		$value = $args[$i+1];
			if ( isset($col) ) {
				$this->td[$row][$col][$attr] = $value;
			} else {
				$this->tr[$row][$attr] = $value;
			}
		}
	}


	function SetAttrRange( $row, $col, $row2, $col2, $attr, $value ) {

		$args = array_slice( func_get_args(), 4 );
		for ( $i=$row; $i <= $row2; $i++ ) {
			if ( isset( $col ) ) {
				for ( $j=$col; $j <= $col2; $j++ ) {
					#echo "$i,$j<br>\n";
					$this->SetAttr( $i, $j, $args );
				}
			} else {
				$this->SetAttr( $i, null, $attr );
			}
		}
	}


	function output_row( $row ) {

		$r = $this->tr[ $row ];
		$s = "  <tr class='$r[class]' id='$r[id]' style='$r[style]'>\n";
		foreach ( $this->td[ $row ] as $d ) {
			$s .= "    <$d[tag]";
			foreach ( $d as $t => $v ) {
				if ( $t != 'tag' and $t != 'content' ) {
					$s .= " $t='$v'";
				}
			}
			$s .= ">$d[content]</td>\n";
			unset( $this->td[ $row ] );
		}
		unset( $this->tr[ $row ] );
		$s .= "  </tr>\n";
		return $s;
	}


        function HideCol( ) {

                $col = func_get_args();
                foreach ( $col as $i ) {
                        foreach ( $this->td as $row => $d ) {
                                unset( $this->td[$row][$i] );
                        }
                }
        }


	function Close() {

		$s = $this->s;	
		foreach ( $this->tr as $key => $r ) {
			$s .= $this->output_row( $key );
		}
		return $s . "</table>\n";
	}


	function Html($table=NULL,$noclose=0) {

                if (is_array($table)) {
                        foreach ($table as $key => $value) {
                                $this->AddRow($this->rowheader ? $key : NULL, $value);
                        }
                }
		return $noclose ? '' : $this->Close();
	}

	function TH($new=NULL) {

		if (isset($new)) {
			$this->th=$new;
		}
		return $this->th;
	}
}




// TODO add array sort

class TableSorter {
        var $id = 'sort';
        var $headers = NULL; // Field => Label (num fields are ignored)
	var $imgup = "/templates/images/sortup.gif";
        var $imgdown = "/templates/images/sortdown.gif";
	var $default = '';
	var $forceupper = 0;
	var $forcelower = 0;
	var $map = Array();

        function TableSorter($id, $default='', $headers=NULL ) {

                $this->id=$id;
		$this->default=$default;
		if (isset($headers)) $this->SetColumns($headers);
        }

	function Sort() {

		return implode(',',$this->map);
	}

	function Id() {
		return $this->id;
	}

	function SetImages($imgdown,$imgup) {
	
		$this->imgup=$imgup;
                $this->imgdown=$imgdown;
	}
	
	function SetColumnsAssoc($headers=Array()) {

		$dup=Array();
		$this->map=Array();
		$this->headers=$headers;
		$sort=explode(',',$_REQUEST[$this->id]);
                foreach ($sort as $value) {
                        $avalue=preg_replace('/^-/','',$value);
                        if (!$dup[$avalue]) {
                                array_push($this->map,$this->headers[$avalue] ? $value : $this->default);
                                $dup[$avalue]=1;
                        }
                }
	}

	function SetColumns($headers=Array()) {

		$new=Array();
		foreach ($headers as $value) {
			$new[$value]=$value;
		}
		$this->SetColumnsAssoc($new);
	}

	function Sql() {

		if (!isset($this->headers)) {
			echo "<p>ERROR: Should call SetColums or SetColumnsAssoc() before Sql()</p>\n";
		}
		$r=Array();
		$sort=explode(',',$this->Sort());
		foreach ($sort as $value) {
			if ($this->forcelower) $value=strtolower($value);
                        if ($this->forceupper) $value=strtoupper($value);
			if ($value[0] == '-') {
				array_push($r,substr($value,1)." DESC");
			} else {
	                	array_push($r,$value);
			}
		}
		return " ORDER BY ".implode(', ',$r);
	}

	function ForceLower() {

		$this->forcelower=1;
		$this->forceupper=0;

	}

        function ForceUpper() {

                $this->forceupper=1;
		$this->forcelower=0;
        }


	function Columns($preserve=Array()) {

		$p=Array();
		$r=Array();
		$prefix='';
		$rprefix='-';
		$image=$this->imgdown;
		$sort=explode(',',$this->Sort());
		if ($sort[0][0] == '-') {
			$prefix='-';
			$rprefix='';
			$image=$this->imgup;
			$sort[0]=substr($sort[0],1);
		}
		$usort=implode(',',$sort);
		foreach ($preserve as $key => $value) {
			array_push($p,urlencode($key).'='.urlencode($value));
		}

		$url="$GLOBALS[PHP_SELF]?".implode('&',$p).($p ? '&' : '');
		foreach ($this->headers as $field => $label) {
			if (is_numeric($field)) {
				array_push($r,$label);
			} else {
				if ($field == $sort[0]) {
					array_push($r,sprintf( 
							"<a href='%s%s=%s%s'><img src='%s' border=0></a>&nbsp;".
							"<a href='%s%s=%s%s'>%s</a>",
							$url,$this->id,$rprefix,$usort,$image,
							$url,$this->id,$rprefix,$usort,$label));	
				} else {
					array_push($r,sprintf("<a href='%s%s=%s'>%s</a>",
								$url,$this->id,"$field,$prefix$usort",$label));
				}
			}
		}
		return($r);
	}

}


class TablePager {
        var $s = '';
	var $curpage = 0;
	var $itemcnt =0;
	var $pagesize=10;

	function TablePager($pagesize=NULL,$itemcnt=NULL,$page=NULL) {

		$this->PageSize($pagesize);
		$this->ItemCnt($itemcnt);
		$this->Page($page);
	}

	function PageSize($new=NULL) {

                if (isset($new) && $new >= 0) {
			$this->pagesize=$new;
                }
                return $this->pagesize;
	}

	function PageCnt() {

		return ceil($this->ItemCnt() / $this->PageSize());	
	}

	function ItemCnt($new=NULL) {

                if (isset($new) && $new >= 0) {     
                        $this->itemcnt=$new;
                }
                return $this->itemcnt;
	}

	function Page($new=NULL) {

		if (isset($new) && $new >= 0 && $new <= $this->PageCnt()) {
			$this->curpage=$new;
		}
		return $this->curpage;
	}

	function Next() {

		if ($this->Page() < $this->PageCnt() -1) {
			return $this->Page($this->Page() + 1);
		}
		return NULL;
	}

	function Previous() {
		if ($this->Page() > 0) {
			return $this->Page($this->Page() - 1);
		}
		return NULL;
	}

	function Prev() {
	
		$this->Previous();	
	}

	function First() {

		return $this->Page() == 0;
	}

	function Last() {

		return $this->Page() == $this->PageCnt() - 1;
	}

	function Pages($template='%p') {

		$r=Array();
		$t=$this->PageCnt();
		$ps=$this->PageSize();
		$ic=$this->ItemCnt();
		for ($p=0; $p < $this->PageCnt(); $p++) {
			$s=$template;
			$l=($p < $t - 1 ? ($p + 1) * $ps : $ic) - 1;
			$s=str_replace('%p',$p,$s);
                        $s=str_replace('%P',$p + 1,$s);
                        $s=str_replace('%t',$t,$s);
			$s=str_replace('%f',$p * $ps,$s);
			$s=str_replace('%F',$p * $ps + 1,$s);
			$s=str_replace('%l',$l,$s);
                        $s=str_replace('%L',$l + 1,$s);
			$s=str_replace('%i',$ic, $s);
			array_push($r,$s);
		}
		return $r;
	}

	function Sql() {

		return sprintf(" LIMIT %d OFFSET %d",
			$this->PageSize(),$this->Page() * $this->PageSize());
	}
}


