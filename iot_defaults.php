<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
const IOT_POST_TYPE = 'iot_post';
const IOT_META_FILE = 'iot_file';
const IOT_TAX_DEPARTMENT = 'iot_department';
const IOT_TAX_UNIVERSITY = 'iot_university';
const IOT_TAX_QUESTION = 'iot_question';

const IOT_FRM_POST_ID = 'iot_frm_post_id';
const IOT_FRM_POST_CONTENT = 'iot_frm_post_content';
const IOT_FRM_POST_FILE = 'iot_frm_post_file';
const IOT_FRM_POST_QUESTION = 'iot_frm_post_question';

const IOT_FRM_POST_DEPARTMENT = 'iot_frm_post_department';
const IOT_FRM_POST_UNIVERSITY = 'iot_frm_post_university';

function findpost($university, $department, $question)
{
    $args = array(
        'post_type' => IOT_POST_TYPE,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => 1,
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => IOT_TAX_DEPARTMENT,
                'field' => 'name',
                'terms' => $department
            ),
            array(
                'taxonomy' => IOT_TAX_UNIVERSITY,
                'field' => 'name',
                'terms' => $university
            ),
            array(
                'taxonomy' => IOT_TAX_QUESTION,
                'field' => 'name',
                'terms' => $question
            )
        )
    );
    
    $query = new WP_Query($args);
    if($query->have_posts()){
        return $query->posts[0];
    }
    
    return 0;
}
function has_permission($post)
{
    if(empty($post)||is_wp_error($post))
        return true;//change to departmetn
    $user_id = wp_get_current_user()->ID;
    if ($post->post_author == $user_id)
        return true;
    if (current_user_can('edit_others_iots'))
        return true;
    return false;
}

function insert_taxonomies($tax_name, $values)
{
    if (!is_array($values))
        $values = array('0' => $values);
    foreach ($values as $val) {
        if (!term_exists($val, $tax_name)) {
            wp_insert_term($val, $tax_name, array());
        }
    }
}

function generateRandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}