<?php
namespace SIMPELBOEK;
#
# Beheer van boekingen
# TODO: testen op geldig boekjaar
#
class Boekingen extends Tableform
{
    protected $fields = array();
    public $table_rekeningen;
	public function Start()
	{
        $html='';
        $this->single = "boeking";
        $this->plural = 'boekingen';
        $this->class = "boekingen";
		$this->table = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $this->columns= [
                                ["id","id","string"],         #table column name, columnname to be displayed, display orientation
                                ["datum","datum","date"],
                                ["omschrijving","omschrijving","string"],
                                ["bedrag","bedrag","euro"],
                                ["btw","btw","euro"],
                                ["type","type","stringright"],
                                ["rekening","rekening","stringright"],
                                ["tegenrekening","tegenrekening","stringright"]];
		$this->filtercolumns = array("datum"=>"datum","bedrag"=>"bedrag","rekening"=>"rekening","tegenrekening"=>"tegenrekening");
        $this->permissions = ["vw","cr","md","dl"];
        $this->maxlines=20;
		$this->uid="id";	#the unique key
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
        $dbio = new Dbio();
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$_SESSION['code'];
        $html = '';
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>__( 'ID', 'prana' ), "id"=>"id", "value"=>$this->fields['id'], "width"=>"100px", "readonly"=>TRUE));
        }
        $html .= $form->Date(array("label"=>__( 'boekdatum', 'prana' ), "id"=>"datum", "value"=>$this->fields['datum'], "width"=>"300px"));
        $html .= $form->Text(array("label"=>__( 'Bankrekening', 'prana' ), "id"=>"bankrekening", "value"=>$this->fields['bankrekening'], "checkclass"=>"checkbankrekening" , "width"=>"300px","required"=>FALSE,"error"=>"bankrekening onjuist"));
		$html .= $form->Text(array("label"=>__( 'Bankrekeninghouder', 'prana' ), "id"=>"bankrekeninghouder", "value"=>$this->fields['bankrekeninghouder'], "width"=>"300px","required"=>FALSE));
        $html .= $form->Text(array("label"=>__( 'Bedrag (in centen)', 'prana' ), "id"=>"bedrag", "value"=>$this->fields['bedrag'], "type"=>"number" , "width"=>"100px","required"=>TRUE));
        $html .= $form->Text(array("label"=>__( 'BTW (in centen)', 'prana' ), "id"=>"btw", "value"=>$this->fields['btw'], "type"=>"number" , "width"=>"100px","required"=>FALSE));
        $options = array("credit"=>"C","debet"=>"D");
        $html .= $form->Radio(array("label"=>__( 'type', 'prana' ), "id"=>"type", "value"=>$this->fields['type'], "options"=>$options));
        #
        # maak keuze uit balansrekening in rekeningschema
        #
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"prefilter"=>array("soort"=>"B"),"sort"=>"rekeningnummer"));
        $options = array();
        foreach ($rekeningen as $r)
        {
            $options += [$r->naam=>$r->rekeningnummer];
        }
        $html .= $form->Dropdown(array("label"=>__( 'rekening', 'prana' ), "id"=>"rekening", "value"=>$this->fields['rekening'], "options"=>$options, "width"=>"300px"));
        #
        # tegenrekening in rekeningschema
        #
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"sort"=>"rekeningnummer"));
        $options = array();
        foreach ($rekeningen as $r)
        {
            $options += [$r->naam=>$r->rekeningnummer];
        }
        $html .= $form->Dropdown(array("label"=>__( 'tegenrekening', 'prana' ), "id"=>"tegenrekening", "value"=>$this->fields['tegenrekening'], "options"=>$options, "width"=>"300px"));
        $html .= $form->TextArea(array("label"=>__( 'omschrijving', 'prana' ), "id"=>"omschrijving", "value"=>$this->fields['omschrijving'], "width"=>"300px","heigth"=>"150px","required"=>FALSE));
        $form->buttons = [
                            ['id'=>'writerecord','value'=>__( 'opslaan', 'prana' ), "onclick"=>"buttonclicked='boeking'"],
                            ['id'=>'writerecordandnext','value'=>__( 'opslaan en nieuwe boeking', 'prana' ), "onclick"=>"buttonclicked='boeking'"],
                            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        $html .='<input id="boekingen" name="boekingen" type="hidden" />';
        return($html);
    }
}
?>	