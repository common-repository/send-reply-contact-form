<?php
/*
Plugin Name: Send Reply Contact Form
Description: Contact Form with the functionality to Send/reply directly from WordPress Admin and stores messages/queries into database.
Version: 1.0
Author: Skylite Networks
Author URI: http://www.skylite.com/
Plugin URI: http://www.skylite.com/sent-and-reply-contact-form/
*/

define( 'Send Reply Contact Form', '1.0' );
define('SCF_PLUGIN_DIR', plugin_dir_url( __FILE__ ));


require_once (dirname(__FILE__) . '/srcf_installation.php');
require_once (dirname(__FILE__) . '/srcf_database_queries.php');
require_once (dirname(__FILE__) . '/admin/srcf_admin.php');

register_activation_hook (__FILE__, 'skylite_srcf_create_tables');
register_activation_hook (__FILE__, 'skylite_srcf_define_outputs');
register_deactivation_hook( __FILE__, 'skylite_srcf_db_tables' );

function skylite_srcf_contact_form(){


	$error			 			= false;
	$error['nameError'] 		= false;
	$error['emailError'] 		= false;
	$error['SubjectError'] 		= false;
	$error['messageNotValid']	= false;
	$error['captchanotValid'] 	= false;	
	$success					=false;
	$form						= true;

//Get all the validation messages from Database
$validations = skylite_contact_form_get_validations();
$scf_char_lengths = unserialize($validations['scf_min_lengths']);

//Perform following code lines if user clicks Submit Button	
	if('POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "scf_submitted"){
		
		//Sanitize Form Data
		$srcf_name    = sanitize_text_field( $_POST["scf_user_name"] );
		$srcf_email   = sanitize_email( $_POST["scf_user_email"] );
		$srcf_subject = sanitize_text_field( $_POST["scf_subject"] );
		$srcf_message = esc_textarea( $_POST["scf_message"] );
		 
		//Validate Form Data
		if(empty($srcf_name) || strlen($srcf_name)<intval($scf_char_lengths['scf_name_min_length'])){
			$error['nameError'] = true;	
		}else if(empty($srcf_email) || !is_email($srcf_email)){
			$error['emailError'] = true;	
		}else if(empty($srcf_subject) || strlen($srcf_subject)<intval($scf_char_lengths['scf_subj_min_length'])){
			$error['SubjectError'] = true;	
		}else if(empty($srcf_message) || strlen($srcf_message)<intval($scf_char_lengths['scf_msg_min_length'])){
			$error['messageNotValid'] = true;	
		}else if(empty($_POST['scf_cpatcha_input']) || !is_numeric($_POST['scf_cpatcha_input']) || $_POST['scf_cpatcha_input']!=$_POST['scf_first_digit']+$_POST['scf_second_digit']){
			$error['captchanotValid'] = true;	
		}else{
			$error=false;
		}

//If Error is false send email and store values to database
	if( $error==false ){
		$name  		= esc_attr( $srcf_name );
		$email 		= esc_attr( $srcf_email );
		$subject 	= esc_attr( $srcf_subject );
		$message 	= esc_textarea( $srcf_message );
		
		$data = array('id'=>'', 'name'=>$name, 'email'=>$email, 'subject'=>$subject, 'message'=>$message, 'reply_status'=>0, 'admin_reply'=>'', 'admin_view'=>0);
		
			if(is_email($validations['scf_adminEmail'])){
				$to = $validations['scf_adminEmail'];
			}else{
				$to = get_option( 'admin_email' );  // get the admin's email address
			}
			
			$headers = "From: ".$data['name']." <".$data['email'].">" . "\r\n";		

			if ( wp_mail( $to, $data['subject'], $data['message'], $headers ) ) {
				skylite_scrf_store_values( $data );
				$success=true;
			}
		} 
	}
	
 //Show Success message if Success is true
	if( $success==true ){
		echo esc_attr( $validations['scf_confirmation'] );
		$form = false;
	} 
?>

<?php
//Show Form When Form is true
	if( $form==true ){
		$first_numbr = rand(10,100);
		$second_numbr = rand(10,100);
	
?>
	<div id="scf_form">
		<form method="POST" action="">
			<p><?php if($error['nameError']==true){echo "<span class='error_msg'>".esc_attr($validations['scf_nameError'])."</span>";} else{echo "Your Name<span class='required_sign'>*</span>";}?> <br />
				<input type="text" name="scf_user_name" pattern="[a-zA-Z0-9 ]+" value="<?php if(isset( $_POST['scf_user_name'])){ echo esc_attr( $_POST["scf_user_name"]); } else{ echo ''; } ?>" size="70" required/>
			</p>		
			
			<p><?php if($error['emailError']==true){echo "<span class='error_msg'>".esc_attr($validations['scf_emailError'])."</span>";} else{echo "Your Email<span class='required_sign'>*</span>";}?> <br />
				<input type="email" name="scf_user_email" value="<?php if(isset( $_POST['scf_user_email'])){ echo esc_attr( $_POST["scf_user_email"]); } else{ echo ''; } ?>"  pattern="[A-Za-z0-9\-\_\.]+\@[A-Za-z0-9]+\.[A-Za-z]{2,4}" title="example@domain.com"  size="70" required/>
			</p>
			
			<p><?php if($error['SubjectError']==true){echo "<span class='error_msg'>".esc_attr($validations['scf_queryError'])."</span>";} else{echo "Subject<span class='required_sign'>*</span>";}?> <br />
				<input type="text" name="scf_subject" pattern="[a-zA-Z ]+" value="<?php if(isset( $_POST['scf_subject'])){ echo esc_attr( $_POST["scf_subject"]); } else{ echo ''; } ?>" size="70" required/>
			</p>
			
			<p><?php if($error['messageNotValid']==true){echo "<span class='error_msg'>".esc_attr($validations['scf_messageError'])."</span>";} else{echo "Your Message<span class='required_sign'>*</span>";}?> <br />
				<textarea rows="10" cols="35" name="scf_message" required><?php if(isset( $_POST['scf_message'])){ echo esc_attr( $_POST["scf_message"]); } else{ echo ''; } ?></textarea>
			</p>
			<p><?php if($error['captchanotValid']==true){echo "<span class='error_msg'>".esc_attr($validations['scf_captchaError'])."</span>";} ?>
			</p>
			<p>Please Enter the Sum of <?php echo $first_numbr; ?> + <?php echo $second_numbr; ?> <span class='required_sign'>*</span>
				<input class="captcha_field" type="text" name="scf_cpatcha_input" required>
			</p>
			<br />
			<br />
			<input type="hidden" name="scf_first_digit"  value="<?php echo $first_numbr; ?>" />
			<input type="hidden" name="scf_second_digit" value="<?php echo $second_numbr; ?>" />
			<input type="hidden" name="action" value="scf_submitted" />
			<p><input type="submit" id="scf_submit" name="scf_submitted" value="Send"/></p>		
		</form>
	</div>

<?php	
	} 
}

//OK, Now it's time to create short code
function skylite_srcf_contact_form_shortcode() {
    ob_start();
    skylite_srcf_contact_form();
 
    return ob_get_clean();
}

add_shortcode( 'src_form', 'skylite_srcf_contact_form_shortcode' );
	
?>