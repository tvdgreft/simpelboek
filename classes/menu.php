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
        array("boekhoudingen","afsluiten","afsluiten"),
        array("boekhoudingen","beginbalans","begin balans"),
        array("overzichten","balans","balans"),
        array("overzichten","resultaat","resultatenrekening"),
        array("overzichten","grootboek","grootboek"),
        array("overzichten","btw","BTW overzichten"),
        array("beheer","rekeningen","beheer rekeningen"),
        array("beheer","boekingen","beheer boekingen"),
        array("bankimport","mutaties","importeren bankmutaties"),
        array("bankimport","boekmutaties","bankmutaties verwerken"),
    );
    public function Start()
	  {
        global $wp;
        $dbio = new DBIO();
        $html = '';
        $disabled = array();
        # Als er nog geen boekhouding is gekozen alleen 'open boekhouding' en 'help' niet gedisabled.
        if(!isset($_SESSION['code'])) {$disabled = array("afsluiten","beginbalans","begroting","overzichten","balans","resultaat","btw","beheer","rekeningen","bankimport","mutaties","boekmutaties","jaarafsluiting","boekingen"); }
        elseif($dbio->CountRecords(Dbtables::boekingen['name']."_".$_SESSION['code']) > 0) { $disabled = array("beginbalans"); } #beginbalans alleen als er nog geen boekingen zijn.
        $action = home_url(add_query_arg(array(), $wp->request));
        $html .= '<div class="navContainer">';
        $html .= '<nav>';
        $html .=    '<ul>';
        foreach($this->menu as $m)
        {
            if($m[1] != "")     # menu zonder submenu
            {
              $d = in_array($m[0],$disabled) ? ' class="disabled-link" ' : ''; # is menu item disabled?
              $html .= '<li><a ' . $d . 'href="' . $action . '?menu=' . $m[0] . '">' . $m[1] . '</a></li>';
            }
            else
            {
              $d = in_array($m[0],$disabled) ? ' class="disabled-link" ' : ''; # is menu item disabled?
              $html .=  '<li><a ' . $d . 'href="#">' . $m[0] . '</a>';
              $html .= '<ul class="levelTwo">';
              foreach($this-> submenu as $s)
              {
                if($s[0] == $m[0])
                {
                  $d = in_array($s[1],$disabled) ? ' class="disabled-link" ' : ''; # is menu item disabled?
                  $html .= '<li><a ' . $d . 'href="' . $action . '?menu=' . $s[1] . '">' . $s[2] . '</a></li>';
                }
              }
              $html .= '</ul></li>';
            }
        }
        $html .=    '</ul>';
        $html .= '<br><br><br><br>';  # @TODO voorlopige oplossing 
        $html .= '</nav>';
        $html .= '</div>';
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