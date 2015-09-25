<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Edit_Primary_Tabs_Chooser
    extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayConfigurationCategoryEditPrimaryTabsChooser');
        $this->setTemplate('M2ePro/ebay/configuration/category/chooser.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $chooserBlockData = Mage::helper('M2ePro/Data_Global')->getValue('chooser_data');

        // ---------------------------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser $chooserBlock */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setMarketplaceId($chooserBlockData['marketplace']);

        $chooserBlock->setSingleCategoryMode();
        $chooserBlock->setSingleCategoryType($chooserBlockData['type']);
        $chooserBlock->setSingleCategoryData(array(
            'mode' => $chooserBlockData['mode'],
            'value' => $chooserBlockData['value'],
            'path' => $chooserBlockData['path'],
        ));

        $this->setChild('chooser_block', $chooserBlock);
        // ---------------------------------------------------

        // ---------------------------------------------------
        $marketplaceTitle = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Marketplace', (int)$chooserBlockData['marketplace'])
            ->getTitle();

        $this->marketplaceData = array(
            'title' => $marketplaceTitle,
        );
        // ---------------------------------------------------

        //------------------------------
        $this->setChild('confirm', $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm'));
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}