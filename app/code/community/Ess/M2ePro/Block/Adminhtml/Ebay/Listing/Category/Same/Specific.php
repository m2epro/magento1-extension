<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Same_Specific
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategorySameSpecific');
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('eBay Same Categories');

        $this->setTemplate('M2ePro/ebay/listing/category/same/specific.phtml');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/*', array('_current' => true, 'step' => 2)) . '\');'
        ));

        $saveUrl = $this->getUrl('*/*/*', array(
            'step' => 3,
            '_current' => true
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "EbayListingCategorySpecificHandlerObj.submitData('{$saveUrl}');"
        ));
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

        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );
        $this->setChild('view_header', $viewHeaderBlock);
        // ---------------------------------------

        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $categoryMode = $this->getData('category_mode');
        $categoryValue = $this->getData('category_value');
        $internalData = $this->getData('internal_data');
        $specifics = $this->getData('specifics');

        $specificBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specificBlock->setMarketplaceId($listingData['marketplace_id']);
        $specificBlock->setCategoryMode($categoryMode);
        $specificBlock->setCategoryValue($categoryValue);

        if (!empty($internalData)) {
            $specificBlock->setInternalData($internalData);
        }

        if (!empty($specifics)) {
            $specificBlock->setSelectedSpecifics($specifics);
        }

        $this->setChild('category_specific', $specificBlock);
        // ---------------------------------------

        // ---------------------------------------
        if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $this->_selectedCategoryPath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $categoryValue, $listingData['marketplace_id']
            );
        } else {
            $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($categoryValue);
            $this->_selectedCategoryPath = Mage::helper('M2ePro')->__('Magento Attribute') . ' > ' . $attributeLabel;
        }
        // ---------------------------------------
    }

    //########################################
}