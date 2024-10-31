<?php
ob_start();

// Add action for Admin menu
add_action('admin_menu', 'skylite_srcf_admin_menu');

function skylite_srcf_admin_menu(){
   $scf_slug = "srcf-admin-page";
   
   add_menu_page('SRC Form', 'SRCF Submissions', 'manage_options', $scf_slug, 'skylite_srcf_plugin_page', plugin_dir_url( __FILE__ ) . '../images/icon.png');
	add_submenu_page($scf_slug, 'SRCF Settings Page', 'SRCF Settings', 'manage_options',  'srcf-admin-settings-page', 'skylite_srcf_plugin_setting_page');
 }

//Include Stylesheet
function skylite_srcf_admin_panel_style() {

    wp_enqueue_style( 'custom_css', plugin_dir_url( __FILE__ ) . '../style/style.css' );
	wp_enqueue_style('jquery-ui',plugin_dir_url( __FILE__ ) . '../style/jquery-ui.css');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
}
add_action( 'init', 'skylite_srcf_admin_panel_style' ); 

//Simple Contact us form homepage
function skylite_srcf_plugin_page(){

?>

	<div class='wrap'>
	  <h2>Welcome to Send Reply Contact Form Plugin Page</h2>
	  <p>To use this plugin please apply this shortcode [src_form]</p>
	   <!-- Add Script to Show Tabs -->
			<script type="text/javascript">
					jQuery(document).ready(function($) {
					$('#tabs').tabs();

					//hover states on the static widgets
					$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); },
					function() { $(this).removeClass('ui-state-hover'); }
					);
					});
			</script>
		<!-- Tabs -->
			<div class="plugin_config">
				<div id="tabs">
					<ul>
						<li><a href="#srcf_inbox">Inbox</a></li>
						<li><a href="#srcf_mailbox">Sent Mails</a></li>
					</ul>
					<?php require_once(dirname(__FILE__) . '/srcf_mails.php');?>
					<div id="srcf_inbox" class="inbox">	<?php	skylite_srcf_plugin_inbox(); ?></div>
					<div id="srcf_mailbox" class="sentbox">	<?php	skylite_srcf_plugin_sent_box(); ?></div>
				</div>
			</div>
	</div>
<?php
}

