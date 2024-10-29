<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Walmart_Account_Repository */
    private $walmartAccountRepository;

    public function __construct()
    {
        $this->walmartAccountRepository = Mage::getModel('M2ePro/Walmart_Account_Repository');

        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartProductType');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_productType';
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
            'label' => Mage::helper('M2ePro')->__('Refresh Walmart Data'),
            'onclick' => 'WalmartMarketplaceWithProductTypeSyncObj.updateAction()',
            'class' => 'save update_all_marketplace primary',
        ));

        $this->addButton('add', array(
            'label' => Mage::helper('M2ePro')->__('Refresh Walmart Data'),
            'onclick' => Mage::helper('M2ePro')->__('Add Product Type'),
        ));
    }

    protected function _prepareLayout()
    {
        $this->addButton(
            'add',
            array(
                'label' => Mage::helper('M2ePro')->__('Add Product Type'),
                'onclick' => sprintf(
                    "setLocation('%s')",
                    $this->getUrl('*/adminhtml_walmart_productType/edit')
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
                'walmart_marketplace_withProductType/runSynchNow' => $this->getUrl(
                    '*/adminhtml_walmart_marketplace_withProductType/runSynchNow'
                ),
                'walmart_marketplace_withProductType/synchGetExecutingInfo' => $this->getUrl(
                    '*/adminhtml_walmart_marketplace_withProductType/synchGetExecutingInfo'
                ),
            )
        );

        $storedStatuses = array();
        foreach ($this->walmartAccountRepository->getAllItems() as $account) {
            $marketplace = $account->getChildObject()
                ->getMarketplace();
            $storedStatuses[] = array(
                'marketplace_id' => $marketplace->getId(),
                'title' => $marketplace->getTitle(),
            );
        }
        $storedStatuses = Mage::helper('M2ePro')->jsonEncode($storedStatuses);

        $syncLogUrl = $this->getUrl('*/adminhtml_walmart_synchronization_log/index');
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'marketplace_sync_success_message' => Mage::helper('M2ePro')->__(
                    'Walmart Data Update was completed.'
                ),
                'marketplace_sync_error_message' => Mage::helper('M2ePro')->__(
                    'Walmart Data Update was completed with errors.'
                    . ' <a target="_blank" href="%url%">View Log</a> for the details.',
                    $syncLogUrl
                ),
                'marketplace_sync_warning_message' => Mage::helper('M2ePro')->__(
                    'Warning Data Update was completed with warnings.'
                    . ' <a target="_blank" href="%url%">View Log</a> for the details.',
                    $syncLogUrl
                ),
            )
        );

        $js = <<<JS
        <script type="text/javascript">
            Event.observe(window, 'load', () => {
                M2ePro.url.add({$urls});
                M2ePro.translator.add({$translations});
                
                const marketplaceProgress = new WalmartMarketplaceWithProductTypeSyncProgress(
                        new ProgressBar('product_type_progress_bar'),
                        new AreaWrapper('product_type_content_container')
                 );
                window.WalmartMarketplaceWithProductTypeSyncObj = new WalmartMarketplaceWithProductTypeSync(
                       marketplaceProgress,
                       $storedStatuses
                );
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