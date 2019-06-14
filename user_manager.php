<?php

require_once 'iot_defaults.php';
require_once 'iot_posttype.php';
require_once 'iot_widget.php';
require_once 'formr/class.formr.php';
add_shortcode('iot_register', 'iot_register_handler');
add_shortcode('iot_profile', 'iot_profile_handler');

function iot_register_handler()
{
	$form = new Formr('bootstrap');
	$form->required = "*";
	$user = wp_get_current_user();
	if ($form->submit()) {

		$fname =  $form->post('iot_fname', 'First Name', 'sanitize_string');
		$lname =  $form->post('iot_lname', 'Last Name', 'sanitize_string');
		$email =  $form->post('iot_email', 'Email', 'valid_email|sanitize_string');
		$university =  $form->post('iot_user_university', 'University', 'sanitize_string');
		$bio =  $form->post('iot_bio', 'Bio', 'sanitize_string');
		if (!$form->errors()) {
			if(empty($user)||$user->ID==0){
				$errors = register_new_user($email, $email);
				if (is_wp_error($errors)) {
					$form->errors['email'] = " Error! Email is duplicated!";
				}else
					$userID = $errors;
			}else
				$userID = $user->ID;
			/*$args  = array(
			'meta_key' => IOT_USR_UNIVERSITY, //any custom field name
			'meta_value' => $university //the value to compare against
		);

		$user_query = new WP_User_Query( $args );
		*/
			if (!$form->errors()) {
				
				$userID = wp_update_user(
					array(
						'ID' => $userID,
						'first_name' => $fname,
						'last_name' => $lname
					)
				);

				wp_set_object_terms($userID, $university, IOT_USR_UNIVERSITY, true);
				wp_set_object_terms($userID, $bio, 'iot_user_bio', true);
				$user = new WP_User((int)$userID);
				$password_reset_ket = get_password_reset_key($user);
				$rp_link = '<a href="' . network_site_url("wp-login.php?action=rp&key=$password_reset_ket&login=" . rawurlencode($email), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$password_reset_ket&login=" . rawurlencode($email), 'login') . '</a>';

				wp_mail($email, '[IOT-RT.ML] Registeration Success', ' To set your password Please click on ' . $rp_link);
				$form->success_message('Success! Please check your email for activation link');
				echo ' To set your password Please click on ' . $rp_link;
				echo $form->messages();
				return;
			}
		}
	}
	echo $form->form_open('', '', esc_url($_SERVER['REQUEST_URI']));
	echo $form->messages();
	$dfname=$user->first_name??'';
	$dlname=$user->last_name??'';
	$demail=$user->user_email??'';
	$dbio=get_user_meta($user->ID,IOT_USR_BIO,true)??'';
	$duuni=get_user_meta($user->ID,IOT_USR_UNIVERSITY,true)??'';
	if(!empty($user)){
		$dfname=$user->first_name;
		$dfname=$user->first_name;

	}
	echo $form->input_text('iot_fname', 'First Name',$dfname);
	echo $form->input_text('iot_lname', 'Last Name',$dlname);
	if(empty($user))
		echo $form->input_email('iot_email', 'Email',$demail);
	echo $form->input_select('iot_user_university', 'University', '', '', '', '', $duuni, getTaxonomyTree(IOT_TAX_UNIVERSITY, true));
	echo $form->label('Biolbl', 'Bio');
	echo wp_editor($_REQUEST['iot_bio'] ?? $dbio, 'iot_bio');
	echo $form->input_submit('iot_submit', '', '', '', 'class="btn btn-primary"');
	echo $form->form_close();
}


function iot_profile_handler(){
	$user=get_user_by('id',$_GET['user_id']??wp_get_current_user());
	if(empty($user)||$user->ID==0){
		echo 'No user!';
		return;
	}
	if($user==wp_get_current_user())
		return iot_register_handler();

	$dfname=$user->first_name??'';
	$dlname=$user->last_name??'';
	$demail=$user->user_email??'';
	$dbio=get_user_meta($user->ID,IOT_USR_BIO,true)??'';
	$duuni=get_user_meta($user->ID,IOT_USR_UNIVERSITY,true)??'';
		wrap_data('First Name',$dfname);
		wrap_data('Last Name',$dlname);
		wrap_data('Email',obfuscate_email($demail ));
		wrap_data('Universite',$duuni);
		wrap_data('Bio',$dbio);

}
function obfuscate_email($email)
{
    $em   = explode("@",$email);

    return $em[0] . " at ". str_repeat('*', 3)  . substr($em[1],3);   
}
function wrap_data($title,$value){
	?>
	<dl class="row">
		<dt class="col-3"><?php echo $title?></dt>
		<dd class="col"><?php echo $value?></dd>

	</dl>
	<?php
}