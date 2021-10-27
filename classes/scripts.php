<?php
#
# load scripts
#
namespace SIMPELBOEK;

class Scripts
{
	public function LoadScripts() 
	{
		$html = '';
		#$self = new self();
		$cssurl = WP_PLUGIN_URL .'/' . SBK_PLUGIN_DIRNAME . '/css/';
		$jsurl = WP_PLUGIN_URL .'/' . SBK_PLUGIN_DIRNAME . '/javascript/';
		$menuurl = WP_PLUGIN_URL .'/' . SBK_PLUGIN_DIRNAME . '/vendor/clicky-menus/';
		$html .= '<meta charset="utf-8">';
  		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		$html .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
		$html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>';
  		$html .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
  		$html .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';
		$html .= '<script src="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"></script>';
		$html .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
		#$html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>';
		$html .= '<title>jQuery UI Datepicker - Default functionality</title>';
		$html .= '<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">';
		$html .= '<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'prana.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'forms.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'simpelboek.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $menuurl . '/demo/demo.css' . '">';		// user script clicky menu
		$html .= '<link rel="stylesheet" href="' . $menuurl . '/clicky-menus.css' . '">';	// script clicky menu
		$html .= '<script src="https://code.jquery.com/jquery-1.12.4.js"></script>';
		$html .= '<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>';
		$html .= '<script src="' . $jsurl . 'mdt_tables.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'forms.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'exportcsv.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'simpelboek.js' . '"></script>';	#javascript to check forms input
		$html .= '<script src="' . $menuurl . 'clicky-menus.js' . '"></script>';	#clicky menu javascripy
		return($html);
		//
		// load JQUERY datepicker
		//
    }
}