<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsPhp
    implements Ess_M2ePro_Block_Adminhtml_Magento_Renderer_InterfaceRenderer
{
    protected $_jsPhp = array();

    //########################################

    public function addConstants($constants, $prefix)
    {
        $this->_jsPhp[$prefix] = $constants;
        return $this;
    }

    public function addClassConstants($className)
    {
        $constants = Mage::helper('M2ePro')->getClassConstants($className);
        $this->addConstants($constants, $className);

        return $this;
    }

    //########################################

    public function render()
    {
        if (empty($this->_jsPhp)) {
            return '';
        }

        $result = array();
        foreach ($this->_jsPhp as $prefix => $constants) {
            $constants = Mage::helper('M2ePro')->jsonEncode($constants);
            $result[] = "M2ePro.php.setConstants({$constants}, '{$prefix}');";
        }

        $result = implode(PHP_EOL, $result);

        $this->_jsPhp = array();
        return $result;
    }

    //########################################
}