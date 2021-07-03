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
        $this->single = "boekhouding";
        $this->plural = 'boekhoudingen';
        $this->class = "boekhoudingen";
		$this->table = Dbtables::boekhoudingen['name'];
        $this->columns= [
                                ["id","id","left"],         #table column name, columnname to be displayed, display orientation
                                ["code","code","left"],
                                ["naam","naam","left"],
                                ["boekjaar","boekjaar","left"]];
		$this->filtercolumns = array();                     #er hoeft niet gefilterd te worden
        $this->permissions = ["vw","cr","md"];
        $this->maxlines=20;
		$this->uid="id";	#the unique key
        $html = $this->run(); # start or restart tableform
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
        $html .= $form->Text(array("label"=>__( 'Code', 'prana' ), "id"=>"code", "value"=>$this->fields['code'], "width"=>"100px;"));
        $html .= $form->Text(array("label"=>__( 'Naam', 'prana' ), "id"=>"naam", "value"=>$this->fields['naam'], "width"=>"300px;"));
        $html .= $form->Text(array("label"=>__( 'Huidige boekjaar', 'prana' ), "id"=>"boekjaar", "type"=>"number","value"=>$this->fields['boekjaar'],"width"=>"100px;"));
        
		$form->buttons = [
                            ['id'=>'writerecord','value'=>__( 'opslaan', 'prana' ), "onclick"=>"buttonclicked='boekhouding'"],
                            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        $html .='<input id="boekhoudingen" name="boekhoudingen" type="hidden" />';
        return($html);
    }
}
?>	