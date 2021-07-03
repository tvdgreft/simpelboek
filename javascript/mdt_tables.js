$(document).ready(HideDetail);
$(document).ready(ShowDetail);
//$(document).ready(OnPopover);

/*
	Extra row after each row in a table with detail information (display all fields)  about the row
	
*/
function HideDetail()
{
	$('.showdetail').closest('tr').hide();
}
function ShowDetail()
{
	$('.showrecord').on('click', function()
	{
		$(this).closest('tr').next().toggle();
	});
}

//function OnPopover()
//{
 // $('[data-toggle="popover"]').popover(
 // {
	//  html:true,
	//  container: 'body'
 //});
//}

