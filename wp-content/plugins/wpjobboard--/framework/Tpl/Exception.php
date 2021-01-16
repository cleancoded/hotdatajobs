<?php

class Daq_Tpl_Exception extends Exception {

    /**
     * Path of template file with error.
     */
    protected $templateFile = '';

    /**
     * Returns path of template file with error.
     *
     * @return string
     */
    public function getTemplateFile() {
        return $this->templateFile;
    }

    /**
     * Sets path of template file with error.
     *
     * @param string $templateFile
     * @return Daq_Tpl_Exception
     */
    public function setTemplateFile($templateFile) {
        $this->templateFile = (string) $templateFile;
        return $this;
    }

}