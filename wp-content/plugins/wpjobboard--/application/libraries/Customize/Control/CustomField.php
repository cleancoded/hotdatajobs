<?php

class Wpjb_Customize_Control_CustomField extends WP_Customize_Control {
    
    /**
     * The type of control being rendered
     * 
     * @var string
     */
    public $type = 'wpjb_control_custom_field';
   
    /**
     * WPJobBoard Related Params
     *
     * @var array
     */
    protected $_params = array();
    
    /**
     * True if icon selector template was already rendered
     *
     * @var bool
     */
    protected static $_iconSelectorRendered = false;
   
    public function __construct( $manager, $id, $args = array(), $params = array() ) {
        parent::__construct($manager, $id, $args);
        $this->_params = $params;
    }
    
    /**
     * Enqueue our scripts and styles
     * 
     * @since 5.1.0
     * @return void
     */
    public function enqueue() {
       wp_enqueue_script("wpjb-admin-customize");
    }
    
    /**
     * Render the control in the customizer
     */
   public function render_content() {
       wp_enqueue_script("wpjb-admin-customize");
       //var_dump($this->value());
       $data = json_decode($this->value());
       $field = array("enabled", "capability", "label", "display", "icon");
       
       $enabled = null;
       $capability = null;
       $label = null;
       $display = null;
       $icon = null;
       
       foreach($field as $key) {
           if(isset($data->$key)) {
               $$key = $data->$key;
           }
       }

       $input_id = "_customize-input-".$this->id;
       
       ?>
        <div class="simple-notice-custom-control wpjb-ctrl-cf-wrap">
            
            <?php if( !empty( $this->label ) ) { ?>
            <input type="checkbox" value="1" name="<?php echo esc_attr($input_id."[enabled]") ?>" class="wpjb-ctrl-cf-enabled" <?php checked($enabled) ?>> 
               <span class="customize-control-title" style="display:inline-block"><?php echo esc_html( $this->label ); ?></span>
            <?php } ?>
            <?php if( !empty( $this->description ) ) { ?>
               <span class="customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
            <?php } ?>
            
            <div class="wpjb-ctrl-cf-options">

                
                <input type="hidden" class="wpjb-ctrl-cf-holder" name="<?php echo esc_attr($input_id."[holder]") ?>" value="<?php #echo esc_attr($this->value()) ?>" <?php echo $this->link() ?> />

                <label style="width:48%;display:inline-block">Visible To</label>
                <select style="margin-left:0; width: 50%" name="<?php echo esc_attr($input_id."[capability]") ?>"  class="wpjb-ctrl-cf-capability">
                    <option value="" <?php selected("", $capability) ?>><?php _e("Anyone", "wpjobboard") ?></option>
                    <option value="read" <?php selected("read", $capability) ?>>read</option>
                </select>

                <label style="width:48%;display:inline-block"><?php _e("Label", "wpjobboard") ?></label>
                <input style="margin-left:0; width: 50%" id="<?php echo esc_attr($input_id) ?>" type="text" class="wpjb-ctrl-cf-label" name="<?php echo esc_attr($input_id."[label]") ?>" value="<?php echo esc_attr($label) ?>" placeholder="<?php _e("Label", "wpjobboard") ?>" _data-customize-setting-link="blogdescription">

                <label style="width:48%;display:inline-block"><?php _e("Display As", "wpjobboard") ?></label>
                <select style="margin-left:0; width: 50%" name="<?php echo esc_attr($input_id."[display]") ?>" style="margin-left:0" class="wpjb-ctrl-cf-display">
                    <option value="text" <?php selected("text", $display) ?>><?php _e("Text", "wpjobboard") ?></option>
                    <option value="html" <?php selected("html", $display) ?>><?php _e("HTML", "wpjobboard") ?></option>
                    <option value="image" <?php selected("image", $display) ?>><?php _e("Image", "wpjobboard") ?></option>
                    <option value="url" <?php selected("url", $display) ?>><?php _e("URL", "wpjobboard") ?></option>
                    <option value="email" <?php selected("email", $display) ?>><?php _e("Email", "wpjobboard") ?></option>
                    <option value="audio" <?php selected("audio", $display) ?>><?php _e("Audio", "wpjobboard") ?></option>
                    <option value="video" <?php selected("video", $display) ?>><?php _e("Video", "wpjobboard") ?></option>
                    <option value="embed" <?php selected("embed", $display) ?>><?php _e("Embed", "wpjobboard") ?></option>
                </select>

                <label style="width:48%;display:inline-block"><?php _e("Icon", "wpjobboard") ?></label>
                <div style="margin-left:0; width: 50%; text-align: right; display: inline-block"> 
                    <a href="#" class="button wpjb-ctrl-cf-icon" data-name="<?php echo esc_attr($icon) ?>"><span class="wpjb-glyhs <?php echo esc_attr($icon) ?>"></span></a>
                </div>
                
                <div class="wpjb-ctrl-cf-icon-list"></div>
            </div>
        </div>
        <?php
   }
   
