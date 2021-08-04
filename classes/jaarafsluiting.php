<?php
namespace SIMPELBOEK;
#
# Beginbalans opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Jaarafsluiting extends Overzichten
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        if(isset($_POST['writejaarafsluiting']))    # de balans is ingevuld, nu vewerken
        {
            return($this->WriteJaarafsluiting());
        }
        else
        {
            return($this->FormJaarafsluiting());         # de balans aanmaken
        }
    }
    function FormJaarafsluiting()
    {
        $dbio = new DBIO();
        $form = new forms();
        $overzicht = new Overzicht();
		$html = '';
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        $html .= '<h2>' . __("Boekjaar","prana") . ' ' . $boekjaar . ' ' . __("afsluiten","prana") . '</h2>';
        $html .= __("Zorg er voor dat alle overzichten mbt het huidige boekjaar zijn gemaakt","prana");
        //
        // Controleren of de jaarafsluiting kan plaats vinden:
        //
        // Zijn er nog open boekingen (boekingen zonder tegenrekening
        // Deze moeten eerste verwerkt worden.
        $error = '';
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
		$openboekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"filter"=>"tegenrekening=''","prefilter"=>array("datum"=>$boekjaar)));
        $aantalopenboekingen = count($openboekingen);
        if($aantalopenboekingen > 0)
        {
            $error .= '<br>' . sprintf (__("Eerst bankmutaties verwerken, Er zijn nog %d boekingen die nog niet zijn verwerkt","prana"), $aantalopenboekingen);
        }
        //
        // Zijn er rekeningnummers vastgelegs voor winst, verlies en kapitaal
        //
        if(!$boekhouding->verliesrekening || !$boekhouding->winstrekening || !$boekhouding->kapitaalrekening)
        {
            $error .= '<br>' . __("Voor het wegboeken van verlies of winst moeten de verliesrekening, winstrekening en kapitaalrekening worden vastgelegd","prana");
            $error .= '<br>' . __("Dat kan onder: boekhoudingen->overzicht->wijzigen","prana");
        }
        if($error) 
        { 
            return('<div class="isa_error">' . $error . '</div>');
        }
        # lees de balansrekeningen
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $balansrekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"B")));
        # lees alle boekingen in lopend boekjaar
		$table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"prefilter"=>array("datum"=>$boekjaar)));  # filter alleen op jaartal
        //
        // wat is het resultaat in dit boekjaar?
        //
        [$balans,$nd,$nd,$nt] = $overzicht->Balans();
        echo '<br>balans $nt='.$nt.'<br>';
		print_r($balans);
        $nieuwebalans = $balans;
        #echo '<br>na copy';
        #print_r($nieuwebalans);
        //
        // nieuwe balans maken
        //
        // wins of verlies vorig jaar wegboeken
        //
        $rekening=0;
        if($balans[$boekhouding->verliesrekening]) { $rekening = $boekhouding->verliesrekening; }
        if($balans[$boekhouding->winstrekening]) { $rekening = $boekhouding->winstrekening; }
        if($rekening)
        {
            $r = $dbio->ReadUniqueRecord(array("table"=>$table_rekeningen,"key"=>"rekeningnummer","value"=>$rekening));
            $k = $dbio->ReadUniqueRecord(array("table"=>$table_rekeningen,"key"=>"rekeningnummer","value"=>$boekhouding->kapitaalrekening));
            $nieuwebalans = $overzicht->CopyBalansPost($nieuwebalans,$r,$k);
            $euro = number_format((abs($balans[$r->rekeningnummer]) /100), 2, ',', '');
            $html .= '<br>' . sprintf(__('rekening %s(%s) van %s wordt afgeboekt op rekening: %s(%s)','prana'),$r->naam,$r->rekeningnummer,$euro,$k->naam,$k->rekeningnummer); 
        }
        #echo '<br>na verlies';
        #print_r($nieuwebalans);
        //
        // winst / verlies wegboeken
        //
        $resultaat=$overzicht->Result();
		$euro = number_format((abs($resultaat) /100), 2, ',', '');
        if($resultaat < 0) { $wv = __('verlies','prana'); $rekening = $boekhouding->verliesrekening; }
        if($resultaat > 0) { $wv = __('winst','prana'); $rekening = $boekhouding->winstrekening; }
        if($resultaat)
        { 
            $r = $dbio->ReadUniqueRecord(array("table"=>$table_rekeningen,"key"=>"rekeningnummer","value"=>$rekening));
            $nieuwebalans = $overzicht->AddBalans($nieuwebalans,$r,$resultaat);
            $html .= '<br>' . sprintf(__('%s van %s wordt geboekt op rekening: %s %s','prana'),$wv,$euro,$rekening,$r->naam); 
        }
        $html .= '<br>'. __('Hieronder het overzicht van de nieuwe beginbalans voor het komende jaar') . '<br>';
        $totaal = $overzicht->array_merge_recursive_two($balans,$nieuwebalans);
        $table=$overzicht->TotalenTabel($totaal);
        $headers = ['rnr','rekening','debet ' . $boekjaar , 'debet nieuwe beginbalans' ,'rnr','rekening','credit ' . $boekjaar , 'credit nieuwe beginbalans'];
        $nieuwjaar = $boekjaar+1;
        $colinfo = array(
			
			array("rkn","string"),
			array("rekening","string"),
			array("debet ".$boekjaar,"euro"),
			array("debet " . $nieuwjaar,"euro"),
            array("",""),
			array("rkn","string"),
			array("rekening","string"),
            array("credit ".$boekjaar,"euro"),
			array("credit " . $nieuwjaar,"euro"),
		);
        $html .= $this->DisplayTabel($table,$colinfo);
        $html .= '<br>';
        $form->buttons = [
            ['id'=>'writejaarafsluiting','value'=>__( 'Jaar afsluiten (er is geen weg meer terug', 'prana' )],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        //print_r($table);
        $posttable = base64_encode(json_encode($nieuwebalans)); // Client side
        $html .='<input id="jaarafsluiting" name="jaarafsluiting" type="hidden" />';
        $html .='<input id="balans" name="balans" type="hidden" value="' . $posttable . '">';
		return($html);
    }
    /*
    function PrintBalans(array $table,array $headers) : string
    {
        $html = '';
        $overzicht = new Overzicht();
        $html .= '<table class="compacttable">';
		$html .= '<tr class="compacttr">';
        // koppen van tabel maken
		for ($col=0; $col < 8; $col++)
		{
			if($col == 2 || $col == 3 || $col ==6 || $col==7) { $html .= '<th class="compactthright">' . $headers[$col] . '</th>'; }
			else { $html .= '<th class="compactth">' . $headers[$col] . '</th>'; }
			if($col == 3) { $html .= '<th class="compactth">&nbsp;&nbsp;</th>'; }
		}
		for($row=1; $row<=count($table); $row++)
		{
				$html .= '<tr class="compacttr">';
				for ($col=0; $col < 8; $col++)
				{
					if(!isset($table[$row][$col])) { $table[$row][$col] = ''; }
                    // bedragen rechts aansluiten in in euro notatie
					if($col == 2 || $col == 3 || $col ==6 || $col==7) { $html .= '<td class="compacttdright">' . $overzicht->Euro($table[$row][$col]) . '</td>'; }
					else { $html .= '<td class="compacttd">' . $table[$row][$col] . '</td>'; }
					if($col == 3) { $html .= '<td class="compacttd">&nbsp;&nbsp;</td>'; }
				}
				$html .= '</tr>';
		}
		$html .= '</table>';
        return($html);
    }
    */
    function WriteJaarafsluiting()
    {
        $html = '';
		$dbio = new DBIO();
        $overzicht = new Overzicht();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        $balanstable = Dbtables::balans['name']."_".$_SESSION['code'];
		//
		// hoe komt resultaat op de balans??
		//
        $balans = json_decode(base64_decode($_POST['balans'])); // Server side
        foreach($balans as $rekening => $bedrag)
        {
            $fields = array();
            $fields += ['rekeningnummer'=>$rekening];
            $fields += ['bedrag'=>$bedrag];
            $fields += ['boekjaar'=>$boekjaar];
            // nieuwe balans naar balanstabel
            $id=$dbio->CreateRecord(array("table"=>$balanstable,"fields"=>$fields));
        }
        // boekjaar ophogen
        $dbio->ModifyRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code'],"fields"=>["boekjaar"=>$boekjaar+1]));
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $html .= '<h2>'.sprintf(__('Het boekjaar is nu %d, er is een nieuwe balans aangemaakt',"prana"),$boekhouding->boekjaar) . '</h2>';
        return($html);
	}
}
?>