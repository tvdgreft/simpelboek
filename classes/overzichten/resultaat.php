<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Resultaat extends Overzichten
{
    public function Start()
	{
        $this->LoadData();
		$form = new Forms();
		$overzicht = new Overzicht();
		$html = '';
		$html .= '<h2>resultatenrekening_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';

		$error = '';
		// Is de begroting al aangemaakt?
		$aantalbegrotingen = count($this->begroting);
        if($aantalbegrotingen == 0)
        {
            $error .= '<br>' . __("Eerst een begroting aanmaken","prana");
        }
        if($error) 
        { 
            return('<div class="isa_error">' . $error . '</div>');
        }
		[$ditjaar,$dc,$dd,$dt] = $overzicht->Resultaten($this->boekjaar);
		[$vorigjaar,$vc,$vd,$vt] = $overzicht->Resultaten($this->boekjaar-1);
		[$begroting,$gc,$gd,$gt] = $overzicht->Begroting($this->boekjaar);
		$totaal = $overzicht->array_merge_recursive_three($vorigjaar,$begroting,$ditjaar);
		$tabel = $overzicht->TotalenTabel($totaal);
		// voeg verlies/winst regel en totalen toe aan tabel
		$dv=$vv=$gv=$dw=$vw=$gw='';
		if($dt < 0) {$dv = abs($dt); $dc += $dv;}
		if($dt > 0) {$dw = $dt; $dd += $dw; }
		if($vt < 0) {$vv = abs($vt); $vc += $vv;}
		if($vt > 0) {$vw = $vt; $vd += $vw;}
		if($gt < 0) {$gv = abs($gt); $gc += $gv;}
		if($gt > 0) {$gw = $gt; $gd += $gw;}
		$tabel[] = ['','winst',$vw,$gw,$dw,'','','verlies',$vv,$gv,$dv];
		$tabel[] = ['','',$vd,$gd,$dd,'','','',$vc,$gc,$dc];
		#echo '<br>tabel<br>';
		#print_r($tabel);
		$lastyear = $this->boekjaar-1;
		$colinfo = array(
			
			array("rkn","string"),
			array("rekening","string"),
			array("uit ".$lastyear,"euro"),
			array("uit begroot","euro"),
			array("uit ".$this->boekjaar,"euro"),
			array("",""),
			array("rkn","string"),
			array("rekening","string"),
			array("in ".$lastyear,"euro"),
			array("in begroot","euro"),
			array("in ".$this->boekjaar,"euro"),	
		);
		$tabel = $this->DisplayTabel($tabel,$colinfo);
		$html .= $tabel;
		$filename = 'resultatenrekening_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="resultaat" name="resultaat"  type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
}