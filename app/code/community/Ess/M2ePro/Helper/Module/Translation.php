<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Translation extends Mage_Core_Helper_Abstract
{
    protected $_text;
    protected $_placeholders = array();

    protected $_values = array();
    protected $_args   = array();

    protected $_translatedText;
    protected $_processedPlaceholders = array();
    protected $_processedArgs         = array();

    //########################################

    public function translate(array $args)
    {
        $this->reset();

        $this->parseInput($args);
        $this->parsePlaceholders();

        if (empty($this->_placeholders)) {
            array_unshift($this->_args, $this->_text);
            return call_user_func_array(array($this,'__'), $this->_args);
        }

        $this->_translatedText = parent::__($this->_text);

        !empty($this->_values) && $this->replacePlaceholdersByValue();
        !empty($this->_args) && $this->replacePlaceholdersByArgs();

        $unprocessedArgs = array_diff($this->_args, $this->_processedArgs);
        if (!$unprocessedArgs) {
            return $this->_translatedText;
        }

        return vsprintf($this->_translatedText, $unprocessedArgs);
    }

    //########################################

    protected function reset()
    {
        $this->_text                  = null;
        $this->_values                = array();
        $this->_args                  = array();
        $this->_placeholders          = array();
        $this->_processedPlaceholders = array();
        $this->_processedArgs         = array();
        $this->_translatedText        = null;
    }

    // ---------------------------------------

    protected function parseInput(array $input)
    {
        $this->_text = array_shift($input);

        if (is_array(current($input))) {
            $this->_values = array_shift($input);
        }

        $this->_args = $input;
    }

    protected function parsePlaceholders()
    {
        preg_match_all('/%[\w\d]+%/', $this->_text, $placeholders);
        $this->_placeholders = array_unique($placeholders[0]);
    }

    //########################################

    protected function replacePlaceholdersByValue()
    {
        foreach ($this->_values as $placeholder=>$value) {
            $newText = str_replace('%'.$placeholder.'%', $value, $this->_translatedText, $count);

            if ($count <= 0) {
                continue;
            }

            $this->_translatedText          = $newText;
            $this->_processedPlaceholders[] = '%' . $placeholder . '%';
        }
    }

    protected function replacePlaceholdersByArgs()
    {
        $unprocessedPlaceholders = array_diff($this->_placeholders, $this->_processedPlaceholders);
        $unprocessedArgs = $this->_args;

        foreach ($unprocessedPlaceholders as $placeholder) {
            if (empty($unprocessedArgs)) {
                break;
            }

            $value = (string)array_shift($unprocessedArgs);

            $this->_translatedText = str_replace($placeholder, $value, $this->_translatedText);

            $this->_processedPlaceholders[] = $placeholder;
            $this->_processedArgs[]         = $value;
        }
    }

    //########################################
}