<?php
#
# button menu
#
namespace SIMPELBOEK;
#
# Formulier om nieuwe boekhouding aan te maken
#
class Formnieuw
{
	public function Start()
	{
		$form = new Forms();
		$form->required=false;
		$form->row=FALSE;
		$html='';
		$html .= '<div class="row">';
		$html .= $form->Text(array("label"=>__( 'code', 'prana' ),"id"=>"bkcode"));
		$html .= $form->Text(array("label"=>__( 'naam', 'prana' ),"id"=>"bkname"));
		$html .= $form->Text(array("label"=>__( 'boekjaar', 'prana' ),"type"=>"number","id"=>"bkyear"));
		$html .= '<div>';
		$form->buttons = [['id'=>'Maaknieuw','value'=>__( 'aanmaken', 'prana' )]];
		$html .= $form->DisplayButtons();
		return($html);
	}
}
?>	