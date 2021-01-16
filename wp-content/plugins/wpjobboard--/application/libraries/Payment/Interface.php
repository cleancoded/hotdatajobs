<?php

/**
 * WPJobBoard Payment Interface
 *
 * @author Grzegorz Winiarski
 */
interface Wpjb_Payment_Interface {

    /**
     * Payment object constructor.
     *
     * $data passed to the constructor is an object that uniquely
     * identifies transaction
     *
     * @param Wpjb_Model_Payment $data
     */
    public function __construct(Wpjb_Model_Payment $data = null);

    /**
     * Returns payment engine
     *
     * Short string identyfying payment class. For example if your payment class
     * name is Wpjb_Payment_SuperPayments. This method should return SuperPayments
     *
     * @return string
     */
    public function getEngine();

    /**
     * Return payment title
     *
     * Short payment description, should not containg HTML, just plain text
     *
     * @return string
     */
    public function getTitle();

    /**
     * Renders payment form
     *
     * Returns payment form HTML.
     *
     * return string
     */
    public function render();

    /**
     * Process transaction
     *
     * Usually transaction data is returned by $_POST. The $_POST data is passed
     * to this function as $data. You can use it to verify and handle
     * transaction.
     *
     * In case of error it is recommended to throw some kind of exception. It will
     * cancel processing transaction.
     *
     * @param array $post Equal to $_POST
     * @param array $get Equal to $_GET
     * @return array
     */
    public function processTransaction(array $post, array $get);
    
    /**
     * Returns $this->_data
     * 
     * @return Wpjb_Model_Payment
     */
    public function getObject();
}
?>
