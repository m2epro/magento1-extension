<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Amazon_Component extends Mage_Core_Helper_Abstract
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

    private function removeEbayFromComponentsArray($components)
    {
        $resultComponents = array();
        foreach ($components as $key => $value) {
            if (Ess_M2ePro_Helper_Component_Amazon::NICK === $key) {
                $resultComponents[] = $key;
            } elseif (Ess_M2ePro_Helper_Component_Amazon::NICK === $value) {
                $resultComponents[] = $value;
            }
        }

        return $resultComponents;
    }

    //########################################
}