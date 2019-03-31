<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Switcher extends Ess_M2ePro_Block_Adminhtml_Switcher
{
    //########################################

    protected function getComponentLabel($label)
    {
        $label = trim($label);

        if (is_null($this->getData('component_mode'))) {
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

    public function getSwitchCallback()
    {
        return 'switch' . ucfirst($this->getParamName());
    }

    //########################################
}