<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once( 'iot_defaults.php' );

function iot_login_load_widget()
{
    register_widget('iot_login_widget');
}
add_action('widgets_init', 'iot_login_load_widget');
// Creating the widget 
class iot_login_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            // Base ID of your widget
            'iot_login_widget',
            // Widget name will appear in UI
            __('IOT login widget', 'iot_login_widget_domain'),
            // Widget description
            array('description' => __('IOT LOGIN', 'iot_login_widget_domain'),)
        );
    }
    // Creating widget front-end
    public function widget($args, $instance)
    {
        $title='';
        if(isset($instance['title']))
            $title = apply_filters('widget_title', $instance['title']);
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];
        // This is where you run the code and display the output
        //echo __('Hello, World!', 'iot_login_widget_domain');
        iot_login();
        echo $args['after_widget'];
    }
    // Widget Backend 
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Title', 'iot_login_widget_domain');
        }
        // Widget admin form
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
<?php
}
// Updating widget replacing old instances with new
public function update($new_instance, $old_instance)
{
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    return $instance;
}
} // Class iot_login_widget ends here






function iot_login(){

    $user=wp_get_current_user();
    


    if(empty($user)||$user->ID==0){
        echo "<div class='btn-group'>";
        echo wrap_link('Connexion',wp_login_url(),'btn btn-primary');
        echo wrap_link('Inscription',site_url('register'),'btn btn-success');
    }else{
        echo wrap_link('Bienvenue'. strtoupper($user->last_name) .', '. $user->first_name ,site_url('/users').'?user_id='.$user->ID,'');
        echo "<div class='btn-group-vertical'>";
        echo wrap_link('Modifier votre réponse' ,site_url('/iot-wiki').'?edit=true','btn btn-primary');
        echo wrap_link('Voir votre réponse' ,site_url('/iot-wiki').'?edit=true','btn btn-success');
        echo wrap_link('Logout',wp_logout_url(),'btn btn-danger');
    }
    echo "</div>";
}
