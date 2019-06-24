<?php
/*
Plugin Name: IOT-RT
Plugin URI: https://github.com/modaresimr/iot-rt-plugin
Description: Add filterable column on admin panel
Version: 1.0.0
Author: Ali Modaresi
Author URI: https://github.com/modaresimr/
GitHub Plugin URI: https://github.com/modaresimr/iot-rt-plugin
*/
require_once 'iot_defaults.php';
require_once 'iot_posttype.php';
require_once 'iot_widget.php';
require_once 'iot_login_widget.php';
require_once 'user_manager.php';

add_shortcode('iot_q', 'iot_q_handler');
add_shortcode('iot_q1', 'iot_q_handler');
add_shortcode('iot_q2', 'iot_q_handler');
add_shortcode('iot_collapse', 'iot_collapse_handler');
add_shortcode('iot_collapse1', 'iot_collapse_handler');
add_shortcode('iot_collapse2', 'iot_collapse_handler');

function iot_collapse_handler($atts, $content)
{
	ob_start();
	$edit = ($_REQUEST['edit'] ?? '') == "true";
	$atts  = shortcode_atts(array(
		'title' => '',
		'data-parent' => '#main'
	), $atts);
	$myid = generateRandomString();
	?>
	<div class="" style="padding-left:10px;">
		<?php if (!empty($atts['title'])) { ?>
			<div class="card-header btn-link" id="heading<?php echo $myid ?>" data-toggle="collapse" data-target="#collapse<?php echo $myid ?>" aria-controls="collapse<?php echo $myid ?>">
				<h5 class="mb-0">

					<?php echo $atts['title']; ?>

				</h5>
			</div>
		<?php } ?>
		<div id="collapse<?php echo $myid ?>" data-parent="<?php echo $atts['data-parent']; ?>" class="collapse show">
			<?php

			//echo do_shortcode(str_replace("]", " data-parent=#".$myid."]", $content));
			preg_match_all('/\[\w+/', $content, $matches);
			$newcontent = str_replace('<br />','',$content);
			$newcontent = str_replace('data-parent','data-parent_old',$newcontent);
			$matches = array_unique($matches[0]);
			foreach ($matches as $m) {
				$newcontent = str_replace($m, $m . " data-parent='#collapse$myid'", $newcontent);
			}

			$code= do_shortcode($newcontent);
			echo $code;
			?>
		</div>
	</div>
	<?php
	if((!$edit)&& empty($code)){
		ob_get_clean();
		return "";
	}
	return ob_get_clean();
}

