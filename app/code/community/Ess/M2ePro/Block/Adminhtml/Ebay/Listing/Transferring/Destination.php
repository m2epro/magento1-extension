<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing getListing()
 */
class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Destination extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct();
        $this->addData($args);

        $this->setId('ebayListingTransferringDestination');
        $this->setTemplate('M2ePro/ebay/listing/transferring/destination.phtml');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Account_Collection
     */
    public function getAccounts()
    {
        $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accounts->setOrder('title', 'ASC');

        return $accounts;
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    public function getMarketplaces()
    {
        $marketplaces = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
        $marketplaces->setOrder('sorder', 'ASC');
        $marketplaces->setOrder('title', 'ASC');

        return $marketplaces;
    }

    //----------------------------------------

    /**
     * @return bool|int|Mage_Core_Model_Store|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->getListing()->getStoreId());
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    <<<HTML
The Sell on Another Marketplace feature allows you to list products on multiple eBay marketplaces.
To do it, you have to choose From and To Accounts, Marketplaces, Store Views and Listings.
Click <a href="%url%" target="_blank">here</a> to learn more detailed information.
HTML
                , Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'sell-on-another-ebay-marketplace')),
                'style'   => 'margin-top: 15px;',
                'title'   => Mage::helper('M2ePro')->__('Sell on Another Marketplace')
            )
        );

        $parentHtml = parent::_toHtml();

        return <<<HTML
{$helpBlock->toHtml()}
<div class="grid">{$parentHtml}</div>
HTML;
    }

    protected function _beforeToHtml()
    {
        $data = array(
            'id'      => 'continue_button_destination',
            'onclick' => 'EbayListingTransferringObj.popupContinue()',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);

        $storeSwitcherBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_storeSwitcher')
            ->setData('id', 'to_store_id');

        $this->setChild('store_switcher', $storeSwitcherBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}
