<?php

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Product
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_category_product';
        //------------------------------

        // Set header text
        //------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('Set eBay Category for Product(s)');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/*/',array('step' => 1, '_current' => true));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('next', array(
            'class' => 'next',
            'label' => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'EbayListingCategoryProductGridHandlerObj.nextStep();'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        //------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $header = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array(
                'listing' => $listing
            )
        );
        //------------------------------

        return $header->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $parentHtml = parent::_toHtml();

        $popupsHtml = $this->getPopupsHtml();

        return <<<HTML
<div id="products_progress_bar"></div>
<div id="products_container">{$parentHtml}</div>
<div style="display: none">{$popupsHtml}</div>
HTML;
    }

    // ########################################

    private function getPopupsHtml()
    {
        $html = '';

        $html .= $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_category_product_warningPopup')
            ->toHtml();

        return $html;
    }

    // ########################################
}