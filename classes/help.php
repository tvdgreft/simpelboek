<?php
#
# load scripts
#
namespace SIMPELBOEK;

class Help
{
    public function Start()
    {
        $html = '';
        $manuals = ["boekhoudingen","begroting","beheer","bankimport","jaarafsluiting"];
        $manual=SBK_DOC_DIR . 'manual.html';
        $html .= file_get_contents($manual);
        foreach ($manuals as $manual)
        {
            $manual=SBK_DOC_DIR . 'manual_' . $manual.'.html';
            $html .= file_get_contents($manual);
        }
        return($html);
    }
}