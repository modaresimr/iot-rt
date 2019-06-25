<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
const IOT_POST_TYPE = 'iot_post';
const IOT_META_FILE = 'iot_file';
const IOT_TAX_DEPARTMENT = 'iot_department';
const IOT_TAX_UNIVERSITY = 'iot_university';
const IOT_TAX_QUESTION = 'iot_question';
const IOT_USR_UNIVERSITY = 'iot_user_university';
const IOT_USR_BIO = 'iot_user_bio';
const IOT_FRM_POST_ID = 'iot_frm_post_id';
const IOT_FRM_POST_CONTENT = 'iot_frm_post_content';
const IOT_FRM_POST_FILE = 'iot_frm_post_file';
const IOT_FRM_POST_QUESTION = 'iot_frm_post_question';

const IOT_FRM_POST_DEPARTMENT = 'iot_frm_post_department';


function findpost($university, $department, $question, $edit, $user)
{

    if ($user != wp_get_current_user()) {
        $args = array(
            'post_type' => IOT_POST_TYPE,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'post_author' => $user->ID,
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
        if ($query->have_posts()) {
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
    if ($query->have_posts()) {
        return $query->posts[0];
    }

    return 0;
}
function has_permission($post)
{
    if (empty($post) || is_wp_error($post))
        return true; //change to departmetn
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
function getTaxonomyTree($tax_name, $addempty = false, $key = 'name')
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
            if ($key == 'name')
                $result[$term['0']->name] = $term['0']->name;
            else
                $result[$term['0']->slug] = $term['0']->name;
        } else {
            foreach ($term as $k2 => $subterm) {
                if ($k2 == '0') {
                    continue;
                }
                if ($key == 'name')
                    $result[$term['0']->name][$subterm->name] = $subterm->name;
                else
                    $result[$term['0']->name][$subterm->slug] = $subterm->name;
            }
        }
    }
    return $result;
}

function sendtestEmail(){
    $email="a.l.i.m.1.3.6.9@gmail.com";
    $password_reset_ket = get_password_reset_key(wp_get_current_user());
	$rp_link = '<a href="' . network_site_url("wp-login.php?action=rp&key=$password_reset_ket&login=" . rawurlencode($email), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$password_reset_ket&login=" . rawurlencode($email), 'login') . '</a>';
	echo wp_mail($email, 'IoT-rt.ml Registeration Success', 'To set your password Please click on ' . $rp_link,array('Content-Type: text/html; charset=UTF-8'));
    

}
add_action('wp_ajax_iot_testemail', 'sendtestEmail');
add_action('wp_ajax_nopriv_iot_testemail', 'sendtestEmail');
function addDefaultUni()
{

    $numTerms = wp_count_terms(IOT_TAX_UNIVERSITY, array(
        'hide_empty' => false,
        'parent'    => 0
    ));
    if ($numTerms > 2)
        return;


    $alljson = json_decode('[{"id_geoloc":"1","nom":"40 - IUT des Pays de l\'Adour","url":"http:\/\/iutpa.univ-pau.fr\/fr\/organisation\/departements\/reseaux-et-telecommunications.html","internal_url":"\/iut\/adour\/","latitude":"43.8858","longitude":"-0.509083","duti":"1","dutid":"0","dutc":"1","dutaa":"0","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"1","cartaa":"0","cartap":"1","cartv":"1","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"1","rthdaa":"0","rthdap":"1","rthdv":"1","du":"0"},{"id_geoloc":"2","nom":"26 - IUT de Valence","url":"http:\/\/www.iut-valence.fr\/dut-r-et-t","internal_url":"\/iut\/valence\/","latitude":"44.9171","longitude":"4.91628","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"0","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"3","nom":"51 - IUT Reims Chalons Charleville- Site de Chalons","url":"http:\/\/rt.chalons.univ-reims.fr\/","internal_url":"\/iut\/reims\/","latitude":"48.9514","longitude":"4.35059","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"4","nom":"25 - IUT de Belfort-Montb\u00e9liard","url":"http:\/\/rt.pu-pm.univ-fcomte.fr","internal_url":"\/iut\/belfort-montbeliard\/","latitude":"47.4949","longitude":"6.80294","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"1","cartaa":"1","cartap":"1","cartv":"1","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"5","nom":"74 - IUT d\'Annecy","url":"https:\/\/www.iut-acy.univ-smb.fr\/departement_rt\/presentation-rt\/","internal_url":"\/iut\/annecy\/","latitude":"45.9206","longitude":"6.15326","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"0","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"1","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"6","nom":"14 - IUT de Caen (site de Ifs)","url":"https:\/\/uniform.unicaen.fr\/catalogue\/formation\/dut\/5260-dut-reseaux-et-telecommunications?s=iut-caen&r=1291042169888","internal_url":"\/iut\/caen\/","latitude":"49.1491","longitude":"-0.352279","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"0","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"7","nom":"973-IUT de Kourou","url":"https:\/\/www.univ-guyane.fr\/formation\/nos-formations\/formations-iut-de-kourou\/dut-reseaux-et-telecommunications\/","internal_url":"\/iut\/kourou\/","latitude":"5.15947","longitude":"-52.653","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"0","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"8","nom":"86 - IUT de Poitiers (site de Ch\u00e2tellerault)","url":"http:\/\/iutp.univ-poitiers.fr\/rt\/","internal_url":"\/iut\/poitiers\/","latitude":"46.8095","longitude":"0.541077","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"1","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"9","nom":"22 - IUT de LANNION","url":"http:\/\/www.iut-lannion.fr\/lyceens-etudiants\/choisir-un-dut\/dut-reseaux-telecoms","internal_url":"\/iut\/lannion\/","latitude":"48.7399","longitude":"-3.45245","duti":"1","dutid":"0","dutc":"1","dutaa":"0","dutap":"1","dutas":"1","dutv":"1","asuri":"1","asurid":"0","asurc":"1","asuraa":"0","asurap":"1","asurv":"1","isvdi":"1","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"10","nom":"41 - IUT de Blois","url":"http:\/\/iut-blois.univ-tours.fr\/rt","internal_url":"\/iut\/blois\/","latitude":"47.5902","longitude":"1.33719","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"1","asurid":"0","asurc":"1","asuraa":"1","asurap":"0","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"1"},{"id_geoloc":"11","nom":"62 - IUT de B\u00e9thune","url":"http:\/\/rt-bethune.univ-artois.fr","internal_url":"\/iut\/bethune\/","latitude":"50.5174","longitude":"2.65523","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"12","nom":"54 - IUT Nancy-Brabois","url":"http:\/\/iutnb.univ-lorraine.fr\/fr\/content\/departement-reseaux-et-telecommunications","internal_url":"\/iut\/nancy\/","latitude":"48.6615","longitude":"6.15324","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"0","rsfsaa":"1","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"13","nom":"76 - IUT de Rouen (site d\'Elbeuf)","url":"http:\/\/iutrouen.univ-rouen.fr\/dut-reseaux-et-telecommunications-351515.kjsp","internal_url":"\/iut\/rouen\/","latitude":"49.2848","longitude":"1.00531","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"0","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"14","nom":"94 - IUT Cr\u00e9teil-Vitry - site de Vitry","url":"http:\/\/iut.u-pec.fr\/departements\/reseaux-et-telecommunications\/","internal_url":"\/iut\/creteil\/","latitude":"48.7766","longitude":"2.37592","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"1","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"1","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"1","rsfsaa":"0","rsfsap":"0","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"15","nom":"17 - IUT de La Rochelle","url":"http:\/\/www.iut-larochelle.fr\/iut-la-rochelle\/departement-reseaux-et-telecommunications","internal_url":"\/iut\/la-rochelle\/","latitude":"46.1398","longitude":"-1.15291","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"16","nom":"42 - IUT de Roanne","url":"https:\/\/iut-roanne.univ-st-etienne.fr\/fr\/formations\/dut-CB\/sciences-technologies-sante-STS\/dut-reseaux-et-telecommunications-rt-program-dut-reseaux-et-telecommunications-rt.html","internal_url":"\/iut\/roanne\/","latitude":"46.0436","longitude":"4.0726","duti":"1","dutid":"0","dutc":"1","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"17","nom":"38 - IUT Grenoble 1","url":"http:\/\/formations.univ-grenoble-alpes.fr\/fr\/catalogue\/dut-diplome-universitaire-de-technologie-CB\/sciences-technologies-sante-STS\/dut-reseaux-et-telecommunications-program-dut-reseaux-et-telecommunications.html","internal_url":"\/iut\/grenoble\/","latitude":"45.1987","longitude":"5.7746","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"0","rsfsaa":"1","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"1"},{"id_geoloc":"18","nom":"06 - IUT de NICE COTE D\'AZUR","url":"http:\/\/rt.unice.fr\/","internal_url":"\/iut\/nice\/","latitude":"43.6168","longitude":"7.0711","duti":"1","dutid":"1","dutc":"1","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"1","isvdaa":"0","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"1","rsfsaa":"1","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"19","nom":"34 - IUT de B\u00e9ziers","url":"https:\/\/www.iutbeziers.fr\/rt-beziers.html","internal_url":"\/iut\/beziers\/","latitude":"43.3372","longitude":"3.2135","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"20","nom":"89 - IUT de Dijon Auxerre - Site d\'Auxerre","url":"http:\/\/iutdijon.u-bourgogne.fr\/www\/formations\/dut\/dut-reseaux-et-telecommunications-rt.html","internal_url":"\/iut\/dijon\/","latitude":"47.791","longitude":"3.55957","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"21","nom":"63 - IUT de Clermont-Ferrand","url":"http:\/\/iutweb.u-clermont1.fr\/RT","internal_url":"\/iut\/clermont-ferrand\/","latitude":"45.7714","longitude":"3.07617","duti":"1","dutid":"0","dutc":"0","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"22","nom":"78 - IUT de V\u00e9lizy","url":"http:\/\/rt.iut-velizy.uvsq.fr","internal_url":"\/iut\/velizy\/","latitude":"48.7821","longitude":"2.21761","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"1","rthdaa":"1","rthdap":"1","rthdv":"1","du":"0"},{"id_geoloc":"23","nom":"93 - IUT de Villetaneuse","url":"https:\/\/iutv.univ-paris13.fr\/reseaux-telecommunications\/","internal_url":"\/iut\/villetaneuse\/","latitude":"48.9553","longitude":"2.34157","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"0","asuri":"1","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"1","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"24","nom":"35 - IUT de Saint Malo","url":"https:\/\/iut-stmalo.univ-rennes1.fr\/formations\/dut\/r\u00e9seaux-et-t\u00e9l\u00e9communications","internal_url":"\/iut\/saint-malo\/","latitude":"48.6576","longitude":"-1.96966","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"1","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"1","cartid":"0","cartc":"1","cartaa":"0","cartap":"1","cartv":"0","rsfsi":"1","rsfsid":"0","rsfsc":"1","rsfsaa":"1","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"25","nom":"68 - IUT de Colmar","url":"http:\/\/www.iutcolmar.uha.fr\/rt","internal_url":"\/iut\/colmar\/","latitude":"48.077","longitude":"7.36998","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"1","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"1","isvdid":"0","isvdc":"1","isvdaa":"1","isvdap":"1","isvdv":"1","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"26","nom":"85 - IUT de La Roche Sur Yon","url":"http:\/\/www.iutlaroche.univ-nantes.fr\/formation\/dut-reseaux-et-telecommunications-2020056.kjsp?RH=1182587504364","internal_url":"\/iut\/la-roche-sur-yon\/","latitude":"46.6765","longitude":"-1.4041","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"1","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"27","nom":"974 - IUT de SAINT PIERRE (REUNION)","url":"http:\/\/iut.univ-reunion.fr\/departements\/reseaux-et-telecommunications\/","internal_url":"\/iut\/saint-pierre\/","latitude":"-21.3329","longitude":"55.4727","duti":"1","dutid":"0","dutc":"0","dutaa":"1","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"1","rsfsaa":"1","rsfsap":"1","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"1","rthdaa":"1","rthdap":"1","rthdv":"0","du":"0"},{"id_geoloc":"28","nom":"31 - IUT de TOULOUSE  BLAGNAC","url":"https:\/\/www.iut-blagnac.fr\/fr\/formations\/dut-rt\r\n","internal_url":"\/iut\/toulouse\/","latitude":"43.6487","longitude":"1.37507","duti":"1","dutid":"0","dutc":"1","dutaa":"1","dutap":"1","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"0","asuraa":"0","asurap":"0","asurv":"0","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"1","rsfsaa":"1","rsfsap":"1","rsfsv":"1","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"},{"id_geoloc":"29","nom":"13 - IUT Aix - Site Marseille Luminy","url":"http:\/\/iut.univ-amu.fr\/departements\/reseaux-telecommunications-rt\r\n","internal_url":"\/iut\/aix\/","latitude":"43.2328","longitude":"5.44259","duti":"1","dutid":"0","dutc":"1","dutaa":"0","dutap":"0","dutas":"0","dutv":"1","asuri":"0","asurid":"0","asurc":"1","asuraa":"1","asurap":"1","asurv":"1","isvdi":"0","isvdid":"0","isvdc":"0","isvdaa":"0","isvdap":"0","isvdv":"0","carti":"0","cartid":"0","cartc":"0","cartaa":"0","cartap":"0","cartv":"0","rsfsi":"0","rsfsid":"0","rsfsc":"0","rsfsaa":"0","rsfsap":"0","rsfsv":"0","rthdi":"0","rthdid":"0","rthdc":"0","rthdaa":"0","rthdap":"0","rthdv":"0","du":"0"}]');
    foreach ($alljson as $term) {
        $name = trim(substr($term->nom, strpos($term->nom, '-') + 1));
        if (!term_exists($name, IOT_TAX_UNIVERSITY)) {
            $slug = str_replace('/iut/', '', $term->internal_url);
            $slug = str_replace('/', '', $slug);
            wp_insert_term($name, IOT_TAX_UNIVERSITY, array('parent' => 0, 'slug' => $slug));
            $tax = get_term_by('slug', $slug, IOT_TAX_UNIVERSITY);
            add_term_meta($tax->term_id, 'latitude', $tax->latitude);
            add_term_meta($tax->term_id, 'longitude', $tax->longitude);
            add_term_meta($tax->term_id, 'internal_url', $tax->internal_url);
            add_term_meta($tax->term_id, 'id_geoloc', $tax->id_geoloc);
            add_term_meta($tax->term_id, 'url', $tax->url);
            add_term_meta($tax->term_id, 'nom', $tax->nom);
        }
    }
}
