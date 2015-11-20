<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingGrid');
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getResourceModel('M2ePro/Listing')->updateStatisticColumns();
        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
        $collection->getSelect()->join(array('a'=>Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                                       '(`a`.`id` = `main_table`.`account_id`)',
                                       array('account_title'=>'title'));
        $collection->getSelect()->join(array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                                       '(`m`.`id` = `main_table`.`marketplace_id`)',
                                       array('marketplace_title'=>'title'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set clear log action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('clear_logs', array(
             'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
             'url'      => $this->getUrl('*/adminhtml_listing/clearLog',array(
                 'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/index',array(
                     'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING
                 ))
             )),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        // ---------------------------------------

        // Set remove listings action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('delete_listings', array(
             'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
             'url'      => $this->getUrl('*/adminhtml_ebay_listing/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    protected function setColumns()
    {
        $this->addColumn('items_sold_count', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'items_sold_count',
            'filter_index' => 'second_table.items_sold_count',
            'frame_callback' => array($this, 'callbackColumnSoldQTY')
        ));

        return $this;
    }

    protected function getColumnActionsItems()
    {
        $helper  = Mage::helper('M2ePro');
        $backUrl = $helper->makeBackUrlParam('*/adminhtml_ebay_listing/index',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING
        ));

        $actions = array(
            'manageProducts' => array(
                'caption' => $helper->__('Manage'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_listing/view',
                    'params' => array('id' => $this->getId(), 'back' => $backUrl)
                )
            ),

            'addProductsSourceProducts' => array(
                'caption'        => $helper->__('Add From Products List'),
                'group'          => 'products_actions',
                'field'          => 'id',
                'onclick_action' => 'EbayListingGridHandlerObj.addProductsSourceProductsAction',
            ),

            'addProductsSourceCategories' => array(
                'caption'        => $helper->__('Add From Categories'),
                'group'          => 'products_actions',
                'field'          => 'id',
                'onclick_action' => 'EbayListingGridHandlerObj.addProductsSourceCategoriesAction',
            ),

            'autoActions' => array(
                'caption' => $helper->__('Auto Add/Remove Rules'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_listing/view',
                    'params' => array('id' => $this->getId(), 'auto_actions' => 1)
                )
            ),

            'viewLogs' => array(
                'caption' => $helper->__('View Logs'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_log/listing',
                    'params' => array('id' => $this->getId())
                )
            ),

            'clearLogs' => array(
                'caption' => $helper->__('Clear Log'),
                'confirm' => $helper->__('Are you sure?'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base' => '*/adminhtml_listing/clearLog',
                    'params' => array(
                        'back' => $backUrl
                    )
                )
            ),

            'delete' => array(
                'caption' => $helper->__('Delete Listing'),
                'confirm' => $helper->__('Are you sure?'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_listing/delete',
                    'params' => array('id' => $this->getId())
                )
            ),

            'editTitle' => array(
                'caption'        => $helper->__('Title'),
                'group'          => 'edit_actions',
                'field'          => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup',
            ),

            'editSelling' => array(
                'caption' => $helper->__('Selling'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_template/editListing',
                    'params' => array(
                        'id' => $this->getId(),
                        'tab' => 'selling',
                        'back' => $backUrl
                    )
                )
            ),

            'editSynchronization' => array(
                'caption' => $helper->__('Synchronization'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_template/editListing',
                    'params' => array(
                        'id' => $this->getId(),
                        'tab' => 'synchronization',
                        'back' => $backUrl
                    )
                )
            ),

            'editPaymentAndShipping' => array(
                'caption' => $helper->__('Payment And Shipping'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_ebay_template/editListing',
                    'params' => array(
                        'id' => $this->getId(),
                        'tab' => 'general',
                        'back' => $backUrl
                    )
                )
            )
        );

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            unset($actions['autoActions']);
            unset($actions['editSynchronization']);
        }

        return $actions;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<span id="listing_title_'.$row->getId().'">' .
                    Mage::helper('M2ePro')->escapeHtml($value) .
                 '</span>';

        /* @var $row Ess_M2ePro_Model_Listing */
        $accountTitle = $row->getData('account_title');
        $marketplaceTitle = $row->getData('marketplace_title');

        $storeModel = Mage::getModel('core/store')->load($row->getStoreId());
        $storeView = $storeModel->getWebsite()->getName();
        if (strtolower($storeView) != 'admin') {
            $storeView .= ' > '.$storeModel->getGroup()->getName();
            $storeView .= ' > '.$storeModel->getName();
        } else {
            $storeView = Mage::helper('M2ePro')->__('Admin (Default Values)');
        }

        $account = Mage::helper('M2ePro')->__('Account');
        $marketplace = Mage::helper('M2ePro')->__('eBay Site');
        $store = Mage::helper('M2ePro')->__('Magento Store View');

        $value .= <<<HTML
<div>
    <span style="font-weight: bold">{$account}</span>: <span style="color: #505050">{$accountTitle}</span><br/>
    <span style="font-weight: bold">{$marketplace}</span>: <span style="color: #505050">{$marketplaceTitle}</span><br/>
    <span style="font-weight: bold">{$store}</span>: <span style="color: #505050">{$storeView}</span>
</div>
HTML;

        return $value;
    }

    public function callbackColumnSoldQTY($value, $row, $column, $isExport)
    {
        return $this->getColumnValue($value);
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_ebay_listing/view', array(
            'id' => $row->getId()
        ));
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR a.title LIKE ? OR m.title LIKE ?',
            '%'.$value.'%'
        );
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $urls = json_encode(array_merge(
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_listing'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_listing_productAdd'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_log'),
            Mage::helper('M2ePro')->getControllerActions('adminhtml_ebay_template'),
            array(
                'adminhtml_common_listing/saveTitle' => Mage::helper('adminhtml')
                    ->getUrl('M2ePro/adminhtml_common_listing/saveTitle')
            )
        ));

        $translations = json_encode(array(
            'Cancel' => Mage::helper('M2ePro')->__('Cancel'),
            'Save' => Mage::helper('M2ePro')->__('Save'),
            'Edit Listing Title' => Mage::helper('M2ePro')->__('Edit Listing Title'),
        ));

        $uniqueTitleTxt = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('The specified Title is already used for other Listing. Listing Title must be unique.'));

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay');

        $javascriptsMain = <<<HTML

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        M2ePro.url.add({$urls});
        M2ePro.translator.add({$translations});

        M2ePro.text.title_not_unique_error = '{$uniqueTitleTxt}';

        M2ePro.php.setConstants(
            {$constants},
            'Ess_M2ePro_Helper_Component'
        );

        EbayListingGridHandlerObj = new EbayListingGridHandler('{$this->getId()}');
        EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}');
    });

</script>

HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################
}