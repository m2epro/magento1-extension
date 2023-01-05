<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Manually
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategoryManually');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_category_manually';

        $this->_headerText = Mage::helper('M2ePro')->__('Set Category (Manually for each Product)');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl('*/*/', array('step' => 1, '_current' => true));
        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/exitToListing',
            array('listing_id' => $this->getRequest()->getParam('listing_id'))
        );
        $confirm =
            $this->__('Are you sure?') . '\n\n'
            . $this->__('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->_addButton(
            'exit_to_listing',
            array(
                'id' => 'exit_to_listing',
                'label' => Mage::helper('M2ePro')->__('Cancel'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'scalable'
            )
        );

        $this->_addButton(
            'next', array(
            'id'      => 'ebay_listing_category_continue_btn',
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'EbayListingCategoryProductGridObj.completeCategoriesDataStep(1, 0);'
            )
        );
    }

    //########################################

    public function getGridHtml()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
        $header = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array(
                'listing' => $listing
            )
        );

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

    //########################################

    protected function getPopupsHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_WarningPopup $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_warningPopup');
        $block->setCategoryGridJsHandler('EbayListingCategoryProductGridObj');

        return $block->toHtml();
    }

    //########################################
}
