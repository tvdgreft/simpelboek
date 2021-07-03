<?php
#
# Open een boekhouding
#
namespace SIMPELBOEK;

class Close
{
    public function Start()
    {
        global $wp;
        unset($_SESSION["code"]);
        #$action = "http://www.pranamas.nl";
        $action = home_url(add_query_arg(array(), $wp->request));
        #wp_redirect($action);
        echo("<script>location.href = '".$action."'</script>");
        #header("Location:" . $action);
        exit;
    }
}
?>