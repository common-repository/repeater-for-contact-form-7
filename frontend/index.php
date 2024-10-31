<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
class Superaddons_Contact_Form_7_Repeater_frontend {
    function __construct(){
        add_action("wp_enqueue_scripts",array($this,"add_lib"),1000);
    }
    function add_lib(){
        $pro = get_option("_redmuber_item_1654"); 
        wp_enqueue_script("cf7_repeater",CT7_REPEATER_PLUGIN_URL."libs/cf7_repeater.js",array("jquery"));
        wp_localize_script( 'cf7_repeater', 'cf7_repeater', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'pro' => $pro,
        ) );
        wp_enqueue_style("repeater_icon",CT7_REPEATER_PLUGIN_URL."libs/css/repeatericons.css",array());
        wp_enqueue_style("cf7_repeater",CT7_REPEATER_PLUGIN_URL."libs/cf7_repeater.css",array());
    }
}
new Superaddons_Contact_Form_7_Repeater_frontend;