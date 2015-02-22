<?php
 
// *****************************************************************************
// *****************************************************************************
// copyright 2014 Sam Mela, all rights reserved
//
// File:             db_support.php
// Date:             1 October 2014
// Description:      This script contains classes to access the database
// ****************************************************************************
// *****************************************************************************
	
// *****************************************************************************
// *****************************************************************************
// Menu Data
// *****************************************************************************
// *****************************************************************************
class cDB_Support extends cDataBaseRoot
{
	// users table
	var $user_fields 		= array("id","fb_id","first","last","phone","email","registered","status");
	var $user_fields_1 	= array("fb_id", "first","last","phone","email","registered","status");
	var $user_fields_2 	= array("fb_id", "first","last","phone","email","registered","status");

	// menus table
	var $menus_fields 	= array("user_id", "date_added", "date_edited", "menu");

	// Contact table
	var $contact_fields 	= array("home_phone","cell_phone","address","city","state","postal_code","best_call_time");
	
	// Contact table
	var $referrers_fields 	= array("ip_address","lead_source","agree_to_terms","agree_to_terms_2","affiliate_id","campaign_id","banner_id",
										"sub_id","pixel_fire","display_non_compliant","notes","affiliate_sub_Id","referring_url");
										
	// Pinhash table
	var $pin_hash_fields 	= array("pin_hash","pin_hash_date");
	
	// Personal table
	var $personal_fields 	= array("first_name", "last_name", "birth_date", "gender","substance");
	
	// Errors table
	var $errors_fields 		= array("error", "date");

	// 	Questions Table
	var $questions_fields 		= array("id","question_identifier","type","question","labels");
	var $quiz_questions_fields 	= array("id","quiz_identifier","question_identifier","quiz_question_number");

	// Mobile Originated SMS Message Queue

	var $mo_sms_q_fields 		= array("time_received", "message","message_xml");


	// Diagnostic table
	var $diagnostic_fields 	= array("page1_processor", "page2_processor","inbound");

	// Exceptions table
	var $exceptions_fields 	= array("time_received", "exception");

	// billing_data table
	var $billing_data_fields 		= array("signup_date", "amount", "notes");

	// billing_history table
	var $billing_history_fields 	= array("billed_date", "amount", "notes");





	// *************************************************************************
	// Constructor
	// *************************************************************************
	
	function __construct($mysqli)
	{
		parent::__construct($mysqli);
	}
	

	

	// *************************************************************************
	// Update row in market table
	// *************************************************************************
	function update_user_row($field_values_array)
	{
	
		$user_fields 	= $this->user_fields_2;
		$query 			= "UPDATE user SET uid='$this->uid',";


		foreach($user_fields as $field_name)
		{
			if (array_key_exists($field_name,$field_values_array))
			{
				$set_array[] = "$field_name="."'".$field_values_array[$field_name]."'";
			}			
		}

		$query	.= 	implode(",",$set_array)." WHERE (id=$this->id) \n";
		$this->query($query,"no comment");
	}
	