function iot_post_update()
{

	$user = wp_get_current_user();

	//$dps = wp_get_object_terms($user->ID, IOT_TAX_DEPARTMENT) ?? '';

	// if (empty($dps) && empty($_REQUEST[IOT_FRM_POST_DEPARTMENT])) {
	// 	echo json_encode(array('status' => 'Error', 'error_code' => "404", 'message' => "No Department"));
	// 	die();
	// 	return;
	// }

	$unis = get_user_meta($user->ID, IOT_USR_UNIVERSITY, true); //wp_get_object_terms($user->ID, IOT_TAX_UNIVERSITY) ?? '';
	if (empty($unis) && empty($_REQUEST[IOT_TAX_UNIVERSITY])) {

		echo json_encode(array('status' => 'Error', 'error_code' => "404", 'message' => "No University"));
		die();
		return;
	}
	// $department = $_REQUEST[IOT_TAX_DEPARTMENT] ?? $dps[0]->name;
	$department = 'Network';
	$university = $_REQUEST[IOT_TAX_UNIVERSITY] ?? $unis;
	insert_taxonomies(IOT_TAX_DEPARTMENT, $department);
	insert_taxonomies(IOT_TAX_UNIVERSITY, $university);
	if (empty($_POST[IOT_FRM_POST_QUESTION])) {
		echo json_encode(array('status' => 'Error', 'error_code' => "404", 'message' => "No Question"));
		die();
		return;
	}
	$question = $_POST[IOT_FRM_POST_QUESTION];
	insert_taxonomies(IOT_TAX_QUESTION, $question);

	$post1 = findpost($university, $department, $question, true, $user);
	$has_permission = has_permission($post1);

	if (!$has_permission || empty($_POST['iot_frm_submit'])) {
		echo json_encode(array('status' => 'Error', 'error_code' => "403", 'message' => "No Permission"));
		die();
		return;
	}

	$postarr = array();
	$postarr['ID'] = $_POST[IOT_FRM_POST_ID];
	
	$postarr['post_title'] = $university . " - " . $department . " - " . $question;
	$postarr['post_content'] = $_POST[IOT_FRM_POST_CONTENT];


	// Default values from the form settings
	$postarr['post_type'] = IOT_POST_TYPE;
	$postarr['post_status'] = 'publish'; //'pending';
	$user_id = get_current_user_id();
	if ($user_id != 0) {
		$postarr['post_author'] = $user_id;
	}
	// Get the post ID or return the error(s)
	$result = wp_insert_post($postarr, true);
	$post_id = $result;

	if (isset($result->errors)) {
		$msg = '';
		foreach ($result->errors as $v) {
			$msg .= '- ' . $v[0] . '<br />';
		}
		echo json_encode(array('status' => 'Error', 'error_code' => "500", 'message' => 'An Technical Error occured.Please Contact Admins.'));
		die();
		return;
	}
	wp_set_object_terms($post_id, $university, IOT_TAX_UNIVERSITY, true);
	wp_set_object_terms($post_id, $department, IOT_TAX_DEPARTMENT, true);
	wp_set_object_terms($post_id, $question, IOT_TAX_QUESTION, true);
	echo json_encode(array(
		'status' => 'Success', 'message' => 'Entry updated successfuly!',
		'post_id' => $post_id
	));
	die();
	return;
}

