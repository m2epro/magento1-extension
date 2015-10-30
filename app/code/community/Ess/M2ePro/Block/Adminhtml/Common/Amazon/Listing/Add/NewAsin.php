<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_NewAsin extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingAddNewAsin');
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('New ASIN/ISBN Creation');

        $url = $this->getUrl('*/*/index', array(
            'step' => 1,
            '_current' => true
        ));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
        ));

        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "descriptionTemplateModeFormSubmit()"
        ));

        $this->setTemplate('M2ePro/common/amazon/listing/add/new_asin.phtml');
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

        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
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

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    public function getProductsIds()
    {
        return $this->getListing()->getSetting('additional_data', 'adding_new_asin_listing_products_ids');
    }

    //########################################

    public function getDescriptionTemplateMode()
    {
        $listingAdditionalData = $this->getListing()->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);

        $mode = 'same';

        $sessionMode = Mage::helper('M2ePro/Data_Session')->getValue('products_source');
        if ($sessionMode == 'category') {
            $mode = $sessionMode;
        }

        if (!empty($listingAdditionalData['new_asin_mode'])) {
            $mode = $listingAdditionalData['new_asin_mode'];
        }

        return $mode;
    }

    //########################################
}