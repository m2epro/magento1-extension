<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonProductType');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_productType';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');
        $this->removeButton('add');

        $this->addButton('run_update_all', array(
            'label' => Mage::helper('M2ePro')->__('Refresh Amazon Data'),
            'onclick' => 'AmazonMarketplaceSyncObj.start()',
            'class' => 'save update_all_marketplace primary',
        ));

        $this->addButton(
            'add',
            array(
                'label' => Mage::helper('M2ePro')->__('Add Product Type'),
                'onclick' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl('*/adminhtml_amazon_template_productType/edit')
                ),
                'class' => 'action-primary',
                'button_class' => '',
            )
        );
    }

    protected function _prepareLayout()
    {
        $this->addButton(
            'add',
            array(
                'label' => Mage::helper('M2ePro')->__('Add Product Type'),
                'onclick' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl('*/adminhtml_amazon_productTypes/edit')
                ),
                'class' => 'add action-primary',
                'button_class' => '',
            )
        );

        return parent::_prepareLayout();
    }

    public function _toHtml()
    {
        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
                'urlForGetMarketplaces' => $this->getUrl(
                    '*/adminhtml_amazon_marketplace/getMarketplaceList'
                ),
                'urlForUpdateMarketplacesDetails' => $this->getUrl(
                    '*/adminhtml_amazon_marketplace/updateDetails'
                ),
                'urlForGetProductTypes' => $this->getUrl(
                    '*/adminhtml_amazon_marketplace/getProductTypeList'
                ),
                'urlForUpdateProductType' => $this->getUrl(
                    '*/adminhtml_amazon_marketplace/updateProductType'
                ),
                'progress_bar_el_id' => 'product_type_progress_bar'
            )
        );

        $syncLogUrl = $this->getUrl('*/adminhtml_amazon_synchronization_log/index');
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'marketplace_sync_success_message' => Mage::helper('M2ePro')->__(
                    'Amazon Data Update was completed.'
                ),
                'marketplace_sync_error_message' => Mage::helper('M2ePro')->__(
                    'Amazon Data Update was completed with errors.'
                    . ' <a target="_blank" href="%url%">View Log</a> for the details.',
                    $syncLogUrl
                ),
                'marketplace_sync_warning_message' => Mage::helper('M2ePro')->__(
                    'Amazon Data Update was completed with warnings.'
                    . ' <a target="_blank" href="%url%">View Log</a> for the details.',
                    $syncLogUrl
                ),
                'Update Marketplace details. Please wait...' =>
                    Mage::helper('M2ePro')->__(
                        'Update Marketplace details. Please wait...'
                    ),
                'Update Product Types. Please wait...'                                                 =>
                    Mage::helper('M2ePro')->__('Update Product Types. Please wait...'),
                'Update Amazon Data'                                                 =>
                    Mage::helper('M2ePro')->__('Update Amazon Data')
            )
        );


        $js = <<<JS
        <script type="text/javascript">
            Event.observe(window, 'load', () => {
                M2ePro.url.add({$urls});
                M2ePro.translator.add({$translations});
                ProgressBarObj = new ProgressBar('product_type_progress_bar');
                AmazonMarketplaceSyncObj = new AmazonMarketplaceSync(ProgressBarObj);
            });
        </script>    
JS;

        return
            '<div id="product_type_progress_bar"></div>' .
            '<div id="product_type_content_container">' .
            parent::_toHtml() .
            '</div>' .
            $js;
    }
}
