<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Edit_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingEditTabs');
        // ---------------------------------------

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('edit_form');
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'selling', array(
                'label'   => Mage::helper('M2ePro')->__('Selling Settings'),
                'title'   => Mage::helper('M2ePro')->__('Selling Settings'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_amazon_listing_create_selling_form')
                    ->setUseFormContainer(false)
                    ->toHtml(),
            )
        );

        $this->addTab(
            'search', array(
                'label'   => Mage::helper('M2ePro')->__('Search Settings'),
                'title'   => Mage::helper('M2ePro')->__('Search Settings'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_amazon_listing_create_search_form')
                    ->setUseFormContainer(false)
                    ->toHtml(),
            )
        );

        $this->setActiveTab($this->getRequest()->getParam('tab', 'selling'));

        return parent::_prepareLayout();
    }

    //########################################
}
