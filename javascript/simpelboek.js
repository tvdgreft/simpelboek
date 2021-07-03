var buttonclicked;			// will be set when button was clicked 
function DisableNoboek() 
{
	document.getElementById("openboekhouding").disabled = true;
}
function DisableNotOpen() {
	document.getElementById("importmutaties").disabled = true;
}
function ValForm()
{
	if(buttonclicked == "cancel")
	{
		return true;			// don't validate if cancel was clicked
	}
	if(buttonclicked == "rekening")
	{
		return(ValidateRekening());
	}
	if(buttonclicked == "maakbalans")
	{
		return(ValidateBalans());
	}
	/*
	var value = document.getElementById("bedrag");
	var regex = /^[0-9]*$/;
	if(regex.test(value.value) == false)
	{
		document.getElementById('bedrag').style.borderColor = "red";
		alert("Bedrag ongeldig");
		return false;
	}
	var value = document.getElementById("boekdatum");
	var regex = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
	if(regex.test(value.value) == false)
	{
		document.getElementById('boekdatum').style.borderColor = "red";
		alert("Boekdatum ongeldig");
		return false;
	}
	*/
	return true;
}
function ValidateRekening()
{
//
		// check rekeningnummer
		//
		var value = document.getElementById("rekeningnummer");
		
		var regex = /^[0-9]{3}$/;
		if(regex.test(value.value) == false)
		{
			document.getElementById('rekeningnummer').style.borderColor = "red";
			alert("rekeningnummer ongeldig 001-999");
			alert(value.value);
			return false;
		}
		var type = document.getElementById("type");
		var soortB = document.getElementById("soort_B");
		var soortR = document.getElementById("soort_R");
		var typeC = document.getElementById("type_C");
		var typeD = document.getElementById("type_D");
		
		//vsoort="n";
		//for (i = 0; i < soort.length; i++) { if (soort[i].checked) { vsoort=soort[i].value; }}
		//for (i = 0; i < type.length; i++) { if (type[i].checked) { vtype=type[i].value; }}
		//if((soort.value == "R") && (type.value == "C") && (value.value < 700))
		
		if(soortB.checked == true && typeC.checked == true && value.value > 100)
		{
			document.getElementById('rekeningnummer').style.borderColor = "red";
			alert("rekeningnummer moet tussen 001 en 099 liggen" + soortR.checked);
			return false;
		}
		if(soortB.checked == true && typeD.checked == true && (value.value < 100 || value.value > 199))
		{
			document.getElementById('rekeningnummer').style.borderColor = "red";
			alert("rekeningnummer moet tussen 100 en 199 liggen" + soortR.checked);
			return false;
		}
		if(soortR.checked == true && typeC.checked == true && value.value < 700)
		{
			document.getElementById('rekeningnummer').style.borderColor = "red";
			alert("rekeningnummer moet tussen 700 en 999 liggen" + soortR.checked);
			return false;
		}
		if(soortR.checked == true && typeD.checked == true && (value.value < 400 || value.value > 699))
		{
			document.getElementById('rekeningnummer').style.borderColor = "red";
			alert("rekeningnummer moet tussen 400 en 699 liggen" + soortR.checked);
			return false;
		}
		return true;
	}
	function ValidateBalans()
	{
		creditbedrag=0;
		debetbedrag=0;
		$('#beginbalans tr').each(function()
		{
			//var cells=[];
			credit = false;
			$(this).find('td').each(function()
			{
				if($(this).text() === 'C') { credit = true; }
				if($(this).text() === '') 
				{ 
					bedrag = $(this).find('input').val();
					ibedrag=parseInt(bedrag);
					if (isNaN(ibedrag)) { ibedrag = 0; }
					//cells.push(bedrag);
					//alert (bedrag);
				}
				//alert($(this).text());
				//cells.push($(this).text);    // adds a new element (Lemon) to fruits 
			//do your stuff, you can use $(this) to get current cell
			// naam,soort,type,bedrag
			})
			if(credit === true) { creditbedrag += ibedrag; }
			else { debetbedrag += ibedrag; }
			//alert('debet='+debetbedrag+'credit='+creditbedrag);
		})
		//alert('credit:' + creditbedrag);
		verschil = debetbedrag - creditbedrag;
		if(verschil != 0) 
		{ 
			totaal = 'Debet en credit moet in tottal gelijk zijn: debet=' + debetbedrag + ' credit=' + creditbedrag + ' verschil=' + verschil;
			$('#totaalbalans').text(totaal);
			return(false);
		}
		return(true);
	}
	function ValFormBoeking()
	{
		var value = document.getElementById("bedrag");
		var regex = /^[0-9]*$/;
		if(regex.test(value.value) == false)
		{
			document.getElementById('bedrag').style.borderColor = "red";
			alert("Bedrag ongeldig");
			return false;
		}
		var value = document.getElementById("boekdatum");
		var regex = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
		if(regex.test(value.value) == false)
		{
			document.getElementById('boekdatum').style.borderColor = "red";
			alert("Boekdatumatum ongeldig");
			return false;
		}
		return true;
	}