	// ANTIQUATED EXAMPLE OF HOW TO DO THIS
	// $subscriber_status	= cSubscriberStatus::PIN_SENT;
	// $master_fields 		= array("phone"=>$phone, "pin"=>$pin,"subscriber_status"=>$subscriber_status,"carrier"=>$data["carrier"]);
	// $db_support->update_master_row($master_fields);
	// *************************************************************************
	// Inser row in market table
	// *************************************************************************
	function insert_user_row($field_values_array)
	{
		$user_fields 	= $this->user_fields_1;
		$query 			= "INSERT users SET id=NULL,";


		foreach($user_fields as $field_name)
		{
			$set_array[] = "$field_name="."'".$field_values_array[$field_name]."'";
		}

		$query	.= 	implode(",",$set_array)."\n";
		$this->query($query,"no comment");
		// Whenever we set or get a user row we set $this->id
		$this->get_id();
	}
	
	
	// *********************************************************************************************
	// Get users rows
	// *********************************************************************************************
	function get_user_rows() 
	{
		$id 	=	$this->id;
	
		$query	= 	"SELECT * FROM users where (id=$id)";
		$result	=  $this->query($query);
		
		$i=0;
		$user_rows = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$user_rows[] = $row;
		}
		return $user_rows;
	}
	
	// *********************************************************************************************
	// Get User Row
	//
	// *********************************************************************************************
	function get_user_row() 
	{
		$id 				=	$this->id;
		$query			= 	"SELECT * FROM users where (id=$id)";
		$result			=  $this->query($query);
		$user_row		= 	$result->fetch_array(MYSQLI_ASSOC);
		return 			$user_row;		
	}





	// *********************************************************************************************
	// Get the test code for a user row
	//
	// *********************************************************************************************
	function get_user_test_code($id) 
	{
		$query				= 	"SELECT test_code FROM users where (id=$id)";
		$result				=  	mysql_query($query, $this->link);
		$user_row			= 	mysql_fetch_array($result, MYSQL_ASSOC);
		return $user_row["test_code"];		
	}

	// *********************************************************************************************
	// Get user record based on facebook id
	// *********************************************************************************************
	function get_user_row_from_fbid($fb_id) 
	{
		$query		= 	"SELECT * FROM users WHERE (fb_id='$fb_id')";
		$result		=  $this->query($query,"get_user_row_from_fbid");
		$num_rows 	= 	$result->num_rows;
		if ($num_rows>0)
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);
			// Whenever we set or get a user row we set $this->id
			$this->id = $row["id"];
		}
		else
		{
			$row	=	false;
		}
		return $row;		
	}



	// *********************************************************************************************
	// Get user record based on pending phone
	// *********************************************************************************************
	function get_pending_phone($phone,$subscriber_status) 
	{
		$query				= 	"SELECT * FROM users WHERE ((phone='$phone') AND (subscriber_status='$subscriber_status')) ORDER BY time_created DESC LIMIT 1";
		$result		=  	$this->query($query,"get_pending_phone");
		$num_rows 	= 		$result->num_rows;
		if ($num_rows>0)
		{
			$row	= 	mysql_fetch_array($result, MYSQL_ASSOC);
		}
		else
		{
			$row	=	false;
		}
		return $row;		
	}


	// *********************************************************************************************
	// Get user record based on newest phone record
	// *********************************************************************************************
	function get_newest_phone($phone) 
	{
		$query		= 		"SELECT * FROM users WHERE (phone='$phone') ORDER BY time_created DESC LIMIT 1";
		$result		=  	$this->query($query,"get_pending_phone");
		$num_rows 	= 		$result->num_rows;
		if ($num_rows>0)
		{
			$row	= 	mysql_fetch_array($result, MYSQL_ASSOC);
		}
		else
		{
			$row	=	false;
		}
		return $row;		
	}

	// *********************************************************************************************
	// Clean out older applications for this phone number
	// *********************************************************************************************
	function clean_applications($phone) 
	{
		$query		= 	"delete FROM users WHERE ((phone='$phone') AND (subscriber_status<>".cSubscriberStatus::SUBSCRIBED."))";
		$result		=  	$this->query($query,"clean_applications");
		return $result;
	}


	// *********************************************************************************************
	// Clean out all applications for this phone number
	// *********************************************************************************************
	function clean_out_applications($phone) 
	{
		$query		= 	"delete FROM users WHERE (phone='$phone')";
		$result		=  	$this->query($query,"clean_out_applications");
		return $result;
	}







	// *************************************************************************
	// Update personal row
	// *************************************************************************
	function update_personal_row($field_values)
	{
		$this->update_row_from_template("personal",$this->personal_fields,$field_values);
	}
	// *************************************************************************
	// Insert personal row
	// *************************************************************************
	function insert_personal_row($field_values)
	{
		$this->insert_row_from_template("personal",$this->personal_fields,$field_values);
	}
	// *************************************************************************
	// Get personal row
	// *************************************************************************
	function get_personal_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM personal WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	// *************************************************************************
	// Get personal rows
	// *************************************************************************
	function get_personal_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM personal";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}


	// *************************************************************************
	// Update errors row
	// *************************************************************************
	function update_errors_row($field_values)
	{
		$this->update_row_from_template("errors",$this->errors_fields,$field_values);
	}

	// *************************************************************************
	// Insert errors row
	// *************************************************************************
	function insert_errors_row($field_values)
	{
		$this->insert_row_from_template("errors",$this->errors_fields,$field_values);
	}
	// *************************************************************************
	// Get errors row
	// *************************************************************************
	function get_errors_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM errors WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}


	// *************************************************************************
	// Clear Old Errors
	// *************************************************************************
	function clear_old_errors()
	{
		$date_yesterday = date("U") - 86400;
		$query = "DELETE FROM errors WHERE errors.Date < '".$date_yesterday."'";
		$this->query($query,"clear_old_errors");
	}




	// *************************************************************************
	// Insert message originated sms queue row
	// *************************************************************************
	function insert_mo_sms_q_row($field_values)
	{
		$this->insert_row_from_template("mo_sms_q",$this->mo_sms_q_fields ,$field_values,false);
	}
	// *************************************************************************
	// Get message originated sms queue row  NOT FINISHED #$%^&
	// *************************************************************************
	function get_mo_sms_q_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM mo_sms_q WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	// *************************************************************************
	// Get message originated sms queue rows
	// *************************************************************************
	function get_mo_sms_q_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM mo_sms_q";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}

	
	// *************************************************************************
	// Get all rows for quiz
	// *************************************************************************
	function get_all_rows_for_quiz($quiz_identifier)
	{
		$query 	= "SELECT quiz_questions.quiz_question_number, questions.question FROM questions, quiz_questions "
		."WHERE ((questions.question_identifier = quiz_questions.question_identifier) and (quiz_questions.quiz_identifier='1'))";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}



	// *************************************************************************
	// Update menu row
	// *************************************************************************
	function update_menu_row($field_values)
	{
		if (isset($field_values["menu"]))
		{
			$field_values["menu"] 	= $this->mysqli->real_escape_string($field_values["menu"]);
		}
		$this->update_row_from_template("menus",$this->menu_fields,$field_values);
	}

	// *************************************************************************
	// Insert menu row
	// *************************************************************************
	function insert_menu_row($field_values)
	{
		if (isset($field_values["menu"]))
		{
			$field_values["menu"] 	= $this->mysqli->real_escape_string($field_values["menu"]);
		}
		$this->insert_row_from_template("menus",$this->menus_fields,$field_values,false);
	}


	// *************************************************************************
	// Get menu row
	// *************************************************************************
	function get_newest_user_menu($user_id=NULL)
	{
		$user_id = is_null($user_id) ? $this->id : $user_id;	
		$query 	= "SELECT * FROM menus WHERE (user_id = $user_id) ORDER BY date_added DESC LIMIT 1 \n";
		$row 		= $this->get_row($query);
		//echo("<pre>"); print_r($row); echo("</pre>");
		$result	= ($row === false) ? false : $row["menu"];
		return $result;
	}

	
	// *************************************************************************
	// Get menu row
	// *************************************************************************
	function get_menu_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM menus WHERE (user_id=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}

	// *************************************************************************
	// Get menu rows
	// *************************************************************************
	function get_menu_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM menus";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}



	// *************************************************************************
	// Get menu rows
	// *************************************************************************
	function delete_oldest_menu_rows($user_id=NULL)
	{
		$user_id 		= 	is_null($user_id) ? $this->id : $user_id;	
		$query 			= 	"SELECT COUNT(*) FROM menus WHERE (user_id = $user_id) \n";
		$result 			=	$this->query($query);
		
		if ($result !== false) 
		{
			$row 				= 	$result->fetch_row();
			$row_count		= 	$row[0];
			
			if (($row_count > 10) && is_numeric($row_count))
			{
				$delete_count 	= 	$row_count - 10;
				$query			=	"DELETE FROM menus WHERE (user_id = $user_id) ORDER BY date_added ASC LIMIT $delete_count \n";
				$this->query($query);
			}
		}
		
		
	}





	// *************************************************************************
	// Update contact row
	// *************************************************************************
	function update_contact_row($field_values)
	{
		$this->update_row_from_template("contact",$this->contact_fields,$field_values);
	}

	// *************************************************************************
	// Insert contact row
	// *************************************************************************
	function insert_contact_row($field_values)
	{
		$this->insert_row_from_template("contact",$this->contact_fields,$field_values);
	}
	
	// *************************************************************************
	// Get contact row
	// *************************************************************************
	function get_contact_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM contact WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}

	// *************************************************************************
	// Get contact rows
	// *************************************************************************
	function get_contact_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM contact";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}

	// *************************************************************************
	// Update referrers row
	// *************************************************************************
	function update_referrers_row($field_values)
	{
		$this->update_row_from_template("referrers",$this->referrers_fields,$field_values);
	}

	// *************************************************************************
	// Insert referrers row
	// *************************************************************************
	function insert_referrers_row($field_values)
	{
		$this->insert_row_from_template("referrers",$this->referrers_fields,$field_values);
	}
	
	// *************************************************************************
	// Get referrers row
	// *************************************************************************
	function get_referrers_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM referrers WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	
	// *************************************************************************
	// Get referrer rows
	// *************************************************************************
	function get_referrers_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM referrers";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}


	// *************************************************************************
	// Update diagnostic row
	// *************************************************************************
	function update_diagnostic_row($field_values)
	{
		$this->update_row_from_template("diagnostic",$this->diagnostic_fields,$field_values);
	}

	// *************************************************************************
	// Insert diagnostic row
	// *************************************************************************
	function insert_diagnostic_row($field_values)
	{
		$this->insert_row_from_template("diagnostic",$this->diagnostic_fields,$field_values);
	}
	
	// *************************************************************************
	// Get diagnostic row
	// *************************************************************************
	function get_diagnostic_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM diagnostic WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	
	// *************************************************************************
	// Get diagnostic rows
	// *************************************************************************
	function get_diagnostic_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM diagnostic";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}

	// *************************************************************************
	// Insert exceptions row
	// *************************************************************************
	function insert_exceptions_row($field_values)
	{
		$this->insert_row_from_template("exceptions",$this->exceptions_fields ,$field_values,false);
	}
	// *************************************************************************
	// Insert exceptions row
	// *************************************************************************
	function get_exceptions_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM exceptions WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	// *************************************************************************
	// Insert exceptions row
	// *************************************************************************
	function get_exceptions_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM exceptions";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}

	// *************************************************************************
	// Update pin_hash row
	// *************************************************************************
	function update_pin_hash_row($field_values)
	{
		$this->update_row_from_template("pin_hash",$this->pin_hash_fields,$field_values);
	}

	// *************************************************************************
	// Insert pin_hash row
	// *************************************************************************
	function insert_pin_hash_row($field_values)
	{
		$this->insert_row_from_template("pin_hash",$this->pin_hash_fields,$field_values);
	}
	
	// *************************************************************************
	// Get pin_hash row
	// *************************************************************************
	function get_pin_hash_row($options=NULL,$comment="")
	{
		$query 	= "select * FROM pin_hash WHERE (parent=$this->id) \n";
		$row 	= $this->get_row($query,$options,$comment);
		return $row;
	}
	
	// *************************************************************************
	// Get pin_hash rows
	// *************************************************************************
	function get_pin_hash_rows($options=NULL,$comment="")
	{
		$query 	= "select * FROM pin_hash";
		$rows 	= $this->get_rows($query,$options,$comment);
		return $rows;
	}
	
	// *************************************************************************
	// Insert question row
	// *************************************************************************
	function insert_question_row($field_values)
	{
		$field_values["type"] 		= 0;
		$this->insert_row_from_template("questions",$this->questions_fields,$field_values,false);
	}
	
	
	// *************************************************************************
	// Insert quiz question row
	// *************************************************************************
	function insert_quiz_question_row($field_values)
	{
		$field_values["type"] 		= 0;
		$this->insert_row_from_template("quiz_questions",$this->quiz_questions_fields,$field_values,false);
	}



	// *************************************************************************
	// Inser row in market table
	// *************************************************************************
	function insert_index_page_offers_row($offer_check_status,$status,$id=NULL)
	{
		$id								= 	is_null($id) ? $this->id : $id;
		$time_created		=	date("Y-m-d H:i:s", time()); 
		$escaped_offer_check_status		=	mysql_real_escape_string($offer_check_status,$this->link);
		$query							= 	"INSERT INTO index_page_offers SET parent='$id', offer_check_status='$escaped_offer_check_status',time_created='$time_created',status='$status'";
		$this->query($query,"insert_index_page_offers_row");
	}
	
	
	
	// *************************************************************************
	// Update row in market table
	// *************************************************************************
	function update_index_page_offers_row($offer_check_status,$status,$id=NULL)
	{
		$id							= 	is_null($id) ? $this->id : $id;
		$time_created		=	date("Y-m-d H:i:s", time()); 
		$escaped_offer_check_status	=	mysql_real_escape_string($offer_check_status,$this->link);
		$query						= 	"UPDATE index_page_offers SET offer_check_status='$escaped_offer_check_status',time_created='$time_created',status='$status' WHERE (parent=$id)";
		$this->query($query,"update_index_page_offers_row");
	}

	// *************************************************************************
	// Update Index Page Offer Status
	// *************************************************************************
	function update_index_page_offer_status($status,$parent)
	{
		$query		= 	"UPDATE index_page_offers SET status='$status' WHERE (parent=$parent)";
		$this->query($query,"update_index_page_offer_status");
	}

	// *************************************************************************
	// Add json offers to index page offers
	// *************************************************************************
	function add_json_offers_to_index_page_offers_row($json_offers,$id=NULL)
	{
		$id							= 	is_null($id) ? $this->id : $id;
		$escaped_json_offers		=	mysql_real_escape_string($json_offers,$this->link);
		$query						= 	"UPDATE index_page_offers SET offers='$escaped_json_offers' WHERE (parent=$id)";
		$this->query($query,"add_json_offers_to_index_page_offers_row");
	}
	

	// *************************************************************************
	// Set Billing Data
	// *************************************************************************
	function set_billing_data($amount)
	{
		$signup_date	=	date("Y-m-d", time()); 
		$field_values 	= 	array("signup_date"=>$signup_date,"amount"=>"999","notes"=>"verizon");
		$this->g_insert_row("billing_data",$field_values);	
	}	
	
	// *********************************************************************************************
	// Get Carrier Terms
	// *********************************************************************************************
	function get_carrier_terms($carrier)
	{
		$query	= "SELECT * FROM `carrier_terms` WHERE (carrier = '$carrier')";
		$row 	= $this->get_row($query);
		return $row;
	}	
	
		
	
	
}

?>