<?php

class Wpjb_Customize_Job {
    
    public function getSetting($key1, $key2 = null, $default = null) {
        $settings = get_option("wpjb_customize_job");

        if(isset($settings[$key1][$key2]) && $key1 && $key2) {
            return $settings[$key1][$key2];
        } elseif(isset($settings[$key1]) && $key1 && !$key2) {
            return $settings[$key1];
        } else {
            return $default;
        }
    }
    
    /**
     * Register "Job Details" section in WPJobBoard panel
     * 
     * This function is executed by 'customize_register' action.
     * 
     * @see customize_register action
     * 
     * @param WP_Customize_Manager $wp_customize
     */
    public function register( $wp_customize ) {
        
        $wp_customize->add_section( 'wpjb_customize_job' , array(
            'title'      => __( 'Job Details', 'wpjobboard' ),
            'priority'   => 100,
            'panel'      => 'wpjobboard'
        ) );
        
        $metas = Wpjb_Project::getInstance()->singular->job->getMetaRowData(new Wpjb_Model_Job);
        
        foreach($metas as $key => $meta) {
            $value = $this->getSetting("field", $key, null);
            //print_r($meta);
            if($value === null) {
                $value = json_encode(array(
                    "enabled" => $meta["enabled"],
                    "name" => $key,
                    "title" => $meta["title"],
                    "icon" => $meta["icon"],
                    "display" => $meta["display"]
                ));
            }
            
            $wp_customize->add_setting( 'wpjb_customize_job[field]['.$key.']' , array(
                'default'   => $value,
                'type'      => 'option',
                'transport' => 'postMessage',
            ) );

            $cf = new Wpjb_Customize_Control_CustomField( $wp_customize, 'wpjb_customize_job[field]['.$key.']',
               array(
                  'label' => __( 'Job Published', "wpjobboard" ),
                  'description'  => null,
                  'section' => 'wpjb_customize_job',
                  'value' => null
               ),
                array(
                    "field" => $key
                )
            ); 
            add_action( "customize_controls_print_footer_scripts", array($cf, "template"));
            $wp_customize->add_control($cf);

            $wp_customize->selective_refresh->add_partial( "wpjb_customize_job[field][$key]", [
                'selector'            => ".wpjb-row-meta-".$key,
                'settings'            => array(
                    "wpjb_customize_job[field][$key]",
                ),
                'render_callback'     => array($cf, "refreshRow"),
                'container_inclusive' => true,
                'fallback_refresh'    => false
            ] );
        }

    }
    

    
    
}