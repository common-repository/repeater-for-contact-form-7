<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
class Superaddons_Contact_Form_7_Repeater_Settings_Repeater{
    function __construct()
    {
        add_filter("wpcf7_editor_panels",array($this,"custom_form"));
        add_action("wpcf7_save_contact_form", array($this,"save_data"));
    }
    function custom_form($panels)
    {
        $panels["form-panel-repeater-setting"] = array(
                'title' => __( 'Text Repeater', "repeater-for-contact-form-7" ),
                'callback' => array($this,"cf7_repeater_setting_form") );
        return $panels;
    }
    public static function get_settings($post_id  ){
        $cf7_repeater_datas = (get_post_meta($post_id,"cf7_repeater_datas",true) ) ? get_post_meta($post_id,"cf7_repeater_datas",true): "";
        return array("datas"=>$cf7_repeater_datas);
    }
    function save_data($contact_form){
        $post_id = $contact_form->id;
        $repeater_cf7_data = array_map("sanitize_text_field",$_POST["repeater_cf7_data"]);
        add_post_meta($post_id, 'cf7_repeater_datas', $repeater_cf7_data,true) or update_post_meta($post_id, 'cf7_repeater_datas', $repeater_cf7_data);
    }
    function cf7_repeater_setting_form($post){
        $settings = Superaddons_Contact_Form_7_Repeater_Settings_Repeater::get_settings($post->id());
        ?>
        <h3><?php esc_html_e("Label email tag",'contact-form-7-multistep-pro') ?></h3>
        <table class="form-table data-setting-step-cf7">
            <?php 
            $tags = $post->scan_form_tags();
            $data = $settings["datas"];
            foreach ($tags as $tag):
            if ($tag['type'] == 'group' || $tag['type'] == 'acceptance' || $tag['name'] == '') continue;   
                $vl = "";
                if( isset($data[$tag['name']])){
                    $vl = $data[$tag['name']];
                }  
            ?>
            <tr>
                <th scope="row">
                    <label for="multistep_cf7_steps_next">
                        <?php echo esc_attr(@$tag['name']);?>
                    </label>
                </th>
                <td>
                    <input name="repeater_cf7_data[<?php echo esc_attr(@$tag['name']) ?>]" type="text" value="<?php echo esc_attr($vl) ?>" class="regular-text">
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
        <?php
    }
}
new Superaddons_Contact_Form_7_Repeater_Settings_Repeater;