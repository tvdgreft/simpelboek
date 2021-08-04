<?php
namespace SIMPELBOEK;
#
# Beheer van rekeningen
#
class Rekeningen extends Tableform
{
    protected $fields = array();
	public function Start()
	{
        $html='';
        $this->single = "rekening";
        $this->plural = 'rekeningen';
        $this->class = "rekeningen";
		$this->table = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $this->columns= [
                                ["id","id","string"],         #table column name, columnname to be displayed, display orientation
                                ["naam","naam","string"],
                                ["bankrekening","Bankrekening","string"],
                                ["rekeningnummer","rekeningnummer","string"],
                                ["soort","soort","string"],
                                ["type","type","string"]];
		$this->filtercolumns = array("soort"=>"soort","type"=>"type");
        $this->permissions = ["vw","cr","md","dl","dm"];
        $this->maxlines=20;
		$this->uid="id";	#the unique key
        #
        # demorecords laden?
        if(isset($_POST['demorecords']))
        {
            $this->LoadDemoRecords();
        }
        $html = $this->run(); # start or restart tableform
        return($html);
    }
    #
    # maak formulier voor het invoeren van de record data
    # $crmod = "create" of "modify"
    #
    public function FormTable($crmod)
	{
        $form = new Forms();
        $html = '';
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>__( 'ID', 'prana' ), "id"=>"id", "value"=>$this->fields['id'], "width"=>"100px;", "readonly"=>TRUE));
        }
        $html .= $form->Text(array("label"=>__( 'Naam', 'prana' ), "id"=>"naam", "value"=>$this->fields['naam'], "width"=>"300px;"));
        $html .= $form->Text(array("label"=>__( 'Bankrekening', 'prana' ), "id"=>"bankrekening", "value"=>$this->fields['bankrekening'], "width"=>"300px;","required"=>FALSE));
        $html .= $form->Text(array("label"=>__( 'Rekeningnummer', 'prana' ), "id"=>"rekeningnummer", "value"=>$this->fields['rekeningnummer'],"width"=>"100px;","required"=>FALSE));
        $options = array("Balans"=>"B","Resultaat"=>"R");
        $html .= $form->Radio(array("label"=>__( 'Soort', 'prana' ), "id"=>"soort", "inline"=>TRUE, "options"=>$options,"value"=>$this->fields['soort'],"required"=>TRUE));
        $options = array("Credit"=>"C","Debet"=>"D");
        $html .= $form->Radio(array("label"=>__( 'Type', 'prana' ), "id"=>"type", "inline"=>TRUE, "options"=>$options,"value"=>$this->fields['type'],"required"=>TRUE));
        $html .= $form->Text(array("label"=>__( 'BTW percentage', 'prana' ), "id"=>"btwpercentage", "value"=>$this->fields['btwpercentage'],"width"=>"100px;", "required"=>FALSE));
        #$html .= '<div>';
		$form->buttons = [
                            ['id'=>'writerecord','value'=>__( 'opslaan', 'prana' ), "onclick"=>"buttonclicked='rekening'"],
                            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        $html .='<input id="rekeningen" name="rekeningen" type="hidden" />';
        return($html);
    }
    #
    # lees demorecords in vanaf een csv bestand.
    # naam van csv bestand is gedefineerd in de opties van de plugin
    #
    public function LoadDemoRecords()
    {
        $dbio = new DBIO();
        get_option('rekeningschema');
        $csvfile=SBK_DATA_DIR . get_option('rekeningschema');
        $fileHandle = fopen($csvfile, "r");
        $rekeningschema = array();
        if(($header = fgetcsv($fileHandle, 0, ";")) !== FALSE)
        {
            //Loop through the CSV rows.
            while (($row = fgetcsv($fileHandle, 0, ";")) !== FALSE) 
            {
                $rekeningschema[] = array_combine($header, $row);
            }
        }
        foreach ($rekeningschema as $rekening)
        {
            $dbio->CreateRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"fields"=>$rekening));
        }   
    }
}
?>	