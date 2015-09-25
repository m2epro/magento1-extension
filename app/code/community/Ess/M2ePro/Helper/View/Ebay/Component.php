<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Ebay_Component extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getComponents());
    }

    public function getComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getComponentsTitles());
    }

    //------------------------------------------

    public function getEnabledComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getEnabledComponents());
    }

    public function getEnabledComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getEnabledComponentsTitles());
    }

    //------------------------------------------

    public function getDisabledComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getDisabledComponents());
    }

    public function getDisabledComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getDisabledComponentsTitles());
    }

    //------------------------------------------

    public function getAllowedComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getAllowedComponents());
    }

    public function getAllowedComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getAllowedComponentsTitles());
    }

    //------------------------------------------

    public function getForbiddenComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getForbiddenComponents());
    }

    public function getForbiddenComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getForbiddenComponentsTitles());
    }

    //------------------------------------------

    public function getActiveComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getActiveComponents());
    }

    public function getActiveComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getActiveComponentsTitles());
    }

    //------------------------------------------

    public function getInactiveComponents()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getInactiveComponents());
    }

    public function getInactiveComponentsTitles()
    {
        return $this->removeCommonFromComponentsArray(Mage::helper('M2ePro/Component')->getInactiveComponentsTitles());
    }

    // ########################################

    public function isSingleActiveComponent()
    {
        return count($this->getActiveComponents()) == 1;
    }

    // ########################################

    private function removeCommonFromComponentsArray($components)
    {
        if (isset($components[Ess_M2ePro_Helper_Component_Ebay::NICK])) {
            return array(
                Ess_M2ePro_Helper_Component_Ebay::NICK => $components[Ess_M2ePro_Helper_Component_Ebay::NICK]
            );
        }

        foreach ($components as $component) {
            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                return array(Ess_M2ePro_Helper_Component_Ebay::NICK);
            }
        }

        return array();
    }

    // ########################################
}