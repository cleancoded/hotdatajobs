<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResumeDetail
 *
 * @author greg
 */
class Wpjb_Model_ResumeDetail extends Daq_Db_OrmAbstract
{
    const EXPERIENCE = 1;
    const EDUCATION = 2;
    
    protected $_name = "wpjb_resume_detail";
    
    protected function _init() {
        
    }
}

?>
