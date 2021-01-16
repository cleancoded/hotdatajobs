<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Myresume
 *
 * @author greg
 */
class Wpjb_Module_AjaxNopriv_Myresume
{
    public static function validateAction()
    {
        $request = Daq_Request::getInstance();
        $class = $request->post("form");
        
        $form = new $class();
        $result = (int)$form->isValid($request->post("input"));
        
        if(!$result) {
            
            $form_error = $form->getGlobalError();
            $form_errors = $form->getErrors();
            
            $result = -1;
            
            $json = array(
                "result" => $result,
                "order_id" => null,
                "success" => null,
                "form_error" => $form_error,
                "form_errors" => $form_errors,
            );
        } else {
            $json = array(
                "result" => "1",
                "order_id" => null,
                "success" => null,
                "form_error" => "",
                "form_errors" => "",
            );
        }
        
        echo json_encode($json);
        exit;
    }
}
