<?php
namespace SIMPELBOEK;
class Tableform
{
    #########################################################################################################
	# tableform
	#########################################################################################################
    #Variables to be set:
	
	public $table;		#tablename
	public $uid;		#unique id of table
	public $columns;	#array of columnnames to be displayed e.g.  [["id","nr","left"],["name","naam","left"]]
						#3 values for each column: column name, header of the table, adjustment in column (left,center,right)
	public $allcolumns;	#all columns of the table
	public $aligns;		#aligns of the columns (left,right or center)
	public $maxlines;	#maximum number of lines per page
	public $selectmaxlines;	#options for maxlines, defined in options.php
	public $backgroundcolor;	#backgroundcolor of table and forms
	public $filtercolor;	#backgroundcolor of filterbox in table
	public $permissions;	#permissions for maintaining table cr,md,dl,vw,cp(kopie maken)dm(demo records laden)
	public $onpage = 1;	#pagenummer will be changed bij POST value during 
	public $nextpage;	#clicked on nextpage in previuos run
	public $previouspage;	#clicked in previouspage in previuos run
	public $onsort = "id";		#column to be sorted
	public $sortorder = "DESC";	#order of sorting (ASC or DESC)
	public $prefilter;	#defined as argument like: field:content Display only the records matching this filter
	public $filters;	#user defined filters
	public $filtercolumns;	#Columns to be filtered  e.g. array("soort"=>"soortlabel","type"=>"typelabel")
	

