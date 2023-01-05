<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Mode extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const MODE_SAME     = 'same';
    const MODE_CATEGORY = 'category';
    const MODE_MANUALLY = 'manually';
    const MODE_PRODUCT  = 'product';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategoryMode');
        $this->setTemplate('M2ePro/ebay/listing/category/mode.phtml');

        $this->_headerText = Mage::helper('M2ePro')->__('Set Category');

        if (!$this->getRequest()->getParam('without_back')) {
            $url = $this->getUrl(
                '*/adminhtml_ebay_listing_productAdd/deleteAll',
                array('_current' => true)
            );

            $this->_addButton(
                'back', array(
                    'label'     => Mage::helper('M2ePro')->__('Back'),
                    'class'     => 'back',
                    'onclick'   => 'setLocation(\''.$url.'\');'
                )
            );
        }

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
                'id'        => 'next',
                'label'     => Mage::helper('M2ePro')->__('Continue'),
                'class'     => 'scalable next',
                'onclick'   => "$('categories_mode_form').submit();"
            )
        );
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
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => '',
                )
            );
        $this->setChild('mode_same_remember_pop_up_confirm_button', $buttonBlock);
    }

    //########################################
}
