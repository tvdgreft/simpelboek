$(document).ready(ExportCsv);
/*
export a table to a csvfile and start download link
The html file should have the following structure
<div>
	<table style="display:none">
		<tr> <th>....</th>.....
		</tr>
		<tr> <td> .... </td> .....<td>....</td> .....
		</tr>
		......
	</table>
	<span style="display:none">'.$filename.'</span>
	<button class="exporttable">export</button>
</div>
*/
function ExportCsv()
{
	$('.exporttable').on("click",function()
	{
		var csv= [];
		var filename = $(this).siblings("span").text();				// get filename
		$(this).siblings("table").find("tr").each(function(index)	// get all tr elements
		{
			var row = [];
			var cols=$(this).find("th,td");							// get th and tr elements in tr element
			for (var j = 0; j < cols.length; j++)
			{
				//alert(cols[j].innerText);
				row.push(cols[j].innerText);
			}
			csv.push(row.join(";"));
		});
		downloadCSV(csv.join("\n"), filename);
		//alert("export pressed");
	});
}
function downloadCSV(csv, filename) 
{
    var csvFile;
    var downloadLink;

    // CSV file
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Hide download link
    downloadLink.style.display = "none";

    // Add the link to DOM
    document.body.appendChild(downloadLink);

    // Click download link
    downloadLink.click();
}