function iot_q_handler($atts, $content = null)
{
	$myid = generateRandomString();
	ob_start();
	$atts  = shortcode_atts(array(
		'question' => '',
		'comment' => '',
		'data-parent' => '#main'
	), $atts);
	if (empty($atts['question']))
		return;

	$user = wp_get_current_user();
	if (!empty($_GET['user_id'])) {
		$user = get_user_by('id', $_GET['user_id']);
		if (empty($user))
			$user = wp_get_current_user();
	}
	//wp_set_object_terms($user->ID, 'University Paris 13', IOT_TAX_UNIVERSITY, true);
	//wp_set_object_terms($user->ID, 'Network', IOT_TAX_DEPARTMENT, true);
	// $dps = wp_get_object_terms($user->ID, IOT_TAX_DEPARTMENT) ?? '';
	// if (empty($dps) && empty($_REQUEST[IOT_FRM_POST_DEPARTMENT])) {
	// 	//echo json_encode(array('status'=>'Error','error_code'=>"404",'message' => "No Department"));
	// 	wp_die("No Department");
	// 	return;
	// }
	// $unis = wp_get_object_terms($user->ID, IOT_TAX_UNIVERSITY) ?? '';
	// if (empty($unis) && empty($_REQUEST[IOT_TAX_UNIVERSITY])) {
	// 	//echo json_encode(array('status'=>'Error','error_code'=>"404",'message' => "No University"));
	// 	wp_die("No University");
	// 	return;
	// }
	// //$department = $_REQUEST[IOT_TAX_DEPARTMENT] ?? $dps[0]->name;
	// $department ='Network';
	// $university = $_REQUEST[IOT_TAX_UNIVERSITY] ?? $unis[0]->name;

	$unis = get_user_meta($user->ID, IOT_USR_UNIVERSITY, true);
	if (empty($unis) && empty($_REQUEST[IOT_TAX_UNIVERSITY])) {
		return json_encode(array('status' => 'Error', 'error_code' => "404", 'message' => "No University"));
	}
	// $department = $_REQUEST[IOT_TAX_DEPARTMENT] ?? $dps[0]->name;
	$department = 'Network';
	$university = $_REQUEST[IOT_TAX_UNIVERSITY] ?? $unis;
	//$department_tax = get_term_by('name', $department, IOT_TAX_DEPARTMENT);
	//$university_tax = get_term_by('name', $university, IOT_TAX_UNIVERSITY);

	$edit = ($_REQUEST['edit'] ?? '') == "true";
	$question = $atts['question'];
	$tax = get_term_by('name', $question, IOT_TAX_QUESTION);
	if (empty($tax) || is_wp_error($tax)) {
		insert_taxonomies('iot_question', $question);
		$tax = get_term_by('name', $question, 'iot_question');
	}

	$post1 = findpost($university, $department, $question, $edit, $user);
	$has_permission = has_permission($post1);

	if($edit)
		echo '<script>setTimeout(function() {$(".collapse").collapse("hide");	}, 1000);</script>';
	?>
	
	<div class="" style="padding-left:10px;">
		<div class="card-header btn-link" id="heading<?php echo $myid ?>" data-toggle="collapse" data-target="#collapse<?php echo $myid ?>" aria-controls="collapse<?php echo $myid ?>">
			<h5 class="mb-0"><?php echo $question; ?></h5>
		</div>

		<div id="collapse<?php echo $myid ?>" class="collapse show" data-parent="<?php echo $atts['data-parent']; ?>">
			<small class="form-text text-muted"> <?php echo $atts['comment']; ?> </small>

			<?php
			if (empty($post1)) {
				$post1 = (object)[
					'post_content' => '',
					'ID' => '0',
					'post_author' => 0
				];
				$metas = '';
			} else
				$metas = get_post_meta($post1->ID);

			if ($has_permission && $edit) {
				echo wrap_link('Filtrer par question', get_term_link($tax), 'badge badge-light');
				echo '<form method="post" id="form_' . $myid . '" class="iot-form" action="' . admin_url('admin-ajax.php') . '">';
				echo '<input type="hidden" name="action" value="iot_post_update"/>';
				echo '<input type="hidden" name="' . IOT_FRM_POST_QUESTION . '" value="' . $tax->name . '"/>';
				//echo '<hidden name="'.IOT_FRM_POST_FILE.'[]" />';
				echo wp_editor($post1->post_content, IOT_FRM_POST_CONTENT . $myid, array('textarea_name' => IOT_FRM_POST_CONTENT, 'textarea_rows' => 20));
				echo '<input type="hidden" name="' . IOT_FRM_POST_ID . '" id="' . IOT_FRM_POST_ID . '" value="' . $post1->ID . '"/>';
				echo '<button name="iot_frm_submit" class="btn btn-primary" type="submit" value="Submit">Submit</button>';
				echo '</form>';
				insertFileUploadScript($myid);
				echo '<br/>';
				//echo '<div class="row">';
				//if (!empty($university_tax) && !is_wp_error($university_tax))
				//wrap('University:', wrap_tag($university_tax, 'badge badge-secondary'));
				//	echo wrap_link($university_tax->name,site_url('/iot-wiki').'?'.IOT_TAX_UNIVERSITY.'='.$university_tax->name,'badge badge-light');

				// if (!empty($department_tax) && !is_wp_error($department_tax))
				// 	wrap('Department:', wrap_tag($department_tax, 'badge badge-primary'));
				//echo '</div>';
			} else {
				if ($post1->ID == 0) {
					echo wrap_link('Filtrer par question', get_term_link($tax), 'badge badge-light');
					echo '<p>Pas encore de texte</p>';
					echo '<div class="row">';
					// if (!empty($university_tax) && !is_wp_error($university_tax))
					// 	wrap('University:', wrap_tag($university_tax, 'badge badge-secondary'));
					// if (!empty($department_tax) && !is_wp_error($department_tax))
					// 	wrap('Department:', wrap_tag($department_tax, 'badge badge-primary'));
					echo '</div>';
				} else {
					global $post;
					$post = $post1;
					echo apply_filters('the_content', $post1->post_content);
				}
			}
			?>
		</div>
	</div>
	<?php
	if((!$edit)&&(empty($post1)||empty($post1->ID))){
		ob_get_clean();
		return "";
	}
			
	return ob_get_clean();
}
function iot_add_to_content($content)
{
	global $post;
	if ($post->post_type != IOT_POST_TYPE)
		return $content;
	ob_start();
	$tax = wp_get_post_terms($post->ID, IOT_TAX_QUESTION);
	if (!empty($tax) && !is_wp_error($tax)) {
		echo wrap_link("Filtrer par question", get_term_link($tax[0]), 'badge badge-light');
	}
	$university_tax = wp_get_post_terms($post->ID, IOT_TAX_UNIVERSITY);
	if (!empty($university_tax) && !is_wp_error($university_tax)) {
		$university_tax = $university_tax[0];
		echo wrap_link($university_tax->name, site_url('/iot-wiki') . '?' . IOT_TAX_UNIVERSITY . '=' . $university_tax->name, 'badge badge-light');
	}
	$user = get_user_by('id', $post->post_author) ?? '';
	if (!empty($user)) {
		echo wrap_link(strtoupper($user->last_name) . ', ' . $user->first_name, site_url('/users') . '?user_id=' . $user->ID, 'badge badge-light');
	}
	echo $content;

	//echo '<div class="row">';


	//wrap('University:', wrap_tags(wp_get_post_terms($post->ID, IOT_TAX_UNIVERSITY), 'badge badge-secondary'));
	// wrap('Department:', wrap_tags(wp_get_post_terms($post->ID, IOT_TAX_DEPARTMENT), 'badge badge-primary'));
	//echo '</div>';
	echo ob_get_clean();
}
add_filter('the_content', 'iot_add_to_content');

