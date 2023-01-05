<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    const SOURCE_LIST = 'products';
    const SOURCE_CATEGORIES = 'categories';
    const SOURCE_OTHER = 'other';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingSourceMode');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';
        $this->_mode = 'sourceMode';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
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

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_ebay_listing_productAdd/sourceMode', array(
                'wizard'           => $this->getRequest()->getParam('wizard', false),
                'listing_id'       => $this->getRequest()->getParam('listing_id'),
                'listing_creation' => $this->getRequest()->getParam('listing_creation')
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

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 1, 'back' => $backUrl));
        $this->_addButton(
            'next', array(
                'id'      => 'next',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'CommonObj.submitForm(\''.$url.'\');',
                'class'   => 'scalable next'
            )
        );
    }

    protected function _toHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
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
