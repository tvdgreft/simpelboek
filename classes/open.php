<?php
#
# Open een boekhouding
#
namespace SIMPELBOEK;

class Open
{
    public function Start()
    {
        $html = '';
        #
        # Er is gekozen voor een boekhouding
        # Bewaar deze in de sessie
        if(isset($_POST['opencode']))
        {
            $html = $this->OpenBoekhouding();
            return($html);
        }
        $dbio = new Dbio();
        $html .= '<h2>' . __( 'Open een bestaande boekhouding', 'prana' ) . '</h2>';
        $result = $dbio->ReadRecords(array("table"=>Dbtables::boekhoudingen['name']));
		if($result === FALSE) 
        { 
            $html .= '<div class="isa_error">' . __( 'Er zijn geen boekhoudingen', 'prana' ) . '</div>';
            return($html);
        }
        $options = array();
        foreach ($result as $r)
        {
            $options += [$r->naam=>$r->code];
            #$p['value'] = $r->code;
            #$p['name']= $r->naam;
            #$options[] = (object)$p;
        }
        $form = new Forms();
        $html .= $form->Dropdown(array("label"=>__( 'kies boekhouding', 'prana' ),"id"=>"opencode","options"=>$options,"row"=>TRUE,"value"=>""));
        $form->buttons = [['id'=>'open','value'=>__( 'openen', 'prana' )]];
		$html .= $form->DisplayButtons();
        return($html);
    }
    protected function OpenBoekhouding()
	{
        global $wp;
        $html = '';
		$_SESSION['code'] = $_POST['opencode'];
        $action = home_url(add_query_arg(array(), $wp->request));
        echo("<script>location.href = '".$action."'</script>");
        exit;
	}
}
?>