	#
	# Displaytable.
	# This function reads the records from the database eventually filtered by the filteroptions.
	# The records are sorted if the headername of the column has been clicked.
	# This function displays a filterbox to make it possible to filter on the fields given in the filterarguments (filtercolumns)
	# The columns, defined in the columns argument (columns)
	# The records are printed in pages. Number of records per page is given as argument (maxlines)
	# At the button a button is displayed for creating a new record en a button to export the records to csv file
	#
	# POST values which are made foor forther actions on the list:
	# nextpage : currentpage
	# previouspage : correntpage
	# onsort : current sort column
	# onpage : current page
	 #
    # start er restart tableform
    #
    public function run()
    {   
        $html = '';
        # get the values of the previous run:
        if(isset($_POST['previouspage'])) {$this->previouspage = $_POST['previouspage']; }
        if(isset($_POST['nextpage'])) {$this->nextpage = $_POST['nextpage']; }
        if(isset($_POST['onpage'])) {$this->onpage = $_POST['onpage']; }
		#
		# sort button clicked.
		# get sort column and switch the sortorder
		#
        if(isset($_POST['sort'])) 
		{
			$this->onsort = $_POST['sort'];
			if(isset($_POST['sortorder']))
			{
				if($_POST['sortorder'] == "ASC") {$this->sortorder = "DESC";}
				if($_POST['sortorder'] == "DESC") {$this->sortorder = "ASC";}
			}
		}
        if(isset($_POST['filters'])) {$this->filters=json_decode(urldecode($_POST['filters'])); } #zet filters terug
        #echo "start";
        if(isset($_POST['createrecord'])) 
        { 
            $html .= $this->CreateRecord(); # function in tableform.php
            return($html);
        }
        if(isset($_POST['modifyrecord'])) 
        { 
            $html .= $this->ModifyRecord(); # function in tableform.php
            return($html);
        }
        if(isset($_POST['copyrecord'])) 
        { 
            $html .= $this->CopyRecord(); # function in tableform.php
            return($html);
        }
        #
        # write record to database
        #
        if(isset($_POST['writerecord'])) 
        { 
            $html .= $this->WriteRecord();
            $html .= $this->DisplayTable();
            $html .='<input id="' . $this->class . '" name="' . $this->class . '" type="hidden" />';
            return($html);
        }
        if(isset($_POST['deleterecord'])) 
        { 
            $html .= $this->DeleteRecord();
            $html .= $this->DisplayTable();
            $html .='<input id="' . $this->class . '" name="' . $this->class . '" type="hidden" />';
            return($html);
        }
        $filters = array();
        if(isset($_POST['filter']))
        {
            foreach ($this->filtercolumns as $c => $label)
            {
                if(isset($_POST[$c])) { $filters[$c] = $_POST[$c]; }
                $this->filters = (object)$filters;
            }
        }
		$html .= $this->DisplayTable();
        
        $html .='<input id="' . $this->class . '" name="' . $this->class . '" type="hidden" />';
		return($html);
	}
	public function DisplayTable()
	{
		global $wp;
		$self = new self();
		$dbio = new dbio();		#class for database I/O
		$form = new forms();	#class for formfields
		$html = '';
		#
		# read all column info
		#
		$this->allcolumns = $dbio->DescribeColumns(array("table"=>$this->table));	#get information about all columns
		#
		# handle the POST values
		#
		#if(isset($_POST['onpage'])) { $this->onpage = $_POST['onpage']; } #on which page are we?
		if($this->nextpage) { $this->onpage += 1; } # next page given so go the next page
		if($this->previouspage) { $this->onpage -= 1; } # next page given so go the next page
		#
		# count number of records and calculate number of pages
		#
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"prefilter"=>$this->prefilter,"filters"=>$this->filters));
		$NumberOfRecords=count($pb);
		if(!$this->maxlines) { $this->maxlines=$NumberOfRecords; } # if maxlines not defines: show all records
		$pages=ceil($NumberOfRecords/$this->maxlines);
		$sort = $this->onsort . ' ' . $this->sortorder;
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"page"=>$this->onpage,"maxlines"=>$this->maxlines,"sort"=>$sort,"prefilter"=>$this->prefilter,"filters"=>$this->filters));
		#
		# start the form and table
		#
		#$html .= '<div class="prana-display">';
		#
		# Show number of rows and pages and a help function if defined
		#
		#
		$html .= '<div class="row">';
		$html .= '<div class="col-md-6")>';
		$html .= '</div>';
		if(isset($this->filtercolumns) && count($this->filtercolumns))
		{
		$html .= '<div class="col-md-5 prana-box">';
		$html .= '<h3>' . __( 'ZOEKEN', 'prana' ) . '</h3>';
		#
		# print filterform
		#
		if(isset($this->filtercolumns))
		{
			foreach ($this->filtercolumns as $c => $label)
			{
				$value="";
				#
				# has filters a content?
				#
				if(isset($this->filters->$c))
				{
					$value=$this->filters->$c;
				}
				#echo "<br>before filter" . $value;
				$form->formdefaults['required']=FALSE;
				$form->formdefaults['collabel']="col-md-5";
				$form->formdefaults['colinput']="col-md-7";
				
				$html .= $form->Text(array("label"=>$label, "id"=>$c, "value"=>$value, "popover"=>__( 'info zoeken', 'prana' )));
			}
		}
		$html .= '<button class="prana-btnsmall" name="filter">' .  __( 'ZOEK', 'prana' ) . '</button>';
		$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '<br><br>';
		#
		#
		#
		#$html .= '</form>';
		#
		# start the table
		#
		$html .= '<br>';
		$html .= '<table class="compacttable">';
		$html .= '<tr>';
		$i=0;
		#foreach ($this->columns as $name => $translation)
		foreach ($this->columns as $c)
		{
			$thclass = "compactth";
			$type = $c[2] ? $c[2] : "string";	// default type is string
			if($type == "number" || $type == "euro" || $type=="stringright") {$thclass = "compactthright"; }	// getallen rechts aansluiten
			$sortfield='<button class="pbtn-header" name="sort" value="' . $c[0] . '">' . $c[1]  . '</button>';
			$html .= '<th class="' . $thclass .'">' . $sortfield . '</th>';
		}
		if (in_array("vw", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("dl", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("md", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		if (in_array("cp", $this->permissions)) {$html .= '<th class="compactth"></th>';}	#Empty header for view button
		$html .= '</tr>';
		#
		# print rows
		#
		$uid = $this->uid;
		$html .= '<tbody>';
		foreach ( $pb as $p )
		{
			$html .= '<tr class="compacttr">';
			$i=0;
			foreach($this->columns as $c)
			{
				$tdclass = "compacttd";
				$type = $c[2] ? $c[2] : "string";	// default type is string
				if($type == "number" || $type == "euro") {$tdclass = "compacttdright"; }	// getallen rechts aansluiten
				$name=$c[0];
				$html .= '<td class="' . $tdclass . '"' . $c[2] . '">' . $p->$name . '</td>';
				$i++;
			}
			#
			# view / modify / delete / copy buttons
			#
			if (in_array("vw", $this->permissions)) 
			{
				#$html .= '<td><button type="submit" class="btn btn-link btn-xs showrecord" name="showrecord" value="' . $p->id . '"><i class="fa fa-eye"></i></button>';
				$html .= '<td class="compacttd showrecord"><a class="btn btn-link btn-xs"><i class="fa fa-eye"></a></td>';
			}
			
			if (in_array("dl", $this->permissions)) 
			{ 
				$message=sprintf( __( '%s %d verwijderen , zeker weten?', 'prana' ),$this->single,$p->$uid);
				#$html .= '<td><button type="submit" class="btn btn-link btn-xs" name="deleterecord" onclick="return confirm(\'' . $message. '\'value="' . $p->$this->uid . '"><i class="fa fa-trash"></i></button></td>'; 
				$html .= '<td class="compacttd"><button type="submit" name="deleterecord" class="btn btn-link btn-xs" onclick="return confirm(\'' . $message. '\');" value="' . $p->$uid . '"><i class="fa fa-trash"></i></button></td>';
			}
			if (in_array("md", $this->permissions)) 
			{ 
				$html .= '<td class="compacttd"><button type="submit" name="modifyrecord" class="btn btn-link btn-xs" value="' . $p->id . '"><i class="fa fa-pencil"></i></button></td>';
			}
			if (in_array("cp", $this->permissions)) 
			{ 
				$html .= '<td class="compacttd"><button type="submit" name="copyrecord" class="btn btn-link btn-xs" value="' . $p->id . '"><i class="fa fa-copy"></i></button></td>'; 
			}

			$html .= '</tr>';
			$uid = $this->uid;
			$detail = $dbio->DisplayAllFields(array("table"=>$this->table,"key"=>$this->uid,"value"=>$p->$uid));
			$html .= '<tr>';
			$html .= '<td colspan="' . $i .'" class="showdetail">' . $detail . '</td>';
			$html .= '</tr>';
			
			
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<br>';
		#
		# buttons for next and previous page
		#
		$html .= __( 'TOTAAL AANTAL RECORDS', 'prana' ) . ':' . $NumberOfRecords . '&nbsp' . __( 'AANTAL PAGINA\'S', 'prana' ) . ':' . $pages;
		if($pages) { $html .= sprintf( __( ' bladeren: ', 'prana' ),$this->onpage,$pages); }
		if($this->onpage > 1) { $html .= '<button type="submit" class="btn btn-link btn-sx" name="previouspage" value="' . $this->onpage . '"><i class="fa fa-caret-square-o-left" style="font-size:24px"></i></button>'; }
		if($this->onpage < $pages) { $html .= '<button type="submit" class="btn btn-link btn-sx" name="nextpage" value="' . $this->onpage . '"><i class="fa fa-caret-square-o-right" style="font-size:24px"></i></button>'; }
		$html .= '<div style="float:right" class="row">';
		#
		# demo records laden
		#
		if($NumberOfRecords == 0 && in_array("dm", $this->permissions)) #demo button if there are no records
		{
			$html .= '<button class="pbtnok" name="demorecords" value="";>'.  __( 'laad demo records', 'prana' ) . '</button>';
			$html .= '&nbsp;';
		}
		#
		# create new record
		#
		if( in_array("cr", $this->permissions))
		{
			$html .= '<button class="pbtnok" name="createrecord" value="";>'.  __( 'nieuw', 'prana' ) . '</button>';
			$html .= '&nbsp;';
		}
		$html .= $this->ExportRecords();		# export records in csv file
		$html .= '</div>';
		$html .= '<br><br><br>';
		#
		# set post values
		#
		$html .='<input id="onpage" name="onpage" type="hidden" value=' . $this->onpage .  ' />';	#current page
		$html .='<input id="onsort" name="onsort" type="hidden" value=' . $this->onsort .  ' />';	#current sort column
		$html .='<input id="sortorder" name="sortorder" type="hidden" value=' . $this->sortorder .  ' />';	#current sort column
		#
        # geef huidige filters door een POST values om ze weer terug te kunnen krijgen bij volgende klik
        #
        $html .='<input id="filters" name="filters" type="hidden" value=' . urlencode(json_encode($this->filters)) .  ' />';
        #
        # zorg ervoor dat class rekeningen weer wordt gestart als er ergens op geklikt is
        #
        $html .='<input id="filters" name="filters" type="hidden" value=' . urlencode(json_encode($this->filters)) .  ' />';
		return($html);
	}
	public function CreateRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        foreach ($columns as $c)
        {
            if($c != $this->uid)
            {
                $this->fields[$c]='';
            }
        }
        $html = '';
		$html .=  sprintf('<h2>' . __( 'Nieuwe %s aanmaken', 'prana' ) . '</h2>',$this->single);
        $html .= $this->FormTable("create");
        return($html);
    }
	public function ModifyRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        $p = $dbio->ReadUniqueRecord(array("table"=>$this->table,"key"=>$this->uid,"value"=>$_POST['modifyrecord']));
        foreach ($columns as $c)
        {
            $this->fields[$c]=$p->$c;
        }
		$html .=  sprintf('<h2>' . __( '%s wijzigen', 'prana' ) . '</h2>',$this->single);
        $html .= $this->FormTable("modify");
        return($html);
    }
	public function CopyRecord()
    {
        $html = '';
        $dbio = new DBIO();
        $columns = $dbio->columns($this->table);
        $p = $dbio->ReadUniqueRecord(array("table"=>$this->table,"key"=>$this->uid,"value"=>$_POST['copyrecord']));
        foreach ($columns as $c)
        {
			if($c != $this->uid)
            {
				$this->fields[$c]=$p->$c;
            }
        }
        $html .=  sprintf('<h2>' . __( '%s kopieren', 'prana' ) . '</h2>',$this->single);
        $html .= $this->FormTable("create");
        return($html);
    }
	#
	# write a record to the database
	# fields should be in POST parameters
	# $_POST['crmod'] = 'create' or 'modify'
	public function WriteRecord()
	{
        $html = '';
        $dbio = new DBIO();
        $fields = array();
        $columns = $dbio->columns($this->table);
        foreach ($columns as $c)
        {
            if(isset($_POST[$c]))
            {
                $fields += [$c=>$_POST[$c]];
            }
        }
        if($_POST['crmod'] == "create")
        {
            $id=$dbio->CreateRecord(array("table"=>$this->table,"fields"=>$fields));
            $html .= sprintf(__('%s %d is aangemaakt','prana'), $this->single, $id);
           
        }
        if($_POST['crmod'] == "modify")
        {
            $dbio->ModifyRecord(array("table"=>$this->table,"fields"=>$fields,"key"=>$this->uid,"value"=>$fields[$this->uid]));
            $html .= sprintf(__('record %d is gewijzigd','prana'), $fields[$this->uid]);
        }
        return($html);
    }
	public function DeleteRecord()
	{
		$html = '';
		$dbio = new dbio();
		$html .= sprintf(__('%s %d is verwijderd','prana'), $this->single, $_POST['deleterecord']);
		$dbio->DeleteRecord(array("table"=>$this->table,"key"=>$this->uid,"value"=>$_POST['deleterecord']));
		return($html);
	}
	#
	# Export records to be used in Excell and now using javascript
	#
	public function ExportRecords()
	{
		global $wpdb;
		$dbio = new dbio();
		$export = '';
		#$export .= '<div>';
		$export .= '<table style="display:none">';
		#$export .= '<table class="csvexport" style="display:none">';
		$export .= '<tr>';
		foreach ($this->allcolumns as $c)
		{
			$export .= '<th>' . $c->Field . '</th>';
		}
		$export .= '</tr>';
		$pb = $dbio->ReadRecords(array("table"=>$this->table,"prefilter"=>$this->prefilter,"filters"=>$this->filters));
		foreach ( $pb as $p )
		{
			$export .= '<tr>';
			foreach ($this->allcolumns as $c)
			{
				$name=$c->Field;
				$export .= '<td>' . $p->$name . '</td>';
			}
			$export .= '</tr>';
		}
		$export .= '</table>';
		$filename = $this->table . '.csv';	#add csv extension
		#$export .= '<p id="exportfilename" style="display:none">'.$filename.'</p>';
		#$export .= '<button id="exporttable" class="prana-btnhigh">export</button>';   #javascript export.js does the rest
		$export .= '<span style="display:none">'.$filename.'</span>';
		$export .= '<button class="pbtnok exporttable">export</button>';   #javascript export.js does the rest
		#$export .= '</div>';
		return($export);
	}
}
?>