<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminNotices
 *
 * @author greg
 */
class Wpjb_Utility_AdminNotices 
{
    const JOBELEON_REQUIRED = "1.4.0";
    
    public static function connect()
    {
        $instance = new self();
        
        $instance->activation();
        $instance->jobeleon();
    }
    
    protected function _doNotShowScript()
    {
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            $(".wpjb-admin-notice-hide").click(function(e) {
                e.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    dataType: "json",
                    data: {
                        action: "wpjb_main_hide",
                        hide: $(this).data("hide"),
                        value: $(this).data("value")
                    },
                    success: function() {
                    }
                });
                
                $(this).closest(".wpjb-admin-notice").fadeOut("slow");
            });
        });
        </script>
        <?php
    }
    
    public function activation()
    {
        //$config = Wpjb_Project::getInstance();
        //$config->setConfigParam("activation_message_hide", "0");
        //$config->saveConfig();
        
        if(wpjb_conf("activation_message_hide", 0) == 1) {
            return;
        }
        
        add_action("admin_notices", array($this, "activationShow"));
    }
    
    public function activationShow()
    {
        $c1 = (bool)strlen(wpjb_conf("license_key"));
        $url_config = esc_attr(wpjb_admin_url("config", "edit", null, array("form"=>"license")));
        $url_gs = esc_attr("https://wpjobboard.net/kb/getting-started/");
        
        ?>
        <style type="text/css">
        .wpjb-post-activation {
            border-left-color: #46b450;
            border-left-width: 4px;
            position: inherit;
            background: url("../wp-content/plugins/wpjobboard/public/images/admin-icons/job_board_color.png") no-repeat 10px 20px;
            background-color: white;
            margin: 1em 1em 1em 0;
            padding: 5px 5px 5px 50px;
            font-size: 1.1em;
        }
        .wpjb-post-activation ol {
            margin-left: 1em;
        }
        .wpjb-post-activation p {
            font-size: 1.2em;
        }
        .wpjb-post-activation li {
            margin: 0 0 1em 0;
        }
        .wpjb-post-activation .del {
            text-decoration: line-through;
        }
        </style>
        <?php $this->_doNotShowScript() ?>
        <div class="wpjb-post-activation wpjb-admin-notice postbox">
            <p><?php _e("Thank you for using <strong>WPJobBoard</strong>! There are few things you can do to get started:", "wpjobboard") ?></p>
            <ol>
                <li class="<?php if($c1) echo "del" ?>"><?php printf(__('<a href="%s">Enter your license code</a> in order to enable automatic updates.', "wpjobboard"), $url_config) ?></li>
                <li class=""><?php printf(__('Read out <a href="%s">Getting Started</a> guide.', "wpjobboard"), $url_gs) ?></li>
            </ol>
            <p><a class="button wpjb-admin-notice-hide"  data-hide="activation_message_hide" data-value="1"><?php _e("Do not show this again", "wpjobboard") ?></a>
        </div>
        <?php
    }
    
    public function jobeleon()
    {
        $theme = wp_get_theme();
        /* @var $theme WP_Theme */
        
        if(!in_array("jobeleon", array($theme->template, $theme->stylesheet))) {
            //do this only for jobeleon theme or child theme
            return;
        }
        
        if($theme->template == "jobeleon") {
            $version = $theme->version;
        } else {
            $version = $theme->parent()->version;
        }
        
        if(version_compare($version, self::JOBELEON_REQUIRED, ">=")) {
            // show message only if version number invalid
            return;
        }
        
        $v = wpjb_conf("jobeleon_message_hide");
        if(!empty($v) && $v == $version) {
            // force hide this message
            return;
        }
        
        add_action("admin_notices", array($this, "jobeleonShow"));
    }
    
    public function jobeleonShow()
    {
        $theme = wp_get_theme();
        if($theme->template == "jobeleon") {
            $version = $theme->version;
        } else {
            $version = $theme->parent()->version;
        }
        
        ?>
        <?php $this->_doNotShowScript() ?>
        <div class="postbox wpjb-admin-notice" style="margin: 1em 1em 1em 0em; clear: both; overflow: hidden; border-left-color: #dc3232; border-left-width: 4px">
            <div class="inside">
                <div class="main">
                    <p style="font-size:1.4em">Jobeleon Update Required!</p>
                    <p style="font-size:14px">
                        Thank you for updating WPJobBoard. 
                        We noticed you are also using <strong>Jobeleon</strong> theme, the WPJB plugn you are using
                        requires Jobeleon <?php echo self::JOBELEON_REQUIRED ?>, you are using Jobeleon <?php echo $version ?>.
                    </p>
                        
                    <p style="font-size:14px">
                        No worries, you can download latest Jobeleon from <a href="http://wpjobboard.net/panel/">client panel</a>.
                        In order to upgrade just overwrite files via FTP.
                    </p>
                    
                    <p>
                        <a href="#" class="button wpjb-admin-notice-hide" data-hide="jobeleon_message_hide" data-value="<?php echo esc_attr($version) ?>">Do not show this again</a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
    
}
