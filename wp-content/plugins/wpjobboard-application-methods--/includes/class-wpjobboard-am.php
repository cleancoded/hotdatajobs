<?php

// Bail if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wpjobboard_Am
{
    /**
     * Wpjobboard_Am Singleton
     * 
     * @var Wpjobboard_Am
     */
    protected static $_instance = null;
    
    /**
     * Path to wpjobboard-am (eg. /public_html/wp-content/plugins/wpjobboard-am)
     *
     * @var string
     */
    protected $_basedir = null;
    
    /**
     * URL to wpjobboard-am (eg. http://example.com/wp-content/plugins/wpjobboard-am)
     *
     * @var string
     */
    protected $_baseurl = null;
    
    /**
     * Admin Object
     *
     * @var Wpjobboard_Am_Admin
     */
    public $admin = null;
    

    /**
     * Frontend Object
     *
     * @var Wpjobboard_Frontend_Admin
     */
    public $frontend = null;
    
    /**
     * Object contructor
     * 
     * Protected constructor allows to create only one instance of this class.
     * 
     * @since 1.0
     * @return Wpjobboard_Am
     */
    protected function __construct() {
        
        $this->_basedir = dirname( dirname( __FILE__ ) );
        $this->_baseurl = plugin_dir_url( dirname( __FILE__ ) );
        
        
        if( is_admin() ) {
            include_once $this->_basedir . "/includes/class-wpjobboard-am-admin.php";
            $this->admin = new Wpjobboard_Am_Admin();
        } else {
            include_once $this->_basedir . "/includes/class-wpjobboard-am-frontend.php";
            $this->frontend = new Wpjobboard_Am_Frontend();
        }
        
        add_action( "init", array( $this, "init" ));
    }
    
    /**
     * Singleton
     * 
     * Creates if NULL and returns Wpjobboard_Am instance
     * 
     * @since 1.0
     * @return Wpjobboard_Am
     */
    public static function get_instance() {
        if( self::$_instance === null ) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Returns basedir path
     * 
     * @since 1.0
     * @return string   Basedir path (eg. /public_html/wp-content/plugins/wpjobboard-am)
     */
    public function get_basedir() {
        return $this->_basedir;
    }
    
    /**
     * Returns plugin base URL
     * 
     * @since 1.0
     * @return string   Basedir path (eg. http://example.com/wp-content/plugins/wpjobboard-am)
     */
    public function get_baseurl() {
        return $this->_baseurl;
    }
    
    /**
     * General Init Function
     * 
     * This function is executed in both frontend and wp-admin
     * 
     * @since 1.0
     * @return void
     */
    public function init() {
        
        include_once $this->_basedir . "/includes/class-wpjobboard-am-extended-checkbox.php";
        
        
        wp_register_style( 
            "wpjobboard-am",
            Wpjobboard_Am::get_instance()->get_baseurl() . "/assets/css/wpjobboard-am.css",
            array( ),
            "1.0" 
        );
        
        wp_register_script(
            "wpjobboard-am",
            Wpjobboard_Am::get_instance()->get_baseurl() . "/assets/js/wpjobboard-am.js",
            array( "jquery" ),
            "1.0",
            true  
        );
        
        wp_localize_script( "wpjobboard-am", "wpjobboard_am_lang", array(
            "ajaxurl" => admin_url( "admin-ajax.php" ),
            "email_placeholder" => __("Provide valid e-mail...", "wpjb-am"),
        ));
        
        
        wpjb_meta_register( "job", "wpjobboard_am_data" );
        wpjb_meta_register( "company", "am_default" );
        
        // AM in Job Form
        add_filter("wpjb_form_init_job", array($this, "wpjb_am_form_init_job") );
        add_filter("wpja_form_init_job", array($this, "wpjb_am_form_init_job") );
        add_filter("wpja_form_save_job", array($this, "wpjb_am_form_save_job") );
        add_filter("wpjb_form_save_job", array($this, "wpjb_am_form_save_job") );
        
        // Default AM in Candidate Form
        add_filter( "wpjb_form_init_company", array($this, "wpjb_form_init_company") );
        add_filter( "wpja_form_init_company", array($this, "wpjb_form_init_company") );
        add_filter( "wpjb_form_save_company", array($this, "wpjb_form_save_company") );
        add_filter( "wpja_form_save_company", array($this, "wpjb_form_save_company") );
        
        //add_filter("wpjb_message_pre_send", array($this, "wpjb_am_message_pre_send"));
        add_filter("wpjb_message", array($this, "wpjb_message"), 10, 2);
        
    }
    
    /**
     * Job Form Init
     * 
     * Adds Application methods into job form
     * 
     * @param Wpjb_Form_AddJob $form
     * @return Wpjb_Form_AddJob
     */
    public function wpjb_am_form_init_job($form) {
        
        wp_enqueue_style("wpjobboard-am");
        wp_enqueue_script("wpjobboard-am");
        
        $request = Daq_Request::getInstance();
        $application_methods = null;
        
        // Get Default Values From Company Config
        $company = Wpjb_Model_Company::current();
        if( isset( $company->meta->am_default ) ) {
            $application_methods = unserialize( $company->meta->am_default->value() ); 
        } 
        
        // Get Values From Job
        if( isset( $form->getObject()->meta->wpjobboard_am_data ) && !empty( $form->getObject()->meta->wpjobboard_am_data ) ) {
            if( $form->getObject()->meta->wpjobboard_am_data->value() != false ) {
                $application_methods = unserialize( $form->getObject()->meta->wpjobboard_am_data->value() );
            }
        } 
        
        if( !is_admin() && !get_query_var( 'wpjb-id' ) ) {
            $id = "wpjb_session_".str_replace( "-", "_", wpjb_transient_id() );
            $transient = wpjb_session()->get($id);
            $job = $transient["job"];
            if( isset( $job["wpjobboard_am_data"] ) && !empty( $job["wpjobboard_am_data"] ) ) {
                $application_methods = array();
                foreach( $job["wpjobboard_am_data"] as $method ) {
                    $application_methods[$method] = $job["wpjb-am-".$method];
                }
            }
        }

        // Get Values from POST
        $keys = $request->post('wpjobboard_am_data', array());
        if( count( $keys ) > 0) {
            $application_methods = array();
            foreach($keys as $method) {
                $application_methods[$method] = $request->post("wpjb-am-".$method, array());
            } 
        }
        
        
        
        if( isset( $application_methods ) && is_array( $application_methods ) ) {
            $keys = array_keys($application_methods);
        }

        // Default E-mail (Form) and URL application methods
        $methods =  array(
            array(
                    "key"           => "mail",
                    "value"         => "email",
                    "description"   => __("Apply Online Form", "wpjobboard-am"),
                ),
            array(
                    "key"           => "globe",
                    "value"         => "url",
                    "description"   => __("External URL", "wpjobboard-am"),
                ),
        );
        
        // Check if LinkedIn connection is Valid
        if(strlen(Wpjb_Service_Linkedin::linkedin()->getOauthToken()) > 0) {
            
            $instance = Wpjb_Project::getInstance();
            $linkedin_apply = $instance->conf( "linkedin_apply" );
            
            // Check if apply with LinkedIn option is on
            if( isset( $linkedin_apply[0] ) && $linkedin_apply[0] == 1 ) {
                $methods[] = array(
                    "key"           => "linkedin",
                    "value"         => "linkedin",
                    "description"   => __("LinkedIn", "wpjobboard-am"),
                );
            }
        }
        
        $methods = apply_filters("wpjobboard_am_application_methods", $methods);
        
        $form->addGroup("wpjobboard-am", __("Application Methods", "wpjobboard-am"));

        $e = new Wpjb_Am_Extended_Checkbox("wpjobboard_am_data");
        $e->setLabel(__("Application Method", "wpjobboard-am"));
        $e->addOptions($methods);
        //$e->setBuiltin(false);
        $e->setValue($keys);
        $e->setHint(__("Select how applicant can apply for a job. You can select nothing, and leave instructions in job description", "wpjobboard-am"));
        $e->setRenderer(array($this, "wpjb_am_form_renderer"));
        //$e->setRequired();
        $e->setConfig($application_methods);
        $e->setMaxChoices(99);
        $form->addElement($e, "wpjobboard-am");
        
        return $form;
    }
    
    public function wpjb_form_init_company($form) {
        
        wp_enqueue_style("wpjobboard-am");
        wp_enqueue_script("wpjobboard-am");
        
        $request = Daq_Request::getInstance();
        
        $keys = array();
        $application_methods = null;
          
        if( isset( $form->getObject()->meta->am_default ) && !empty( $form->getObject()->meta->am_default ) ) {
            $application_methods = unserialize($form->getObject()->meta->am_default->value());
            if($application_methods !== false) {
                $keys = array_keys($application_methods);
            }
        } else {
            $keys = $request->post('am_default', array());
        }
        

        // Default E-mail (Form) and URL application methods
        $methods =  array(
            array(
                    "key"           => "mail",
                    "value"         => "email",
                    "description"   => __("Apply Online Form", "wpjobboard-am"),
                ),
            array(
                    "key"           => "globe",
                    "value"         => "url",
                    "description"   => __("External URL", "wpjobboard-am"),
                ),
        );
        
        // Check if LinkedIn connection is Valid
        if(strlen(Wpjb_Service_Linkedin::linkedin()->getOauthToken()) > 0) {
            
            $instance = Wpjb_Project::getInstance();
            $linkedin_apply = $instance->conf( "linkedin_apply" );
            
            // Check if apply with LinkedIn option is on
            if( isset( $linkedin_apply[0] ) && $linkedin_apply[0] == 1 ) {
                $methods[] = array(
                    "key"           => "linkedin",
                    "value"         => "linkedin",
                    "description"   => __("LinkedIn", "wpjobboard-am"),
                );
            }
        }
        
        $methods = apply_filters("wpjobboard_am_application_methods", $methods);
        
        $form->addGroup("wpjobboard-am", __("Application Methods", "wpjobboard-am"));

        $e = new Wpjb_Am_Extended_Checkbox("am_default");
        $e->setLabel(__("Default Application Method", "wpjobboard-am"));
        $e->addOptions($methods);
        //$e->setBuiltin(false);
        $e->setValue($keys);
        $e->setHint( __( "Select default application methods for your jobs. Application Methods for new jobs will be prefilled with this configuration (you can change for each job).", "wpjobboard-am" ) );
        $e->setRenderer(array($this, "wpjb_am_form_renderer"));
        //$e->setRequired();
        $e->setConfig($application_methods);
        $e->setMaxChoices(99);
        $form->addElement($e, "wpjobboard-am");
        
        return $form;
    }
    
    
    
    /**
     * Renderer for application method field
     * 
     * Render application methods
     * 
     * @param Wpjb_Am_Extended_Checkbox $field
     */
    public function wpjb_am_form_renderer($field) {
        
 
        $values = $field->getValue();
        if( is_string( $values ) ) {
            $values = unserialize( $values );
            $values = array_keys($values);
        }
        if( !is_array( $values ) ) {
            $values = array();
        }
        $config = $field->getConfig();
              
        ?> 
        <div class="wpjb-am-methods-box"> 
            <?php if( is_array( $field->getOptions() ) && count( $field->getOptions() ) > 0 ): ?>
                <?php foreach($field->getOptions() as $key => $option): ?>        

                <label class="wpjb-am-box-method wpjb-am-box-method-<?php echo $option["value"]; ?> <?php if(in_array($option["value"], $values)): ?> wpjb-am-active <?php endif; ?>" >

                    <input class="wpjb-am-chbox" type="checkbox" name="<?php echo $field->getName() ?>[]" id="wpjb-am-method-chbox-<?php echo $option["value"]; ?>" value="<?php echo $option["value"]; ?>" <?php if(in_array($option["value"], $values)): ?> checked <?php endif; ?> />
                    <div class="wpjb-am-chbox-label wpjb-am-chbox-label-<?php echo $option["value"]; ?>"></div>

                    <a href="" title="<?php echo $option["desc"]; ?>" 
                               class="wpjb-am-method wpjb-am-method-<?php echo $option["value"]; ?>" 
                               data-wpjb-am-value="<?php echo $option["value"] ?>"
                               data-wpjb-am-key="<?php echo $option["key"] ?>"><?php echo $option["desc"]; ?></a>


                    <div class="wpjb-am-box-config wpjb-am-box-config-<?php echo $option["value"]; ?>">
                        <?php $this->wpjb_am_render_config($option["value"], $config); ?>
                    </div>

                </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> 
        <?php
    }
    
    /**
     * Job Form Save
     * 
     * Saves application methods for job
     * 
     * @param Wpjb_Form_AddJob $form
     * @return Wpjb_Form_AddJob
     */
    public function wpjb_am_form_save_job($form) {
        
        $request = Daq_Request::getInstance();
        $application_methods = array();
          
        $methods = $request->post("wpjobboard_am_data", array());
        foreach($methods as $method) {
            $application_methods[$method] = $request->post("wpjb-am-".$method, array());
        }

        // Job Add - preview have all in session instead of request
        if( empty( $application_methods ) && !get_query_var( 'wpjb-id' ) ) {
            $id = "wpjb_session_".str_replace( "-", "_", wpjb_transient_id() );
            $transient = wpjb_session()->get($id);
            $job = $transient["job"];
            if( isset( $job["wpjobboard_am_data"] ) && !empty( $job["wpjobboard_am_data"] ) ) {
                foreach( $job["wpjobboard_am_data"] as $method ) {
                    $application_methods[$method] = $job["wpjb-am-".$method];
                }
            }
        }
        
        $mq = new Daq_Db_Query();
        $meta_id = $mq->select()->from("Wpjb_Model_Meta t")->where("t.meta_object = ?", "job")->where("t.name = ?", "wpjobboard_am_data")->fetchColumn();
        
        $mvq = new Daq_Db_Query();
        $mve = $mvq->select()->from("Wpjb_Model_MetaValue t")->where("t.meta_id = ?", $meta_id)->where("t.object_id = ?", $form->getObject()->id)->execute();
        
        $mv = new Wpjb_Model_MetaValue();
        if(isset($mve[0]) && !empty($mve[0])) {
            $mv = new Wpjb_Model_MetaValue($mve[0]->id);
        }        
        $mv->value = serialize($application_methods);
        $mv->meta_id = $meta_id;
        $mv->object_id = $form->getObject()->id;
        $mv->save();
        
        if( $form->hasElement( "wpjobboard_am_data" ) ) {
            $form->getElement('wpjobboard_am_data')->setValue(serialize($application_methods));
        }
        
        return $form;
    }
    
    public function wpjb_form_save_company($form) {
        
        $request = Daq_Request::getInstance();
        $application_methods = array();
          
        $methods = $request->post("am_default", array());
        foreach($methods as $method) {
            $application_methods[$method] = $request->post("wpjb-am-".$method, array());
        }
        
        $mq = new Daq_Db_Query();
        $meta_id = $mq->select()->from("Wpjb_Model_Meta t")->where("t.meta_object = ?", "company")->where("t.name = ?", "am_default")->fetchColumn();
        
        $mvq = new Daq_Db_Query();
        $mve = $mvq->select()->from("Wpjb_Model_MetaValue t")->where("t.meta_id = ?", $meta_id)->where("t.object_id = ?", $form->getObject()->id)->execute();
        
        $mv = new Wpjb_Model_MetaValue();
        if(isset($mve[0]) && !empty($mve[0])) {
            $mv = new Wpjb_Model_MetaValue($mve[0]->id);
        }        
        $mv->value = serialize($application_methods);
        $mv->meta_id = $meta_id;
        $mv->object_id = $form->getObject()->id;
        $mv->save();
        
        return $form;
    }
    
    /**
     * Renders options for application method
     * 
     * @param string $method
     * @param string $config
     */
    public function wpjb_am_render_config($method, $config) {
        
        // Job have saved values
        if( isset($config[$method]) && !empty($config[$method]) ) {
            $value = $config[$method];
        } else {
            $value = null;
        }
        
        // Value in $_POST
        $request = Daq_Request::getInstance();
        $methods = $request->post("wpjobboard_am_data", array());
        if( in_array( $method, $methods ) ) {
            $t_value = $request->post("wpjb-am-".$method, null);   
            if($t_value != null) {
                $value = $t_value;
            }
        }

        // Only for preview (new jobs)
        if( !is_admin() && empty($value) && !get_query_var('wpjb-id') ) {
            $id = "wpjb_session_".str_replace("-", "_", wpjb_transient_id());
            $transient = wpjb_session()->get($id);
            $job = $transient["job"];
            if( in_array( $method, (array)$job['wpjobboard_am_data'] ) ) {
                $value = $job["wpjb-am-".$method];
            }
        }
        
        //ob_start(); 
            ?>
                <?php if($method == "email"): ?>
                    <small><?php _e("This method will display application form. Application will be sent to all listed e-mails.", "wpjb-am"); ?></small>
                    <input type="hidden" class="wpjb-am-stop-propagation" id="wpjb-am-<?php echo $method; ?>" name="wpjb-am-<?php echo $method; ?>" value="<?php echo $value ?>" />
                    <?php foreach(explode(",", $value) as $val): ?>
                    <div>
                        <input style="width: 85%;" type="text" class="wpjb-am-stop-propagation wpjb-am-email-mock" id="wpjb-am-email-mock" name="wpjb-am-email-mock" placeholder="<?php _e("Provide valid e-mail...", "wpjb-am") ?>" value="<?php echo $val ?>" />
                        <a href="" class="wpjb-am-stop-propagation wpjb-button wpjb-icons wpjb-icon-minus wpjb_am_remove_email <?php if(is_admin()): ?> button <?php endif; ?>"></a>
                    </div>
                    <?php endforeach; ?>
                    <a href="" id="wpjb_am_add_email" class="wpjb-am-stop-propagation wpjb-button wpjb-icons wpjb-icon-plus <?php if(is_admin()): ?> button <?php endif; ?>"></a>
                <?php elseif($method == "url"): ?>
                    <small><?php _e("This method will display apply button, which redirects user to an external page where the user can apply.", "wpjb-am"); ?></small>
                    <input type="text" placeholder="<?php _e("Provide valid URL...", "wpjb-am") ?>" class="wpjb-am-stop-propagation" id="wpjb-am-<?php echo $method; ?>" name="wpjb-am-<?php echo $method; ?>" value="<?php echo $value ?>"/>
                <?php elseif($method == "linkedin"): ?>
                    <small><?php _e("This method allow to apply using LinkedIn account.", "wpjb-am"); ?></small>
                <?php endif; ?>
            <?php 
        //ob_flush();
    }
    
    /**
     * Adds additional recipients of e-mail if provided
     * 

     * @param Array $params
     * @param Wpjb_Utility_Message $message
     * @return Array
     */
    public function wpjb_message($params, $message)  {
        
       
        if($message->getTemplate()->name != "notify_employer_new_application") {
            return $params;
        }
        
        $job_id = $message->getTpl()->var["job"]["id"];
        $job = new Wpjb_Model_Job($job_id);
        

        if( isset( $job->meta->wpjobboard_am_data ) && !empty( $job->meta->wpjobboard_am_data ) ) {
            $application_methods = unserialize( $job->meta->wpjobboard_am_data->value() );
        }
        if(!isset($application_methods["email"]) || empty($application_methods["email"])) {
            return $params;
        }
        
        $am_emails = explode(",", $application_methods["email"]);
        
        if( !is_email( $message->getTo() ) ) {
            $params['to'] = $am_emails[0];
        }
        
        foreach( $am_emails as $email) {
            if( $email != $message->getTo() ) {
                $params['headers'][] = 'Cc: ' . $email;    
            }
        }
 
        return $params;
    }
}
