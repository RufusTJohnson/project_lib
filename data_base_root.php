<?php

// *********************************************************************************************
// *********************************************************************************************
// copyright 2014 SamSu, all rights reserved
//
// File:             data_base_root.php
// Date:             1 October 2014
// Description:      This script contains classes to access the database
// *********************************************************************************************
// *********************************************************************************************

// *****************************************************************************
// *****************************************************************************
// Business Data Class
// *****************************************************************************
// *****************************************************************************
class cDataBaseRoot
{
	var $mysqli;
	var $id;
	var $uid;
	var $sql_date_time;
	
	// Sql error
	var $sql_error;
	// *************************************************************************
	// Constructor
	// *************************************************************************
	function __construct($mysqli)	{
		$this->mysqli				= $mysqli;
		$this->sql_date_time		=	date("Y-m-d H:i:s", time()); 
	}	
	
	
	// *****************************************************************************
	// Get uid
	// *****************************************************************************
	function get_uid()
	{	
		return md5(uniqid(rand(), true));	
	}

	// *****************************************************************************
	// Get uid
	// *****************************************************************************
	function get_id()
	{	
		$this->id = $this->mysqli->insert_id;
	}


	// *****************************************************************************
	// query and throw error on problem
	// *****************************************************************************
	function query($query,$comment="no comment")
	{
		$mysqli		=	$this->mysqli;
		$result 		= 	$mysqli->query($query);
		
		if ($mysqli->error) 
		{
			throw new Exception("MySQL error - $mysqli->error :: Query - $query", $mysqli->errno);    
		}
		return $result;
	}	


	// *********************************************************************************************
	// Get database rows
	// *********************************************************************************************
	function get_rows($query,$options=NULL,$comment="")
	{
		$result		= $this->query($query,"get_rows");
		
		$rows = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$rows[] = $row;
		}
		return $rows;
	}
	
	// *********************************************************************************************
	// Get database row
	// *********************************************************************************************
	function get_row($query,$options=NULL,$comment="")
	{
		$result			= $this->query($query,"get_row");
		$num_rows 		= $result->num_rows;
		if ($num_rows==0)
		{
			$row 	= false;
		}
		else
		{
			$row 	= 	$result->fetch_array(MYSQLI_ASSOC);
			
		}
		return $row;
	}	
	
	// *********************************************************************************************
	// Make SQL Now2
	// *********************************************************************************************
	function make_sql_now()
	{
		return date("Y-m-d H:i:s", time()); 
	}	

	// *************************************************************************
	// Update Row in Table from Template
	//
	// Input:
	//    $table -                Name of the table the row should be written to
	//    $field_template -       List of columns (fields) to put data into
	//    $field_values -         Field Values
	// *************************************************************************
	function update_row_from_template($table,$field_template,$field_values)
	{
		$query 			= "UPDATE $table SET ";

		foreach($field_template as $field_name)
		{
			if (array_key_exists($field_name,$field_values))
			{
				$set_array[] = "$field_name="."'".$field_values[$field_name]."'";
			}
		}
		$query	.= 	implode(",",$set_array)." WHERE (parent=$this->id) \n";
		//echo("query $query<br />");
		$this->query($query,"Insert row into $table using update_row_from_template function");
	}
	

	// *************************************************************************
	// Insert Row into Table from Template
	//
	// Input:
	//    $table -                Name of the table the row should be written to
	//    $field_template -       List of columns (fields) to put data into
	//    $field_values -         Field Values
	// *************************************************************************
	function insert_row_from_template($table,$field_template,$field_values,$use_parent=true)
	{
		//echo("<pre>"); print_r($field_template); echo("</pre>");	
		//echo("<pre>"); print_r($field_value); echo("</pre>");	
		if ($use_parent)
		{
			$query 				= "INSERT $table SET id=NULL, parent=".$this->id.", ";
		}
		else
		{
			$query 				= "INSERT $table SET id=NULL, ";
		}

		foreach($field_template as $field_name)
		{
			if (array_key_exists($field_name,$field_values))
			{			
				$set_array[] = "$field_name="."'".$field_values[$field_name]."'";
			}
		}
		$query	.= 	implode(",",$set_array)."\n";
		//echo("ok query $query<br />");
		$this->query($query,"Insert row into $table using insert_row_from_template function");
	}







	// *************************************************************************
	// Generic Insert Row Function
	//
	// Input:
	//         $table -            name of table
	//         $$field_values -    array indexed by field names with values of
	//                             fields
	//
	// *************************************************************************
	function g_insert_row($table,$field_values,$use_parent=true)
	{
		//echo("<pre>"); print_r($field_values); echo("</pre>");
		$field_names_array = $table."_fields";
		//echo("<h3>$field_names_array ok </h3>");
		$this->insert_row_from_template($table,$this->$field_names_array,$field_values,$use_parent);
	}
	
	// *************************************************************************
	// Generic Update Row Function
	//
	// Input:
	//         $table -            name of table
	//         $$field_values -    array indexed by field names with values of
	//                             fields
	//
	// *************************************************************************
	function g_update_row($table,$field_values)
	{
		$field_names_array = $table."_fields";
		$this->update_row_from_template($table,$this->$field_names_array,$field_values);
	}

	// *************************************************************************
	// Generic Get Row Function
	//
	// Input:
	//         $table -            name of table
	//         $options -          tbd
	//         $cmments -          tbd
	//
	// *************************************************************************
	function g_get_row($table,$options=NULL,$comment="")
	{
		$query 	= "select * FROM $table WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	
	// *************************************************************************
	// Generic Get Rows Function
	//
	// Input:
	//         $table -            name of table
	//         $options -          tbd
	//         $cmments -          tbd
	//
	// *************************************************************************
	function g_get_rows($table,$options=NULL,$comment="")
	{
		$query 	= "select * FROM $table";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}	
	
	


}

?>