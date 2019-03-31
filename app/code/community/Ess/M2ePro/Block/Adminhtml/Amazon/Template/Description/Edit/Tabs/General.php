<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateDescriptionEditTabsGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/template/description/tabs/general.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $templateModel = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // ---------------------------------------
        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForAsinCreation();
        $marketplaces = $marketplaces->toArray();
        $this->setData('marketplaces', $marketplaces['items']);
        // ---------------------------------------

        // ---------------------------------------
        $marketplaceLocked = $categoryLocked = $newAsinSwitcherLocked = false;

        if ($templateModel && $templateModel->getId()) {
            $marketplaceLocked = $templateModel->isLocked();
            $categoryLocked = $templateModel->getChildObject()->isLockedForCategoryChange();
            $newAsinSwitcherLocked = $templateModel->getChildObject()->isLockedForNewAsinCreation();
        }

        $this->setData('marketplace_locked', $marketplaceLocked);
        $this->setData('category_locked', $categoryLocked);
        $this->setData('new_asin_switcher_locked', $newAsinSwitcherLocked);
        // ---------------------------------------

        // ---------------------------------------
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $this->setData('all_attributes', $attributeHelper->getAll());
        $this->setData('general_attributes', $attributeHelper->getGeneralFromAllAttributeSets());
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}