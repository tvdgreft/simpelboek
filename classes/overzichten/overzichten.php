<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Overzichten
{
    public $boekjaar;
    public $rekeningen;   #rekeningschema
	public $table_boekingen;	# tabelnaam met boekingen.
    public $boekingen;    #boekingen in lopend boekjaar
    public $vorigebalans;  # balans vorig jaar;
	public $begroting;		# begroting lopend jaar
    public $btwtarieven; # Welke BTW percentages zijn er.
	public function LoadData()
    {
        $html = '';
        $dbio = new DBIO();
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $this->boekjaar = $boekhouding->boekjaar;
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $this->rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"sort"=>"rekeningnummer ASC"));
        $this->btwtarieven = $dbio->DistinctRecords(array("table"=>$this->table_rekeningen,"column"=>"btwpercentage"));
        #
        # lees boekingen in lopend boekjaar
        #
        $this->table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        #$filter = 'datum >= "' . strval($this->boekjaar) .'-01-01" and datum <= "' . strval($this->boekjaar).'-12-31"';
        #echo '<br>filter:' . $filter;
        #$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"filter"=>$filter));
		#$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar)));  # filter alleen op jaartal
		$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"filter"=>"tegenrekening!=''","prefilter"=>array("datum"=>$this->boekjaar)));
		$this->vorigeboekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar-1)));  # filter alleen op jaartal
        $this->table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        $this->vorigebalans = $dbio->ReadRecords(array("table"=>$this->table_balans,"prefilter"=>array("boekjaar"=>$this->boekjaar-1)));
		$this->table_begroting = Dbtables::begroting['name']."_".$_SESSION['code'];
        $this->begroting = $dbio->ReadRecords(array("table"=>$this->table_begroting,"prefilter"=>array("boekjaar"=>$this->boekjaar)));
    }
    /*
	function DisplayTable(array $table) : string
	{
		$html='';
		$rows = count($table);
		$cols = count($table[0]);
		$html .= '<table class="prana">';
		$html .= '<tr class="compacttr">';
		for ($col=0; $col < $cols; $col++)
		{
			if(!isset($table[0][$col])) { $table[0][$col] = ''; }
			$html .= '<th class="compactth">' . $table[0][$col] . '</th>';
		}
		$tdclass = "compacttdeven";
		for($row=1; $row<=$rows; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col <$cols; $col++)
				{
					if(!isset($table[$row][$col])) { $table[$row][$col] = ''; }
					$html .= '<td class="' . $tdclass . '">' . $table[$row][$col] . '</td>';
				}
		}
		$html .= '</table>';
		return($html);
	}
	*/
	/**
	 * DisplayTabel
	 * converteert tabel naar HTML output
	 * table{row][col]
	 * headers[01,....] = header kolom
	 * headers[0,01......] type: string, date, number, euro (eurocents)
	 */
	function DisplayTabel(array $table,array $headers) : string
	{
		$html='';
		#print_r($table);
		$rows = count($table);
		#echo '<br>rows:'.$rows;
		$cols = count($headers);
		$html .= '<table class="compacttable">';
		$html .= '<tr>';
		for ($col=0; $col < $cols; $col++)
		{
			$thclass = "compactth";
			$type = $headers[$col][1] ? $headers[$col][1] : "string";	// default type is string
			if($type == "number" || $type == "euro") {$thclass = "compactthright"; }	// getallen rechts aansluiten
			$html .= '<th class="' . $thclass . '">' . $headers[$col][0] . '</th>';
		}
		for($row=1; $row<=$rows; $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col <$cols; $col++)
				{
					$tdclass = "compacttd";
					$type = $headers[$col][1] ? $headers[$col][1] : "string";	// default type is string
					if($type == "number" || $type == "euro") {$tdclass = "compacttdright"; }	// getallen rechts aansluiten
					$cel = '';
					if(isset($table[$row][$col]))
					{
						$cel = $table[$row][$col];
						if($type == "euro") 	{ $cel = $this->Euro($table[$row][$col]); }
						else 					{ $cel = $table[$row][$col];}
					}
					$html .= '<td class="' . $tdclass . '">' . $cel . '</td>';
				}
		}
		$html .= '</table>';
		return($html);
	}
	/**
     *  converteert centen naar euro notatie (vb: 34560 = 345,60)
     */
    function Euro(string $cents) : string
    {
        $html = '';
        if($cents == '') { return(''); }
        $euro = number_format((abs($cents) /100), 2, ',', '.');
        if($cents < 0) {$html .= '-';}
        $html .= $euro;
        return($html);
    }
}
/*
#
    # Print de balans
    #
    function PrintTabel(array $table,array $headers) : string
    {
        $html = '';
        $html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
        $cols = count($headers);
        $midcol = $cols/2;
        // koppen van tabel maken
		for ($col=0; $col < $cols; $col++)
		{
			if($headers[$col][1] == "right") { $html .= '<th class="compactthright">' . $headers[$col][0] . '</th>'; }
			else { $html .= '<th class="compactth">' . $headers[$col][0]. '</th>'; }
			if($col == $midcol-1) { $html .= '<th class="compactth">&nbsp;&nbsp;</th>'; }
		}
		for($row=1; $row<=count($table); $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < $cols; $col++)
				{
					if(!isset($table[$row][$col])) { $table[$row][$col] = ''; }
                    // bedragen rechts aansluiten in in euro notatie
					if($headers[$col][1] == "right") { $html .= '<td class="compacttdright">' . $this->Euro($table[$row][$col]) . '</td>'; }
					else { $html .= '<td class="compacttd">' . $table[$row][$col] . '</td>'; }
					if($col == $midcol-1) { $html .= '<td class="compacttd">&nbsp;&nbsp;</td>'; }
				}
				$html .= '</tr>';
		}
		$html .= '</table>';
        return($html);
*/