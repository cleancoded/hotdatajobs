<?php
/**
 * Description of Payment
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Admin_Payment extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->view->slot("logo", "payments.png");
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Payment",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "redirectAction" => array(
                "accept" => array("payment_id"),
                "object" => "payment"
            ),
            "deleteAction" => array(
                "info" => __("Payment #%d deleted.", "wpjobboard"),
                "page" => "payment"
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Payment"
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted payments: {success}", "wpjobboard")
                ),
                "markpaid" => array(
                    "success" => __("Number of payments marked as paid: {success}", "wpjobboard")
                ),
            )
        ), "payment" );
    }
    
    public function markpaidAction() 
    {
        $id = $this->_request->get("id");
        
        $this->redirectIf($id<1, wpjb_admin_url("payment"));

        $payment = new Wpjb_Model_Payment($id);
        
        if($payment->payment_paid == $payment->payment_sum) {
            $this->_addInfo(__("Payment was already marked as paid.", "wpjobboard"));
            $this->redirect(wpjb_admin_url("payment"));
        }
        
        $payment->payment_paid = $payment->payment_sum;
        $payment->paid_at = current_time("mysql");
        $payment->external_id = __("<Manually Accepted>", "wpjobboard");
        $payment->status = 2;
        $payment->accepted();
        $payment->save();
        
        $m = __('User <strong>%1$s</strong> changed payment status to <strong>%2$s</strong>.', "wpjobboard");
        $status = wpjb_get_payment_status($payment->status);
        $payment->log(sprintf($m, wp_get_current_user()->user_login, $status["label"]));
        
        $this->_addInfo(__("Payment marked as Completed", "wpjobboard"));
        
        $this->redirect(wpjb_admin_url("payment"));
    }

    public function indexAction()
    {
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        
        $id = null;
        $this->view->payment_id = null;
        if($this->_request->get("payment_id")) {
            $id = (int)$this->_request->get("payment_id", 1);
            $this->view->payment_id = $id;
        }

        $this->view->id = $id;
        $perPage = $this->_getPerPage();

        $query = new Daq_Db_Query();
        $query = $query->select("t.*, t2.*")
            ->from("Wpjb_Model_Payment t")
            ->joinLeft("t.user t2")
            ->order("created_at DESC")
            ->limitPage($page, $perPage);

        if($id > 0) {
            $query->where("t.id = ?", $id);
            $query->orWhere("t.external_id LIKE ?", "%$id%");
        }
        $this->view->data = $query->execute();

        $query = new Daq_Db_Query();
        $total = $query->select("COUNT(*) AS total")
            ->from("Wpjb_Model_Payment t")
            ->joinLeft("t.user t2")
            ->limit(1);
        
        if($id > 0) {
            $query->where("t.id = ?", $id);
        }
        $total = $total->fetchColumn();

        $this->view->current = $page;
        $this->view->total = ceil($total/$perPage);
    }

    public function editAction()
    {
        $extract = $this->_virtual[__FUNCTION__];
        
        $form = $extract["form"];
        $info = $extract["info"];
        $error = $extract["error"];

        $id = $this->_request->getParam("id");
        
        $f = new $form($id);
        $defaults = $f->getValues();
        
        $pricing = new Wpjb_Model_Pricing($f->getObject()->pricing_id);
        
        $list = new Wpjb_List_Pricing();
        $this->view->listing = $list->getBy("id", $pricing->price_for);
        
        if($this->isPost()) {
            $isValid = $f->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo($info);
                $f->save();
                $id = $f->getId();
                
                $payment = new Wpjb_Model_Payment($id);
                $payment->log(sprintf(__("User <strong>%s</strong> updated payment.", "wpjobboard"), wp_get_current_user()->user_login));
                
                if($defaults["status"] != $payment->status) {
                    $m = __('User <strong>%1$s</strong> changed payment status to <strong>%2$s</strong>.', "wpjobboard");
                    $status = wpjb_get_payment_status($payment->status);
                    $payment->log(sprintf($m, wp_get_current_user()->user_login, $status["label"]));
                }
                if($payment->status == 2) {
                    $payment->accepted();
                }
                
                
                $f = new $form($id);
                
            } else {
                $this->_addError($error);
            }
        }

        $this->view->form = $f;
    }
    
    protected function _multiDelete($id)
    {

        try {
            $model = new Wpjb_Model_Payment($id);
            $model->delete();
            return true;
        } catch(Exception $e) {
            // log error
            return false;
        }
    }
    
    protected function _multiMarkpaid($id)
    {
        $payment = new Wpjb_Model_Payment($id);

        if($payment->payment_paid == $payment->payment_sum) {
            return false;
        }
        
        
        $payment->payment_paid = $payment->payment_sum;
        $payment->paid_at = date("Y-m-d H:i:s");
        $payment->save();
        return true;
    }

}

?>