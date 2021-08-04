<?php
namespace SIMPELBOEK;
#
# Beheer van de boekhoudingen
#
class Boekhoudingen extends Tableform
{
    protected $fields = array();
	public function Start()
	{

        $dbio = new dbio();
        $html='';
        $html .= '<h2>'. __("Overzicht van boekhoudingen","prana") . '</h2>';
        $this->single = "boekhouding";
        $this->plural = 'boekhoudingen';
        $this->class = "boekhoudingen";
		$this->table = Dbtables::boekhoudingen['name'];
        $this->columns= [
                                ["id","id","left"],         #table column name, columnname to be displayed, display orientation
                                ["code","code","left"],
                                ["naam","naam","left"],
                                ["boekjaar","boekjaar","left"],
                                ["kapitaalrekening","kapitaalrekening","left"],
                                ["winstrekening","winstrekening","left"],
                                ["verliesrekening","verliesrekening","left"],
                            ];
		$this->filtercolumns = array();                     #er hoeft niet gefilterd te worden
        $this->permissions = ["vw","cr","md"];
        $this->maxlines=20;
		$this->uid="id";	#the unique key
        $html .= $this->run(); # start or restart tableform
        #
        # Tabellen aanmaken als een nieuwe boekhouding is aangemaakt
        #
        if(isset($_POST['crmod']) && $_POST['crmod'] == "create")
        {
            $html .= "tabellen worden aangemaakt";
            $result = $dbio->CreateTable(Dbtables::rekeningen['name']."_".$_POST['code'],Dbtables::rekeningen['columns']);
            $result = $dbio->CreateTable(Dbtables::balans['name']."_".$_POST['code'],Dbtables::balans['columns']);
            $result = $dbio->CreateTable(Dbtables::begroting['name']."_".$_POST['code'],Dbtables::begroting['columns']);
            $result = $dbio->CreateTable(Dbtables::boekingen['name']."_".$_POST['code'],Dbtables::boekingen['columns']);
            if($result === FALSE) 
            { 
                $html .= '<div class="isa_error">' . __( 'Fout bij aanmaken boekhouding', 'prana' ) . '</div>';
                return($html);
            }
        }
        $html .='<input id="menu" name="menu" value="boekhoudingen" type="hidden" />';
        return($html);
    }
    #
    # maak formulier voor het invoeren van de record data
    # $crmod = "create" of "modify"
    #
    public function FormTable($crmod)
	{
        $form = new Forms();
        $dbio = new Dbio();
        if($crmod == "create") { unset($_SESSION['code']); } // Bij create lopende boekhouding afslkuite
        #
        # balansrekeningen
        #
        $rekeningen = 0;
        if(isset($_SESSION['code']))
        {
            $table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
            $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"prefilter"=>array("soort"=>"B"),"sort"=>"rekeningnummer ASC"));
            $options = array();
            foreach ($rekeningen as $r)
            {
                $naam = sprintf("%03d %s %s",$r->rekeningnummer,$r->type,$r->naam);
                $options += [$naam=>$r->rekeningnummer];
            }
        }
        $html = '';
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>__( 'ID', 'prana' ), "id"=>"id", "value"=>$this->fields['id'], "width"=>"100px;", "readonly"=>TRUE));
        }
        $html .= $form->Text(array("label"=>__( 'Code', 'prana' ), "id"=>"code", "value"=>$this->fields['code'], "width"=>"100px;"));
        $html .= $form->Text(array("label"=>__( 'Naam', 'prana' ), "id"=>"naam", "value"=>$this->fields['naam'], "width"=>"300px;"));
        $html .= $form->Text(array("label"=>__( 'Huidige boekjaar', 'prana' ), "id"=>"boekjaar", "type"=>"number","value"=>$this->fields['boekjaar'],"width"=>"100px;"));
        // Als rekeningschema is ingevuld kunnen eventueel de rekeningen voor het afboeken van verlies en winst worden aangemaakt
        // Bij de jaarafrekening wordt gekeken of dat is gebeurd.
        if($rekeningen)
        {
            $html .= $form->Dropdown(array("label"=>__("Rekening kapitaal","prana"),"id"=>"kapitaalrekening","options"=>$options, "value"=>$this->fields['kapitaalrekening'],"required"=>FALSE));
            $html .= $form->Dropdown(array("label"=>__("Rekening winst vorig jaar","prana"),"id"=>"winstrekening","options"=>$options, "value"=>$this->fields['winstrekening'],"required"=>FALSE));
            $html .= $form->Dropdown(array("label"=>__("Rekening verlies vorig jaar","prana"),"id"=>"verliesrekening","options"=>$options, "value"=>$this->fields['verliesrekening'],"required"=>FALSE));
        }
        $form->buttons = [
                            ['id'=>'writerecord','value'=>__( 'opslaan', 'prana' ), "onclick"=>"buttonclicked='boekhouding'"],
                            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        $html .='<input id="boekhoudingen" name="boekhoudingen" type="hidden" />';
        $html .='<input id="menu" name="menu" "value="boekhoudingen" type="hidden" />';
        return($html);
    }
}
?>