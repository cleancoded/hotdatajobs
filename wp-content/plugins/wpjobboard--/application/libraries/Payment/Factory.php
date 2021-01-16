<?php
/**
 * Creates instance of payment object
 *
 * @author greg
 */
class Wpjb_Payment_Factory
{
    protected $_engine = array();
    
    public function __construct(array $engines = null)
    {
        foreach((array)$engines as $e) {
            $this->register($e);
        }
        
        $this->_engine = apply_filters("wpjb_payments_list", $this->_engine);
    }
    
    public function sort()
    {
        uasort($this->_engine, array($this, "_sort"));
    }
    
    protected function _sort($a, $b)
    {
        $ta = new $a;
        $tb = new $b;
        
        return strcasecmp($ta->getTitle(), $tb->getTitle());
    }
    
    /**
     * Returns payment class, identified by $engine variable
     *
     * @param string $engine
     * @param Wpjb_Model_Payment $payment
     * @return Wpjb_Payment_Abstract
     */
    public function factory(Wpjb_Model_Payment $payment)
    {
        $engine = $payment->engine;
        if(!$this->hasEngine($engine)) {
            throw new Exception("Payment engine [$engine] not registered!");
        }

        $class = $this->getEngine($engine);
        $object = new $class($payment);
        return $object;
    }
    
    public function register(Wpjb_Payment_Abstract $payment)
    {
        $this->_engine[$payment->getEngine()] = get_class($payment);
    }

    /**
     * Return all available payment engines
     *
     * @return array
     */
    public function getEngines()
    {
        return $this->_engine;
    }
    
    public function getEnabled()
    {
        $conf = get_option("wpjb_payment_method");
        $enabled = array();
        foreach($this->getEngines() as $k => $v) {
            if($k == "Credits") {
                continue;
            }
            
            if(isset($conf[$k]) && (!isset($conf[$k]["disabled"]) || $conf[$k]["disabled"]!=1)) {
                $enabled[$k] = array(
                    "title" => $conf[$k]["title"],
                    "order" => $conf[$k]["order"],
                    "class" => $v
                );
            }
        }
        
        $enabled = $this->sortByTitle($enabled);
        $enabled = $this->sortByOrder($enabled);
        
        $final = array();
        
        foreach($enabled as $k => $v) {
            $final[$k] = $v["class"];
        }
        
        return $final;
    }
    
    public function sortByTitle($list) 
    {
        uasort($list, array($this, "_byTitle"));
        return $list;
    }
    
    protected function _byTitle($a, $b)
    {
        return strcasecmp($a["title"], $b["title"]);
    }
    
    public function sortByOrder($list) 
    {
        uasort($list, array($this, "_byOrder"));
        return $list;
    }
    
    protected function _byOrder($a, $b)
    {
        $a = (int)$a["order"];
        $b = (int)$b["order"];
        
        return ($a < $b) ? -1 : 1;
    }
    
    public function hasEngine($engine) 
    {
        $engines = $this->getEngines();
        
        if(isset($engines[$engine])) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Returns payment engine
     * 
     * @param string $engine
     * @return Wpjb_Payment_Abstract
     */
    public function getEngine($engine)
    {
        return $this->_engine[$engine];
    }
}
?>
