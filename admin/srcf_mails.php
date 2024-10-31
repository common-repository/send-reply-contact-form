<?php	
function skylite_srcf_plugin_inbox(){
	global $wpdb;
	$table_scf_submissions		 = $wpdb->prefix."skylite_srcf_user_submissions";
	
	$validations 		= skylite_contact_form_get_validations();
	$from 				= $validations['scf_adminEmail'];
	//Send Reply to user
	if('POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "view_details"){
		
		$fetch_user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_scf_submissions WHERE id = %d", $_POST['user_id']), OBJECT );
?>
			<!--Send Reply Form-->
			<form method="POST" action="">
				<h3>User Query:</h3>
					<input type="hidden" name="user_email" value="<?=$fetch_user_data->email; ?>" readonly>
					<input type="hidden" name="user_id" value="<?=$fetch_user_data->id; ?>" readonly>
				<span>User Name:</span> 
				<br />
					<input type="text" name="user_name" value="<?=$fetch_user_data->name; ?>" readonly>
				<br />
				<br />
				<span>Query Subject:</span>
				<br />
					<textarea rows="4" cols="40" name="query_subject" readonly><?=$fetch_user_data->subject; ?></textarea>
				<br />
				<br />
				<span>User Message:</span> 
				<br />
					<textarea rows="4" cols="40" readonly><?=$fetch_user_data->message; ?></textarea>
				<br />
				<h3>Please Enter Reply:</h3>
				<span>Your Reply:</span> 
				<br />
					<textarea name="admin_reply" rows="4" cols="40"></textarea>
				<br />
					<input type="hidden" name="action" value="send_mail" />
					<input type="submit" name="admin_reply_submitted" value="Reply">
			</form>
<?php	

	}else if('POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "send_mail"){
				$to = $_POST['user_email'];
				$msg = $_POST['admin_reply'];
				$subject = "Re:".$_POST['query_subject'];
				$blog_name = get_bloginfo('name');
				$validations 		= skylite_contact_form_get_validations();
				$from 				= $validations['scf_adminEmail'];


				$headers = "From: ".$blog_name." <".$from.">" . "\r\n";
				
				$sc_mail = wp_mail($to, $subject, $msg, $headers);
			
			if($sc_mail){
				//Update Reply status to 1 as admin has replied to user
				$userid = $_POST['user_id'];
				$reply = $_POST['admin_reply'];
				
				$update = $wpdb->query( $wpdb->prepare( "UPDATE $table_scf_submissions SET reply_status = '%d', admin_reply = '%s' WHERE id = '%d' ", 1, $reply, $userid) );
				$location = admin_url('admin.php?page=srcf-admin-page');
				
				if($update){
					//send back to admin page
					echo "Mail Sent Successfully";
?>
					<br /><br /><button onclick="location.href='<?php echo $location; ?>'">Go Back</button>
<?php
				}else {
					echo "Reply Could Not be sent. Please Try Again";
?>
					<br /><br /><button id="button" onclick="location.href='<?php echo $location; ?>'">Go Back</button>					
			<?php }	}?>	

<?php }else{
			//Execute Query to fetch submissions
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_scf_submissions WHERE reply_status = %d", 0), OBJECT );	

?>
		<table>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Subject</th>
				<th>Reply</th>
			</tr>	
<?php		foreach($result as $row){ ?>			
			<tr <?php if($row->admin_view==0){echo "class='new_arrivals'";}?>>
				<td><?=$row->name; ?></td>
				<td><?=$row->email; ?></td>
				<td><?=$row->subject; ?></td>
				<td>
					<form method="POST" action="">
						<input type="hidden" value="<?php echo $row->id; ?>" name="user_id">
						<input type="hidden" name="action" value="view_details" />
						<input type="submit" id="view_details" value="Reply" name="reply_user">
					</form>
				</td>
			</tr>
			<?php	}?>
		</table>
<?php   } } ?>


<!-- Sent box starts here -->
<?php	function skylite_srcf_plugin_sent_box(){
			
		global $wpdb;
		$table_scf_submissions = $wpdb->prefix."skylite_srcf_user_submissions";
?>		
		
<?php	//Delete user data from database
		if('POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "delete_submission"){
			
			$id = $_POST['user_id'];
			$delete = $wpdb->query( $wpdb->prepare( " DELETE FROM $table_scf_submissions WHERE id = %d " ,$id) );
			
				if($delete){
					wp_redirect(admin_url('admin.php?page=srcf-admin-page'));
				}else{
					echo "Not Deleted";
				}
		}else {
			//Fetch sent mails from database
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_scf_submissions WHERE reply_status = %d", 1), OBJECT );				
?>
			<table>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Subject</th>
					<th>Message</th>
					<th>Reply</th>
					<th>Delete</th>
				</tr>	
<?php		foreach($result as $row){ ?>	
				<tr>
					<td><?=$row->name; ?></td>
					<td><?=$row->email; ?></td>
					<td><?=$row->subject; ?></td>
					<td><?=$row->message; ?></td>
					<td><?=$row->admin_reply; ?></td>
					<td>
						<form method="POST" action="">
							<input type="hidden" value="<?php echo $row->id; ?>" name="user_id">
							<input type="hidden" name="action" value="delete_submission" />
							<input type="submit" id="delete_submission_details" value="Delete" name="delete_submission_details">
						</form>
					</td>
				</tr>
<?php	 		} ?>	
			</table>		
<?php  } }?>