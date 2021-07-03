$(document).ready(ManParagraph);
$(document).ready(ManTable);
//$(document).ready(NiceTable);
//$(document).ready(DoWrap);
$(document).ready(ToggleConstants);
$(document).ready(Animate);
$(document).ready(AnimateMymenu);
$(document).ready(AnimateMytabs);
$(document).ready(CheckInput);
$(document).ready(CheckForm);
$(document).ready(ExLoadPage);
$(document).ready(WeatherEvent);
$(document).ready(DatePicker);
$(document).ready(Slider);
$(document).ready(Tabs);
$(document).ready(ShowImage);
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
}
/*
	change the src of the image with id = showphoto to the file which is choosen by file element with class showfile
*/
function ShowImage(event)
{
	$(".showimage").on("change",function(event)
	{
		//$('#showphoto').attr('src',URL.createObjectURL(event.target.files[0]));
		$(this).siblings("img").attr('src',URL.createObjectURL(event.target.files[0]));
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
function ManParagraph()
{
		//
		// test1 mnupilaties
		//
		$("p.test1")
				.css("background-color","red")
				.on("click",function()
				{
						$(this).slideUp(); // Hide the matched elements with a sliding motion.
				});
}
/*
	add sort, paginate, filter to a table
*/
function NiceTable()
{
	$('#mdt_table').DataTable(
	{
		"language":
		{
			"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Dutch.json"
		}
	});	
	//$("#mdt_table tr:odd").css("background-color","grey");
}
function ManTable()
{
		//
		// tabel artikelen  manipulaties
		//
		const rijen = $("#artikelen tr");		//alle rijen
		$("#result").html("aantal rijen: " + rijen.length);
		$("#result").append("aantal rijen: " + rijen.length);
		//
		// oneven rijen in tabel achtergrond grijs
		//
		$("#artikelen tr:odd").css("background-color","grey");
}
function DoWrap()
{
		$("#test2").wrap("<strong />");
}
function ToggleConstants()
{
		$('#constants').on('click', function()
		{
				$('#constantscontent').slideToggle();
		});
}
function Animate()
{
		const animationOptions =
		{

				'font-size' :'40px'
		};
		$('#constants').on('click', function()
		{
				$('#constantscontent').animate(animationOptions,1500);
		});
}
function AnimateMymenu()
{
	const hoverInOptions =
	{
		'margin-left': '+=50px',
		'font-size': '+=2px'
	};
	const hoverOutOptions =
	{
		'margin-left': '-=50px',
		'font-size': '-=2px'
	};
	$('#mymenu li').hover
	(
		function()
		{
			$(this).animate(hoverInOptions,200);
		},
		function()
		{
			$(this).animate(hoverOutOptions,200);
		}
	);
}
function AnimateMytabs()
{
	// verberg alle tabs behalve de eerste
	$('#mycontent div:not(:first)').hide();
	// aanhaken van klik op de tab hyperlink
	$('#mytabs a').on('click', function(e)
	{
		// voorkom standaardgedrag klik op hyperlink
		e.preventDefault();
		// verberg inhoud van alle tabs
		$('#mycontent div').hide();
		//verwijder classe .selected van alle tabs
		$('#mytabs a.selected').removeClass('selected');
		//voeg classe . selecetd toe aan huidige tab
		$(this).addClass('selected');
		// toon aangeklikte tab
		const selectie = $(this).attr('href');
		$(selectie).show();
	});
}
/*
function ExLoadPage()
{
	const url="https://localhost/mijnwordpress/license.txt";
	$('#exload1').on('click',function()
	{
		$('#exload1result').load('https://localhost/mijnwordpress/overzicht-artikelen/');
	}
	);
}
*/
/*
	load a server page and put in element
*/
function ExLoadPage()
{
	const url=$('#exloadurl').text();		// defined in php function 
	//console.log("url"+url);
	$('#exload').on('click',function()		// when clicked on button load data fram url
	{
		$.ajax({url:url,success:DoLoad});
	}
	);
}
function DoLoad(data)
{
	$('#exloadresult').text(data);
}
/*
	load wheater
*/
function WeatherEvent()
{
	$('#getweather').on('click',WeatherLoad);
}
function WeatherLoad()
{
	const city = $('#city').val();
	const apid = 'f2e6852577627c446d4de7769c370b50';
	const url = 'https://api.openweathermap.org/data/2.5/weather/?appid=' + apid + '&q=' + city;
	$.ajax({url:url,success:WeatherShow});
}
function WeatherShow(data)
{
	//console.log(data);
	const html = '<h3>' + data.name + '</h3>';
	console.log(html);
	$('#weatherresult').html(html);
}
function DatePicker()
{
	$('.datepicker').datepicker(
	
	{
		dateFormat : 'dd-mm-yy',
		monthNames : ['januari', 'februari', 'maart', 'april', 'mei', 'juni','juli', 'augustus', 'september', 'oktober', 'november', 'december']
	}

	);
}
/*
	Een getal met een schuifbalk opgeven
*/
function Slider()
{
	$('.slider').slider(
	{
		min: 0,
		max: 100,
		value:50,
		animate: true,
		slide: function(event,ui)
		{
			$('#myresultslider').html('Waarde: ' + ui.value);
		}
	}
	);
		
}
/*
 Informatie onder tabs plaatsen
 de meest eenvoudige inhoud van de functie is $('.dotabs').tabs();
 Hieronder wat uitgebreider met verplaatsbare tabs en zo
*/
function Tabs()
{
	$('.dotabs').tabs(
	{
		event: 'mouseover',
		activate: function(event,ui)
		{
			$('#resulttab').html('Zichtbare tabs: ' + $('.ui-tabs-active').text());
		}
	});
	// tabs sorteerbaar / versleepbaar maken
	$('.dotabs .ui-tabs-nav').sortable(
	{
		axis: 'x',
		stop: function()
		{
			$('.dotabs').tabs('refresh');
		}
	});
}


