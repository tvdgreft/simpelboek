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
		$this->boekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar)));  # filter alleen op jaartal
		$this->vorigeboekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"prefilter"=>array("datum"=>$this->boekjaar-1)));  # filter alleen op jaartal
        $this->table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        $this->vorigebalans = $dbio->ReadRecords(array("table"=>$this->table_balans,"prefilter"=>array("boekjaar"=>$this->boekjaar-1)));
		$this->table_begroting = Dbtables::begroting['name']."_".$_SESSION['code'];
        $this->begroting = $dbio->ReadRecords(array("table"=>$this->table_begroting,"prefilter"=>array("boekjaar"=>$this->boekjaar)));
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
    function DisplayTable($table)
	{
		$html='';
		$rows = count($table);
		$cols = count($table[0]);
		$html .= '<table class="compacttable">';
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
}