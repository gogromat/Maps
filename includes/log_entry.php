<?php
//require_once(dirname(__FILE__)."/Log.php");

/**
 * Possible contexts
 * Common to all:	OTHER, SYSTEM, ERROR
 * My Library:		APPLICATIONS, GROUPS, USERS
 * Art Collection:	IMAGES, ARTISTS, COMMENTS
 * Directory:		STAFF, UNITS, ROOMS, ROOM_TYPES, DEPARTMENTS
 * Donations:		DONATIONS
 * Hours:			SEMESTERS, UNIT_DEFAULTS, EXCEPTIONS
 * Scroller:		ANNOUNCEMENTS
 * WIMS:			LIBRARIAN, SUBJECT, CATEGORY, RESOURCE, VENDOR, FUNDING, CUSTOMIZE
 *
 * Possible actions
 * OTHER, ERROR, LOGIN, LOGOUT, ADD, UPDATE, DELETE, CHANGE_PASSWORD
 */
function log_entry($context, $action, $item_id, $details)
{
	global $db;
	global $login_info;
	
	///////////////////////////////////////////////////////
	$details = $db->quote($details);////////////////
    ///////////////////////////////////////////////////////


	if (!isset($login_info) || !is_object($login_info))
		return;
	
	$user_info = $login_info->get_user_info();
	$username = $user_info['username'];
	$ip = $_SERVER['REMOTE_ADDR'];

	$app_info = $login_info->get_application_info(APPLICATION_ID);
	$application = $app_info['application_name'];

	$status = new Status();
	$status->destination = "home";

	/*$entry = new Log();
	$entry->setPropertyValues(array('context' => $context, 'action' => $action, 'item_id' => $item_id, 'details' => $details));
	$result = $entry->save();*/
		
		//dbo.
	$query="INSERT INTO logs.log (entry_date, username, ip_address, application, context, action, item_id, details) 
			VALUES (GETDATE(), '$username', '$ip', '$application', '$context', '$action', '$item_id', '$details')";
	
	//echo $query; //die;

	//$result = $db->query($query);
	try{
		$result = $db->prepare($query);
		$result->execute();
	}
	catch(PDOException $e){
		print "Error : " . $e->getMessage(). "  (" . __LINE__ . ")";
		return FALSE;
	}
	//if (PEAR::isError($result))
	//	handle_error($status, $result, false);
	


	//else 
		if ($result == 0)
		handle_error($status, "Could not log entry", false);

	return TRUE;
}
?>