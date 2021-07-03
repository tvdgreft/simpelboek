<?php
namespace SIMPELBOEK;
class Forms
{
	# default values
	#
	/*
	public $width="100%";
	public $heigth="25px";
	public $readonly = FALSE;
	public $collabel="col-md-3";	#size of label
	public $colinput="col-md-6";
	public $col="col-md-3";
	public $required= TRUE;
	public $uploaderror = "";
	public $inline = TRUE;		#label and input in one line
	*/
	public $formdefaults = array(
		'name' => '',
		'value' => '',
		'width' => '100%',
		'height' => '35px',
		'readonly' => FALSE,
		'collabel' => 'col-md-3',	#size of label
		'colinput' => 'col-md-6',	#size of inputfield
		'col' => 'col-md-3',
		'required' => TRUE,
		'uploaderror' => '',
		'inline' => TRUE,		#label and input in one line
		'dateformat' => 'yy-mm-dd',	#format of date
		'checkclass' => '',
		'error' => 'input error',
		'type' => 'text',
		'popover' => '',			#text for a popover
		'placeholder' => '',		#placeholder input field
		'choose' => 'maak een keuze',	#keuze tekst
		'onchange' => ''		#jscript function after changing the input
	);
	#
	# button variables
	public $buttons = array();
	public $buttonclass =  "pbtnok";
	public $buttoncol = "col-md-12";
	public $status = '';
	#
	# button
	#
	public function DisplayButtons()
    {
        $html = '';
		#$html .= '<div class="' . $this->buttoncol . '">';	
		foreach ($this->buttons as $m)
        {
			$id = $m['id'];
            $value = $m['value'];
			$status = isset($m['status']) ? $m['status'] : $this->status;
			$onclick= isset($m['onclick']) ? $m['onclick'] : "";
			$class= isset($m['class']) ? $m['class'] : "";
			$html .= '<button id="' . $id . '" class="' . $this->buttonclass . ' ' . $class . '" name="' . $id . '" value="' . $id . '"';
			if($onclick) { $html .= 'onclick="' . $onclick . ';"'; }
			$html .= ' ' . $status . '>' . $value;
			$html .= '</button>';
			$html .= '&nbsp';
		}
		#$html .= '</div>';
        return $html;
    }
	/**
	 * Text - invoerveld voor tekst
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'type' => type of input (default is 'text'
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'width' => size of inputfield
	 * 'checkclass' => (string) classname for checking content (see: forms.js)
	 * 'error' => (string) message in case of error
	 * 'popover' => (string) text for a popover
	 * 'placeholder' => placeholder for the inputfield
	 * ]
	 */
	public function Text($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		
		$html='';
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= "*"; }
		if($args['inline'] == TRUE) 
		{
			$html .= '<div class="form-group row">'; 
			$html .= '<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= '<label for="' . $args["id"] . '"';
		/*
		if($args["popover"]) 
		{
			$html .= 'data-toggle="popover" data-placement="top" title="popover on top" data-content="content"';
		}
		*/
		#{ $html .= ' class="hasPopover"  title="' . $args["popover"] . '"'; }
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		else { $html .= '<div class="controls">';}
		$args['name'] = $args['name'] ? $args['name'] : $args['id']; # name is id if not defined
		$html .= 	'<input class="form-control ' . $args['checkclass'] .'" type="' . $args["type"] . '" id="' . $args["id"] . '" name="' . $args["name"] . '" value="' . $args['value'] . '"';
		$html .= ' style="width:' . $args['width'] . '; height:' . $args['height'] . ';"';
		if($args['required']== TRUE) { $html .= ' required="required"'; }
		if($args['readonly'] == TRUE) { $html .= ' readonly="readonly"'; }
		if($args["placeholder"]) $html .= 'placeholder="' . $args["placeholder"] . '"';
		$html .= '>';
		$html .= '<span class="error_hide">'.$args['error'].'</span>'; #span for error field see: forms.js
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * TextArea - invoerbox voor tekst
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'type' => type of input (default is 'text'
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'width' => size of inputfield (...px)
	 * 'checkclass' => (string) classname for checking content (see: forms.js)
	 * 'error' => (string) message in case of error
	 * 'popover' => (string) text for a popover (todo)
	 * 'placeholder' => placeholder for the inputfield
	 * ]
	 */
	public function TextArea($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		$html='';
		if($args['required']) { $args["label"] .= "*"; }
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args["collabel"] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		$html .= 	'<div class="' . $args["colinput"] . '">';
		$html .= 	'<textarea class="form-control' . $args["checkclass"] .'" type="text" id="' . $args["id"] . '" name="' . $args["id"] . '"';
		$html .= 'style="width:' . $args["width"] . '; height:' . $args["heigth"] . ';"';
		#$html .= 'style="height:' . $args['heigth'] . ';"';
		if($args["required"]) { $html .= ' required="required"'; }
		if($args["readonly"]) { $html .= ' readonly="readonly"'; }
		if($args["placeholder"]) $html .= 'placeholder="' . $args["placeholder"] . '"';
		$html .= '>';
		$html .= $args["value"];
		$html .= '</textarea>';
		$html .= 	'<span class="error_hide">'.$args["error"].'</span>';
		$html .= 	'</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * Radio - maak een keuze mbv radio buttons.
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * ]
	 */
	public function Radio($args)
	{
		$html='';
		$args = wp_parse_args( $args, $this->formdefaults );
		if($args['required']) { $args["label"] .= "*"; }
		$id=$args["id"];
		$html='';
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args['collabel'] .'">';
		$html .= 		'<label for="radios">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		$html .= 	'<div class="' . $args['colinput'] . '">';
		foreach($args["options"] as $key => $value)
		{
			$selected="";
			if($value == $args['value']) { $selected = " checked";}
			if($args['inline'] == TRUE) { $html .= '<div class="form-check form-check-inline">'; }
			if($args['inline'] == FALSE) { $html .= '<div class="form-check">'; }
			$rid = $args["id"] . '_' . $value;
			$html .= 	'<input class="form-check-input" type="radio" id="' . $rid . '" name="' . $args['id'] . '" value="' . $value . '"' . $selected;
			if($args['required']) { $html .= ' required'; }
			if($args['readonly']) { $html .= ' disabled="disabled"'; }
			$html .= '>';
			$html .= '<label class="form-check-label" for="'. $args['id'] . '">' . '&nbsp;&nbsp;' . $key . '</label>';
			$html .=		'</div>';
		}
		$html .=		'</div>';
		$html .=		'</div>';
		return($html);
	}
	/**
	 * Text - invoerveld voor tekst
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 * 'checked' => (Boolean) default is checked
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'error' => (string) message in case of error
	 * ]
	 */
	public function CheckBox($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		
		$html='';
		$args['name'] = $args['name'] ? $args['name'] : $args['id']; # name is id if not defined
		if($args['inline'] == TRUE) 
		{
			$html .= '<div class="form-group row">'; 
			$html .= '<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= '<label for="' . $args["id"] . '"';
		$html .= '>' .  $args["label"] . '</label>';
		$html .= '</div>';
		#if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		#else { $html .= '<div class="form-check">';}
		$html .= '<div class="form-check">';
		$html .= 	'<input class="form-check-input" type="checkbox" id="' . $args["id"] . '" name="' . $args["name"] . '" value="' . $args['value'] . '"';
		if($args['checked']== TRUE) { $html .= ' checked'; }
		$html .= '>';
		$html .= '<span class="error_hide">'.$args['error'].'</span>'; #span for error field see: forms.js
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	public function Checkboxes($args)
	{
		if($this->required) { $args["label"] .= "*"; } 
		$id=$args["id"];
		$html='';
		if($row == TRUE) 
		{
			$html .= '<div class="form-group row">'; 
			$html .= 	'<div class="' . $this->collabel .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $this->col . '">';
			$html .= '<div class="control-label">';
		}
		$html .= 		'<label for="checkbox">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		$html .= 	'<div class="' . $this->colinput . '">';
		foreach ($args["options"] as $p)
		{
			$selected="";
			if(in_array($p,$args["value"])) { $selected = " checked";}
			$html .= '<div class="form-check">';
			$rid = $args["id"] . '_' . $p;
			$html .= 	'<input class="form-check-input" type="checkbox" id="' . $rid . '" name="' . $id . '[]" value="' . $p . '"' . $selected;
			if($this->readonly) { $html .= ' disabled="disabled"'; }
			$html .= '>';
			$html .= '<label class="form-check-label" for="'. $args["id"] . '">' . $p . '</label>';
			$html .=		'</div>';
		}
		$html .=		'</div>';
		$html .=		'</div>';
		return($html);
	}
	/**
	 * Date - invoerveld voor een datum
	 * @param array $args[
	 * label => (string) Label van het invoerveld
	 * value => (string) beginwaarde van het invoerveld
	 * format => (string) datum formaat
	 * colinput => (string) size of inputfield
	 * width => size of inputfield (...px)
	 * ]
	 * datepicker gedefinieerd in forms.js
	 */
	public function Date($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		if($args['required']) { $args["label"] .= "*"; } 
		$checkclass = isset($args["check"]) ? ' ' . $args["check"] : ''; # add check class if given so that javascript can test the content
		
		$html='';
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args['collabel'] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		#if(isset($args["popover"])) { $html .= ' class="hasPopover"  title="' . $args["popover"] . '"'; }
		$html .= 		'>' .  $args["label"] . '</label>';
		$html .=	'</div>';
		$html .= 	'<div class="' . $args['colinput'] . '">';
		$html .= '<input class="form-control ' . $args["checkclass"] . ' datepicker" type="text" id="' . $args["id"] . '" name="' . $args["id"]. '" style="width:' . $args["width"] . '" value="' . $args["value"] .'"';
		#
		# set event on classes checkclass and datepicker in jquery !!
		#
		if($args['required']) { $html .= ' required="required"'; }
		if($args['readonly']) { $html .= ' readonly="readonly"'; }
		if(isset($args["placeholder"])) { $html.= ' placeholder="' . $args["placeholder"] . '"'; }
		
		$html .= 	'>';
		$html .= 	'<span class="error_hide">'.$args['error'].'</span>';
		$html .= 	'</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * Dropdown - maak een keuze mbv een dropdown box
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'choose' => keuze tekst bv Maak een keuze
	 * ]
	 */
	public function Dropdown($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= '*'; } 
		$html='';
		if($args['inline']== TRUE) 
		{
			$html .= '<div class="form-group row">'; 
			$html .= 	'<div class="' . $args["collabel"] .'">';
		}
		else 
		{ 
			$html .= '<div class="' . $args["col"] . '">';
			$html .= '<div class="control-label">';
		}
		$html .= 		'<label for="checkbox">' . $args["label"] . '</label>';
		$html .= 	'</div>';
		if($args['inline'] == TRUE) { $html .= '<div class="' . $args['colinput'] . '">'; }
		else { $html .= '<div class="controls">';}
		$options = "";
		$options .= '<option value="" selected>' . $args["choose"] . '</option>';   # keuze tekst
		foreach($args["options"] as $key => $value)
		{
			$selected = $value == $args["value"] ? " selected=selected" : "";
			$options .= '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
		}
		$html .= '<select name="' . $args['id'] . '" style="padding:0px;width:' . $args['width'] . ';height:' . $args['height'] . ';"';
		if($args['required']) { $html .= ' required'; }
		if($args['readonly']) { $html .= ' readonly="readonly"'; }
		$html .= '>';
		$html .= $options;
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	/**
	 * File - Read a file.
	 * @param array $args[
	 * 'label' => (string) Label of inputfield
	 * 'id' => (string) element id
	 * 'name' => (string) element name
	 * 'value' => (string) beginwaarde van het invoerveld
	 *  options = array of options objects to choose e.g. array[labelvalue1=>$value1,labelvalue2=$value2,...]
	 * 'required' => (Boolean) Input is required (default) or not
	 * 'readonly' => (Boolean) field is readonly
	 * 'collabel' => bootstrap position of label (in case of inline=TRUE)
	 * 'col' => bootstrap position (inline = FALSE)
	 * 'colinput' => bootstrap position of inputfield (inline = FALSE)
	 * 'inline' => (Boolean) label and field on one line
	 * 'accept' =>  comma-separated list of one or more file types, describing which file types to allow
	 * ]
	 */
	public function File($args)
	{
		$args = wp_parse_args( $args, $this->formdefaults );
		if(!$args["name"]) { $args["name"] = $args["id"]; }		# als name niet gedefinieerd is name = id
		if($args['required']) { $args["label"] .= '*'; } 
		$html='';
		$r='';
		$checkclass = isset($args["check"]) ? ' ' . $args["check"] : ''; # javascript test input on this class
		$html .= '<div class="form-group row">';
		$html .= 	'<div class="' . $args["collabel"] .'">';
		$html .= 		'<label for="' . $args["id"] . '"';
		#if(isset($args["popover"])) { $html .= ' class="hasPopover"  title="' . $args["popover"] . '"'; }
		$html .= 		'>' .  $args["label"] . '</label>';
		$html .=	'</div>';
		$html .= 	'<div class="' . $args["colinput"] . '">';
		$html .= '<input type="file" id="' . $args["id"] . '" class="form-control ' . $checkclass .'" name="' . $args['name'] . '" value="' . $args["value"] . '"';
		$html .= ' style="width:' . $args["width"] . '"';
		if($args["required"]) { $html .= ' required="required"'; }
		if($args["readonly"]) { $html .= ' readonly="readonly"'; }
		if($args["onchange"]) { $html .= ' onchange="' . $args["onchange"] . '"'; }
		if($args["accept"]) { $html .= ' accept="' . $args["accept"] . '"'; }
		$html .= $args["placeholder"] ? '' : ' placeholder="' . $args["placeholder"] . '"';
		$html .= 	'>';
		$html .= 	'</div>';
		$html .= '</div>';
		return($html);
	}
	#
	# Image
	# upload an image and show it directly
	# $args["uploads"] - upload map of images
	# $args["value"] - current image
	# $args["label"] = label of text box
	# $args["id"] = id and name
	# $args["width"] = width of image
	# $args["heigth"] = width of image
	# $args["required"] = 1 if the box is required
	# $args["collabel"] = bootstrap position label 
	# $args["accept"] = Only accept certain files (e.g. ".jpg,.jpeg")
	#
	public function Image($args)
	{
		$r='';
		if($this->required) { $args["label"] .= "*"; } 
		$value = isset($args["value"]) ? $args["value"] : "";
		$width = isset($args["width"]) ? $args["width"] : $this->width;
		$heigth = isset($args["heigth"]) ? $args["heigth"] : $this->heigth;
		$collabel = isset($args['collabel']) ? $args['collabel'] : $this->collabel;	
		$colinput = isset($args['colinput']) ? $args['colinput'] : $this->colinput;	
		$id=$args["id"];
		$value=isset($args["value"]) ? $args["value"] : '';
		$html .= '<div class="form-group row">';
		if(isset($args["label"]))
		{
			$html .= 	'<div class="' . $collabel .'">';
			$html .= 		'<label for="' . $args["id"] . '"';
			$html .= 		'>' .  $args["label"] . '</label>';
			$html .=	'</div>';
		}
		#$html .= '<div class="' . $this->colinput . '">';
		#
		# image element to place image in it
		#
		$uploads = $args['uploads'];
		$photo_url = home_url() . '/' . $uploads  . '/' . $value;
		$photo_map = ABSPATH . '/' . $uploads  . '/' . $value;
		$html .= '<img id="showimage" src="' . $photo_url . '?' . filemtime($photo_file) .'" width="' . $width .'" height="' . $heigth . '" alt="foto">';
		#
		# input the file
		# the class showimage is trigger for javascript ShowImage to show the image in the img above
		#
		$html .= '<div class="' . $colinput . '">';
		$html .= '<input type="file" id="' . $id . '" class="form-control showimage" name="' . $id . '" value="' . $value . '"';
		$html .= ' style="width:400px;"';
		if($this->required) { $html .= ' required="required"'; }
		if(isset($args["accept"])) { $html .= ' accept="' . $args["accept"] . '"'; }
		$html .= '>';
		$html .= '</div>';
		$html .= '</div>';
		return($html);
	}
	#
	# upload the seleceted file
	#
	#targetdir = directory to put the file in
	#name = name attribute of input element
	#filetypes = legal filetypes seperated by , e.g.: doc,docx,pdf
	#maxkb = maximum size of file in Kb
	#overwrite=1 (overwrite existing file allowed)
	#filename = filename (without extension), if not defined the original filename of the uploaded file is given
	#			extension is extension of original file
	#prefix=unique prefix to force unique filename (optional)
	#return value:
	# 1 : Bad filetype
	# 2 : file exists
	# 3 : file too big
	# 4 : File cannot be uploaded
	# 0 : upload succesfull
	public function UploadFile($args)
	{
		if(!isset($args["name"])) { $this->uploaderror = "name attribute not defined"; return(1); }
		$name = $args["name"];
		$prefix = isset($args["prefix"]) ? $args["prefix"] : "";
		$overwrite = isset($args["overwrite"]) ? $args["overwrite"] : FALSE;
		$ext = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		$filename = isset($args["filename"]) ? $args["filename"] . '.' . $ext : basename($_FILES[$name]["name"]);
		$file = $args["targetdir"] . '/' . $prefix . $filename;
		if(isset($args["filetypes"]))
		{
			$fileType = strtolower(pathinfo($file,PATHINFO_EXTENSION));
			$types=explode(",",$filetypes);
			$found = false;
			foreach($types as $t) { if($t == $fileType) { $found=true; } }
			if($found == false) { $this->uploaderror = "bad filetype"; return(FALSE); }
		}
		if(isset($args["maxkb"]))
		{
			$fileSize = $_FILES[$name]["size"];
			$maxsize = $args["maxkb"] * 1000;
			if($fileSize > $maxsize) { $this->uploaderror = " file too big"; return(FALSE); }
		}
		if($overwrite == FALSE && file_exists($file)) { $this->uploaderror = "file exists"; return(FALSE); }
		if (!move_uploaded_file($_FILES[$name]["tmp_name"], $file)) { $this->uploaderror = "cannot upload"; return(FALSE); }
		return(TRUE);
	}
	#
	# StoreImage
	# store the image in the map 
	# $args["uploads"] - upload map of images
	# $args["id"] = id and name
	# $args["name"] = name of image
	# $args["width"] = wiidth of image
	public function StoreImage($args)
	{
		$map = ABSPATH . $args["uploads"];
		echo '<br>map=' . $map . 'id=' . $args['id'];
		echo '<br>';
		#print_r($_FILES);
		if(isset($_FILES[$args["id"]]))
		{
			echo '<br>startupload';
			if (move_uploaded_file($_FILES[$args["id"]]['tmp_name'], $map))
			{
				return(TRUE);
			}
			return(FALSE);
		}
		return(FALSE);
	}
	#
	# resize the image 
	#
	public function resize_image($file, $w, $h, $crop=FALSE) 
	{
		list($width, $height) = getimagesize($file);
		$r = $width / $height;
		if ($crop) 
		{
			if ($width > $height) 
			{
				$width = ceil($width-($width*abs($r-$w/$h)));
			} 
			else 
			{
				$height = ceil($height-($height*abs($r-$w/$h)));
			}
			$newwidth = $w;
			$newheight = $h;
		} 
		else 
		{
			if ($w/$h > $r) 
			{
				$newwidth = $h*$r;
				$newheight = $h;
			} 
			else 
			{
				$newheight = $w/$r;
				$newwidth = $w;
			}
		}
		$src = imagecreatefromjpeg($file);
		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagejpeg( $dst, $file );
		return;
	}
}

?>