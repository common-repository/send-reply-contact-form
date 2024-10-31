<?php

function skylite_srcf_create_tables(){
	global $wpdb;
	$table_scf_submissions		 = $wpdb->prefix."skylite_srcf_user_submissions";
	$table_scf_settings			 = $wpdb->prefix."skylite_srcf_settings";
	$charset_collate = $wpdb->get_charset_collate();
	
	$sql_scf_submissions = "CREATE TABLE IF NOT EXISTS $table_scf_submissions (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(60) NOT NULL,
			`email` varchar(60) NOT NULL,
			`subject` varchar(60) NOT NULL,
			`message` text,
			`reply_status` int(1) NOT NULL,
			`admin_reply` text,
			`admin_view` int(1) NOT NULL,
			 UNIQUE KEY id (id)
		) $charset_collate;";
		
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_scf_submissions);	
	
	$sql_scf_settings = "CREATE TABLE IF NOT EXISTS $table_scf_settings (
			`id` int(11) NOT NULL AUTO_INCREMENT,
 			`scf_error` varchar(250) NOT NULL,
			`scf_confirmation` varchar(250) NOT NULL,
			`scf_nameError` varchar(250) NOT NULL,
			`scf_messageError` varchar(250) NOT NULL,
			`scf_emailError` varchar(250) NOT NULL,
			`scf_queryError` varchar(250) NOT NULL,
			`scf_captchaError` varchar(250) NOT NULL,
			`scf_adminEmail` varchar(70) NOT NULL,
			`scf_min_lengths` varchar(300) NOT NULL,
			UNIQUE KEY id (id)
 		) $charset_collate;";
		
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_scf_settings);		
}



function skylite_srcf_define_outputs(){
	$scf_lengths = serialize(array('scf_name_min_length'=>3, 'scf_subj_min_length'=>3, 'scf_msg_min_length'=>10));
	$scf_adminMail = get_option( 'admin_email' );
	global $wpdb;
	$table_scf_settings 	= $wpdb->prefix."skylite_srcf_settings";
	
	$wpdb->query( $wpdb->prepare( "INSERT INTO $table_scf_settings ( id, scf_error, scf_confirmation, scf_nameError, scf_messageError, scf_emailError, scf_queryError, scf_captchaError, scf_adminEmail, scf_min_lengths ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
					array(
					'id'=>'',
 					'scf_error'=>'An Error Occurred. Please Try Again Later', 
					'scf_confirmation'=>'Your Message Has Been Received! Expect a response soon',
					'scf_nameError'=>'Please make sure your Name contains at least 3 characters',
					'scf_messageError'=>'Your Message Should Contain at least 10 characters',
					'scf_emailError'=>'Please enter Valid Email Address',
					'scf_queryError'=>'Your Question Should have at least 3 characters',
					'scf_captchaError'=>'Please Enter Valid Captcha',
					'scf_adminEmail'=>$scf_adminMail,
					'scf_min_lengths'=>$scf_lengths
					)) );
}

//Drop database tables after plugin is uninstalled
function skylite_srcf_db_tables(){
	global $wpdb;
	$table_scf_submissions 	= $wpdb->prefix."skylite_srcf_user_submissions";
	$table_scf_settings 	= $wpdb->prefix."skylite_srcf_settings";
	
	$sql1 = "DROP TABLE IF EXISTS $table_scf_submissions";
	$wpdb->query($sql1);
	
	$sql2 = "DROP TABLE IF EXISTS $table_scf_settings";
	$wpdb->query($sql2);
}

?>