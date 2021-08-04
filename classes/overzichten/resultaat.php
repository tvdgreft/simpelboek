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
		/*
		// resultaat berekenen
		$result=$this->BerekenResult();
		$maxrow=count($result);
		$html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
		for ($col=0; $col < 10; $col++)
		{
			if(!isset($result[0][$col])) { $result[0][$col] = ''; }
			if($col == 2 || $col == 3 || $col ==6 || $col==7) { $html .= '<th class="compactthright">' . $result[0][$col] . '</th>'; }
			else { $html .= '<th class="compactth">' . $result[0][$col] . '</th>'; }
			if($col == 4) { $html .= '<th class="compactth">&nbsp;&nbsp;</th>'; }
		}
		$html .= '</tr>';
		for($row=1; $row<=$maxrow; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < 10; $col++)
				{
					if(!isset($result[$row][$col])) { $result[$row][$col] = ''; }
					if($col == 2 || $col == 3 || $col ==4 || $col==7 || $col==8 || $col==9) { $html .= '<td class="compacttdright">' . $result[$row][$col] . '</td>'; }
					else { $html .= '<td class="compacttd">' . $result[$row][$col] . '</td>'; }
					if($col == 4) { $html .= '<td class="compacttd">&nbsp;&nbsp;</td>'; }
				}
				$html .= '</tr>';
		}
		$html .= '</table>';
		*/
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
	/*
	protected function BerekenResult()
	{
		$overzicht = new Overzicht();
		$begrootresult=0;
		#
		# resultaat lopend boekjaar
		#
		$result=0;
		foreach ($this->rekeningen as $p)
		{
			$totaal[$p->rekeningnummer] = 0;
			$name[$p->rekeningnummer]=$p->naam;
			$type[$p->rekeningnummer]=$p->type;
			foreach ($this->boekingen as $b)
			{	
				if($p->soort == 'R')
				{
					$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
					$totaal[$p->rekeningnummer] += $bedrag;
					if($p->type == 'C' && $bedrag) { $result += $bedrag;}
					if($p->type == 'D' && $bedrag) { $result -= $bedrag;}
				}
			}
		}
		#
		# resultaat vorig boekjaar
		#
		$vorigresult=0;
		foreach ($this->rekeningen as $p)
		{
			$vorigtotaal[$p->rekeningnummer] = 0;
			$name[$p->rekeningnummer]=$p->naam;
			$type[$p->rekeningnummer]=$p->type;
			foreach ($this->vorigeboekingen as $b)
			{	
				if($p->soort == 'R')
				{
					$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
					$vorigtotaal[$p->rekeningnummer] += $bedrag;
					if($p->type == 'C' && $bedrag) { $vorigresult += $bedrag;}
					if($p->type == 'D' && $bedrag) { $vorigresult -= $bedrag;}
				}
			}
		}
		#
		# begroting lopend boekjaar
		#
		$ptype=$totaalbegroot=$totaaleindbedrag=$vorigtotaaleindbedrag=0;
		
		$lastenbaten[0][0] = 'reknr';
		$lastenbaten[0][1] = 'naam';
		$lastenbaten[0][2] = 'uit ' . strval($this->boekjaar -1);
		$lastenbaten[0][3] = 'begr ' . strval($this->boekjaar);
		$lastenbaten[0][4] = 'uit ' . strval($this->boekjaar);
		$lastenbaten[0][5] = 'reknr';
		$lastenbaten[0][6] = 'naam';
		$lastenbaten[0][7] = 'in ' . strval($this->boekjaar -1);
		$lastenbaten[0][8] = 'begr ' . strval($this->boekjaar);
		$lastenbaten[0][9] = 'in ' . strval($this->boekjaar);
		$row=0;
		$col=0;
		#
		# totaal bedragen begroot, vorig jaar en lopendjaar ( debet =index 0 credit = index 5)
		$tbg[0]=$tbg[5]=$teb[0]=$teb[5]=$tvj[0]=$tvj[5]=0;
		foreach ($this->begroting as $p)
		{
			$eindbedrag=$totaal[$p->rekeningnummer];
			$vorigeindbedrag=$vorigtotaal[$p->rekeningnummer];
			if($ptype && $ptype != $type[$p->rekeningnummer])
			{
				$row=0;
				$col=5;
			}
			$row++;
			$ptype=$type[$p->rekeningnummer];
			$lastenbaten[$row][$col] = $p->rekeningnummer;
			$lastenbaten[$row][$col+1] = $name[$p->rekeningnummer];
			$lastenbaten[$row][$col+2] = number_format($vorigeindbedrag/100,2,',','');
			$lastenbaten[$row][$col+3] = number_format($p->bedrag/100,2,',','');
			$lastenbaten[$row][$col+4] = number_format($eindbedrag/100,2,',','');
			$tbg[$col] += $p->bedrag;
			$teb[$col] += $eindbedrag;
			$tvj[$col] += $vorigeindbedrag;
			if($ptype == 'C' && $p->bedrag) { $begrootresult += $p->bedrag;}
			if($ptype == 'D' && $p->bedrag) { $begrootresult -= $p->bedrag;}

		}
		$row=count($lastenbaten);
		#
		# winst of verlies regel
		#
		$row++;
		$lastenbaten[$row][0]='';
		$lastenbaten[$row][1]='winst';
		$lastenbaten[$row][6]='verlies';
		$absresult = abs($result);
		$absvorigresult = abs($vorigresult);
		$absbegrootresult = abs($begrootresult);
		
		if($result > 0) { $lastenbaten[$row][4]=number_format($absresult/100,2,',',''); }
		else            { $lastenbaten[$row][9]=number_format($absresult/100,2,',',''); }
		if($vorigresult > 0) { $lastenbaten[$row][2]=number_format($absvorigresult/100,2,',',''); }
		else            { $lastenbaten[$row][7]=number_format($absvorigresult/100,2,',',''); }
		if($begrootresult > 0) { $lastenbaten[$row][3]=number_format($absbegrootresult/100,2,',',''); }
		else            { $lastenbaten[$row][8]=number_format($absbegrootresult/100,2,',',''); }
		#
		# totaal regel
		#
		$row++;
		$col=0;
		$lastenbaten[$row][$col+1]='totaal';
		$lastenbaten[$row][$col+2]=number_format($tvj[0]/100,2,',','');
		if($result > 0) { $teb[0] += $absresult; }
		$lastenbaten[$row][$col+4]=number_format($teb[0]/100,2,',','');
		if($vorigresult > 0) { $tvj[0] += $absvorigresult; }
		$lastenbaten[$row][$col+2]=number_format($tvj[0]/100,2,',','');
		if($begrootresult > 0) { $tbg[0] += $absbegrootresult; }
		$lastenbaten[$row][$col+3]=number_format($tbg[0]/100,2,',','');
		$lastenbaten[$row][$col+6]='totaal';
		$lastenbaten[$row][$col+7]=number_format($tvj[5]/100,2,',','');
		if($result < 0) { $teb[5] += $absresult; }
		$lastenbaten[$row][$col+9]=number_format($teb[5]/100,2,',','');
		if($vorigresult < 0) { $tvj[5] += $absvorigresult; }
		$lastenbaten[$row][$col+7]=number_format($tvj[5]/100,2,',','');
		if($begrootresult < 0) { $tbg[5] += $absbegrootresult; }
		$lastenbaten[$row][$col+8]=number_format($tbg[5]/100,2,',','');
		return($lastenbaten);
	}
	*/
}