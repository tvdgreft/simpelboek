<?php
namespace SIMPELBOEK;
class Overzicht
{
    /**
     * Functies t.b.v. overzichten
     *
     * @copyright 2021 pranamas
     */
    function PlusOrMin($btype,$rekening,$vanrekening,$naarrekening,$psoort,$ptype,$bedrag) : int
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
    /**
     * controleer of alle boekingen kloppen (rekeningnummers bekend?)
     */
    /**
     * De huidige balans bepalen
     */
    function Balans() : array
    {
        $dbio = new DBIO();
        // Beginbalans inlezen
        $table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        // lees de balansrekeningen
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $balansrekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"B"),"sort"=>"rekeningnummer"));
        // lees alle boekingen in lopend boekjaar
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"prefilter"=>array("datum"=>$boekjaar)));  # filter alleen op jaartal
        // bedragen berekenen die aan de beginbalans toegevoegd moeten worden.
        $balans = array();
        foreach ($balansrekeningen as $p)
        {
            $beginbalans = $dbio->ReadRecords(array("table"=>$table_balans,"prefilter"=>array("boekjaar"=>$boekjaar-1,"rekeningnummer"=>$p->rekeningnummer)));
            if($beginbalans == NULL) { $balans[$p->rekeningnummer] = 0;}
            else { $balans[$p->rekeningnummer] = (int)$beginbalans[0]->bedrag; }
            foreach ($boekingen as $b)
            {
                $bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
                $balans[$p->rekeningnummer] += $bedrag;
            }
        }
        // totalen berekenen
        $totalc = $totald = 0;
        foreach ($balansrekeningen as $p)
        {
            if($p->type == 'C') { $totalc += $balans[$p->rekeningnummer]; }
            if($p->type == 'D') { $totald += $balans[$p->rekeningnummer]; }
        }
        $total = $totald - $totalc;
        return([$balans,$totalc,$totald,$total]);
    }
     /**
     * De balans van een willekeurig ander jaar
     * Zorg er voor dat alle huidige balansposten er ook bij staan.
     * Als ze niet voorkomen voeg ze toe met bedrag 0
     */
    function OudeBalans(int $boekjaar) : array
    {
        $dbio = new DBIO();
        // Beginbalans inlezen
        $table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        $balansposten = $dbio->ReadRecords(array("table"=>$table_balans,"prefilter"=>array("boekjaar"=>$boekjaar)));
        // bedragen berekenen die aan de beginbalans toegevoegd moeten worden.
        $balans = array();
        $total =$totalc = $totald = 0;
        foreach ($balansposten as $b)
        {
            $p = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"key"=>"rekeningnummer","value"=>$b->rekeningnummer));
            $balans[$p->rekeningnummer] = $b->bedrag;
            if($p->type == 'C' && $b->bedrag) { $total += $b->bedrag; $totalc += $b->bedrag; }
			if($p->type == 'D' && $b->bedrag) { $total -= $b->bedrag; $totald += $b->bedrag; }
        }
        // Zorg er voor dat alle huidige balansposten er ook bij staan.
        // Als ze niet voorkomen voeg ze toe met bedrag 0
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $balansrekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"B"),"sort"=>"rekeningnummer"));
        foreach($balansrekeningen as $p)
        {
            if(!isset($balans[$p->rekeningnummer])) { $balans[$p->rekeningnummer] = 0; }
        }

        return([$balans,$totalc,$totald,$total]);
    }
    /**
     * De huidige resultatenrekening bepalen
     */
    function Resultaten(int $boekjaar) : array
    {
        $dbio = new DBIO();
        // lees de resultaatrekeningen
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $resultaatrekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"R"),"sort"=>"rekeningnummer"));
        // lees alle boekingen in lopend boekjaar
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"prefilter"=>array("datum"=>$boekjaar)));  # filter alleen op jaartal
        // bedragen berekenen die aan de beginbalans toegevoegd moeten worden.
        $resultaat = array();
        $result =$resultc = $resultd = 0;
        foreach ($resultaatrekeningen as $p)
        {
            $resultaat[$p->rekeningnummer]=0;
            foreach ($boekingen as $b)
            {
                $bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
                if(!$bedrag) { continue; }
                $resultaat[$p->rekeningnummer] += $bedrag;
                if($p->type == 'C') { $result += $bedrag; $resultc += $bedrag; }
				elseif($p->type == 'D') { $result -= $bedrag; $resultd += $bedrag; }
                else { echo '<br>mis:'.$p->rekeningnummer.'bedrag'.$bedrag;}
            }
        }
        return([$resultaat,$resultc,$resultd,$result]);
    }
     /**
     * De begroting bepalen
     */
    function Begroting(int $boekjaar) : array
    {
        $dbio = new DBIO();
        // Beginbalans inlezen
        $table_begroting = Dbtables::begroting['name']."_".$_SESSION['code'];
        $begroting = $dbio->ReadRecords(array("table"=>$table_begroting,"prefilter"=>array("boekjaar"=>$boekjaar)));
        // bedragen berekenen die aan de beginbalans toegevoegd moeten worden.
        $resultaat = array();
        $result =$resultc = $resultd = 0;
        foreach ($begroting as $b)
        {
            $p = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"key"=>"rekeningnummer","value"=>$b->rekeningnummer));
            $resultaat[$p->rekeningnummer] = $b->bedrag;
            if($p->type == 'C' && $b->bedrag) { $result += $b->bedrag; $resultc += $b->bedrag; }
			if($p->type == 'D' && $b->bedrag) { $result -= $b->bedrag; $resultd += $b->bedrag; }
        }
        return([$resultaat,$resultc,$resultd,$result]);
    }
    /**
     *  Bereken resultaat van lopen boekjaar.
     */
    function Result() : int
    {
        //
        // wat is het resultaat in dit boekjaar?
        //
        $dbio = new DBIO();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        // lees de balansrekeningen
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $balansrekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"B")));
        // lees alle boekingen in lopend boekjaar
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"prefilter"=>array("datum"=>$boekjaar)));  # filter alleen op jaartal
        $result=0;
        foreach ($balansrekeningen as $p)
        {
            foreach ($boekingen as $b)
            {
                $bedrag = $this->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
                if($p->type == 'C' && $bedrag) { $result -= $bedrag;}
                if($p->type == 'D' && $bedrag) { $result += $bedrag;}
            }
        }
        return($result);
    }
    /**
     *  CopyBalansPost
     * Kopieer een bedrag op de balans
     * $balans = balans (output SBK_Balans()
     * $van = rekeningnummer waarvandaanm
     * $naar = rekeningnummer naar toe
     */
    function CopyBalansPost(array $balans,object $van,object $naar ) : array
    {
        $bedrag = $balans[$van->rekeningnummer];
        #echo '<br>bedrag van:'.$van->rekeningnummer . '=' . $bedrag;
        if($van->type == $naar->type) { $balans[$naar->rekeningnummer] += $bedrag; }
        else { $balans[$naar->rekeningnummer] -= $bedrag; }
        $balans[$van->rekeningnummer] = 0;
        #echo '<br>bedrag naar:'.$naar->rekeningnummer . '=' . $balans[$naar->rekeningnummer];
        return($balans);
    }
    /**
     * SBK_AddBalans
     * voeg een bedrag toe op de balans
     * $balans = balans (output SBK_Balans()
     * $r = rekeningnummer naar toe
     * $bedrag = bedrag
     */
    function AddBalans(array $balans,object $r,string $bedrag ) : array
    {
        if($r->type == 'D' && $bedrag < 0) { $balans[$r->rekeningnummer] += abs($bedrag); }
        if($r->type == 'C' && $bedrag > 0) { $balans[$r->rekeningnummer] += abs($bedrag); }
        if($r->type == 'D' && $bedrag > 0) { $balans[$r->rekeningnummer] -= abs($bedrag); }
        if($r->type == 'C' && $bedrag < 0) { $balans[$r->rekeningnummer] -= abs($bedrag); }
        return($balans);
    }
    /**
     * Maak een tabel van een array van resultaat of balans arrays
     * totalen = array(array(rekenr=>totaal,rekenr=>total2 ...))
     * 
     */
    function TotalenTabel(array $totalen) : array
    {
        $dbio = new DBIO();
        $table = array();
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rowc=$rowd=0;
        foreach($totalen as $rekening=>$vals)
        {
            $r = $dbio->ReadUniqueRecord(array("table"=>$table_rekeningen,"key"=>"rekeningnummer","value"=>$rekening));
            // debet en credit uitsplitsen
            // debet komt links in de tabel en credit rechts.
            // berkenen de kolom waarr credit begint (lege kolom na debet)
            $ckol = count($vals) + 3;
            if($r->type == 'D') {$column = 0; $rowd++; $row=$rowd;}
            if($r->type == 'C') {$column = $ckol; $rowc++; $row=$rowc;}
            {
                $table[$row][$column] = $r->rekeningnummer; $column++;
                $table[$row][$column] = $r->naam; $column++;
                foreach($vals as $v)
                {
                    $table[$row][$column] = $v; $column++;
                }
            }
        }	
        return($table);
    }
    
    /*
    function BalansTable(array $balanses) : array
    {
        $dbio = new DBIO();
        $table = array();
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $balans = $this->array_merge_recursive_improved($balanses[0],$balanses[1]);
        $rowc=$rowd=0;
        foreach($balans as $rekening=>$vals)
        {
            #echo '<br>rekening='.$rekening;
            #echo '<br>';
            $r = $dbio->ReadUniqueRecord(array("table"=>$table_rekeningen,"key"=>"rekeningnummer","value"=>$rekening));
                // debet en credit uitsplitsen
            if($r->type == 'D') {$column = 0; $rowd++; $row=$rowd;}
            if($r->type == 'C') {$column = 4; $rowc++; $row=$rowc;}
            {
                $table[$row][$column] = $r->rekeningnummer; $column++;
                $table[$row][$column] = $r->naam; $column++;
                foreach($vals as $v)
                {
                    $table[$row][$column] = $v; $column++;
                }
            }
        }	
        return($table);
    }
    */
    /**
     *  In plaats van array_merge_recursive
     *  Die werkt niet bij numerieke keys.
     */
    // Adds a _ to top level keys of an array
    function prefixer($array) {
        $out = array();
        foreach($array as $k => $v) {
            $out['_' . $k] = $v;
        }
        return $out;
    }
    // Remove first character from all keys of an array
    function unprefixer($array) {
        $out = array();
        foreach($array as $k => $v) {
            $newkey = substr($k,1);
            $out[$newkey] = $v;
        }
        return $out;
    }
    // Combine 2 arrays and preserve the keys
    function array_merge_recursive_two($a, $b) {
        $a = $this->prefixer($a);
        $b = $this->prefixer($b);
        $out = $this->unprefixer(array_merge_recursive($a, $b));
        return $out;
    }
    /**
     * array_merge_recursive accepteert geen array met arrays.
     * Ik weet niet hoe dat om te zetten naar een lijst van argumenten
     * Dus voorlopig maar even een aparte functie voor elk aantal arrays.
     */
    function array_merge_recursive_three() {
        $arrays = func_get_args();
        foreach ($arrays as $array)
        {
            $newarrays[] = $this->prefixer($array);
        }
        $out = $this->unprefixer(array_merge_recursive($newarrays[0],$newarrays[1],$newarrays[2]));
        return $out;
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
    }
}