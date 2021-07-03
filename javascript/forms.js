$(document).ready(CheckInput);
$(document).ready(CheckForm);
$(document).ready(DatePicker);
//$(document).ready(ShowImage);
//$(document).ready(SetPopover);
$(document).ready(DisableEnterKey);	// disable enter key for forms
/*
	check input of fields in a form.
	The updatebutton to close the form should have the class checkformbutton
	When button has been pressed, the fields are checked to be valid.
*/
	
function CheckForm()
{
	// After Form Submitted Validation
	$(".checkformbutton").click(function(event)
	{
		//var invalid=$("#myemail").hasClass("invalid");
		//var invalid=$("#myform #myemail").hasClass("invalid");
		//console.log("emailvar="+invalid);
		var formid = $(this).closest("form").attr("id");	// get id of the form where the button is in.
		//console.log("formid="+formid);
		//alert("formid="+formid);
		var form_data=$("#"+formid).serializeArray();       // Encode form elements as an array of names and values.
		//alert( JSON.stringify(form_data) );
		var error_free=true;
		var regex = new RegExp(/\[\]/);
		for (var input in form_data)
		{
			name=form_data[input]['name'];
			if(regex.test(name) == true) { continue; }
			//console.log("form="+form_data[input]['name']);
			var element=$("#"+formid+" #"+form_data[input]['name']);
			//console.log( JSON.stringify(element) );
			var invalid=element.hasClass("invalid");
			//console.log("var="+invalid);
			var error_element=$("span", element.parent()); // get parent of <input> and <span> of that parent, that is the errormessage
			//console.log("error="+error_element);
			if (invalid)
			{
				error_element.removeClass("error_hide").addClass("error_show"); 
				error_free=false;
			}
			else
			{
				error_element.removeClass("error_show").addClass("error_hide");
			}
		}
		if (!error_free){
			event.preventDefault(); 
		}
	});
}
function CheckInput()
{
	$(".checkemail").on("change",function()
	{ 
		var regex = new RegExp(/\S+@\S+\.\S+/);
		PranaWarning(this,regex);
	});
	$(".checkphone").on("change",function()
	{ 
		var regex = /(^\+[0-9]{2}|^\+[0-9]{2}\(0\)|^\(\+[0-9]{2}\)\(0\)|^00[0-9]{2}|^0)([0-9]{9}$|[0-9\-\s]{10}$)/i;
		PranaWarning(this,regex);
	});
	$(".checkbankrekening").on("change",function()
	{ 
		var regex = /(^[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}$)/i;
		PranaWarning(this,regex);
	});
}
/*
	change the src of the image with id = showphoto to the file which is choosen by file element with class showfile
*/
function ShowImage(event)
{
	$(".showimage").on("change",function(event)
	{
		//$('#showimage').attr('src',URL.createObjectURL(event.target.files[0]));
		$(this).parent("div").siblings("img").attr('src',URL.createObjectURL(event.target.files[0]));
	});
}
function PranaWarning(element,regex)
{
	var cssborder = { 'borderColor': 'red' };
	var cssoldborder = { 'borderColor': '' };
	var error = $(element).siblings("span").text();
	if(regex.test($(element).val()) == false)
	{
		alert (error);
		$(element).css(cssborder);
		$(element).addClass("invalid");
		$(element).removeClass("valid");
	}
	else
	{
		$(element).addClass("valid");
		$(element).removeClass("invalid");
		$(element).css(cssoldborder);
	}
}

function DatePicker()
{
	$('.datepicker').datepicker(
	
	{
		dateFormat : 'yy-mm-dd',
		monthNames : ['januari', 'februari', 'maart', 'april', 'mei', 'juni','juli', 'augustus', 'september', 'oktober', 'november', 'december']
	}

	);
}
function SetPopover() 
{
	$('[data-toggle="popover"]').popover();
}
/**
 * Disable the enter key for submitting a form
 */
function DisableEnterKey()
{
	$("form").keypress(function(e) 
	{
		//Enter key
		if (e.which == 13) {
	 	 return false;
		}
  	});
}