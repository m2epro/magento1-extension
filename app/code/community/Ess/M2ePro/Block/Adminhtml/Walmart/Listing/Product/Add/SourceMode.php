<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    const SOURCE_LIST = 'list';
    const SOURCE_CATEGORIES = 'categories';
    const SOURCE_OTHER = 'other';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingProductSourceMode');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_product_add';
        $this->_mode = 'sourceMode';

        $this->_headerText = Mage::helper('M2ePro')->__('Add Products');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Add Products', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add Products');
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/exitToListing',
            array('id' => $this->getRequest()->getParam('id'))
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

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 2));
        $this->_addButton(
            'next', array(
                'id'      => 'next',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'CommonObj.submitForm(\'' . $url . '\');',
                'class'   => 'scalable next'
            )
        );
    }

    protected function _toHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        return parent::_toHtml();
    }

    //########################################
}
