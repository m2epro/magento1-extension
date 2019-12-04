<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Renderer_Css
    implements Ess_M2ePro_Block_Adminhtml_Magento_Renderer_InterfaceRenderer
{
    protected $_css = array();

    //########################################

    public function add($css)
    {
        $this->_css[] = $css;
        return $this;
    }

    //########################################

    public function render()
    {
        $result = implode(PHP_EOL, $this->_css);

        $this->_css = array();
        return $result;
    }

    //########################################
}