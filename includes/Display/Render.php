<?php if ( ! defined( 'ABSPATH' ) ) exit;

final class NF_Display_Render
{
    protected static $loaded_templates = array(
        'app-layout',
        'app-before-form',
        'app-after-form',
        'app-before-fields',
        'app-after-fields',
        'app-before-field',
        'app-after-field',
        'form-layout',
        'form-hp',
        'field-layout',
        'field-before',
        'field-after',
        'fields-wrap',
        'fields-wrap-no-label',
        'fields-wrap-no-container',
        'fields-label',
        'fields-error',
        'form-error',
        'field-input-limit'
    );

    protected static $use_test_values = FALSE;

    protected static function user_can_display_test_values()
    {
        $capability = apply_filters( 'ninja_forms_display_test_values_capabilities', 'read' ) ;
        return isset( $_GET[ 'ninja_forms_test_values' ] ) && current_user_can( $capability );
    }

    protected static function form_is_locked( $form )
    {
        if ( $form->get_setting( 'lock' ) ) {
            echo __( 'This form is not available.', 'ninja-forms' );
            return true;
        }
    }

    protected static function form_not_available_for_guests( $form )
    {
        if( $form->get_setting( 'logged_in' ) && ! is_user_logged_in() ){
            echo $form->get_setting( 'not_logged_in_msg' );
            return true;
        }
    }

