<?php
//Store Values to Database
function skylite_scrf_store_values($values){
	 	global $wpdb;
		$table = $wpdb->prefix."skylite_srcf_user_submissions";
		$wpdb->query( $wpdb->prepare( "INSERT INTO $table ( id, name, email, subject, message, reply_status, admin_reply, admin_view ) VALUES ( %d, %s, %s, %s, %s, %d, %s, %d )", $values) );
}

//Get All Errors from skylite_srcf_settings database 
function skylite_contact_form_get_validations(){
	global $wpdb;
	$table_settings = $wpdb->prefix."skylite_srcf_settings";
	$values = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_settings WHERE id = %d", 1), ARRAY_A );
	
	return $values;
}

?>