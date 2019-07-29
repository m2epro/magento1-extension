<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Add_SourceProduct
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct($attributes)
    {
        parent::__construct($attributes);

        $this->setData($attributes);

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingAddFromProductList');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_product';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__("%component_name% / Select Products",
                Mage::helper('M2ePro/Component_Walmart')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Select Products');
        }
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
            $url = $this->getUrl('*/adminhtml_walmart_listing_productAdd/index', array(
                '_current' => true,
                'step' => 1,
                'clear' => 1
            ));
        } else {
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_walmart_listing/index'
            );
        }
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));

        // ---------------------------------------
        $this->_addButton('auto_action', array(
            'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
            'onclick'   => 'ListingAutoActionHandlerObj.loadAutoActionHtml();'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'ProductGridHandlerObj.save_click(\'view\')',
            'class'     => 'scalable next'
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_walmart_listing_autoAction',
            array(
                'listing_id' => $this->getRequest()->getParam('id')
            )
        );
        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $translations = Mage::helper('M2ePro')->jsonEncode(array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
        ));

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    ListingAutoActionHandlerObj = new WalmartListingAutoActionHandler();

</script>
HTML;

        return $viewHeaderBlock->toHtml() . parent::getGridHtml() . $js;
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