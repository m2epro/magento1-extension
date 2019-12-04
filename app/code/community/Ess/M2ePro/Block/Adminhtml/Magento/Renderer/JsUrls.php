<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsUrls
    implements Ess_M2ePro_Block_Adminhtml_Magento_Renderer_InterfaceRenderer
{
    protected $_jsUrls = array();

    //########################################

    public function add($url, $alias = null)
    {
        if ($alias === null) {
            $alias = $url;
        }

        $this->_jsUrls[$alias] = $url;
        return $this;
    }

    public function addControllerActions($controllerName)
    {
        $this->addUrls(Mage::helper('M2ePro')->getControllerActions($controllerName));
        return $this;
    }

    public function addUrls(array $urls)
    {
        $this->_jsUrls = array_merge($this->_jsUrls, $urls);
        return $this;
    }

    //########################################

    public function render()
    {
        if (empty($this->_jsUrls)) {
            return '';
        }

        $urls = Mage::helper('M2ePro')->jsonEncode($this->_jsUrls);
        $result = "M2ePro.url.add({$urls});";

        $this->_jsUrls = array();
        return $result;
    }

    //########################################
}