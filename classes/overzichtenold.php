<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class OverzichtenOld
{
    public $boekjaar;
    public $rekeningen;   #rekeningschema
    public $boekingen;    #boekingen in lopend boekjaar
    public $vorigebalans;  # balans vorig jaar;
	public $begroting;		# begroting lopend jaar
	public function Start()        
	{
        if(isset($_POST['maakoverzicht']))    # 
        {
            return($this->MaakOverzicht());
        }
        else   # formulier om bestand te zoeken met bankmutaties
        {
            return($this->FormOverzicht());
        }
    }
    #
    # Welk overicht?
    #
    function FormOverzicht()
    {
        $form = new forms();
        $html = '';
        $html .= '<h2>' . __('overzichten', 'prana') . '</h2>';
        #
        # todo: popup voor toelichting
        #
        $options = array(
            "Balans"=>"balans",
            "Resultatenrekening"=>"result",
			"Grootboek"=>"grootboek",
			"BTW overzichten"=>"formbtw"
        );
        $html .= $form->Radio(array("label"=>__( 'Overzicht', 'prana' ), "id"=>"overzicht", "value"=>"", "options"=>$options, "width"=>"300px"));
        $form->buttons = [
            ['id'=>'maakoverzicht','value'=>__( 'overzicht maken', 'prana' )],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="overzichten" name="overzichten" type="hidden" />';
        return($html);
    }
    function MaakOverzicht()
    {
        $html = '';
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $this->boekjaar = $boekhouding->boekjaar;
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $this->rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"sort"=>"rekeningnummer ASC"));
        #
        # lees boekingen in lopend boekjaar
        #
        $this->table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        #$filter = 'datum >= "' . strval($this->boekjaar) .'-01-01" and datum <= "' . strval($this->boekjaar).'-12-31"';
        #echo '<br>filter:' . $filter;
        #$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"filter"=>$filter));
		$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar)));  # filter alleen op jaartal
		$this->vorigeboekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar-1)));  # filter alleen op jaartal
        $this->table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        $this->vorigebalans = $dbio->ReadRecords(array("table"=>$this->table_balans,"prefilter"=>array("boekjaar"=>$this->boekjaar-1)));
		$this->table_begroting = Dbtables::begroting['name']."_".$_SESSION['code'];
        $this->begroting = $dbio->ReadRecords(array("table"=>$this->table_begroting,"prefilter"=>array("boekjaar"=>$this->boekjaar)));
		switch ($_POST['overzicht'])
        {
            case "balans":
            $html .= $this->DisplayBalans();
			break;

			case "result":
			$html .= $this->DisplayResult();
			break;

			case "grootboek":
			$html .= $this->DisplayGrootboek();
			break;

			case "formbtw":
			$btw = new BTW();
			$html .= $btw->FormBTW();
			break;
        }
		$html .= '<input id="overzichten" name="overzichten" type="hidden" />';
		$html .= '<input id="maakoverzicht" name="maakoverzicht" type="hidden" />';
        return($html);
    }
    function DisplayBalans()
	{
		$form = new Forms();
		$html='';
		$html .= '<h2>balans_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';
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
		$filename = 'balans_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="overzicht" name="overzicht" value="balans" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
    protected function BerekenBalans()
	{
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
					$bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
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
    protected function PlusOrMin($btype,$rekening,$vanrekening,$naarrekening,$psoort,$ptype,$bedrag)
	{
		$van=$naar=0;
		if($rekening == $vanrekening) { $van = 1; }
		if($rekening == $naarrekening) { $naar = 1; }
		if(	($btype == 'C' && $van  && $psoort == 'B' && $ptype == 'D') || 
			($btype == 'C' && $naar && $psoort == 'B' && $ptype == 'C') ||
			($btype == 'C' && $naar && $psoort == 'R' && $ptype == 'C') ||
			($btype == 'D' && $van && $psoort == 'B' && $ptype == 'C') ||
			($btype == 'D' && $naar && $psoort == 'B' && $ptype == 'D') ||
			($btype == 'D' && $naar && $psoort == 'R' && $ptype == 'D') ) { return ($bedrag);}
		if(	($btype == 'C' && $van  && $psoort == 'B' && $ptype == 'C') || 
			($btype == 'C' && $naar && $psoort == 'B' && $ptype == 'D') ||
			($btype == 'C' && $naar && $psoort == 'R' && $ptype == 'D') ||
			($btype == 'D' && $van && $psoort == 'B' && $ptype == 'D') ||
			($btype == 'D' && $naar && $psoort == 'B' && $ptype == 'C') ||
			($btype == 'D' && $naar && $psoort == 'R' && $ptype == 'C') ) { return(-$bedrag);}
		return(0);
	}
	protected function DisplayResult()
	{
		$form = new Forms();
		$html = '';
		$html .= '<h2>resultatenrekening_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';
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
		$filename = 'resultatenrekening_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="overzicht" name="overzicht" value="result" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	protected function BerekenResult()
	{
		
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
					$bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
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
					$bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
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
	/**
	 * Grootboek tonen
	 */
	protected function DisplayGrootboek()
	{
		$form = new Forms();
		$html = '';
		$html .= '<h2>grootboek_' . $_SESSION['code'] . '_' . $this->boekjaar . '</h2>';
		$grootboek=$this->BerekenGrootboek();
		$maxrow=count($grootboek);
		$html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
		for ($col=0; $col < 7; $col++)
		{
			if(!isset($grootboek[0][$col])) { $grootboek[0][$col] = ''; }
			$html .= '<th class="compactth">' . $grootboek[0][$col] . '</th>';
		}
		$tdclass = "compacttdeven";
		$tdclassright = "compacttdevenright";
		for($row=1; $row<=$maxrow; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < 7; $col++)
				{
					if(!isset($grootboek[$row][$col])) { $grootboek[$row][$col] = ''; }
				}
				$html .= '<td class="' . $tdclassright . '">' . $grootboek[$row][0] . '</td>';
				$html .= '<td class="' . $tdclassright . '">' . $grootboek[$row][1] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $grootboek[$row][2] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $grootboek[$row][3] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $grootboek[$row][4] . '</td>';
				$html .= '<td class="' . $tdclass . '">' . $grootboek[$row][5] . '</td>';
				$html .= '<td class="' . $tdclassright . '">' . $grootboek[$row][6] . '</td>';
				$html .= '</tr>';
				if($grootboek[$row][2] == 'totaal')
				{ 
					if($tdclass == 'compacttdeven') { $tdclass = "compacttdodd"; $tdclassright = "compacttdoddright";}
					else { $tdclass = "compacttdeven"; $tdclassright = "compacttdevenright";}
				}
		}
		$html .= '</table>';
		$filename = 'grootboek_' . $_SESSION['code'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="overzicht" name="overzicht" value="grootboek" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	protected function BerekenGrootboek()
	{
		$grootboek[0][0] = 'vnr';
		$grootboek[0][1] = 'rkn';
		$grootboek[0][2] = 'naam';
		$grootboek[0][3] = 'datum';
		$grootboek[0][4] = 'bknr';
		$grootboek[0][5] = 'oms';
		$grootboek[0][6] = 'bedrag';
		foreach ($this->vorigebalans as $p) { $balansvorigjaar[$p->rekeningnummer] = $p->bedrag; }
		$row=0;
		foreach ($this->rekeningen as $p)
		{
			if($p->soort == 'B' && $p->type == 'C') {$soort = "passiva"; }
			if($p->soort == 'B' && $p->type == 'D') {$soort = "activa"; }
			if($p->soort == 'R' && $p->type == 'C') {$soort = "baten"; }
			if($p->soort == 'R' && $p->type == 'D') {$soort = "kosten"; }
			$totaal=0;
			if($p->soort == 'B') 
			{
				if(isset($balansvorigjaar[$p->rekeningnummer])) { $totaal=$balansvorigjaar[$p->rekeningnummer]; }
				else { $total = 0; }
			}
			$row++;
			$grootboek[$row][1] = $p->rekeningnummer;
			$grootboek[$row][2] = $p->naam;
			$grootboek[$row][5] = 'beginbalans';
			$grootboek[$row][6] = number_format($totaal/100,2,',','');
			foreach ($this->boekingen as $b)
			{
				$bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
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
	
}
?>