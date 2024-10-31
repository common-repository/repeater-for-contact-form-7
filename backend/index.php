<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
class Superaddons_Contact_Form_7_Repeater_Field {
    private $list_emails = array();
    private $check_repeater = false;
    private $list_name = array();
    function __construct()
    {
        add_action( 'wpcf7_init', array($this,'add_tag') );
        add_action( 'wpcf7_admin_init', array($this,'wpcf7_add_tag_generator'), 10 );
        add_filter( 'wpcf7_contact_form_properties', array($this,'custom_repeater'), 10, 2 );
        add_filter( 'wpcf7_mail_tag_replaced_repeater', array($this,"wpcf7_mail_tag_replaced"), 10, 4 );
        add_filter( 'wpcf7_mail_tag_replaced_repeater*', array($this,"wpcf7_mail_tag_replaced"), 10, 4 );
        add_filter( 'wpcf7_mail_tag_replaced', array($this,"wpcf7_mail_tag_replaced"), 10, 4 );
        add_action("admin_enqueue_scripts",array($this,"add_lib"),0,0);
        add_filter( 'wpcf7_additional_mail', array($this,'send_additional_mails'),10,2);
        add_action('wpcf7_form_tag',array($this,'wpcf7_form_tag'),10, 2);
        add_filter( 'wpcf7_validate', array($this,'wpcf7_validate_repeater'),10,2);
		add_action( 'wpcf7_form_hidden_fields', array( $this, 'uacf7_form_hidden_fields' ), 10, 1 );
		add_filter( 'wpcf7_posted_data', array( $this, 'remove_hidden_post_data' ) );
    }
    function wpcf7_swv_add_textarea_rules( $schema, $contact_form ) {
        if(isset($_POST["_yeeaddons_cf7_repeater_fields"])){
            $posted_data = sanitize_text_field($_POST["_yeeaddons_cf7_repeater_fields"]);
        }
    }
    public function uacf7_form_hidden_fields( $hidden_fields ) {
		return array_merge( $hidden_fields, array(
			'_yeeaddons_cf7_repeater_fields' => '',
		) );
	}
    public function remove_hidden_post_data( $posted_data ) {
        $hidden_fields = array();
		if ( isset( $posted_data['_yeeaddons_cf7_repeater_fields'] ) ) {
			$hidden_fields = explode("|",$posted_data['_yeeaddons_cf7_repeater_fields'] );;
		}
		foreach ( $hidden_fields as $name ) {
			unset( $posted_data[ $name ] );
		}
		return $posted_data;
	}
    function wpcf7_validate_repeater($result, $tag){
        $pro = get_option("_redmuber_item_1654"); 
        $posted_data ="";
        if(isset($_POST["_yeeaddons_cf7_repeater_fields"])){
            $posted_data = sanitize_text_field($_POST["_yeeaddons_cf7_repeater_fields"]);
        }
        $invalid_fields = $result->get_invalid_fields();
        $invalid_fields_old = $invalid_fields;
        $hidden_fields = array();
        $hidden_fields = explode("|",$posted_data );
        $return_result = new Yeeaddons_WPCF7_Validation();
        $datas_post = map_deep( $_POST, 'sanitize_text_field' );
        foreach ( $invalid_fields as $invalid_field_key => $invalid_field_data ) {
            if(in_array($invalid_field_key,$hidden_fields)){   
                if(is_array($datas_post)){
                    foreach($datas_post as $k =>$v){
                        if($pro == "ok"){
                            if($v == ""){
                                if( strpos($k, "__") !== false){
                                    $first_n = explode("__",$k);
                                    if($invalid_field_key== $first_n[0]){
                                        $return_result->invalidate_new( $k, $invalid_field_data['reason'] ,$invalid_field_data['idref']); 
                                    } 
                                }
                            }
                        }else{
                            if( strpos($k, "__") !== false){
                                $first_n = explode("__",$k);
                                if($invalid_field_key== $first_n[0]){
                                    $return_result->invalidate_new( $k, "Repeater Required: Upgrade to pro version" ,$invalid_field_data['idref']); 
                                } 
                            }
                        }
                    }
                }
            }else{
                $return_result->invalidate( $invalid_field_key, $invalid_field_data['reason']);
            }
        }
        return $return_result;
    }
    function wpcf7_form_tag($tag){
        if( $tag["raw_name"] == "repeater_check_begin" ) {
            $this->check_repeater = true;
            $tag = false;  
        }elseif( $tag["raw_name"] == "repeater_check_end" ) {
            $this->check_repeater = false;
            $tag = false;  
        }
        else{
            if( $this->check_repeater ) {
                if(isset($tag["type"]) && $tag["type"] != ""){
                    $tag["type"] = str_replace("*","",$tag["type"]);
                }
            }
        }
        return $tag;
    }
    function add_lib(){
        if (isset($_GET["page"]) && $_GET["page"] == "wpcf7") :
            wp_enqueue_script("cf7_repeater",CT7_REPEATER_PLUGIN_URL."backend/js/repeater.js");
        endif;
    }
    function add_tag(){
        wpcf7_add_form_tag( array( 'repeater', 'repeater*' ),array($this,'tag_handler'));
    }
    function tag_handler($tag) {
        $tag = new WPCF7_FormTag($tag);
        return $tag->content;
    }
    function wpcf7_add_tag_generator() {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add( 'repeater', __( 'Repeater', "repeater-for-contact-form-7" ),
            array($this,'wpcf7_tag_generator') );
    }
    function wpcf7_tag_generator( $contact_form, $args = '' ) {
    ?>
    <div class="control-box">
    <fieldset>
    <table class="form-table">
    <tbody>
        <tr>
        <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
            </tr>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-title' ); ?>"><?php echo esc_html( __( 'Title', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="text" name="title" class="tg-name option name-add-option" value="'person'" id="<?php echo esc_attr( $args['content'] . '-title' ); ?>" /> <p><?php esc_html_e("An optional title before each row of the repeater","repeater-for-contact-form-7") ?></p></td>
        </tr>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-button' ); ?>"><?php echo esc_html( __( 'Button text', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="text" name="button" class="tg-name option name-add-option" value="'Add more...'" id="<?php echo esc_attr( $args['content'] . '-button' ); ?>" /> <p><?php esc_html_e("Add button text","repeater-for-contact-form-7") ?></p></td>
        </tr>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-max' ); ?>"><?php echo esc_html( __( 'max', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="number" name="max" class="tg-name option" value="10" id="<?php echo esc_attr( $args['content'] . '-max' ); ?>" /><p><?php esc_html_e("Max number of rows applicable by the user, leave empty for no limit","repeater-for-contact-form-7") ?></p></td>
        </tr>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-initial_rows' ); ?>"><?php echo esc_html( __( 'initial_rows', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="number" name="initial_rows" class="tg-name option" value="1" id="<?php echo esc_attr( $args['content'] . '-initial_rows' ); ?>" /><p><?php esc_html_e("The number of rows at start, if empty no rows will be created","repeater-for-contact-form-7") ?></p></td>
        </tr>
        <?php 
        $pro = get_option("_redmuber_item_1654");
        if($pro != "ok"){
        ?>
        <tr>
            <th scope="row"><label><?php echo esc_html( __( 'Maps field with Initial Rows', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><p class="pro_text_style">Upgrade to pro version</p><p><?php esc_html_e("The number of rows at the start map with a field","repeater-for-contact-form-7") ?></p></td>
        </tr>
        <?php }else{
            ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-initial_rows_map' ); ?>"><?php echo esc_html( __( 'Maps field with Initial Rows', "repeater-for-contact-form-7" ) ); ?></label></th>
            <td><input type="text" name="initial_rows_map" class="tg-name option" placeholder="Name field" value="" id="<?php echo esc_attr( $args['content'] . '-initial_rows_map' ); ?>" /><p><?php esc_html_e("The number of rows at the start map with a field","repeater-for-contact-form-7") ?></p></td>
        </tr>   
            <?php
        } ?>
    </tbody>
    </table>
    </fieldset>
    </div>
    <div class="insert-box">
        <input type="text" name="repeater" class="tag code" readonly="readonly" onfocus="this.select()" />
        <div class="submitbox">
        <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', "repeater-for-contact-form-7" ) ); ?>" />
        </div>
        <br class="clear" />
    </div>
    <?php
    }
    function upload_files($files,$name =""){
        $datas = array();
        $wp_upload_dir = wp_upload_dir();
        $path_main = $wp_upload_dir['basedir'] . '/contact_form_7_uploads/';
        if ( ! file_exists( $path_main ) ) {
            wp_mkdir_p( $path_main );
        }
        $tmpFilePath = $files["tmp_name"];
        $rand_name =  rand(1000,9999)."_" . sanitize_file_name($files["name"]);
        $attachFileName = $path_main . $rand_name; 
        $filetype = wp_check_filetype($rand_name);
        $allowed_file_types = wpcf7_acceptable_filetypes();
        $allowed_file_types = explode("|",$allowed_file_types);
        if( in_array($filetype["ext"],$allowed_file_types)) {
            if(move_uploaded_file($tmpFilePath, $attachFileName)) {
                $datas[] = $wp_upload_dir['baseurl'] . '/contact_form_7_uploads/'. $rand_name;
            }  
        } 
        return $datas;
    }
    function is_json_string($value){
        if ( ! is_scalar( $value ) ) {
            return null;
        }
        return ! preg_match( '/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', 
        preg_replace( '/"(\\.|[^"\\\\])*"/', '', strval( $value ) ) );
    }
    function wpcf7_mail_tag_replaced( $replaced, $submitted, $html , $mail_tag) {
        if($submitted != "" && !is_array($submitted)){
            if( $this->is_json_string($submitted)){
                $datas_json = json_decode( $submitted, true );
            }else{
                $datas_json = null;
            } 
        }else{
            $datas_json = null;
        }
        if( $datas_json && isset($datas_json["fields"]) ) {
            $wpcf = WPCF7_ContactForm::get_current();
            $settings = Superaddons_Contact_Form_7_Repeater_Settings_Repeater::get_settings($wpcf->id());
            $data_email = array();
            foreach( $datas_json["id"] as $id ){
                $check_ok = false;
                $data_email_row = array();
                foreach( $datas_json["fields"] as $field ){
                    if(isset($_FILES[$field."__".$id])) {
                        $file = $_FILES[$field."__".$id];
                        $files = $this->upload_files($file);
                        $vl = implode(", ",$files);
                        $check_ok = true;
                    }else{
                        $vl = "";
                        if(isset($_POST[$field."__".$id])){
                            if( is_array($_POST[$field."__".$id] ) ){
                                $vl = map_deep( $_POST[$field."__".$id] , 'sanitize_textarea_field' );
                                $vl = implode(", ",$vl);
                            }else{
                                $vl = sanitize_textarea_field( $_POST[$field."__".$id] );
                            }
                            $check_ok = true;
                        }
                    }
                    if($field == "email_repeater"){
                        $datas_emails = $this->list_emails;
                        $datas_emails[] = $vl;
                        $this->list_emails = $datas_emails;
                    }
                    $label = $field;
                    if( isset($settings["datas"][$field]) && $settings["datas"][$field] != ""){
                        $label=  $settings["datas"][$field];
                    };
                    $data_email_row[] = array("label" => $label,"content"=>$vl);
                }
                if($check_ok){
                    $data_email[] = $data_email_row;
                }
            }
            if($html){
                $html_return= "";
                $style = 'padding-top: 25px;padding-bottom: 25px;padding-left:50px;min-width: 113px;padding-right: 10px;line-height: 22px;';
			    $style_first = 'padding-top: 25px;padding-bottom: 25px;min-width: 113px;padding-right: 10px;line-height: 22px;';
                $i = 0;
                foreach($data_email as $row ){
                    if($i == 0){
                        $html_return .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e2e2e2;">';
                    }else{
                        $html_return .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e2e2e2;">';
                    }
                    foreach($row as $inner_row){
                        $html_return .= '<tr>
								<td style="'.$style.'"><strong>'.$inner_row["label"].'</strong></td>
								<td style="'.$style.'">'.$inner_row["content"].'</td></tr>';
                    }
                    $html_return .="</table>";
                    $i++;
                }
                return apply_filters( "cf7_repeater_formats" , $html_return, $datas_json,$html,$data_email );
            }else{
                $html_return="";
                foreach($data_email as $row ){
                    foreach($row as $inner_row){
                        $html_return .= $inner_row["label"].": ".$inner_row["content"]."\n";
                    }
                    $html_return .="\n";
                }
                return apply_filters( "cf7_repeater_formats" , $html_return, $datas_json,$html,$data_email );
            }
        }else{
            return $replaced;
        }
    }
    function send_additional_mails($mails, $form){
        $submission = WPCF7_Submission::get_instance();
        $check_your_mail = $this->list_emails;
        if( is_array($check_your_mail) && count($check_your_mail)>0  ) {
            $template = $form->prop('mail');
            //or if you have enabled mail 2 template, you can use that instead.
            $template = $form->prop('mail_2');
            $template['recipient'] = $check_your_mail;
            foreach( $check_your_mail as $email ){
                if( is_email($email) ){
                    $template['recipient'] = $email ;
                    $mails[$email] = $template;
                }
            }
        }
        return $mails;
    }
    function custom_repeater($properties, $wpcf7form) {
        if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) { 
            $headers = '<div class="repeater-field-header-data hidden"><div class="repeater-field-header">
                <div class="repeater-field-header-title"><span class="repeater-field-header-title-text">repeater_title</span> <span class="repeater-field-header-count">1</span></div>
                <div class="repeater-field-header-acctions">
                    <ul>
                        <li><i class="repeater-icon icon-down-open repeater-field-header-acctions-toogle" aria-hidden="true"></i></li>
                        <li><i class="repeater-icon icon-cancel-1 repeater-field-header-acctions-remove" aria-hidden="true"></i></li>
                    </ul>
                </div>
            </div></div>';
            $footer = '<div class="repeater-field-footer">[text repeater_check_end]
                            <a href="#" class="repeater-field-button-add" >Add more</a>
                        </div>';
            $form = preg_replace( array(
                '/\[repeater\s+([^\s\]]*)\s*([^\]]*)\]/s', 
                '/\[\/repeater\]/s'
            ),  array(
                '<div class="cf7-repeater-container">
                    <div id="$1" class="cf7-repeater">
                        '.$headers.'
                        <div class="cf7-repeater-input-c "><input type="text" class="field-repeater-data hidden" value="" name="$1" id="cf7-$1" data-datas="$2" /></div>
                            <div class="cf7-field-repeater-reponese">
                                <div class="cf7-field-repeater-data-html hidden">',
                                '</div>
                            </div>
                '.$footer.'
                </div></div>'
            ), $properties['form'] );
            $properties['form'] = $form;
        }
        return $properties;
    }
}
new Superaddons_Contact_Form_7_Repeater_Field;