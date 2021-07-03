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
        $manual=SBK_DOC_DIR . 'manual.html';
        $html .= file_get_contents($manual);
        return($html);
    }
}