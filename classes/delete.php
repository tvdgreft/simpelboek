<?php
#
# Verwijder een boekhouding
#
namespace SIMPELBOEK;

class Delete
{
    public function Start()
    {
        if(isset($_POST['delete']) && $_POST["delete"] == 'delete')    // nu definitief verwijderen
        {
            return($this->Delete());
        }
        else
        {
            return($this->FormDelete());         # de balans aanmaken
        }
    }
    public function FormDelete()
    {
        $html = '';
        $dbio = new Dbio();
        $form = new forms();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $html .= sprintf(__('Door om onderstaande link te klikken wordt de boekhouding %s definitief verwijderd.','prana'), $boekhouding->naam);
        $html .= '<br>Maak eventueel eerst een backup van de databank van de website<br>';
        $message=sprintf( __( 'boekhouding %s definitief wijderen , zeker weten?', 'prana' ),$boekhouding->naam);
        $form->buttons = [
            ['id'=>'deleteboekhouding','value'=>__( 'boekhouding verwijderen', 'prana' ),'onclick'=>"return confirm('".$message. "');"],
            ['id'=>'cancel','value'=>__( 'annuleren', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="delete" name="delete" value="delete" type="hidden" />';
        return($html);
        /*
        unset($_SESSION["code"]);
        $action = home_url(add_query_arg(array(), $wp->request));
        echo("<script>location.href = '".$action."'</script>");
        exit;
        */
    }
    public function Delete()
    {
        $html = '';
        $dbio = new Dbio();
        $form = new forms();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $dbio->DeleteRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $result = $dbio->DeleteTable(Dbtables::rekeningen['name']."_".$_SESSION['code']);
        $result = $dbio->DeleteTable(Dbtables::balans['name']."_".$_SESSION['code']);
        $result = $dbio->DeleteTable(Dbtables::begroting['name']."_".$_SESSION['code']);
        $result = $dbio->DeleteTable(Dbtables::boekingen['name']."_".$_SESSION['code']);
        $html .= sprintf(__('Boekhouding %s  is definitief verwijderd.','prana'), $boekhouding->naam);
        $html .= '<br>';
        unset($_SESSION["code"]);
        $form->buttons = [
            ['id'=>'cancel','value'=>__( 'doorgaan', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        return($html);
    }
}
?>