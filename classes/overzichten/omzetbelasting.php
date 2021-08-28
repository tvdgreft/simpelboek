<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Omzetbelasting extends Overzichten
{
	/**
	 * BTW overzichten
	 * Periode aangeven waarover het overzicht gemaakt moet worden
	 */
	public function Start()
	{
		$html = '';
		if(isset($_POST['btwoverzichten'])) 
		{
			$html .= $this->BTWOverzichten();
			return($html);
		}
		$form = new Forms();
		$html .= '<h2>BTW overzichten_' . $_SESSION['code'] . '</h2>';
		$html .= $form->date(array("label"=>__( 'vanaf', 'prana' ), "id"=>"from","width"=>"150px"));
		$html .= $form->date(array("label"=>__( 'tot en met', 'prana' ), "id"=>"till","width"=>"150px"));
		$html .= '<input id="btw" name="btw" value="btw" type="hidden" />';
		$form->buttons = [
			['id'=>'btwoverzichten','value'=>__( 'btw overzichten', 'prana' )],
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	public $periodboekingen;
	protected function BTWOverzichten()
	{
		$dbio = new DBIO();
		$form = new Forms();
		$eufrom = date_format(date_create($_POST['from']),"d-m-Y");
		$eutill= date_format(date_create($_POST['till']),"d-m-Y");
		$html = '';
		$html .= '<h2>'.__('omzetbelasting over de periode ','prana') . $eufrom . __(' tot en met ','prana') . $eutill . "</h2>";
		$this->LoadData();
		#
		# verzamel boekingen over opgegeven periode
		$period = 'datum >="'.$_POST['from'].'" and datum <="'.$_POST['till'] .'"';
		$this->periodboekingen = $dbio->ReadRecords(array("table"=>$this->table_boekingen,"filter"=>$period));
		$tabel = $this->Omzetbelasting();
		$colinfo = array(
			array("omzetbelasting","string"),
			array("rkn","string"),
			array("naam","string"),
			array("datum","date"),
			array("tarief","number"),
			array("bedrag incl BTW","euro"),
			array("bedrag ex BTW","euro"),
			array("omzetbelasting","euro"),
		);
		$html .= $this->DisplayTabel($tabel,$colinfo);
		#$html .= $this->DisplayTable($tabel);
		$filename = 'omzetbelasting_' . $_SESSION['code'] . '_' . $eufrom . '_' . $eutill .'.csv';
		$html .= '<h2>'.__('voorheffing over de periode ','prana') . $eufrom . __(' tot en met ','prana') . $eutill . "</h2>";
		$tabel = $this->Voorheffing();
		$colinfo = array(
			array("voorheffing","string"),
			array("rkn","string"),
			array("naam","string"),
			array("datum","date"),
			array("bedrag incl BTW","euro"),
			array("voorheffing","euro"),
		);
		$html .= $this->DisplayTabel($tabel,$colinfo);
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="btw" name="btw" value="btw" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>__( 'exporteren', 'prana' )],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>__( 'terug', 'prana' ),"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	protected function Omzetbelasting()
	{
		#
		# omzetbelasting
		#
		$btwoverzicht = array();
		#$btwoverzicht[] = array('omzetbelasting','rkn','naam','datum','tarief','bedrag incl btw'.'bedrag ex btw','omzetbelasting');
		
		$row=1;
		$totaalbedrag=$totaalexbtw=$totaalbtw=0;
		$totaalgeneraalexbtw =0;
		$totaalgeneraalbedrag=0;
		$totaalgeneraalbtw=0;
		foreach($this->btwtarieven as $t)
		{
			if($t->btwpercentage)
			{
				$totaalexbtw =0;
				$totaalbedrag=0;
				$totaalbtw=0;
				$pr=$this->rekeningen;
				foreach ($this->periodboekingen as $b)
				{	
					foreach($pr as $r)
					{
						if($b->tegenrekening == $r->rekeningnummer && $r->btwpercentage== $t->btwpercentage && $r->btwpercentage > 0)
						{
							$bedrag=$b->bedrag;
							$btw=intval(($bedrag * $r->btwpercentage)/(100 + $r->btwpercentage));
							$exbtw=$bedrag-$btw;
							#$row++;
							$totaalbedrag += $bedrag;
							$totaalexbtw += $exbtw;
							$totaalbtw += $btw;
							$totaalgeneraalbedrag += $bedrag;
							$totaalgeneraalexbtw += $exbtw;
							$totaalgeneraalbtw += $btw;

							$btwoverzicht[] = array($b->id,$r->rekeningnummer,$r->naam,$b->datum,$r->btwpercentage,$bedrag,$exbtw,$btw);
						}
					}
				}
				#$row++;
				$btwoverzicht[] = array('','','totaal','','',$totaalbedrag,$totaalexbtw,$totaalbtw);
			}
		}
		#$row++;
		$btwoverzicht[] = array('','','totaal generaal','','',$totaalgeneraalbedrag,$totaalgeneraalexbtw,$totaalgeneraalbtw);
		return($btwoverzicht);
	}
	protected function Voorheffing()
	{
		#
		# omzetbelasting
		#
		$btwoverzicht = array();
		$btwoverzicht[] = array('voorheffing','rkn','naam','datum','bedrag incl btw'.'voorheffing');
		$totaalbedrag=$totaalbtw=0;
		foreach ($this->periodboekingen as $b)
		{	
			if($b->btw)
			{
				$bedrag=$b->bedrag;
				$btw= $b->btw;;
				$totaalbedrag += $bedrag;
				$totaalbtw += $btw;
				$btwoverzicht[] = array($b->id,$b->tegenrekening,$b->omschrijving,$b->datum,$bedrag,$btw);
			}
		}
		$btwoverzicht[] = array('','','totaal','',$totaalbedrag,$totaalbtw);
		return($btwoverzicht);
	}
}