function wrap($title, $html)
{

	echo '<div class="col-md-6"> ';
	echo '  <dl class="row">';
	echo '    <dt class="col-5">' . $title . '</dt>';
	echo '    <dd class="col-7">';
	echo $html;
	echo '    </dd>';
	echo '  </dl>';
	echo '</div>';
}
function wrap_link($title, $link, $class)
{
	return '<a target="_blank" href="' . $link . '" class="' . $class . '">' . $title . '</a>';
}
function wrap_tag($tax, $class)
{
	if (isset($tax) && !is_wp_error($tax)) {
		return wrap_link($tax->name, get_term_link($tax), $class);
		//return '<a href="' . get_term_link($tax) . '" class="' . $class . '">' . $tax->name . '</a>';
	}
	return '';
}
function wrap_tags($taxs, $class)
{
	$return = '';
	if (isset($taxs) && !is_wp_error($taxs)) {
		foreach ($taxs as $id => $tax) {
			$return .= wrap_tag($tax, $class);
		}
	}
	return $return;
}



function insertFileUploadScript($myid)
{
	?>

	<script>
		$(function() {
			// bind 'myForm' and provide a simple callback function
			$('#form_<?php echo $myid ?>').ajaxForm(function(responseText, statusText, xhr, $form) {
				responseText = JSON.parse(responseText);

				if (responseText.status == 'Success') {
					$form.find('#<?php echo IOT_FRM_POST_ID ?>').val(responseText.post_id);
					puyModal({
						title: '',
						heading: responseText.status,
						//message:responseText.message,
						//icon: 'fab fa-js-square fa-3x',
						showHeader: false
					});
					$("#collapse<?php echo $myid ?>").collapse('hide');
				} else {
					puyModal({
						title: '',
						heading: responseText.status,
						message: responseText.message,
						//icon: 'fab fa-js-square fa-3x',
						showHeader: false
					});


				}
			});
		});
	</script>
<?php
}








add_action('wp_ajax_iot_post_update', 'iot_post_update');

function q_scripts()
{
	$src = plugins_url('/js/', __FILE__);
	wp_enqueue_script('jquery-form', $src . 'jquery.form.js', array('jquery'));
	wp_enqueue_script('puymodals', $src . 'puymodals.js', array('jquery'));
}
add_action('init', 'q_scripts');
