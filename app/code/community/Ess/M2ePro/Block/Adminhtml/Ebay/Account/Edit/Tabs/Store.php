<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_Store extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabsStore');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/store.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'EbayAccountObj.refreshStoreCategories();',
            'class'   => 'update_ebay_store'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('refresh_ebay_store', $buttonBlock);

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Hide'),
            'onclick' => 'EbayAccountObj.ebayStoreSelectCategoryHide();',
            'class'   => 'hide_selected_category'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('hide_selected_category', $buttonBlock);
        // ---------------------------------------

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_ebay_accountStoreCategory');
        return parent::_beforeToHtml();
    }

    //########################################
}
