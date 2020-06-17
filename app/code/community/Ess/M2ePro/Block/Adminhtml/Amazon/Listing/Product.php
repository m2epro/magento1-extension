<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_product';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $headerText = Mage::helper('M2ePro')->__(
            'Add Products To Listing "%listing_title%"',
            $this->escapeHtml($listingData['title'])
        );

        $this->_headerText = $headerText;
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

        // ---------------------------------------
        if ($this->getRequest()->getParam('back') === null) {
            $url = $this->getUrl(
                '*/adminhtml_amazon_listing/categoryProduct',
                array(
                    'id'                  => $listingData['id'],
                    'save_categories'     => $this->getRequest()->getParam('save_categories', 1),
                    'selected_categories' => $this->getRequest()->getParam('selected_categories')
                )
            );
        } else {
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_amazon_listing/index'
            );
        }

        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridObj.back_click(\''.$url.'\')',
            'class'     => 'back'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductGridObj.save_click(\'view\')',
            'class'     => 'save'
            )
        );
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_product_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>'.
               '<div id="add_products_container">'.
               parent::_toHtml().
               '</div>';
    }

    //########################################
}
