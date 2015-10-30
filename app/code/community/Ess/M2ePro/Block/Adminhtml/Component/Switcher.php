<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Switcher extends Ess_M2ePro_Block_Adminhtml_Switcher
{
    //########################################

    protected function getComponentLabel($label)
    {
        $label = trim($label);

        if (is_null($this->getData('component_mode')) ||
            ($this->getData('component_mode') != Ess_M2ePro_Helper_Component_Ebay::NICK &&
             count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) == 1)) {

            return trim(preg_replace(array('/%component%/', '/\s{2,}/'), ' ', $label));
        }

        $componentTitles = Mage::helper('M2ePro/Component')->getComponentsTitles();

        $component = '';
        if (isset($componentTitles[$this->getData('component_mode')])) {
            $component = $componentTitles[$this->getData('component_mode')];
        }

        if (strpos($label, '%component%') === false) {
            return "{$component} {$label}";
        }

        return str_replace('%component%', $component, $label);
    }

    //########################################

    public function getParamName()
    {
        if (is_null($this->getData('component_mode'))) {
            return parent::getParamName();
        }

        return $this->getData('component_mode') . ucfirst($this->paramName);
    }

    public function getSwitchUrl()
    {
        $params = array(
            '_current' => true,
            $this->getParamName() => $this->getParamPlaceHolder()
        );

        $tabId = Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::getTabIdByComponent(
            $this->getData('component_mode')
        );

        if (!is_null($tabId)) {
            $params['tab'] = $tabId;
        }

        $controllerName = $this->getData('controller_name') ? $this->getData('controller_name') : '*';

        return $this->getUrl("*/{$controllerName}/*", $params);
    }

    public function getSwitchCallback()
    {
        return 'switch' . ucfirst($this->getParamName());
    }

    //########################################
}