<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
const IOT_POST_TYPE = 'iot_post';
const IOT_META_FILE = 'iot_file';
const IOT_TAX_DEPARTMENT = 'iot_department';
const IOT_TAX_UNIVERSITY = 'iot_university';
const IOT_TAX_QUESTION = 'iot_question';
const IOT_USR_UNIVERSITY= 'iot_user_university';
const IOT_USR_BIO= 'iot_user_bio';
const IOT_FRM_POST_ID = 'iot_frm_post_id';
const IOT_FRM_POST_CONTENT = 'iot_frm_post_content';
const IOT_FRM_POST_FILE = 'iot_frm_post_file';
const IOT_FRM_POST_QUESTION = 'iot_frm_post_question';

const IOT_FRM_POST_DEPARTMENT = 'iot_frm_post_department';
const IOT_FRM_POST_UNIVERSITY = 'iot_frm_post_university';

function findpost($university, $department, $question,$edit,$user)
{
    if($edit&&!current_user_can('edit_others_iots')){
        $args = array(
            'post_type' => IOT_POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'post_author'=>$user->ID,
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
    $args = array(
        'post_type' => IOT_POST_TYPE,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
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
function getTaxonomyTree($tax_name, $addempty = false,$key='name')
    {

        $terms = get_terms($tax_name, array(
            'hide_empty' => false,
        ));

        $all = array();
        foreach ($terms as $k => $term) {
            //var_dump($term);
            if ($term->parent != 0 && !isset($all[$term->parent])) {
                $all[$term->parent] = array();
            }
            if ($term->parent == 0 && !isset($all[$term->term_id])) {
                $all[$term->term_id] = array();
            }
            if ($term->parent == 0) {
                $all[$term->term_id]['0'] = $term;
            } else {
                $all[$term->parent][$term->term_id] = $term;
            }
        }
        $result = array();
        if ($addempty)
            $result[''] = '';
        foreach ($all as $k => $term) {
            if (sizeof($term) == 1) {
                if($key=='name')
                    $result[$term['0']->name] = $term['0']->name;
                else
                    $result[$term['0']->slug] = $term['0']->name;
            } else {
                foreach ($term as $k2 => $subterm) {
                    if ($k2 == '0') {
                        continue;
                    }
                    if($key=='name')
                        $result[$term['0']->name][$subterm->name] = $subterm->name;
                    else
                        $result[$term['0']->name][$subterm->slug] = $subterm->name;
                }
            }
        }
        return $result;
    }
