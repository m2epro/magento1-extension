<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategorySpecific');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_category_specific';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_headerText = Mage::helper('M2ePro')->__('Set Category Specifics');

        $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $url = $this->getUrl('*/*/', array('step' => 2, '_current' => true));

        if ($this->_listing->getSetting('additional_data', 'source') == SourceModeBlock::SOURCE_OTHER) {
            $url = $this->getUrl('*/*/otherCategories', array('_current' => true));
        }

        $this->_addButton(
            'back',
            array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'class'   => 'back',
                'onclick' => 'setLocation(\'' . $url . '\');'
            )
        );

        $this->_addButton(
            'next',
            array(
                'id'      => 'ebay_listing_category_continue_btn',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'class'   => 'scalable next',
                'onclick' => "EbayListingCategoryProductGridObj.completeCategoriesDataStep(0, 1)"
            )
        );
    }

    //########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header',
            '',
            array('listing' => $this->_listing)
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
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
        $block->setCategoryGridJsHandler('EbayListingCategorySpecificGridObj');

        return $block->toHtml();
    }

    //########################################
}
