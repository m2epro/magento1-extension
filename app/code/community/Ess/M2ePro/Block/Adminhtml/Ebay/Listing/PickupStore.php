<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingPickupStore'.$this->_listing->getId());
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_pickupStore';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        $isExistsPickupStores = Mage::getModel('M2ePro/Ebay_Account_PickupStore')->getCollection()
            ->addFieldToFilter('account_id', $this->_listing->getAccountId())
            ->addFieldToFilter('marketplace_id', $this->_listing->getMarketplaceId())
            ->getSize();

        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')
            ->__('In-Store Pickup Management "%s%"', $this->_listing->getTitle());
        // ---------------------------------------

        // ---------------------------------------
        $backUrl = $this->getUrl(
            '*/adminhtml_ebay_listing/view', array(
            'id' => $this->_listing->getId()
            )
        );
        $this->_addButton(
            'back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $backUrl .'\')',
            'class'     => 'back',
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_ebay_accountPickupStore/new');
        $currentUrl = $this->getUrl('*/*/*', array('_current' => true));
        $callback = 'var newPickupStore = window.open(\'' . $url . '\',\'_blank\');';

        if (!$isExistsPickupStores) {
            $callback .= 'var tmpInterval = setInterval(function() {
                          if (newPickupStore.closed) {
                              clearInterval(tmpInterval);
                              setLocation(\''.$currentUrl.'\');
                          }
                      }, 300);';
            $this->_addButton(
                'create_new_store', array(
                'label'   => Mage::helper('M2ePro')->__('Create New Store'),
                'onclick' => $callback,
                'class'   => 'add'
                )
            );
        } else {
            $locale = Mage::app()->getLocale()->getLocaleCode();
            $filter = array(
                'marketplace_id' => $this->_listing->getMarketplaceId(),
                'create_date[locale]' => $locale,
                'update_date[locale]' => $locale
            );
            $myStoresUrl = $this->getUrl(
                '*/adminhtml_ebay_accountPickupStore', array(
                'filter' => base64_encode(http_build_query($filter))
                )
            );
            $this->_addButton(
                'my_stores', array(
                'label' => Mage::helper('M2ePro')->__('My Stores'),
                'class' => 'scalable button_link',
                'onclick' => 'window.open(\''.$myStoresUrl.'\',\'_blank\');'
                )
            );
            $this->_addButton(
                'add_products_to_stores', array(
                    'label'     => Mage::helper('M2ePro')->__('Assign Products to Stores'),
                    'onclick'   => 'EbayListingPickupStoreGridObj.pickupStoreStepProducts('
                    .$this->_listing->getId() . ')',
                    'class'     => 'add'
                )
            );
        }

        // ---------------------------------------
    }

    //########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $html = '';

        // ---------------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_help');
        $html .= $helpBlock->toHtml();
        // ---------------------------------------

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => Mage::helper('M2ePro/Data_Global')->getValue('temp_data'))
        );
        $html .= $viewHeaderBlock->toHtml();
        // ---------------------------------------

        return $html . parent::getGridHtml();
    }

    //########################################
}
