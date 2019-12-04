<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Magento_Configuration_Init extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return $this->disableAllConfig();
        }

        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            return $this->moduleDisabledConfig();
        }

        if (!Mage::helper('M2ePro/Module')->isReadyToWork()) {
            return $this->disablePartialConfig();
        }
    }

    // ---------------------------------------

    protected function disableAllConfig()
    {
        /** @var Varien_Simplexml_Config $config */
        $config = $this->getEvent()->getData('config');
        $tab = $config->getNode('tabs/m2epro');

        if ($tab && $tab instanceof SimpleXMLElement) {
            $dom = dom_import_simplexml($tab);
            $dom->parentNode->removeChild($dom);
        }
    }

    protected function moduleDisabledConfig()
    {
        /** @var Varien_Simplexml_Config $config */
        $config = $this->getEvent()->getData('config');
        $sections = $config->getXpath('//sections/*[@module="M2ePro"]');

        if (!$sections) {
            return;
        }

        foreach ($sections as $section) {
            if ($section->tab != 'm2epro') {
                continue;
            }

            if (strtolower(trim($section->label)) == 'advanced') {
                continue;
            }

            $dom = dom_import_simplexml($section);
            $dom->parentNode->removeChild($dom);
        }
    }

    protected function disablePartialConfig()
    {
        /** @var Varien_Simplexml_Config $config */
        $config = $this->getEvent()->getData('config');
        $sections = $config->getXpath('//sections/*[@module="M2ePro"]');

        if (!$sections) {
            return;
        }

        foreach ($sections as $section) {
            if ($section->tab != 'm2epro') {
                continue;
            }

            if ($this->isSectionAllowed($section->label)) {
                continue;
            }

            $dom = dom_import_simplexml($section);
            $dom->parentNode->removeChild($dom);
        }
    }

    protected function isSectionAllowed($sectionName)
    {
        $sectionName = strtolower(trim($sectionName));
        if (in_array($sectionName, array('channels', 'advanced'))) {
            return true;
        }

        if ($sectionName == 'billing info') {
            return Mage::helper('M2ePro/Module_License')->getKey();
        }

        return false;
    }

    //########################################
}