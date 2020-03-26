<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Renderer_Js
    implements Ess_M2ePro_Block_Adminhtml_Magento_Renderer_InterfaceRenderer
{
    protected $_js = array();

    //########################################

    public function add($script, $sOrder = 1)
    {
        $this->_js[(string)$sOrder][] = $script;
        return $this;
    }

    public function addOnReadyJs($script, $sOrder = 1)
    {
        $this->_js[(string)$sOrder][] = /** @lang JavaScript */ <<<JS
Event.observe(window, 'load', function() {
    {$script}
});
JS;
        return $this;
    }

    //########################################

    public function render()
    {
        if (empty($this->_js)) {
            return '';
        }

        ksort($this->_js);

        $result = '';
        foreach ($this->_js as $orderIndex => $jsS) {
            $result .= implode(PHP_EOL, array_values($jsS)) . PHP_EOL;
        }

        $this->_js = array();
        return $result;
    }

    //########################################
}