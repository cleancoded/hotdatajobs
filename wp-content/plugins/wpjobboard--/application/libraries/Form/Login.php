<?php

/**
 * Description of Login
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Login extends Daq_Form_Abstract
{
    public function init()
    {
        $e = $this->create("_wpjb_action", "hidden");
        $e->setValue("login");
        $this->addElement($e, "hidden");
        
        $e = $this->create("user_login", Daq_Form_Element::TYPE_TEXT);
        $e->setLabel(__("Username or Email", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");

        $e = $this->create("user_password", Daq_Form_Element::TYPE_PASSWORD);
        $e->setLabel(__("Password", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");

        $e = $this->create("remember", Daq_Form_Element::TYPE_CHECKBOX);
        $e->setLabel(__("Remember me", "wpjobboard"));
        $e->addOption(1, 1, "");
        $this->addElement($e, "default");

        $e = $this->create("redirect_to", Daq_Form_Element::TYPE_HIDDEN);
        $e->setValue("");
        $this->addElement($e, "hidden");

        apply_filters("wpjb_form_init_login", $this);
    }

    public function isValid(array $values)
    {
        $valid = parent::isValid($values);
        if(!$valid) {
            return false;
        }

        $credentials = $values;
	$user = wp_authenticate($credentials['user_login'], $credentials['user_password']);

        if ( is_wp_error($user) ) {
            if ( $user->get_error_codes() == array('empty_username', 'empty_password') ) {
                $user = new WP_Error('', '');
            }

            return $user;
	}

        if(isset($credentials['remember'])) {
            $remember = true;
        } else {
            $remember = false;
        }
         
        $secure_cookie = "";
        
	wp_set_auth_cookie($user->ID, $remember, $secure_cookie);
	do_action('wp_login', $credentials['user_login'], $user);

        return $user;
    }

}

?>