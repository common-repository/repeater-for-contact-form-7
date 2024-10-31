<?php
/**
* Plugin Name: Repeater for Contact Form 7
* Plugin URI: https://add-ons.org/plugin/contact-form-7-repeater
* Description: Contact Form 7 Repeater fields allows you to create one or more sets of fields that can be repeated.
* Author: add-ons.org
* Requires Plugins: contact-form-7
* Version: 4.3
* Author URI: https://add-ons.org
*/
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
define( 'CT7_REPEATER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CT7_REPEATER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
/*
* Check plugin contact form 7
*/
class Superaddons_Contact_Form_7_Repeater_init {
    function __construct(){
        include CT7_REPEATER_PLUGIN_PATH."backend/settings.php";
        include CT7_REPEATER_PLUGIN_PATH."backend/validation.php";
        include CT7_REPEATER_PLUGIN_PATH."backend/index.php";
        include CT7_REPEATER_PLUGIN_PATH."frontend/index.php";
        include CT7_REPEATER_PLUGIN_PATH."superaddons/check_purchase_code.php";
        new Superaddons_Check_Purchase_Code( 
            array(
                "plugin" => "repeater-for-contact-form-7/repeater-for-contact-form-7.php",
                "id"=>"1654",
                "pro"=>"https://add-ons.org/plugin/elementor-forms-repeater-fields/",
                "plugin_name"=> "Repeater Field For Contact Form 7",
                "document"=>"https://add-ons.org/document-contact-form-7-repeater/"
            )
        );
    }
}
new Superaddons_Contact_Form_7_Repeater_init;
if(!class_exists('Superaddons_List_Addons')) {  
    include CT7_REPEATER_PLUGIN_PATH."add-ons.php"; 
}