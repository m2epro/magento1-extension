<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_GENERAL       = 'general';
    const TAB_ID_LISTING_OTHER = 'listingOther';
    const TAB_ID_ORDERS        = 'orders';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartAccountEditTabs');

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            self::TAB_ID_GENERAL,
            array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_walmart_account_edit_tabs_general')->toHtml(),
            )
        );

        $this->addTab(
            self::TAB_ID_LISTING_OTHER,
            array(
                'label'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
                'title'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_walmart_account_edit_tabs_listingOther')->toHtml(),
            )
        );

        $this->addTab(
            self::TAB_ID_ORDERS,
            array(
                'label'   => Mage::helper('M2ePro')->__('Orders'),
                'title'   => Mage::helper('M2ePro')->__('Orders'),
                'content' => $this->getLayout()
                                  ->createBlock('M2ePro/adminhtml_walmart_account_edit_tabs_order')->toHtml(),
            )
        );

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_GENERAL));

        return parent::_beforeToHtml();
    }

    //########################################
}