    protected static function form_has_max_submission( $form )
    {
        if( $form->get_setting( 'sub_limit_number' ) ){
            global $wpdb;

            $prepared = $wpdb->prepare( "SELECT COUNT(*) as total, post_id, ID
                FROM {$wpdb->prefix}postmeta meta,
                  {$wpdb->prefix}posts posts
                 WHERE
                 `post_id` = `ID` AND
                 `post_status` = 'publish' AND
                 `meta_key` = '_form_id' AND
                 `meta_value` = %d", $form->get_id() );

            $count =  $wpdb->get_var($prepared);

            if( $count >= $form->get_setting( 'sub_limit_number' ) ) {
                echo $form->get_setting( 'sub_limit_msg' );
                return true;
            }
        }
    }

    protected static function update_settings_filters( $form )
    {
        $form_id = $form->get_id();

        $before_form = apply_filters( 'ninja_forms_display_before_form', '', $form_id );
        $form->update_setting( 'beforeForm', $before_form );

        $before_fields = apply_filters( 'ninja_forms_display_before_fields', '', $form_id );
        $form->update_setting( 'beforeFields', $before_fields );

        $after_fields = apply_filters( 'ninja_forms_display_after_fields', '', $form_id );
        $form->update_setting( 'afterFields', $after_fields );

        $after_form = apply_filters( 'ninja_forms_display_after_form', '', $form_id );
        $form->update_setting( 'afterForm', $after_form );
    }

    protected static function update_field_siblings( $field, $sibling )
    {
        // ninja_forms_display_before_field_type_
        // ninja_forms_display_before_field_key_
        // ninja_forms_display_after_field_type_
        // ninja_forms_display_after_field_key_
        $display_before = apply_filters( "ninja_forms_display_{$sibling}_field_type_" . $field->get_setting( 'type' ), '' );
        $display_before = apply_filters( "ninja_forms_display_{$sibling}_field_key_" . $field->get_setting( 'key' ), $display_before );

        return $display_before;
    }

    protected static function has_default_label_position( $settings )
    {
        return ! isset( $settings[ 'label_pos' ] ) || 'default' == $settings[ 'label_pos' ];
    }

    public static function get_parsed_field_settings( $setting )
    {
        return is_numeric($setting) ? floatval($setting) : $setting;
    }

    protected static function get_field_settings( $field )
    {
        $settings = $field->get_settings();
        $settings = array_map('self::get_parsed_field_settings', $settings);

        return $settings;
    }

    protected static function is_list_item( $settings )
    {
        return 'list' == $settings[ 'parentType' ] && isset( $settings[ 'options' ] ) && is_array( $settings[ 'options' ] );
    }

    protected static function parse_list_item( $settings, $field_type )
    {
        $settings[ 'options' ] = apply_filters( 'ninja_forms_render_options', $settings[ 'options' ], $settings );
        $settings[ 'options' ] = apply_filters( 'ninja_forms_render_options_' . $field_type, $settings[ 'options' ], $settings );

        return $settings[ 'options' ];
    }

    protected static function get_default_value( $settings, $field_type )
    {
        $default_value = apply_filters('ninja_forms_render_default_value', $settings['default'], $field_type, $settings);

        $default_value = preg_replace( '/{.*}/', '', $default_value );

        if ($default_value) {
            ob_start();
            do_shortcode( $default_value );

            return ob_get_clean();
        }
    }

    protected static function parse_currency_markers( $price )
    {
        // TODO: Does the currency marker need to stripped here?
        $price = str_replace( array( '$', '£', '€' ), '', $price);
        $price = str_replace( Ninja_Forms()->get_setting( 'currency_symbol' ), '', $price);
        $price = number_format($price, 2);

        return $price;
    }

    protected static function get_shipping_cost( $shipping_cost )
    {
        return self::parse_currency_markers($shipping_cost);
    }

    protected static function get_product_price( $product_price )
    {
        return self::parse_currency_markers($product_price);
    }



    public static function render( $form_id )
    {
        self::$use_test_values =  self::user_can_display_test_values() ;

        if( ! has_action( 'wp_footer', 'NF_Display_Render::output_templates', 9999 ) ){
            add_action( 'wp_footer', 'NF_Display_Render::output_templates', 9999 );
        }
        $form = Ninja_Forms()->form( $form_id )->get();

        if (self::form_is_locked( $form ) || self::form_not_available_for_guests( $form ) || self::form_has_max_submission( $form )) {
            return;
        }

        self::update_settings_filters( $form );

        $form_fields = Ninja_Forms()->form( $form_id )->get_fields();

        $fields = array();

        if( empty( $form_fields ) ){
            echo __( 'No Fields Found.', 'ninja-forms' );
        } else {
            foreach ($form_fields as $field) {

                $field_type = $field->get_settings('type');

                if( ! isset( Ninja_Forms()->fields[ $field_type ] ) ) continue;
                if( ! apply_filters( 'ninja_forms_display_type_' . $field_type, TRUE ) ) continue;
                if( ! apply_filters( 'ninja_forms_display_field', $field ) ) continue;

                $field = apply_filters('ninja_forms_localize_fields', $field);
                $field = apply_filters('ninja_forms_localize_field_' . $field_type, $field);

                $field_class = Ninja_Forms()->fields[$field_type];

                if (self::$use_test_values) {
                    $field->update_setting('value', $field_class->get_test_value());
                }

                $field->update_setting('id', $field->get_id());

                /*
                 * TODO: For backwards compatibility, run the original action, get contents from the output buffer,
                 * and return the contents through the filter. Also display a PHP Notice for a deprecate filter.
                 */

                $field->update_setting( 'beforeField', self::update_field_siblings($field, 'before') );
                $field->update_setting( 'afterField', self::update_field_siblings($field, 'after') );

                $templates = $field_class->get_templates();

                if (!array($templates)) {
                    $templates = array($templates);
                }

                self::load_templates( $templates );
                $settings = self::get_field_settings( $field );

                if( self::has_default_label_position( $settings ) ){
                    $settings[ 'label_pos' ] = $form->get_setting( 'default_label_pos' );
                }

                $settings[ 'parentType' ] = $field_class->get_parent_type();

                if(self::is_list_item( $settings )){
                    $settings[ 'options' ] = self::parse_list_item( $settings, $field_type );
                }

                if (isset($settings['default'])) {
                    $settings['value'] = self::get_default_value( $settings, $field_type );
                }



                // TODO: Find a better way to do this.
                if ('shipping' == $settings['type']) {
                    $settings['shipping_cost'] = self::get_shipping_cost( $settings['$shipping_cost'] );
                } elseif ('product' == $settings['type']) {
                    $settings['product_price'] = self::get_product_price( $settings['product_price'] );
                } elseif ('total' == $settings['type'] && isset($settings['value'])) {
                    $settings['value'] = number_format($settings['value'], 2);
                }

                $settings['element_templates'] = $templates;
                $settings['old_classname'] = $field_class->get_old_classname();
                $settings['wrap_template'] = $field_class->get_wrap_template();

                $fields[] = apply_filters( 'ninja_forms_localize_field_settings_' . $field_type, $settings, $form );
            }
        }

        // Output Form Container
        do_action( 'ninja_forms_before_container', $form_id, $form->get_settings(), $form_fields );
        Ninja_Forms::template( 'display-form-container.html.php', compact( 'form_id' ) );

        ?>
        <!-- TODO: Move to Template File. -->
        <script>
            var formDisplay = 1;

            // Maybe initialize nfForms object
            var nfForms = nfForms || [];

            // Build Form Data
            var form = [];
            form.id = '<?php echo $form_id; ?>';
            form.settings = <?php echo wp_json_encode( $form->get_settings() ); ?>;

            form.fields = <?php echo wp_json_encode( $fields ); ?>;

            // Add Form Data to nfForms object
            nfForms.push( form );
        </script>

        <?php
        self::enqueue_scripts( $form_id );

    }

    public static function localize( $form_id )
    {
        self::render( $form_id );
    }

    public static function localize_preview( $form_id )
    {
        self::$use_test_values = self::user_can_display_test_values() ;

        add_action( 'wp_footer', 'NF_Display_Render::output_templates', 9999 );

        $form = get_user_option( 'nf_form_preview_' . $form_id );

        if( ! $form ){
            self::localize( $form_id );
            return;
        }

        if( isset( $form[ 'settings' ][ 'logged_in' ] ) && $form[ 'settings' ][ 'logged_in' ] && ! is_user_logged_in() ){
            echo $form[ 'settings' ][ 'not_logged_in_msg' ];
            return;
        }

        $form[ 'settings' ][ 'is_preview' ] = TRUE;

        $before_form = apply_filters( 'ninja_forms_display_before_form', '', $form_id, TRUE );
        $form[ 'settings' ][ 'beforeForm'] = $before_form;

        $before_fields = apply_filters( 'ninja_forms_display_before_fields', '', $form_id, TRUE );
        $form[ 'settings' ][ 'beforeFields'] = $before_fields;

        $after_fields = apply_filters( 'ninja_forms_display_after_fields', '', $form_id, TRUE );
        $form[ 'settings' ][ 'afterFields'] = $after_fields;

        $after_form = apply_filters( 'ninja_forms_display_after_form', '', $form_id, TRUE );
        $form[ 'settings' ][ 'afterForm'] = $after_form;

        $fields = array();

        if( empty( $form['fields'] ) ){
            echo __( 'No Fields Found.', 'ninja-forms' );
        } else {
            foreach ($form['fields'] as $field_id => $field) {

                $field_type = $field['settings']['type'];

                if( ! isset( Ninja_Forms()->fields[ $field_type ] ) ) continue;
                if( ! apply_filters( 'ninja_forms_preview_display_type_' . $field_type, TRUE ) ) continue;
                if( ! apply_filters( 'ninja_forms_preview_display_field', $field ) ) continue;

                $field['settings']['id'] = $field_id;

                $field = apply_filters('ninja_forms_localize_fields_preview', $field);
                $field = apply_filters('ninja_forms_localize_field_' . $field_type . '_preview', $field);

                $display_before = apply_filters( 'ninja_forms_display_before_field_type_' . $field['settings'][ 'type' ], '' );
                $display_before = apply_filters( 'ninja_forms_display_before_field_key_' . $field['settings'][ 'key' ], $display_before );
                $field['settings'][ 'beforeField' ] = $display_before;

                $display_after = apply_filters( 'ninja_forms_display_after_field_type_' . $field['settings'][ 'type' ], '' );
                $display_after = apply_filters( 'ninja_forms_display_after_field_key_' . $field['settings'][ 'key' ], $display_after );
                $field['settings'][ 'afterField' ] = $display_after;

                foreach ($field['settings'] as $key => $setting) {
                    if (is_numeric($setting)) $field['settings'][$key] = floatval($setting);
                }

                if( ! isset( $field['settings'][ 'label_pos' ] ) || 'default' == $field['settings'][ 'label_pos' ] ){
                    if( isset( $form[ 'settings' ][ 'default_label_pos' ] ) ) {
                        $field['settings'][ 'label_pos' ] = $form[ 'settings' ][ 'default_label_pos' ];
                    }
                }

                $field_class = Ninja_Forms()->fields[$field_type];

                $templates = $field_class->get_templates();

                if (!array($templates)) {
                    $templates = array($templates);
                }

                foreach ($templates as $template) {
                    self::load_template('fields-' . $template);
                }

                if (self::$use_test_values) {
                    $field['settings']['value'] = $field_class->get_test_value();
                }

                $field[ 'settings' ][ 'parentType' ] = $field_class->get_parent_type();

                if( 'list' == $field[ 'settings' ][ 'parentType' ] && isset( $field['settings'][ 'options' ] ) && is_array( $field['settings'][ 'options' ] ) ){
                    $field['settings'][ 'options' ] = apply_filters( 'ninja_forms_render_options', $field['settings'][ 'options' ], $field['settings'] );
                }

                if (isset($field['settings']['default'])) {
                    $default_value = apply_filters('ninja_forms_render_default_value', $field['settings']['default'], $field_type, $field['settings']);

                    $default_value = preg_replace( '/{.*}/', '', $default_value );

                    if ($default_value) {
                        $field['settings']['value'] = $default_value;

                        ob_start();
                        do_shortcode( $field['settings']['value'] );
                        $ob = ob_get_clean();

                        if( $ob ){
                            $field['settings']['value'] = $ob;
                        }
                    }
                }

                // TODO: Find a better way to do this.
                if ('shipping' == $field['settings']['type']) {
                    $field['settings']['shipping_cost'] = str_replace( array( '$', '£', '€' ), '', $field['settings']['shipping_cost'] );
                    $field['settings']['shipping_cost'] = str_replace( Ninja_Forms()->get_setting( 'currency_symbol' ), '', $field['settings']['shipping_cost'] );
                    $field['settings']['shipping_cost'] = number_format($field['settings']['shipping_cost'], 2);
                } elseif ('product' == $field['settings']['type']) {
                    // TODO: Does the currency marker need to stripped here?
                    $field['settings']['product_price'] = str_replace( array( '$', '£', '€' ), '', $field['settings']['product_price'] );
                    $field['settings']['product_price'] = str_replace( Ninja_Forms()->get_setting( 'currency_symbol' ), '', $field['settings']['product_price'] );
                    $field['settings']['product_price'] = number_format($field['settings']['product_price'], 2);
                } elseif ('total' == $field['settings']['type']) {
                    if( ! isset( $field[ 'settings' ][ 'value' ] ) ) $field[ 'settings' ][ 'value' ] = 0;
                    $field['settings']['value'] = number_format($field['settings']['value'], 2);
                }

                $field['settings']['element_templates'] = $templates;
                $field['settings']['old_classname'] = $field_class->get_old_classname();
                $field['settings']['wrap_template'] = $field_class->get_wrap_template();

                $fields[] = apply_filters( 'ninja_forms_localize_field_settings_' . $field_type, $field['settings'], $form );
            }
        }

        // Output Form Container
        do_action( 'ninja_forms_before_container_preview', $form_id, $form[ 'settings' ], $fields );
        Ninja_Forms::template( 'display-form-container.html.php', compact( 'form_id' ) );

        ?>
        <!-- TODO: Move to Template File. -->
        <script>
            // Maybe initialize nfForms object
            var nfForms = nfForms || [];

            // Build Form Data
            var form = [];
            form.id = '<?php echo $form['id']; ?>';
            form.settings = JSON.parse( '<?php echo WPN_Helper::addslashes( wp_json_encode( $form['settings'] ) ); ?>' );

            form.fields = JSON.parse( '<?php echo WPN_Helper::addslashes( wp_json_encode(  $fields ) ); ?>' );

            // Add Form Data to nfForms object
            nfForms.push( form );
        </script>

        <?php
        self::enqueue_scripts( $form_id );
    }

    public static function enqueue_scripts( $form_id )
    {
        wp_enqueue_media();
        wp_enqueue_style( 'jBox', Ninja_Forms::$url . 'assets/css/jBox.css' );
        wp_enqueue_style( 'summernote', Ninja_Forms::$url . 'assets/css/summernote.css' );
        wp_enqueue_style( 'codemirror', Ninja_Forms::$url . 'assets/css/codemirror.css' );
        wp_enqueue_style( 'codemirror-monokai', Ninja_Forms::$url . 'assets/css/monokai-theme.css' );
        wp_enqueue_style( 'rating', Ninja_Forms::$url . 'assets/css/rating.css' );


        if( Ninja_Forms()->get_setting( 'opinionated_styles' ) ) {

            if( 'light' == Ninja_Forms()->get_setting( 'opinionated_styles' ) ){
                wp_enqueue_style('nf-display', Ninja_Forms::$url . 'assets/css/display-opinions-light.css');
                wp_enqueue_style( 'nf-font-awesome', Ninja_Forms::$url . 'assets/css/font-awesome.min.css' );
            }

            if( 'dark' == Ninja_Forms()->get_setting( 'opinionated_styles' ) ){
                wp_enqueue_style('nf-display', Ninja_Forms::$url . 'assets/css/display-opinions-dark.css');
                wp_enqueue_style( 'nf-font-awesome', Ninja_Forms::$url . 'assets/css/font-awesome.min.css' );
            }
        } else {
            wp_enqueue_style( 'nf-display', Ninja_Forms::$url . 'assets/css/display-structure.css' );
        }

        wp_enqueue_style( 'pikaday-responsive', Ninja_Forms::$url . 'assets/css/pikaday-package.css' );

        wp_enqueue_script( 'backbone-marionette', Ninja_Forms::$url . 'assets/js/lib/backbone.marionette.min.js', array( 'jquery', 'backbone' ) );
        wp_enqueue_script( 'backbone-radio', Ninja_Forms::$url . 'assets/js/lib/backbone.radio.min.js', array( 'jquery', 'backbone' ) );
        wp_enqueue_script( 'math', Ninja_Forms::$url . 'assets/js/lib/math.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'modernizr', Ninja_Forms::$url . 'assets/js/lib/modernizr.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'moment', Ninja_Forms::$url . 'assets/js/lib/moment-with-locales.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'pikaday', Ninja_Forms::$url . 'assets/js/lib/pikaday.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'pikaday-responsive', Ninja_Forms::$url . 'assets/js/lib/pikaday-responsive.min.js', array( 'jquery' ) );
        $recaptcha_lang = Ninja_Forms()->get_setting( 'recaptcha_lang' );
        wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . $recaptcha_lang, array( 'jquery' ) );
        wp_enqueue_script( 'masked-input', Ninja_Forms::$url . 'assets/js/lib/jquery.maskedinput.min.js', array( 'jquery' ) );

        wp_enqueue_script( 'bootstrap', Ninja_Forms::$url . 'assets/js/lib/bootstrap.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'codemirror', Ninja_Forms::$url . 'assets/js/lib/codemirror.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'codemirror-xml', Ninja_Forms::$url . 'assets/js/lib/codemirror-xml.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'codemirror-formatting', Ninja_Forms::$url . 'assets/js/lib/codemirror-formatting.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'summernote', Ninja_Forms::$url . 'assets/js/lib/summernote.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'jBox', Ninja_Forms::$url . 'assets/js/lib/jBox.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'starrating', Ninja_Forms::$url . 'assets/js/lib/rating.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'nf-global', Ninja_Forms::$url . 'assets/js/min/global.js', array( 'jquery' ) );

        wp_enqueue_script( 'nf-front-end', Ninja_Forms::$url . 'assets/js/min/front-end.js', array( 'jquery', 'backbone', 'backbone-radio', 'backbone-marionette', 'math' ) );

        $data = apply_filters( 'ninja_forms_render_localize_script_data', array(
            'ajaxNonce' => wp_create_nonce( 'ninja_forms_display_nonce' ),
            'adminAjax' => admin_url( 'admin-ajax.php' ),
            'requireBaseUrl' => Ninja_Forms::$url . 'assets/js/',
            'use_merge_tags' => array(),
            'opinionated_styles' => Ninja_Forms()->get_setting( 'opinionated_styles' )
        ));

        foreach( Ninja_Forms()->fields as $field ){
            foreach( $field->use_merge_tags() as $merge_tag ){
                $data[ 'use_merge_tags' ][ $merge_tag ][ $field->get_type() ] = $field->get_type();
            }
        }

        wp_localize_script( 'nf-front-end', 'nfFrontEnd', $data );

        do_action( 'ninja_forms_enqueue_scripts', array( 'form_id' => $form_id ) );

        /*
        ?>
        <script type="text/javascript">
            function nf_recaptcha_set_field_value( inpval ) {
                console.log( inpval );
                jQuery( "#nf-field-<%= id %>" ).val( inpval );
            }

        </script>
        <?php
        */
        do_action( 'nf_display_enqueue_scripts' );
    }

    protected static function load_templates( $templates )
    {
        foreach ($templates as $template) {
            self::load_template('fields-' . $template);
        }
    }

    protected static function load_template( $file_name = '' )
    {
        if( ! $file_name ) return;

        if( self::is_template_loaded( $file_name ) ) return;

        self::$loaded_templates[] = $file_name;
    }

    public static function output_templates()
    {
        // Build File Path Hierarchy
        $file_paths = apply_filters( 'ninja_forms_field_template_file_paths', array(
            get_template_directory() . '/ninja-forms/templates/',
        ));

        $file_paths[] = Ninja_Forms::$dir . 'includes/Templates/';

        // Search for and Output File Templates
        foreach( self::$loaded_templates as $file_name ) {

            foreach( $file_paths as $path ){

                if( file_exists( $path . "$file_name.html" ) ){
                    echo file_get_contents( $path . "$file_name.html" );
                    break;
                }
            }
        }

        ?>
        <script>
            var post_max_size = '<?php echo WPN_Helper::string_to_bytes( ini_get('post_max_size') ); ?>';
            var upload_max_filesize = '<?php echo WPN_Helper::string_to_bytes( ini_get( 'upload_max_filesize' ) ); ?>';
            var wp_memory_limit = '<?php echo WPN_Helper::string_to_bytes( WP_MEMORY_LIMIT ); ?>';
        </script>
        <?php

        // Action to Output Custom Templates
        do_action( 'ninja_forms_output_templates' );
    }

    /*
     * UTILITY
     */

    protected static function is_template_loaded( $template_name )
    {
        return ( in_array( $template_name, self::$loaded_templates ) ) ? TRUE : FALSE ;
    }

} // End Class NF_Display_Render
