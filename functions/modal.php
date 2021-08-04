<?php

/**
 * Functies t.b.v. modals
 *
 * @copyright 2022 pranamas
 */

/** Toon een help in een modal
 *  $manual is de bestandsnaam van de algemene handleiding
 *  $extension is het deel dat bij het lopende menu item hoort. ($manual_$extension)
 *  Als die niet bestaat, dan wordt de algemene handleiding getoond
 */
function SBK_HelpModal(string $manual,string $extension,string $header) : string 
{
	$html = '';
	$main = SBK_DOC_DIR . $manual . '.html';
	$part = SBK_DOC_DIR . $manual . '_' . $extension . '.html';
	if(file_exists($part)) 		{ $manual = $part; }
	elseif(file_exists($main))	{ $manual = $main; }
	else { return($html); }
	$fh = fopen($manual, 'r');
	$help = fread($fh, filesize($manual));
	fclose($fh);
	$html .= SBK_ModalScroll('<i class="fa fa-question fa-lg" style="font-size:24px;"></i>',$header,$help);
	return($html);
}
function SBK_ModalScroll($link,$title,$content)
{
	$m = '';
	$m .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
	$m .= '<style>
		/* The Modal (background) */
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			padding-top: 100px; /* Location of the box */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 50%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: rgb(0,0,0); /* Fallback color */
			background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}
			/* Modal Content */
		.modal-content {
			background-color: #fefefe;
			margin: auto;
			padding: 20px;
			border: 1px solid #888;
			width: 80%;
		}
			/* The Close Button */
		.close {
			color: #a80707;
			float: right;
			font-size: 28px;
			font-weight: bold;
			background-color: #9ad0ae;
		}
		.close:hover,
		.close:focus {
			color: #a80707;
			text-decoration: none;
			cursor: pointer;
		}
		</style>';
	
		#$m .= '<h2>' . $title . '</h2>';
		# Trigger/Open The Modal
	$m .= '<button id="myBtn" class="pbtnok">' . $link . '</button>';
		//$m .= '<button id="' . $link . '">' . $link . '</button>';

		#The Modal -->
	$m .= '<div id="myModal" class="modal">';
	$m .= '	<div class="modal-content">';
	$m .= '		<span class="close">close</span>';
	$m .= $content;
	$m .= '</div>';
	$m .= '</div>';
	$m .= '
		<script>
		// Get the modal
		var modal = document.getElementById("myModal");
			// Get the button that opens the modal
		var btn = document.getElementById("myBtn");
		//var btn = document.getElementById("' . $link . '");
			// Get the <span> element that closes the modal
		var span = document.getElementsByClassName("close")[0];
			// When the user clicks the button, open the modal 
		btn.onclick = function() {
			modal.style.display = "block";
		}
			// When the user clicks on <span> (x), close the modal
		span.onclick = function() {
			modal.style.display = "none";
		}
			// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
		</script>';
		
	return($m);
}