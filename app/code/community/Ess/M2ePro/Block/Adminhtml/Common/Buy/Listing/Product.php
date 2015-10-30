<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_listing_product';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $headerText = Mage::helper('M2ePro')->__(
                'Add Products To %component_name% Listing "%listing_title%"',
                Mage::helper('M2ePro/Component_Buy')->getTitle(),
                $this->escapeHtml($listingData['title'])
            );
        } else {
            $headerText = Mage::helper('M2ePro')->__('Add Products To Listing "%listing_title%"',
                $this->escapeHtml($listingData['title'])
            );
        }

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
        if (is_null($this->getRequest()->getParam('back'))) {
            $url = $this->getUrl(
                '*/adminhtml_common_buy_listing/categoryProduct',
                array(
                    'id'                  => $listingData['id'],
                    'save_categories'     => $this->getRequest()->getParam('save_categories', 1),
                    'selected_categories' => $this->getRequest()->getParam('selected_categories')
                )
            );
        } else {
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_common_listing/index',
                array(
                    'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY
                )
            );
        }
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridHandlerObj.back_click(\''.$url.'\')',
            'class'     => 'back'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductGridHandlerObj.save_click(\'view\')',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_product_help');
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
