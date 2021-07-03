<?php
#
# button menu
#
namespace SIMPELBOEK;
#
# Maak een nieuwe boehouding aan.
#
class Maaknieuw
{
	public function Start()
	{
        $html = '<h2>' . __( 'Nieuwe boekhouding aanmaken code=', 'prana' ) .  $_POST['bkcode'] . '</h2>';
        $dbio = new Dbio();
		$fields['code'] = $_POST["bkcode"];
        $fields['naam'] = $_POST["bkname"];
        $fields['boekjaar'] = $_POST["bkyear"];
        $ofields = (object) $fields;
        $result = $dbio->CreateRecord(array("table"=>Dbtables::index['name'],"fields"=>$ofields));
		if($result === FALSE) 
        { 
            $html .= '<div class="isa_error">' . __( 'Deze boekhouding betstaat al', 'prana' ) . '</div>';
            return($html);
        }
        #
        # Tabellen aanmaken
        
        $result = $dbio->CreateTable(Dbtables::rekeningen['name']."_".$fields['code'],Dbtables::rekeningen['columns']);
        $result = $dbio->CreateTable(Dbtables::balans['name']."_".$fields['code'],Dbtables::balans['columns']);
        $result = $dbio->CreateTable(Dbtables::begroting['name']."_".$fields['code'],Dbtables::begroting['columns']);
        $result = $dbio->CreateTable(Dbtables::boekingen['name']."_".$fields['code'],Dbtables::boekingen['columns']);
        if($result === FALSE) 
        { 
            $html .= '<div class="isa_error">' . __( 'Fout bij aanmaken boekhouding', 'prana' ) . '</div>';
            return($html);
        }
        #
		# lees het voorbeeld rekeningschema in
		# Hiermee wordt bij een nieuwe boekhouding een rekeningschema aangemaakt.
		# Het voorbeeld rekeningschema is het bestand rekeningschema.csv in de map data
        #
		get_option('rekeningschema');
		$csvfile=SBK_DATA_DIR . get_option('rekeningschema');
		$fileHandle = fopen($csvfile, "r");
		$rekeningschema = array();
		if(($header = fgetcsv($fileHandle, 0, ";")) !== FALSE)
		{
				//Loop through the CSV rows.
			while (($row = fgetcsv($fileHandle, 0, ";")) !== FALSE) 
			{
				$rekeningschema[] = array_combine($header, $row);
			}
		}
		foreach ($rekeningschema as $rekening)
		{
            $dbio->CreateRecord(array("table"=>Dbtables::rekeningen['name']."_".$fields['code'],"fields"=>$rekening));
		}
        $html .= '<div class="isa_success">' . __( 'Boekhouding code=', 'prana' ) .  $_POST['bkcode'] .  __( ' is aangemaakt', 'prana' );
		return($html);
	}
}
?>	