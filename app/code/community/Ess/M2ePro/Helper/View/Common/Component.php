<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Common_Component extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getComponents());
    }

    public function getComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getComponentsTitles());
    }

    // ---------------------------------------

    public function getEnabledComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getEnabledComponents());
    }

    public function getEnabledComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getEnabledComponentsTitles());
    }

    // ---------------------------------------

    public function getDisabledComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getDisabledComponents());
    }

    public function getDisabledComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getDisabledComponentsTitles());
    }

    // ---------------------------------------

    public function getAllowedComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getAllowedComponents());
    }

    public function getAllowedComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getAllowedComponentsTitles());
    }

    // ---------------------------------------

    public function getForbiddenComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getForbiddenComponents());
    }

    public function getForbiddenComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getForbiddenComponentsTitles());
    }

    // ---------------------------------------

    public function getActiveComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getActiveComponents());
    }

    public function getActiveComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getActiveComponentsTitles());
    }

    // ---------------------------------------

    public function getInactiveComponents()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getInactiveComponents());
    }

    public function getInactiveComponentsTitles()
    {
        return $this->removeEbayFromComponentsArray(Mage::helper('M2ePro/Component')->getInactiveComponentsTitles());
    }

    //########################################

    public function isSingleActiveComponent()
    {
        return count($this->getActiveComponents()) == 1;
    }

    //########################################

    public function isAmazonDefault()
    {
        return $this->getDefaultComponent() == Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    public function isBuyDefault()
    {
        return $this->getDefaultComponent() == Ess_M2ePro_Helper_Component_Buy::NICK;
    }

    // ---------------------------------------

    public function isRakutenDefault()
    {
        return in_array($this->getDefaultComponent(), Mage::helper('M2ePro/Component')->getRakutenActiveComponents());
    }

    // ---------------------------------------

    public function getDefaultComponent()
    {
        $defaultComponent = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/common/component/', 'default'
        );
        return in_array($defaultComponent, $this->getActiveComponents())
            ? $defaultComponent : Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################

    private function removeEbayFromComponentsArray($components)
    {
        if (!array_key_exists(0, $components)) {
            unset($components[Ess_M2ePro_Helper_Component_Ebay::NICK]);
            return $components;
        }

        $resultComponents = array();
        foreach ($components as $component) {
            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                continue;
            }
            $resultComponents[] = $component;
        }

        return $resultComponents;
    }

    //########################################
}