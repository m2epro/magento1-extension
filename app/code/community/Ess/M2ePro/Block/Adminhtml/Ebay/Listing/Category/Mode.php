<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Mode extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategoryMode');
        // ---------------------------------------

        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('Set Your eBay Categories');

        $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd',array('step' => 2, '_current' => true));

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd/deleteAll',array('_current' => true));
        }

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue('ebay_listing_product_add');

        if (isset($productAddSessionData['show_settings_step'])) {
            !(bool)$productAddSessionData['show_settings_step'] &&
                $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd/deleteAll',array('_current' => true));
        } elseif (isset($listingAdditionalData['show_settings_step'])) {
            !(bool)$listingAdditionalData['show_settings_step'] &&
                $url = $this->getUrl('*/adminhtml_ebay_listing_productAdd/deleteAll',array('_current' => true));
        }

        if (!$this->getRequest()->getParam('without_back')) {
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'class'     => 'back',
                'onclick'   => 'setLocation(\''.$url.'\');'
            ));
        }

        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "$('categories_mode_form').submit();"
        ));

        $this->setTemplate('M2ePro/ebay/listing/category/mode.phtml');
    }

    //########################################

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => '',
            ));
        $this->setChild('mode_same_remember_pop_up_confirm_button', $buttonBlock);
    }

    //########################################
}