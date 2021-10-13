<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Balans extends Overzichten
{
    function Start()
	{
		$overzicht = new Overzicht();
        $this->LoadData();
		$form = new Forms();
		$html='';
		$html .= '<h2>balans_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';

		// De huidige balans
		[$ditjaar,$dc,$dd,$dt] = $overzicht->Balans();
		#echo '<br>ditjaar dc= ' . $dc . 'dd=' . $dd .' dt='.$dt.'<br>';
		#print_r($ditjaar);
		[$vorigjaar,$vc,$vd,$vt] = $overzicht->OudeBalans($this->boekjaar-1);
		#echo '<br>vorigjaar';
		#print_r($vorigjaar);
		$totaal = $overzicht->array_merge_recursive_two($vorigjaar,$ditjaar);
		#echo '<br>totaal<br>';
		#print_r($totaal);
		$tabel = $overzicht->TotalenTabel($totaal);
		// voeg verlies/winst regel en totalen toe aan tabel
		$dv=$vv=$gv=$dw=$vw=$gw='';
		if($dt < 0) {$dv = abs($dt); $dd += $dv;}
		if($dt > 0) {$dw = $dt; $dc += $dw; }
		if($vt < 0) {$vv = abs($vt); $vd += $vv;}
		if($vt > 0) {$vw = $vt; $vc += $vw;}
		#$tabel[] = ['','winst',$vw,$dw,'',' ','verlies',$vv,$dv];
		$tabel[] = ['','verlies',$vv,$dv,'',' ','winst',$vw,$dw];
		$tabel[] = ['','',$vd,$dd,'','','',$vc,$dc];
		$lastyear = $this->boekjaar-1;
		$colinfo = array(
			
			array("rkn","string"),
			array("activa","string"),
			array($lastyear,"euro"),
			array($this->boekjaar,"euro"),
			array('',''),
			array("rkn","string"),
			array("passiva","string"),
			array($lastyear,"euro"),
			array($this->boekjaar,"euro"),	
		);
		$tabel = $this->DisplayTabel($tabel,$colinfo);
		$html .= $tabel;
		$filename = 'balans_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="balans" name="balans" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
}