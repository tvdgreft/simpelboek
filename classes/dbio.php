<?php
#
# dbio 
# read and write database 
#
namespace SIMPELBOEK;

class Dbio
{
	#Create a table
	public function CreateTable($table,$columns)
	{
		global $wpdb;
		if(!$table) { return(__( 'Geen tabelnaam opgegeven', 'prana' )); }
		$query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix.$table . '` (' . $columns . ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		$wpdb->query($query);
		if($wpdb->last_error !== '')
		{
			$wpdb->print_error();
			return(__( 'database error', 'prana' ));
		}
		return(0);
	}
	/**
 	* Returns the count of records in the database.
 	*
	 * @return null|string
 	*/
	public function CountRecords($table) 
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $table;
		$sql = "SELECT COUNT(*) FROM $wptable";
  
		return $wpdb->get_var( $sql );
 	}
	#
	# get description of all columns
	#
	
	public function DescribeColumns($args)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $args["table"];
		$query = 'DESCRIBE '.$wptable;
		$result=$wpdb->get_results($query);
		return($result);
	}
	public function Columns($table)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $table;
		$columns = $wpdb->get_col("DESC {$wptable}", 0);
		return($columns);
	}
	#
	# read a record 
	# $args['table'] - databasetable
	# $args['id'] - id of record
	public function ReadRecord($args)
	{
		global $wpdb;
		$table = isset($args["table"]) ? $args["table"] : "";
		$wptable = $wpdb->prefix . $table;
		$id = isset($args["id"]) ? $args["id"] : "";
		$query='SELECT * FROM '. $wptable .' WHERE  id ="' . $id .'"';
		$row=$wpdb->get_row( $query );
		return($row);
	}
	#
	# read a record with unique key
	# $args['table'] - databasetable
	# $args['key'] - name of unique key
	# $args['value'] - value of unique key
	public function ReadUniqueRecord($args)
	{
		global $wpdb;
		$table = isset($args["table"]) ? $args["table"] : "";
		$wptable = $wpdb->prefix . $table;
		$query='SELECT * FROM '. $wptable .' WHERE ' . $args["key"] . ' ="' . $args["value"] .'"';
		#echo "<br>" . $query;
		$row=$wpdb->get_row( $query );
		return($row);
	}
	# ReadRecords 
	# $args['table'] - databasetable
	# $args['sort'] - column to be sorted
	# $args['prefilter'] - overall filter defined in call (columnname:value)
	# $args['filters'] - Array ( [column1] => value [column2] => value ........ ) 
	# 					Bij prefilter en filters : value may be preceded by:
	#					# : search on full content
	#					< : content should be <= value
	#					> : content should be >= value
	# $args['filter']	string: user defined query e.g. "datum >= 2021-01-01 and datum <= 2021-12-31
	# $args["search'] - array(array ('column1','column2' ....),$value)
	#					- match $value in the given columns
	# $args['page'} - current pagenumber
	# $args['maxlines'] - maxlines per page
	# $args['output'] - (string) (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. default=OBJECT
	public function ReadRecords($args)
	{
		$table = isset($args["table"]) ? $args["table"] : "";		
		$sort = isset($args["sort"]) ? $args["sort"] : "";
		$prefilter = isset($args["prefilter"]) ? $args["prefilter"] : "";
		$filters = isset($args["filters"]) ? $args["filters"] : "";
		$filter = isset($args["filter"]) ? $args["filter"] : "";
		$search = isset($args["search"]) ? $args["search"] : "";
		$page = isset($args["page"]) ? $args["page"] : "";
		$maxlines = isset($args["maxlines"]) ? $args["maxlines"] : "";
		$output = isset($args["output"]) ? $args["output"] : "OBJECT";
		#
		# make conditions for the query
		#
		$conditions='';
		#
		# translate filters to query conditions
		#
		#
		# first check prefilter
		# Modified: zoek op hele tekst of deel ervan
		if($prefilter)
		{
			foreach($prefilter as $i => $value) 
			{
				if($conditions) {$conditions .= ' and '; }
				if(preg_match("/#/",$value))
				{
					$key=substr($value,1);   #search on full content
				}
				else
				{
					$key = "%" . $value . "%"; #match on content
				}
				$conditions .= '('. $i . ' LIKE "' . $key . '")';
			}
		}
		if($filter)
		{
			if($conditions) {$conditions .= ' and '; }
			$conditions .= '(' . $filter . ')';
		}
		#
		# search value in given columns
		#
		if($search)
		{
			$columns = $search[0];
			$value = $search[1];
			
			foreach ($columns as $f)
			{
				$key = "%" . $value . "%"; #match on content
				if($conditions) {$conditions .= ' or '; }
				$conditions .= '('. $f . ' LIKE "' . $key . '")';
			}
		}
		if($filters)
		{
			#print_r($filters);
			foreach($filters as $f => $value)
			{
				#echo $f . ':' . $value .'<br>';
				if($conditions) {$conditions .= ' and '; }
				#
				# If < or > before value search on <= resp >=
				#
				if(preg_match('/^>(.*)/',$value,$match))   
				{
					$value = $match[1];
					$conditions .= '('. $f . ' >= "' . $value . '")';
				}
				#
				# when prefix of filter is max_ then the key  the maximum value of a field.
				#
				elseif(preg_match('/^<(.*)/',$value,$match))   
				{
					$value = $match[1];
					$conditions .= '('. $f . ' <= "' . $value . '")';
				}
				# if key numerical search on full field or word in field
				#
				#
				elseif(is_numeric($value))
				{
					$conditions .= '('. $f . ' = "' . $value . '"';
					$conditions .= ' or ';
					$key = '"' . $value . '" ';
					$conditions .= $f . ' LIKE ' . $key;
					$conditions .= " or ";
					$key = ' "' . $value . '" ';
					$conditions .= $f . " LIKE " . $key;
					$conditions .= " or ";
					$key = ' "' . $value . '"';
					$conditions .= $f . ' LIKE ' . $key . ')';
				}
				else
				{
					if(preg_match("/#/",$value))
					{
						$key=substr($value,1);   #search on full content
					}
					else
					{
						$key = "%" . $value . "%"; #match on content
					}
					$conditions .= '('. $f . ' LIKE "' . $key . '")';
				}
			}
		}
		#
		# start the query
		#
		#echo "<br>conditions=" . $conditions;
		global $wpdb;
		$wptable = $wpdb->prefix . $table;
		$query='SELECT * FROM '. $wptable;
		if($conditions) { $query .= ' WHERE ' . $conditions;}
		#
		# sort argument
		# translate to query sort field
		#
		#echo "<br>sort=" . $sort;
		if($sort &&  $sort != "no")
		{
			$query .= ' ORDER BY ' . $sort;
		}
		#
		# $limit is maximum number of rows to be displayed
		# $page = current pagenumber
		# so calculate offset
		#
		if($maxlines)
		{
			$offset=0;
			if(is_numeric($maxlines)) { $offset=($page-1)*$maxlines; }
			$query .= ' LIMIT '.$offset.','. $maxlines;
		}
		#
		#echo '<br>' . $query;
		$rows=$wpdb->get_results( $query , $output );
		return($rows);
	}
	#
	# create a record
	# the fields created and modified are set to the current date
	# $args['fields'] - array of fields $fields=array("field1"=>$value,"field2"=>$value .... )
	# $args['table'] - table 
	public function CreateRecord($args)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $args["table"];
		$query = 'INSERT INTO ' . $wptable . '(';
		foreach ($args["fields"] as $f =>$value)
		{
			$query .= $f .',';
		}
		$query = rtrim($query,',');	#remove last komma
		$query .= ')';
		$query .= ' VALUES (';
		foreach ($args["fields"] as $f =>$value)
		{
			if($f == "created") { $value = date("Y-m-d H:i:s"); }
			if($f == "modified") { $value = date("Y-m-d H:i:s"); }
			$query .= '"' . $value . '",';
		}
		$query = rtrim($query,',');	#remove last komma
		$query .= ')';
		#echo $query;
		#$sql=$wpdb->prepare($query);
		#print_r($sql);
		$result=$wpdb->query($query);
		return $wpdb->insert_id;
	}
	# $args['table'] - databasetable
	# $args['fields'] - array of fields $fields=array("field1"=>$value,"field2"=>$value .... )
	# $args['key'] - name of unique key
	# $args['value'] - value of unique key
	public function ModifyRecord($args)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $args["table"];
		$query = 'UPDATE('.$wptable . ')';
		$query .= ' SET';
		foreach ($args["fields"] as $f =>$value)
		{
			if($f == "modified") { $value = date("Y-m-d H:i:s"); }
			$query .= ' ' . $f . '="' .$value . '",';
		}
		$query = rtrim($query,',');	#remove last komma
		$query .= ' WHERE ' . $args["key"] . ' ="' . $args["value"].'"';
		echo $query;
		
		$result=$wpdb->query($query);
		return($result);
	}
	# $args['table'] - databasetable
	# $args['fields'] - array of fields $fields=array("field1"=>$value,"field2"=>$value .... )
	# $args['where'] - array of fields for where clause e.g."where"=array("id"=>$id)
	public function UpdateRecord($args)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $args["table"];
		#
		# als er een veld modified voorkomt in een tabel zet er dan een tiemstamp in
		#
		if(in_array("modified",$this->columns($args["table"])))
		{
			$date = date("Y-m-d H:i:s");
			$args["fields"] += array("modified"=>$date);
		}
		$result = $wpdb->update($wptable, $args["fields"], $args["where"]);
		$result=1;
		return($result);
	}
	# $args['table'] - databasetable
	# $args['key'] = name of unique key
	# $args['value'] = value of unique key
	public function DeleteRecord($args)
	{
		global $wpdb;
		$table = isset($args["table"]) ? $args["table"] : "";
		$wptable = $wpdb->prefix . $table;
		$result=$wpdb->delete( $wptable, array( $args["key"] => $args["value"] ) );
		return($result);
	}
	# $args['table'] - databasetable
	public function DeleteAllRecords($args)
	{
		global $wpdb;
		$table = isset($args["table"]) ? $args["table"] : "";
		$wptable = $wpdb->prefix . $table;
		$query = 'DELETE FROM ' . $wptable;
		$result=$wpdb->query($query);
		return($result);
	}
	#
	# display all fields of a record
	# $args['table'] - databasetable
	# $args['key'] - name of unique key
	# $args['value'] - value of unique key
	public function DisplayAllFields($args)
	{
		global $wp;
		global $wpdb;
		$table = isset($args["table"]) ? $args["table"] : "";
		$wptable = $wpdb->prefix . $table;
		$html = '';
		#
		# get the column names in the table
		#
		$columns = $wpdb->get_col("DESC {$wptable}", 0);
		$p=$this->ReadUniqueRecord($args);
		#
		# display content of all fields
		#
		foreach($columns as $c)
		{
			#$x=$f->Field;
			$html .= '<div class="form-group row" style="margin-bottom:2px;">';
			$html .= 	'<div class="col-md-2">';
			$html .= $c;
			$html .= '</div>';
			$html .= 	'<div class="col-md-6">';
			$html .= $p->$c;
			$html .= '</div>';
			$html .= '</div>';
		}
		return($html);
	}
	/**
	 * $args['table'] - databasetable
	 * $args["column'] - distinct column
	 * $args['output'] - (string) (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. default=OBJECT
	 */
	public function DistinctRecords($args)
	{
		global $wpdb;
		$wptable = $wpdb->prefix . $args["table"];
		$output = isset($args["output"]) ? $args["output"] : "OBJECT";
		$query = 'SELECT DISTINCT ' . $args['column'] . ' FROM ' . $wptable;
		$query .= ' ORDER BY ' . $args['column'];
		#echo '<br>' . $query;
		$rows=$wpdb->get_results( $query , $output );
		return($rows);
    }
}
?>
