<?php

/*
Plugin Name: IBM Max Image Auto Alt Capture
Plugin URI: https://github.com/kkannaji/kkannaji
Description: IBM Max Image Auto Alt Capture
Version: 5.7.2
Author: Chaitanya
Author URI: https://github.com/kkannaji/kkannaji
License: GPLv2 or later
 
*/


function ibm_admin_menu() {

    add_menu_page(__( 'Image Alt', 'my-textdomain' ),__( 'Image Alt', 'my-textdomain' ),'manage_options','image_alt','ibm_admin_page_contents','dashicons-schedule',99);

}
add_action( 'admin_menu', 'ibm_admin_menu' );
add_action( 'admin_init', 'ibm_register_settings' );


function ibm_admin_page_contents() {

    ?>

    <h2>Image Alt Text Plugin Settings</h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'ibm_plugin_options' );
        do_settings_sections( 'ibm_example_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>

    <?php

}

function ibm_register_settings() {
    register_setting( 'ibm_plugin_options', 'ibm_plugin_options', 'ibm_plugin_options_validate' );

    add_settings_section( 'api_settings', 'API Settings', 'ibm_plugin_section_text', 'ibm_example_plugin' );

    add_settings_field( 'ibm_plugin_setting_api_key', 'Enable ?', 'ibm_plugin_setting_api_key', 'ibm_example_plugin', 'api_settings' );

}

function ibm_plugin_section_text() {
    echo '<p>Here you can set all the options for using the API</p>';
}

function ibm_plugin_setting_api_key() {
   $options = get_option('ibm_plugin_options');
   $default = isset($options['enable']) ? $options['enable'] : 0;

   printf(
    '<input type="checkbox" name="%1$s[enable]" value="1" %2$s>',
    'ibm_plugin_options',
    checked($default, 1, false)
);


}

function ibm_update_posts() {

 $options = get_option( 'ibm_plugin_options' );

 if(isset($options['enable']) && $options['enable'] == 1) {
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    $json = file_get_contents(plugins_url( '/ibmmax/json/data.json',dirname(__FILE__ )), false, stream_context_create($arrContextOptions));
    $json_data = json_decode($json,true);
    $key = rand(0,40);
    $alt_text = $json_data[$key]['name'];
    $args = array(
        'post_type' => 'post',
        'numberposts' => -1
    );
    $myposts = get_posts($args);
    foreach ($myposts as $mypost){
        $attachment_id = get_post_thumbnail_id($mypost->ID);

        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
    }
}
}
add_action( 'wp_loaded', 'ibm_update_posts' );
