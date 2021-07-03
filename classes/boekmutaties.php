<?php
namespace SIMPELBOEK;
/**
 * boekingen van mutatiebestanden banken verwerken
 * tegenrekeningen opgeven
 */
class BoekMutaties
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        if(isset($_POST['writemutaties']))    # 
        {
            return($this->WriteBoekingen());
        }
        else   # formulier om bestand te zoeken met bankmutaties
        {
            return($this->FormOpenBoekingen());
        }
    }
    #
    # Welke bank en welk bestand?
    #
    function FormOpenBoekingen()
    {
        $dbio = new DBIO();
        $form = new forms();
        $html = '';
        $html .= '<h1>' . __('Bankmutaties verwerken (tegenrekeningen opgeven)', 'prana') . '</h1>';
        #
        # todo: popup voor toelichting
        #
        #
        # boekjaar inlezen
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        #
        # array van tegenrekeningen
        #
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"sort"=>"rekeningnummer ASC"));
        $options = '';
		$options .= '<option value="" selected>' . __('selecteer tegenrekening','prana') . '</option>';
        foreach ($rekeningen as $r)
        {
            $options .= '<option value=' . $r->rekeningnummer . '>' . $r->naam . '</option>';
        }
        #
        # boekingen waarvan tegenrekening nog niet is opgegeven
        #
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
		$boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"filter"=>"tegenrekening=''","prefilter"=>array("datum"=>$boekjaar)));
        $html .= '<table id="boekingen" class="compact-table">';
        $html .= '<tr>';
        $html .= '<th>datum</th><th>bedrag</th><th>tyoe</th>';
        $html .= '</tr>';
        foreach($boekingen as $b)
        {
            $html .= '<tr>';
            $html .= '<td>'. $b->datum . '</td>';
            $bedrag = number_format(($b->bedrag /100), 2, ',', '');
            $html .= '<td>'. $bedrag . '</td>';
            $html .= '<td>'. $b->type . '</td>';
            $html .= '<td>'. $b->bankrekeninghouder . '</td>';
            $html .= '<td>'. $b->omschrijving . '</td>';
            $html .= '<td>';
            $html .= '<select name=' . $b->id . ' style="width:250px;">';
			$html .= $options;
			$html .= '</select>';
            $html .= '</td>';
            $html .= '</tr>';

        }
        $html .= '</table>';
        /*
        $form->buttons = [
            ['id'=>'writemutaties','value'=>__( 'mutaties inlezen', 'prana' )],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="mutaties" name="mutaties" type="hidden" />';
        */
        return($html);
    }
    function WriteMutaties()
    {
        #
        # wat is het huidige boekjaar?
        #
        $dbio = new DBIO();
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;

        $html = '';
        if($_POST['bank'] == "triodos") { $mutaties=$this->Triodos(); }
        #
        # test of de mutaties kloppen
        #
        $line = 1;
        foreach ($mutaties as $m)
        {
            # Test de datum
            $d = preg_split("/-/",$m["datum"]);
			if(!checkdate($d[1],$d[2],$d[0])) { $html .= '<br>regel:' . $line . __(' foute datum: ','prana'). $m["datum"]; }
            if($d[0] != $boekjaar) { $html .= '<br>regel:' . $line . __(' valt buiten boekjaar: ','prana'). $m["datum"];}
            # test of banknummer bestaat
            $rekening = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"key"=>"bankrekening",'value'=>$m['banknr']));
            if(!$rekening) { $html .= '<br>regel:' . $line . __(' onbekende bankrekening: ','prana'). $m["banknr"];}
            if(!is_numeric($m["bedrag"])) { $html .= '<br>regel:' . $line . __(' foutief bedrag: ','prana'). $m["bedrag"];}
            if(!in_array($m["type"],array("D","C"))) { $html .= '<br>regel:' . $line . __(' debet/credit klopt niet: ','prana'). $m["type"];}
            #$html .= '<br>' . $m["datum"] . ' rekening=' . $rekening->rekeningnummer . ' bedrag'.$m["bedrag"];
            $line++;
        }
        if($html)
        {
            $html .= '<div class="isa_error">' . __('Bestand niet verwerkt','prana'). '</div>';
            return($html);
        }
        #
        # testen of records al zijn ingelezen
        if(isset($_POST['checkdouble']))
        {
            $line=1;
            foreach ($mutaties as $m)
            {
                $fields = $m;
                unset ($fields["banknr"]); # zit niet in boekingrecord
                $r = $dbio->ReadRecords(array("table"=>$table_boekingen,"filters"=>$fields));
                if(count($r) > 0)
                {
                    $euro=number_format(($m["bedrag"] /100), 2, ',', '');
                    $html .= '<br>regel:' . $line . __(' is al eens ingelezen: ','prana'). $m["datum"] . ' ' . __(' bedrag: ','prana'). $m["bedrag"];
                }
                $line++;
             }
             if($html)
            {
                $html .= '<div class="isa_error">' . __('Bestand niet verwerkt','prana'). '</div>';
                return($html);
            }
        }
        return;
        #
        # boekingen in database opslaan
        #
        foreach ($mutaties as $m)
        {
            $rekening = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"key"=>"bankrekening",'value'=>$m['banknr']));
            $fields = $m;
            unset ($fields["banknr"]); # zit niet in boekingrecord
            $fields += ["rekening"=>$rekening->rekeningnummer];
            $id=$dbio->CreateRecord(array("table"=>$table_boekingen,"fields"=>$fields));
            $euro=number_format(($fields["bedrag"] /100), 2, ',', '');
            $html.='<br>' .  __('Boeking ','prana') . $id . __(' bedrag ','prana') . $euro . __(' is aangemaakt','prana');
        }
        $html.= '<br>'. ($line-1) . __(' boekingen zijn ingelezen. Ze moeten nog wel verwerkt worden. (boek mutaties)','prana');
        return($html);
    }
    /**
     * Lees de Triodos mutaties
     * en zet ze in een array met mutaties
     * een mutatie bestaat uit de volgende elementen:
     * datum (y-m-d)
     * 
     */
    function Triodos()
    {
        $mutaties = array();
        $fp = fopen($_FILES['bestand']["tmp_name"],"rb");
		while(($line=fgets($fp)) !== false)
		{
            #echo "<br>".$line;
            $m=str_getcsv($line);
            $mutatie = array();
            $mutatie += ["banknr"=>$m[1]];
            $mutatie += ["datum"=>date_format(date_create($m[0]),"Y-m-d")];
			$bedrag = str_replace(',','', $m[2]);  // bedrag in centen
			$bedrag = str_replace('.','', $bedrag);  # . voor duizendtallen verwijderen
            $mutatie += ["bedrag"=>$bedrag];
            $type = "";
            if($m[3] == "Debet") { $type="D"; }
            if($m[3] == "Credit") { $type = "C"; }
            $mutatie += ["type"=>$type];
            $b = preg_split("/ /",$m[5]);
			$bankrekening = $b[1];
            $mutatie += ["bankrekening"=>$b[1]];
            $mutatie += ["bankrekeninghouder" => $m[4]];
            $mutatie += ["omschrijving"=>$m[7]];
            $mutaties[] = $mutatie;
            #echo "<br>".$line.'<br>mutatie<br>';
            #print_r($mutatie);
        }
        return($mutaties);
    }
    
}
?>