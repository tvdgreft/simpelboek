<?php
##################################################################################
# class: 		main
# description:	
# 				detects the plugin name and arguments form content
#				plugintag should be the following syntax
#				[mtndbtable arg1="...." arg2="...." ...... ]
##################################################################################
namespace SIMPELBOEK;

class Main
{
	public $single = '';				# boekhouding die als enige geopend kan worden
	public $open;				# geopende boekhouding
	public $backgroundcolor;	#backgroundcolor of table and forms
	public $action;		#url to restart plugin
	
	function init($args)
	{
		global $wp;
		$dbio = new Dbio();
		$form = new Forms();
		$menu = new Menu();

		$bootstrap = new bootstrap();
		$scripts=new Scripts();
		$self = new self();
		
		$html = '';
		$html .= $scripts->LoadScripts();			#load scripts
		$this->action = home_url(add_query_arg(array(), $wp->request));
		$this->backgroundcolor=get_option('backgroundcolor');
		$this->organisation=get_option('organisation');
		#
		# treat arguments
		#
		$this->single = isset($args["single"]) ? $args["single"] : "";
		if(isset($args['single']))
		{
			$_POST['single'] = $this->single;
			$boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_POST['single']));
			if(!$boekhouding)
			{
				$html .= '<h1>' . __( 'Boekhouding', 'prana' ) . ' ' . $_POST['single'] . __( ' bestaat niet', 'prana' ).'</h1>';
				return($html);
			}

		}
		#
		# create init table if not exists
		#
		$result = $dbio->CreateTable(Dbtables::boekhoudingen['name'],Dbtables::boekhoudingen['columns']);
		if($result) { return($result); }
		$menu = new Menu;
		$html .= '<div class="prana-display">';
		$html .= '<center><h1>' . __('SIMPELBOEK BOEKHOUDING','prana') . '</h1></center>';
		$manual = 'manual';
		$part = '';
		if(isset($_POST['menu'] )) { $part = $_POST['menu']; }
		elseif(isset($_GET['menu'] )) { $part=$_GET['menu']; }
		if($main = $menu->MainMenu($part)) { $part = $main; }
		$html .= $menu->start();
		$html .= SBK_HelpModal($manual,$part);	#popup help voor gekozen menu
		# toon welke boekhouding geopend is
		if(isset($_SESSION['code'])) 
		{ 
			$boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
			$html .= '<h1>' . __( 'Boekhouding', 'prana' ) . ' ' . $boekhouding->naam . __( ' is geopend, boekjaar is:', 'prana' ). $boekhouding->boekjaar . '</h1>';
		}
		$html .='<form action=' . $this->action . ' method="post" enctype="multipart/form-data" onSubmit="return ValForm()">';
		if(isset($_GET['menu']))
		{
			if($classfile=Bootstrap::ClassFile($_GET['menu'])) 
			{ 
				$class = Bootstrap::NameSpace() . '\\' . $_GET['menu'];
				$run = new $class;
				$html .= $run->Start();
			}
		}
		if(!isset($_POST['cancel']))
		{
			foreach ($_POST as $key => $value)
			{
				#echo "<br>key=" . $key;
				$class = Bootstrap::NameSpace() . '\\' . $key;
				#echo "<br>class=" . $class;
				if($classfile=Bootstrap::ClassFile($key)) 
				{ 
					#echo "<br>classfile:".$classfile;
					$run = new $class;
					$html .= $run->Start(); 
					break;					# zorgt er voor dat een eerder gestarte class opnieuw wordt gestart
				}
			}
		}
		$html .= '</form>';
		$html .= '<hr>';
		$html .= '</div>';
		return($html);
	}
	/*
	public function Menu()
	{
		$html = '';
		$html .= '<div class="prana-menu">';
		$forms = new Forms();
		$dbio = new DBIO();
		$forms->buttonclass = 'pbtnmenu';
		if($this->single == '')	#Bij als argument opgegeven boekhouding geen open en create mogelijk
		{
			array_push($forms->buttons , ['id'=>'boekhoudingen','value'=>'boekhoudingen']);		#nieuwe boekhouding aanmaken
			array_push($forms->buttons , ['id'=>'Open','value'=>__( 'openen', 'prana' )]);
		}
		$forms->buttuns=array();
		$show="disabled";
		$beginbalans='disabled';
		if(isset($_SESSION['code'])) 
		{
			$show="enabled"; # onderstaande menu items alleen als er een boekhouding is geopend
			# beginbalans mag niet meer worden aangemaakt wanneer er boekingen zijn verricht.
			$beginbalans = "enabled";
			if($dbio->CountRecords(Dbtables::boekingen['name']."_".$_SESSION['code']) > 0) { $beginbalans = "disabled"; }
		}
		array_push($forms->buttons , ['id'=>'close','value'=>__( 'afsluiten', 'prana' ),'status'=>$show]);
		array_push($forms->buttons , ['id'=>'rekeningen','value'=>'beheer rekeningen','status'=>$show]);
		#
		# beginblans disabelen als er boekingen zijn geregistreerd
		#
		array_push($forms->buttons , ['id'=>'beginbalans','value'=>'beginbalans','status'=>$beginbalans]);
		array_push($forms->buttons , ['id'=>'begroting','value'=>'begroting','status'=>$show]);
		array_push($forms->buttons , ['id'=>'boekingen','value'=>'boekingen','status'=>$show]);
		array_push($forms->buttons , ['id'=>'mutaties','value'=>'importeer mutaties','status'=>$show]);
		array_push($forms->buttons , ['id'=>'boekmutaties','value'=>'boek mutaties','status'=>$show]);
		array_push($forms->buttons , ['id'=>'balans','value'=>'balans','status'=>$show]);
		array_push($forms->buttons , ['id'=>'btw','value'=>'btw overzichten','status'=>$show]);
		array_push($forms->buttons , ['id'=>'jaarafsluiting','value'=>'jaarafsluiting','status'=>$show]);
		array_push($forms->buttons , ['id'=>'Help','value'=>'help']);
		$html .= $forms->DisplayButtons();
		$html .= '</div>';
		return($html);
	}
	*/
	/*
	public function Start()
	{
		$html = '';
		$forms = new Forms();
		$html .= '<div class="prana-display">';
		#
		# Show number of rows and pages and a help function if defined
		#
		#
		$html .='<form action=' . $this->action . ' method="post" name="formulier">';
		$html .= '<h1>Hello World</h1>';
		$html .= __( 'Zet hier de plugin code', 'prana' );	
		$html .= '<br>organisation=' . get_option('organisation');
		$forms->buttons = [['id'=>'Nextrun','value'=>'next']];
		$html .= $forms->DisplayButtons();
		#$html .= '<button class="pbtnok" name="Nextrun">' . "next" . '</button>';
		$html .= '</form>';
		return($html);
	}
	*/
}
?>	