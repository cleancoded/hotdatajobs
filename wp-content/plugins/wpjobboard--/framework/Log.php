<?php
/**
 * Description of Log
 *
 * @author greg
 * @package 
 */

class Daq_Log
{
    const LEVEL_ERROR = 1;
    const LEVEL_DEBUG = 2;
    
    /**
     * Path to directory containing log files
     *
     * @var string
     */
    protected $_path = null;

    /**
     * Error log file name
     *
     * @var string
     */
    protected $_errorLog = null;

    /**
     * Debug log file name
     *
     * @var string
     */
    protected $_debugLog = null;
    
    /**
     * Error log level
     *
     * @var int
     */
    protected $_level = null;

    public function __construct($path, $errorLog, $debugLog, $level = null)
    {
        $this->_path = $path;
        $this->_errorLog = $errorLog;
        $this->_debugLog = $debugLog;
        $this->_level = $level;
    }
    
    public function error($err)
    {
        if($this->_level != self::LEVEL_ERROR) {
            return;
        }
        
        $file = $this->_path."/".$this->_errorLog;
        
        if(!is_dir($this->_path)) {
            wp_mkdir_p($this->_path);
            touch($file);
        }
        
        if(is_writable($file)) {
            file_put_contents($file, date("[Y-m-d H:i:s] ").print_r($err, true).PHP_EOL, FILE_APPEND);
        }
    }
}

?>