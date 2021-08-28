<?php

namespace SIMPELBOEK;
class Menu
{
    # defineer het menu
    #
    # menu-id, label, classname
    #
    public $menu = array(
            array("boekhoudingen",""),
            array("begroting","begroting maken"),
            array("overzichten",""),
            array("beheer",""),
            array("bankimport",""),
            array("jaarafsluiting","jaarafsluiting"),
            array("help","help"),
    );
    # defineer het submenu's
    #
    # main menu id, label, classname
    #
    public $submenu = array(
        array("boekhoudingen","boekhoudingen","overzicht"),
        array("boekhoudingen","open","openen"),
        array("boekhoudingen","close","afsluiten"),
        array("boekhoudingen","rekeningen","rekeningenschema"),
        array("boekhoudingen","beginbalans","begin balans"),
        array("boekhoudingen","delete","verwijderen"),
        array("overzichten","balans","balans"),
        array("overzichten","resultaat","resultatenrekening"),
        array("overzichten","grootboek","grootboek"),
        array("overzichten","omzetbelasting","BTW overzichten"),
        array("beheer","rekeningen","beheer rekeningen"),
        array("beheer","boekingen","beheer boekingen"),
        array("bankimport","mutaties","importeren bankmutaties"),
        array("bankimport","boekmutaties","bankmutaties verwerken"),
    );
    public function Start() : string
	  {
      global $wp;
      $dbio = new DBIO();
      // Als er nog geen boekhouding is gekozen alleen 'open boekhouding' en 'help' niet gedisabled.
      $disabled = array();
      if(isset($_POST['single']))
      {
          $_SESSION['code'] = $_POST['single'];
          array_push($disabled,"close","delete","open");
      }
      if(!isset($_SESSION['code'])) 
      {
          array_push($disabled,"close","delete","beginbalans","begroting","overzichten","balans","resultaat","grootboek","omzetbelasting","beheer","rekeningen","bankimport","mutaties","boekmutaties","jaarafsluiting","boekingen");
      }
      else
      {
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$_SESSION['code']));
        $boekjaar = $boekhouding->boekjaar;
        $table_boekingen = Dbtables::boekingen['name']."_".$_SESSION['code'];
        $AantalBoekingen = count ($dbio->ReadRecords(array("table"=>$table_boekingen,"prefilter"=>array("datum"=>$boekjaar))));
        $table_balans = Dbtables::balans['name']."_".$_SESSION['code'];
        $AantalBalansRecords=count($dbio->ReadRecords(array("table"=>$table_balans,"prefilter"=>array("boekjaar"=>$boekjaar-1))));
        if($AantalBoekingen > 0) { array_push($disabled,"beginbalans"); }    // Zodra boekingen hebben plaats gevonden kan beginbalans niet meer worden gewijzigd.
        if(!$AantalBalansRecords) { array_push($disabled,"boekingen"); }  // Geen boekingen registreren, wanneer er nog geen beginbalans is.
      }
      $action = home_url(add_query_arg(array(), $wp->request));
      $html = '';
      $html .= '<nav id="site-navigation" class="site-navigation" aria-label="Simpelboek">';
		  $html .= '<ul class="main-menu clicky-menu no-js">';
      foreach($this->menu as $m)
      {
        if($m[1] != "")     # menu zonder submenu
			  {
          $html .='<li>';
          $d = in_array($m[0],$disabled) ? ' class="disabled-link" ' : ''; # is menu item disabled?
				  $html .= '<a ' . $d . 'href="' . $action . '?menu=' . $m[0] . '">' . $m[1] . '</a>';
			    $html .= '</li>';
        }
        else
        {
          $html .='<li>';
				  $html .= '<a href="#">' . $m[0] . '<svg aria-hidden="true" width="16" height="16"><use xlink:href="#arrow" /></svg></a>';
				  $html .= '<ul>';
          foreach($this-> submenu as $s)
          {
            if($s[0] == $m[0])
            {
              $d = in_array($s[1],$disabled) ? ' class="disabled-link" ' : ''; # is menu item disabled?
              $html .= '<li><a ' . $d . ' href="' . $action . '?menu=' . $s[1] . '">' . $s[2] . '</a></li>';
            }
          }
				  $html .= '</ul>';
          $html .= '</li>';
        }
      }
      $html .= '</ul>';
	    $html .= '</nav>';
      return($html);
    }
    public function MainMenu($menu)
    {
        foreach($this->submenu as $s)
        {
            if($menu == $s[1]) {return($s[0]);}
        }
        return("");
    }
}