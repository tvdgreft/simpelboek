<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Grootboek extends Overzichten
{
    function Start()
    {
		$overzicht = new Overzicht();
        $this->LoadData();
		$form = new Forms();
		$html='';
		$html .= '<h2>grootboek_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';
        $grootboek = $this->Grootboek($this->boekjaar);
        #print_r($grootboek);
		$html .= '<table class="compacttable">';
		foreach($grootboek as $grootboekrekening)
		{
			#echo '<br>grootboekrekening<br>';
			#print_r($grootboekrekening[0]);
			$header=$grootboekrekening[0];
			#$html .= '<br>'.$header[0].$header[1];
			$posts = $grootboekrekening[1];
			/*
			foreach($posts as  $post)
			{
				$html .= '<br>'.$post[0].' | ' . $post[1];
			}
			*/
			$html .= '<tr class="compacttr">';
			$html .= '<th class="compactth">' . $header[0] . '</th>';
			$html .= '<th class="compactth">' . $header[1] . '</th>';
			$html .= '<th class="compactth">' . 'datum' . '</th>';
			$html .= '<th class="compactth">' . 'omschrijving' . '</th>';
			$html .= '<th class="compactthright">' . 'bedrag' . '</th>';
			$html .= '</tr class="compacttr">';
			// beginbalans tonen
			if($header[2] == 'B') 
			{
				$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . 'beginbalans' . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($header[3]) . '</td>';
				$html .= '</tr>';
			}
			foreach($posts as $p)
			{
				$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . $p[0] . '</td>';
				$html .= '<td class="compacttd">' . $p[2] . '</td>';
				$html .= '<td class="compacttd">' . $p[1] . '</td>';
				$html .= '<td class="compacttd">' . $p[3] . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($p[4]) . '</td>';
				$html .= '</tr>';
			}
			$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . 'totaal' . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($header[4]) . '</td>';
				$html .= '</tr>';
		}
		$html .= '</table>';
		/*
        $tabel=$this->BerekenGrootboek();
		$maxrow=count($tabel);
		$html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
		for ($col=0; $col < 7; $col++)
		{
			if(!isset($tabel[0][$col])) { $tabel[0][$col] = ''; }
			$html .= '<th class="compactth">' . $tabel[0][$col] . '</th>';
		}
		$tdclass = "compacttdeven";
		for($row=1; $row<=$maxrow; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < 7; $col++)
				{
					if(!isset($tabel[$row][$col])) { $tabel[$row][$col] = ''; }
					#if($col==0 || $col==1 || $col==6) { $html .= '<td class="compacttd" align="right">' . $tabel[$row][$col] . '</td>'; }
					#else { $html .= '<td class="compacttd">' . $tabel[$row][$col] . '</td>'; }
				}
				$html .= '<td class="' . $tdclass . '" align="right">' . $tabel[$row][0] . '</td>';
				$html .= '<td class="' . $tdclass . '" align="right">' . $tabel[$row][1] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $tabel[$row][2] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $tabel[$row][3] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $tabel[$row][4] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $tabel[$row][5] . '</td>';
				$html .= '<td class="' . $tdclass . '" align="right">' . $tabel[$row][6] . '</td>';
				$html .= '</tr>';
				if($tabel[$row][2] == 'totaal')
				{ 
					if($tdclass == 'compacttdeven') { $tdclass = "compacttdodd"; }
					else { $tdclass = "compacttdeven"; }
				}
		}
		$html .= '</table>';



		*/

		$filename = 'grootboek_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="grootboek" name="grootboek" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	/*
	protected function BerekenGrootboek()
	{
        $overzicht = new Overzicht();
		$bookyear=$this->boekjaar;
		$lastbookyear=$bookyear-1;
		$grootboek[0][0] = 'vnr';
		$grootboek[0][1] = 'rkn';
		$grootboek[0][2] = 'naam';
		$grootboek[0][3] = 'datum';
		$grootboek[0][4] = 'bknr';
		$grootboek[0][5] = 'oms';
		$grootboek[0][6] = 'bedrag';
		$lastbookyear=$this->boekjaar-1;
		$bookyear=$lastbookyear+1;
		$pb=$this->boekingen;
		$pr=$this->rekeningen;
		$pl=$this->vorigebalans;
		foreach ($pl as $p) { $balansvorigjaar[$p->rekeningnummer] = $p->bedrag; }
		$row=0;
		foreach ($pr as $p)
		{
			$totaal=0;
			if($p->soort == 'B') 
			{
				if(isset($balansvorigjaar[$p->rekeningnummer])) { $totaal=$balansvorigjaar[$p->rekeningnummer]; }
				else { $totaal = 0; }
			}
			$row++;
			$grootboek[$row][1] = $p->rekeningnummer;
			$grootboek[$row][2] = $p->naam;
			$grootboek[$row][5] = 'beginbalans';
			$grootboek[$row][6] = number_format($totaal/100,2,',','');
			foreach ($pb as $b)
			{
				$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
				$totaal += $bedrag;
				if($bedrag) 
				{ 
					$row++;
					$grootboek[$row][0] = $b->id;
					$grootboek[$row][1] = $p->rekeningnummer;
					$grootboek[$row][2] = $p->naam;
					$grootboek[$row][3] = $b->datum;
					$grootboek[$row][4] = $b->bankrekeninghouder;
					$grootboek[$row][5] = $b->omschrijving;
					$grootboek[$row][6] = number_format($bedrag/100,2,',','');
				}
			}
			$row++;
			$grootboek[$row][2] = 'totaal';
			$grootboek[$row][6] = number_format($totaal/100,2,',','');
			$row++;
			$grootboek[$row][0] = '';
		}
		return($grootboek);
	}
	*/
	function Grootboek($boekjaar) : array
	{
        $overzicht = new Overzicht();
        $dbio = new DBIO();
		foreach ($this->vorigebalans as $p) { $balans[$p->rekeningnummer] = $p->bedrag; }
        $grootboek = array();
		foreach ($this->rekeningen as $p)
		{
			$beginbedrag = 0;;
			$totaal=0;
			// Bij balansrekening de beginbalans bepalen
			if($p->soort == 'B')
            {
                if (isset($balans[$p->rekeningnummer])) { $beginbedrag = $totaal = $balans[$p->rekeningnummer]; }
			}
            $grootboekrekening = array();
            $posten=array();
            $grootboekrekening[] = $p->rekeningnummer;
            $grootboekrekening[] = $p->naam;
            $grootboekrekening[] = $p->soort;
			$grootboekrekening[] = $beginbedrag;

			foreach ($this->boekingen as $b)
			{
				$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
                if($bedrag)
                {
                    $posten[] = [$b->id,$b->datum,$b->bankrekeninghouder,$b->omschrijving,$bedrag];
					$totaal += $bedrag;
                }
			}
            #echo '<br>posten<br>';
            #print_r($posten);
			$grootboekrekening[] = $totaal;
            $grootboek[] = [$grootboekrekening,$posten];
		}
		return($grootboek);
    }
}