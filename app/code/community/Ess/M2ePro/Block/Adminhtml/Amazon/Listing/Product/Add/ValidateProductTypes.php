<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_ValidateProductTypes
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var  Ess_M2ePro_Model_Listing */
    protected $_listing;

    public function __construct()
    {
        parent::__construct();

        $this->setId('validateProductTypes');
        $this->_blockGroup = 'M2ePro';
        $this->_headerText = Mage::helper('M2ePro')->__("Product Type Data Validation");
        $this->_controller = 'adminhtml_amazon_listing_product_add_validateProductTypes';

        $this->initToolbarButtons();
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $progressBarHtml = sprintf('<div id="%s"></div>', 'product_type_validation_progress_bar');

        $javascript = <<<HTML
<script type="text/javascript">
if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    Event.observe(window, 'focus', function() {

         if (
                LocalStorageObj.get('is_need_revalidate_product_types')
                && typeof window['ProductTypeValidatorGridObj'] !== 'undefined'
            ) {
                LocalStorageObj.remove('is_need_revalidate_product_types');
                window['ProductTypeValidatorGridObj'].validateAll();
            }
    });

</script>
HTML;

        return $javascript . $progressBarHtml . $viewHeaderBlock->toHtml()
            . parent::getGridHtml();
    }

    private function initToolbarButtons()
    {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/*/index', array(
                'step'     => 4,
                '_current' => true
            )
        );
        $this->addButton('back',
            array(
                'label' => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'back',
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/exitToListing',
            array('id' => $this->getRequest()->getParam('id'))
        );

        $confirm =
           Mage::helper('M2ePro')->__('Are you sure?')
            . Mage::helper('M2ePro')->__('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->addButton(
            'exit_to_listing',
            array(
                'label' => Mage::helper('M2ePro')->__('Cancel'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'action-primary',
            )
        );

        $url = $this->getUrl('*/*/index',
            array(
                'id' => $this->getRequest()->getParam('id'),
                'step' => 6,
            )
        );
        $confirm = Mage::helper('M2ePro')->__('Are you sure?');

        $this->addButton('add_products_search_asin_continue',
            array(
                'label' => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'action-primary forward',
            )
        );
    }
}