   public function refreshRow($data) {
       
        $object = wpjb_get_object_from_post_id(get_the_ID());
        $k = $this->_params["field"];
        $key = "wpjb_customize_job[field][$k]";
        
       
        $request = Daq_Request::getInstance();
        $customizes = $request->post("customized");
        $customize = (json_decode($customizes));

        $c = $customize->$key;
        $data = (array)json_decode($c);

        $row = $data;
        $row["name"] = $k;
        $row["value"] = $object->$k;
       
       if(isset($row["enabled"]) && $row["enabled"] == "0") {
           return '<div class="wpjb-grid-row wpjb-row-meta-'.$row["name"].'" style="display:none"></div>';
       }
       
        ?>
        <?php if(!isset($row["enabled"]) || $row["enabled"] == "1"): ?>
        <div class="wpjb-grid-row <?php esc_attr_e("wpjb-row-meta-".$row["name"]) ?>">
            <div class="wpjb-grid-col wpjb-col-35">
                <?php echo esc_html($row["title"]); ?>
                <span class="wpjb-grid-row-icon wpjb-glyphs <?php echo esc_attr($row["icon"]) ?>"></span>
            </div>
            <div class="wpjb-grid-col wpjb-col-60 ">
                <?php //var_dump($row) ?>
                <?php if($object->doScheme($k)): ?>
                <?php else: ?>
                <?php echo wpjb_row_value($row["value"], $k, $object, $row["display"]) ?>
                <?php endif; ?>
            </div>
            <?php do_action("wpjb_template_job_meta_row_after", $row, $object) ?>
        </div>
        <?php endif; ?>
        <?php
    }
    
    public function template() { 
        
        if(self::$_iconSelectorRendered === true) {
            return;
        } 
        self::$_iconSelectorRendered = true;
        
        $icons = array("");
        $prefix = ".wpjb-icon-";
        $file = file( Wpjb_Project::getInstance()->getBaseDir() . '/public/css/wpjb-glyphs.css');

        foreach($file as $line) {
            if(stripos($line, $prefix) === 0) {
                $l = explode(":", $line);
                $icons[] = str_replace($prefix, "", $l[0]);
            }
        }
        
        ?>
        <script type="text/html" id="tmpl-wpjb-customizer-icon-select">
        <div class="wpjb-custom-menu-icon-picker-wrap">
            <input type="text" autocomplete="off" class="wpjb-category-icon-filter" placeholder="<?php _e("Filter Icons ...", "wpjobboard") ?>" />
            <ul class="wpjb-image-icon-picker">
                <?php foreach($icons as $icon): ?>
                <?php $title = ucfirst(str_replace("-", " ", $icon ) ) ?>
                <li data-name="<?php esc_html_e($icon) ?>">
                    <a href="#" class="button-secondary" title="<?php esc_html_e( $title ) ?>" data-name="<?php esc_html_e($icon) ?>">
                        <span class="wpjb-glyphs <?php esc_html_e(str_replace(".", "", $prefix).$icon) ?>"></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        </script>
    <?php }
}