//Settings Page
function skylite_srcf_plugin_setting_page(){
	
		global $wpdb;
		$table				= $wpdb->prefix."skylite_srcf_settings";
		$location = admin_url('admin.php?page=srcf-admin-settings-page');

	if('POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == 'scf_store_error_msgs'){
				
		$scf_name_length				= $_POST['scf_name_min_length'];
		$scf_subj_length				= $_POST['scf_subject_min_length'];
		$scf_msg_length					= $_POST['scf_msg_min_length'];
		$scf_lengths 					= serialize(array('scf_name_min_length'=>$scf_name_length, 'scf_subj_min_length'=>									$scf_subj_length, 'scf_msg_min_length'=>$scf_msg_length));
		
		$updated_scf_error_msg 			= sanitize_text_field ($_POST['scf_general_error_msg']);
		$updated_scf_success_msg		= sanitize_text_field ($_POST['scf_success_msg']);		
		$updated_scf_name_validation	= sanitize_text_field ($_POST['scf_name_not_valid']);		
		$updated_scf_msg_validation		= sanitize_text_field ($_POST['scf_msg_not_valid']);		
		$updated_scf_email_validation	= sanitize_text_field ($_POST['scf_email_not_valid']);		
		$updated_scf_query_validation	= sanitize_text_field ($_POST['scf_query_not_valid']);		
		$updated_scf_captcha_validation	= sanitize_text_field ($_POST['scf_captcha_error']);
		$updated_scf_admin_mail			= sanitize_email ($_POST['scf_adminMail']);
		$updated_scf_char_lengths		= $scf_lengths;
		
		 
		if(is_numeric($scf_name_length) || is_numeric($scf_subj_length) || is_numeric($scf_msg_length) || is_email($updated_scf_admin_mail)){
			$update = $wpdb->query( $wpdb->prepare( "UPDATE $table SET scf_error = '%s', scf_confirmation = '%s', scf_nameError = '%s', scf_messageError = '%s', scf_emailError = '%s', scf_queryError = '%s', scf_captchaError = '%s', scf_adminEmail = '%s', scf_min_lengths = '%s' WHERE id = '%d' ", $updated_scf_error_msg, $updated_scf_success_msg, $updated_scf_name_validation,$updated_scf_msg_validation, $updated_scf_email_validation,	$updated_scf_query_validation, $updated_scf_captcha_validation, $updated_scf_admin_mail, $updated_scf_char_lengths, 1) );
			
		
				if($update){
					echo "<p>Settings Saved Successfully</p>";
			?>
					<br /><br /><button id="button" onclick="location.href='<?php echo $location; ?>'">Go Back</button>
			<?php
				}else{
					echo "<p>Settings Could Not Saved. Please Try Again Later. Please Make Sure You do some Changing</p>";
			?>
					<br /><br /><button id="button" onclick="location.href='<?php echo $location; ?>'">Go Back</button>	
			<?php
				} 
		}else{
			?>
				<p>Settings Could Not Saved. Please Try Again Later. Please Make Sure You did some Changings and entered Valid Data...</p>
				<br /><br /><button id="button" onclick="location.href='<?php echo $location; ?>'">Go Back</button>
			<?php	
		}
	}else{
	$scf_errors = skylite_contact_form_get_validations();
	$min_lengths = unserialize($scf_errors['scf_min_lengths']);
	?>	
	<h1>Send Reply Contact Form Settings Page</h1>
	
	<!-- Plugin Settings Page Main Div -->
	<div id="normal-sortables" class="meta-box-sortables">
	<!-- Plugin Instructions Div -->
		<div id="metabox_basic_settings" class="postbox">
			<h3 class="hndle" style="padding:5px;"><span>How to use Simple Contact US Plugin?</span></h3>
			<div class="inside">
				<h4>To use this plugin please apply this shortcode [src_form]</h4>
				<h4>Whereas PHP Code will be "<?php echo "&lt;?php echo do_shortcode(['src_form']); ?&gt;";?>"</h4>
				<h4>Please Change Validation messages according to Field (Characters) Length</h4>
			</div>
		</div>
		
		<!-- Plugin Errors and Validation Messages -->
		<form method="POST" action="">
			<div id="metabox_basic_settings" class="postbox">
				<h3 class="hndle" style="padding:5px;"><span>Plugin Errors and Messages</span></h3>
				<div class="inside">
					<table class="form-table"> 
						<tbody>
							<tr>
								<th>General Error Message</th>
								<td><input type="text" name="scf_general_error_msg" size="80" value="<?=esc_attr($scf_errors['scf_error']);?>"></td>
							</tr>
							<tr>
								<th>Name Not Valid</th>
								<td><input type="text" name="scf_name_not_valid" size="80" value="<?=esc_attr($scf_errors['scf_nameError']);?>"></td>
							</tr>
							<tr>
								<th>Message Not Valid</th>
								<td><input type="text" name="scf_msg_not_valid" size="80" value="<?=esc_attr($scf_errors['scf_messageError']);?>"></td>
							</tr>
							<tr>
								<th>Email Not Valid</th>
								<td><input type="text" name="scf_email_not_valid" size="80" value="<?=esc_attr($scf_errors['scf_emailError']);?>"></td>
							</tr>
							<tr>
								<th>User Query Error</th>
								<td><input type="text" name="scf_query_not_valid" size="80" value="<?=esc_attr($scf_errors['scf_queryError']);?>"></td>
							</tr>
							<tr>
								<th>Captcha Error</th>
								<td><input type="text" name="scf_captcha_error" size="80" value="<?=esc_attr($scf_errors['scf_captchaError']);?>"></td>
							</tr>
							<tr>
								<th>Success Message</th>
								<td><input type="text" name="scf_success_msg" size="80" value="<?=esc_attr($scf_errors['scf_confirmation']);?>"></td>
							</tr>
							<tr>
								<th>Send For Submissions TO</th>
								<td><input type="text" name="scf_adminMail" size="80" value="<?=esc_attr($scf_errors['scf_adminEmail']);?>"></td>
							</tr>
							<tr>
								<th>Min. Length of Name</th>
								<td><input type="text" name="scf_name_min_length" value="<?=intval($min_lengths['scf_name_min_length']);?>"></td>
							</tr>
							<tr>
								<th>Min. Length of Subject</th>
								<td><input type="text" name="scf_subject_min_length" value="<?=intval($min_lengths['scf_subj_min_length']);?>"></td>
							</tr>
							<tr>
								<th>Min. Length of Message</th>
								<td><input type="text" name="scf_msg_min_length" value="<?=intval($min_lengths['scf_msg_min_length']);?>"></td>
							</tr>
							<br />
							<input type="hidden" name="action" value="scf_store_error_msgs" />
							<tr><td><input class="button button-primary" type="submit" id="scf_store_error_msgs" name="scf_store_error_msgs" value="Save"/></td></tr>	
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
<?php
} }
?>