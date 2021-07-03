<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Begroting
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        $this->table = Dbtables::begroting['name']."_".$_SESSION['code'];
        if(isset($_POST['writebegroting']))    # de begroting is ingevuld, nu vewerken
        {
            return($this->WriteBegroting());
        }
        elseif(isset($_POST['formbegroting']))    # de begroting is ingevuld, nu vewerken
        {
            return($this->FormBegroting());         # de begroting aanmaken
        }
        else
        {
            return($this->FormBoekjaar());      # het boekjaar vragen
        }
    }
    #
    # Voor welk jaar moet een begroting worden aangemaakt?
    #
    function FormBoekjaar()
    {
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        $this->table = Dbtables::begroting['name']."_".$_SESSION['code'];
        $html = '';
        $html .= '<br><h2>'. __("Opstellen begroting","prana") . '  ' . __("huidige boekjaar is ","prana") . $boekjaar . '</h2>';
        $html .= $form->Text(array("label"=>__( 'voor welk boekjaar', 'prana' ), "id"=>"begrotingjaar", "type"=>"number" , "value"=>$boekjaar+1, "width"=>"100px;"));
        $form->buttons = [
            ['id'=>'formbegroting','value'=>__( 'begroting aanmaken', 'prana' )],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="begroting" name="begroting" type="hidden" />';
        return($html);
    }
    function FormBegroting()
    {
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $begrotingjaar = $_POST["begrotingjaar"];
        $vorigbegrotingjaar = $begrotingjaar-1;
        $this->table = Dbtables::begroting['name']."_".$_SESSION['code'];
        $html = '';
        $html .= '<h2>'. __("Opstellen begroting voor boekjaar ","prana") . $begrotingjaar . '</h2>';
        #
        # inlezen begroting rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"prefilter"=>array("soort"=>"R"),"sort"=>"type"));
        $html .= '<table id="begroting" class="compact-table">';
        $html .= '<tr>';
        $html .= '<th>rekening</th><th>soort</th><th>tyoe</th><th>' . $vorigbegrotingjaar . '</th><th>' . $begrotingjaar . '</th>';
        $html .= '</tr>';
        foreach($rekeningen as $r)
        {
            $begroting = $dbio->ReadRecords(array("table"=>$this->table,"prefilter"=>array("boekjaar"=>$begrotingjaar,"rekeningnummer"=>$r->rekeningnummer)));
            $bedrag = count($begroting) ? $begroting[0]->bedrag : "";
            $vorigebegroting = $dbio->ReadRecords(array("table"=>$this->table,"prefilter"=>array("boekjaar"=>$vorigbegrotingjaar,"rekeningnummer"=>$r->rekeningnummer)));
            $vorigbedrag = count($vorigebegroting) ? $vorigebegroting[0]->bedrag : "";
            $html .= '<tr>';
            $html .= '<td>'. $r->naam . '</td>';
            $html .= '<td>'. $r->soort . '</td>';
            $html .= '<td>'. $r->type . '</td>';
            $html .= '<td>'. $vorigbedrag . '</td>';
            $html .= '<td><input type="number" style="width:100px" id="' . $r->rekeningnummer . '" name="' . $r->rekeningnummer .'" value="' . $bedrag . '"></td>';
            $html .= '</tr>';

        }
        $html .= '</table>';
        #$html .= '<p id="totaalbalans" class="isa_error"></p>';
        $form->buttons = [
            ['id'=>'writebegroting','value'=>__( 'opslaan', 'prana' ), "onclick"=>"buttonclicked='maakbegroting'"],   #maakbalans zorgt voor valideren van input
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="begroting" name="begroting" type="hidden" />';
        $html .='<input id="begrotingjaar" name="begrotingjaar" value=' . $begrotingjaar . ' type="hidden" />';
        return($html);
    }
    #
    # Sla de begroting op in de databank
    #
    function WriteBegroting()
    {
        $dbio = new DBIO();
        $html = '';
        $begrotingjaar = $_POST["begrotingjaar"];
        $this->table = Dbtables::begroting['name']."_".$_SESSION['code'];
        $html .= '<h1>' . __('begroting wordt aangemaakt voor het jaar: ','prana') . $begrotingjaar . '</h1>';
        #
        # inlezen begroting rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"prefilter"=>array("soort"=>"B"),"sort"=>"type"));
        $dbio->DeleteRecord(array("table"=>$this->table,"key"=>"boekjaar","value"=>$begrotingjaar));    #delete all records and make new ones
        #
        # sla de begroting op in de databank.
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"prefilter"=>array("soort"=>"R"),"sort"=>"type"));
        foreach($rekeningen as $r)
        {
            $fields = array();
            if(isset($_POST[$r->rekeningnummer]))   #bedrag ingevuld in vorige stap
            {
                $fields += ['rekeningnummer'=>$r->rekeningnummer];
                $fields += ['bedrag'=>$_POST[$r->rekeningnummer]];
                $fields += ['boekjaar'=>$begrotingjaar];
            }
            $id=$dbio->CreateRecord(array("table"=>$this->table,"fields"=>$fields));
        }
        return($html);
    }
}
?>