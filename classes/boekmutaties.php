<?php
namespace SIMPELBOEK;
/**
 * boekingen van mutatiebestanden banken verwerken
 * tegenrekeningen opgeven
 */
class BoekMutaties
{
    public $boekjaar;
    public $boekingen;
	public function Start()        
	{
        #
        # boekjaar inlezen
        #
        $dbio = new DBIO();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $this->boekjaar = $boekhouding->boekjaar;
        #
        # boekingen waarvan tegenrekening nog niet is opgegeven
        #
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
		$this->boekingen = $dbio->ReadRecords(array("table"=>$table_boekingen,"filter"=>"tegenrekening=''","prefilter"=>array("datum"=>$this->boekjaar)));
        if(isset($_POST['writeboekingen']))    # 
        {
            return($this->WriteBoekingen());
        }
        else   # formulier om bestand te zoeken met bankmutaties
        {
            return($this->FormOpenBoekingen());
        }
    }
    #
    # Toon tabel met open boekingen
    # @todo: btw bedrag invullen.
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
        # array van tegenrekeningen
        #
        $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"sort"=>"rekeningnummer ASC"));
        $options = '';
		$options .= '<option value="" selected>' . __('selecteer tegenrekening','prana') . '</option>';
        foreach ($rekeningen as $r)
        {
            $naam = sprintf("%s %03d %s %s",$r->naam,$r->rekeningnummer,$r->soort,$r->type);
            $options .= '<option value=' . $r->rekeningnummer . '>' . $naam . '</option>';
        }
        $html .= '<table id="boekingen" class="prana">';
        $html .= '<tr>';
        $html .= '<th>datum</th><th>bedrag</th><th>type</th><th>rekeninghouder</th><th>omschrijving</th><th>tegenrekening</th>';
        $html .= '</tr>';
        $afbij = array("D"=>__("AF","prana"),"C"=>__("BIJ","prana"));
        foreach($this->boekingen as $b)
        {
            $html .= '<tr>';
            $html .= '<td>'. $b->datum . '</td>';
            $bedrag = number_format(($b->bedrag /100), 2, ',', '');
            $html .= '<td>'. $bedrag . '</td>';
            
            $html .= '<td>'. $afbij[$b->type] . '</td>';
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
        $html .= '<br>';
        $form->buttons = [
            ['id'=>'writeboekingen','value'=>__( 'opslaan', 'prana' )],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="boekmutaties" name="boekmutaties" type="hidden" />';
        return($html);
    }
    function WriteBoekingen()
    {
        $dbio = new DBIO();
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $html = '';
        $openboekingen = 0;  #tel het aantal boekingen zonder tegenrekening
        foreach($this->boekingen as $b)
        {
            if($_POST[$b->id])
            {
                # plaats tegenreking in boeking
                $result = $dbio->UpdateRecord(array("table"=>$table_boekingen,"where"=>array("id"=>$b->id),"fields"=>array("tegenrekening"=>$_POST[$b->id])));
                $bedrag = number_format(($b->bedrag /100), 2, ',', '');
                $html .= '<br>' . __("boeking","prana") . ' ' . $b->id . 
                        ' ' . __("bedrag","prana") . ' ' . $bedrag . 
                        ' ' . __("tegenrekening","prana") . ' ' . $_POST[$b->id];
                if($result == false)
                {
                    $html .= ' ' . __("niet verwerkt","prana");
                    $openboekingen++;
                }
                else $html .= ' ' . __("verwerkt","prana");
            }
            else
            {
                $openboekingen++;
            }
        }
        if($openboekingen)
        {
            $html .= '<br><br>' . sprintf(__("Nog openstaande boekingen: %d","prana"),$openboekingen);
        }
        return($html);
    }
}
?>