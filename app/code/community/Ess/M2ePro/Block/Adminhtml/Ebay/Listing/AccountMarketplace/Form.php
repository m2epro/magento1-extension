<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AccountMarketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingAccountMarketplace');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/account_marketplace.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->setData(
            'title',
            Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getSize() == 0 ? 'Default' : ''
        );
        // ---------------------------------------

        // ---------------------------------------
        $tempMarketplaces = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->getItems();

        foreach ($tempMarketplaces as $id => $marketplace) {
            $tempMarketplaces[$id] = $marketplace->getData();
        }

        $this->setData('marketplaces',$tempMarketplaces);
        // ---------------------------------------

        $account = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getLastItem();
        if ($account->getId()) {
            $info = json_decode($account->getEbayInfo(), true);
            $this->setData(
                'marketplace_id',
                Mage::getModel('M2ePro/Marketplace')->getIdByCode($info['Site'])
            );
        }

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => 'Add',
                'onclick' => '',
                'id' => 'add_account_button',
            ));
        if ($account->getId()) {
            Mage::helper('M2ePro/View_Ebay')->isSimpleMode()    && $buttonBlock->setData('style','display: none');
            (bool)$this->getRequest()->getParam('wizard',false) && $buttonBlock->setData('style','display: none');
        }

        $this->setChild('add_account_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $sessionKey = 'ebay_listing_create';
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);

        isset($sessionData['listing_title'])  && $this->setData('title',$sessionData['listing_title']);
        isset($sessionData['account_id'])     && $this->setData('account_id',$sessionData['account_id']);
        isset($sessionData['marketplace_id']) && $this->setData('marketplace_id',$sessionData['marketplace_id']);
        isset($sessionData['store_id'])       && $this->setData('store_id',$sessionData['store_id']);
        // ---------------------------------------

        // ---------------------------------------
        $this->setChild(
            'store_switcher',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_storeSwitcher', '', array(
                    'id'=>'store_id',
                    'selected' => $this->getData('store_id'),
                    'display_default_store_mode' => 'down',
                    'required_option' => true,
                    'empty_option' => true
                )
            )
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_breadcrumb','',
            array('step' => 1)
        );

        return $breadcrumb->_toHtml() . parent::_toHtml();
    }

    //########################################
}