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
			array("rekening","string"),
			array($lastyear,"euro"),
			array($this->boekjaar,"euro"),
			array('',''),
			array("rkn","string"),
			array("rekening","string"),
			array($lastyear,"euro"),
			array($this->boekjaar,"euro"),	
		);
		$tabel = $this->DisplayTabel($tabel,$colinfo);
		$html .= $tabel;

		// old version
		/*
		$balans=$this->BerekenBalans();
		$maxrow=count($balans);
		$html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
		for ($col=0; $col < 8; $col++)
		{
			if(!isset($balans[0][$col])) { $balans[0][$col] = ''; }
			if($col == 2 || $col == 3 || $col ==6 || $col==7) { $html .= '<th class="compactthright">' . $balans[0][$col] . '</th>'; }
			else { $html .= '<th class="compactth">' . $balans[0][$col] . '</th>'; }
			if($col == 3) { $html .= '<th class="compactth">&nbsp;&nbsp;</th>'; }
		}
		for($row=1; $row<=$maxrow; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < 8; $col++)
				{
					if(!isset($balans[$row][$col])) { $balans[$row][$col] = ''; }
					if($col == 2 || $col == 3 || $col ==6 || $col==7) { $html .= '<td class="compacttdright">' . $balans[$row][$col] . '</td>'; }
					else { $html .= '<td class="compacttd">' . $balans[$row][$col] . '</td>'; }
					if($col == 3) { $html .= '<td class="compacttd">&nbsp;&nbsp;</td>'; }
				}
				$html .= '</tr>';
		}
		$html .= '</table>';
		*/
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
	/*
    protected function BerekenBalans()
	{
		$overzicht = new Overzicht();
		$balans[0][0] = 'reknr';
		$balans[0][1] = 'naam';
		$balans[0][2] = 'balans' . strval($this->boekjaar -1);
		$balans[0][3] = 'balans' . strval($this->boekjaar);
		$balans[0][4] = 'reknr';
		$balans[0][5] = 'naam';
		$balans[0][6] = 'balans' . strval($this->boekjaar -1);
		$balans[0][7] = 'balans' . strval($this->boekjaar);
		$result=0;
		foreach ($this->rekeningen as $p)
		{
			$totaal[$p->rekeningnummer] = 0;
			$name[$p->rekeningnummer]=$p->naam;
			$type[$p->rekeningnummer]=$p->type;
			foreach ($this->boekingen as $b)
			{
				if($p->soort == 'B')
				{
					$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
					$totaal[$p->rekeningnummer] += $bedrag;
					if($p->type == 'C' && $bedrag) { $result -= $bedrag;}
					if($p->type == 'D' && $bedrag) { $result += $bedrag;}
				}
			}
		}
		
		$ptype=$totaalvorigjaar=$totaaleindbedrag=0;
		$row=0;
		$col=0;
		$tvj[0]=$tvj[4]=$teb[0]=$teb[4]=0;
		foreach ($this->vorigebalans as $p)
		{
			$eindbedrag=$p->bedrag + $totaal[$p->rekeningnummer];
			if($ptype && $ptype != $type[$p->rekeningnummer])
			{
				$row=0;
				$col=4;
			}
			$ptype=$type[$p->rekeningnummer];
			$row++;
			$balans[$row][$col]=$p->rekeningnummer;
			$balans[$row][$col+1]=$name[$p->rekeningnummer];
			$balans[$row][$col+2]=number_format($p->bedrag/100,2,',','');
			$balans[$row][$col+3]=number_format($eindbedrag/100,2,',','');
			$tvj[$col] += $p->bedrag;
			$teb[$col] += $eindbedrag;
		}
		$row=count($balans);
		$row++;
		$absresult=abs($result);
		if($result < 0) { $col=4; $balans[$row][$col+1]='verlies';}
		else { $col=0; $balans[$row][$col+1]='winst';}
		$balans[$row][$col+3]=number_format($absresult/100,2,',','');
		$row++;
		$col=0;
		$balans[$row][$col+1]='totaal';
		$balans[$row][$col+2]=number_format($tvj[0]/100,2,',','');
		if($result > 0) { $teb[0] += $absresult; }
		$balans[$row][$col+3]=number_format($teb[0]/100,2,',','');
		$balans[$row][$col+5]='totaal';
		$balans[$row][$col+6]=number_format($tvj[4]/100,2,',','');
		if($result < 0) { $teb[4] += $absresult; }
		$balans[$row][$col+7]=number_format($teb[4]/100,2,',','');
		return($balans);
	}
	*/
}