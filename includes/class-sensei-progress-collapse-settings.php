<?php
//security first
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Sensei Progress Collapse Settings class
 *
 * This class handles all of the functionality for the plugins functionality.
 *
 * @package WordPress
 * @subpackage Sensei Progress Collapse
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * - __construct
 * - get_setting
 * - register_settings_tab
 * - register_settings_fields
 * todo go through all functions to make sure the doc info is correct
 */
class Sensei_Progress_Collapse_Settings {
    public function __construct(){
        if( is_admin() ){
            add_filter( 'sensei_settings_tabs', array( $this, 'register_settings_tab' ) );
            add_filter( 'sensei_settings_fields', array( $this, 'register_settings_fields' ) );
        }
    }// end __construct

    /**
     * sensei get_setting value wrapper
     *
     * @return string $settings value
     */
    public function get_setting( $setting_token ){

        // get all settings from sensei
        $settings = Sensei()->settings->get_settings();

        if( empty( $settings )  || ! isset(  $settings[ $setting_token ]  ) ){
            return '';
        }

        return $settings[ $setting_token ];
    }

    /**
     * Attaches the the progress collapse settings to the sensei admin settings tabs
     *
     * @param array $sensei_settings_tabs;
     * @return array  $sensei_settings_tabs
     */
    public function register_settings_tab( $sensei_settings_tabs ){

        $smc_tab  = array(
            'name' 			=> __( 'Module Collapse', 'sensei-module-collapse' ),
            'description'	=> __( 'Optional settings for the Module Collapse extension', 'sensei-module-collapse' )
        );

        $sensei_settings_tabs['sensei-module-collapse-settings'] = $smc_tab;

        return $sensei_settings_tabs;
    }// end register_settings_tab


    /**
     * Includes the sensei progress collapse settings fields
     *
     * @param array $sensei_settings_fields;
     * @return array  $sensei_settings_fields
     */
    public function register_settings_fields( $sensei_settings_fields ){

        $sensei_settings_fields['sensei_progress_collapse'] = array(
            'name' => __( 'Enable Progress Collapse', 'sensei_progress_collapse' ),
            'description' => __( 'Check to enable module collapse in the course progress sidebar', 'woothemes-sensei' ),
            'type' => 'checkbox',
            'default' => true,
            'section' => 'sensei-module-collapse-settings'
        );

        return $sensei_settings_fields;

    }// end register_settings_tab
}// end Scp_Ext_settings