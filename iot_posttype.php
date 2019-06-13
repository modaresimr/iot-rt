<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once 'iot_defaults.php';

function create_iot_posttype() {
	
	register_post_type( IOT_POST_TYPE,
	  array(
		'labels' => array(
		  'name' => __( 'IoT Posts' ),
		  'singular_name' => __( 'IoT Post' )
		),
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'iot_post'),
		'hierarchical' => true	,
		'menu_icon' => 'dashicons-text-page',
		'capability_type'=>array('iot','iots'),
		'map_meta_cap' => true,
	  )
	);
	register_taxonomy(IOT_TAX_DEPARTMENT, array(IOT_POST_TYPE), array(
		  'hierarchical' => true,
		  'label' => 'Department'
	   ));
	register_taxonomy(IOT_TAX_UNIVERSITY, array(IOT_POST_TYPE), array(
        'hierarchical' => true,
        'label' => 'University'
     ));
     register_taxonomy(IOT_TAX_QUESTION, array(IOT_POST_TYPE), array(
        'hierarchical' => true,
        'label' => 'Question'
     ));
	   
  }
  add_action( 'init', 'create_iot_posttype' );
  