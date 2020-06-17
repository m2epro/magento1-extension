<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('walmartListingGrid');
    }

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getResourceModel('M2ePro/Walmart_Listing')->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing');

        // Set global filters
        // ---------------------------------------
        $filterSellingFormatTemplate = $this->getRequest()->getParam('filter_walmart_selling_format_template');
        $filterDescriptionTemplate = $this->getRequest()->getParam('filter_walmart_description_template');
        $filterSynchronizationTemplate = $this->getRequest()->getParam('filter_walmart_synchronization_template');

        if ($filterSellingFormatTemplate != 0) {
            $collection->addFieldToFilter(
                'second_table.template_selling_format_id', (int)$filterSellingFormatTemplate
            );
        }

        if ($filterDescriptionTemplate != 0) {
            $collection->addFieldToFilter(
                'second_table.template_description_id', (int)$filterDescriptionTemplate
            );
        }

        if ($filterSynchronizationTemplate != 0) {
            $collection->addFieldToFilter(
                'second_table.template_synchronization_id', (int)$filterSynchronizationTemplate
            );
        }

        // ---------------------------------------

        // join marketplace and accounts
        // ---------------------------------------
        $collection->getSelect()
            ->join(
                array('a'=>Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                '(`a`.`id` = `main_table`.`account_id`)',
                array('account_title'=>'title')
            )
            ->join(
                array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                '(`m`.`id` = `main_table`.`marketplace_id`)',
                array('marketplace_title'=>'title')
            );
        // ---------------------------------------

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
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_walmart_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING
            )
        );

        $this->getMassactionBlock()->addItem(
            'clear_logs', array(
            'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
            'url'      => $this->getUrl('*/adminhtml_listing/clearLog', array('back' => $backUrl)),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );
        // ---------------------------------------

        // Set remove listings action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'delete_listings', array(
            'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
            'url'      => $this->getUrl('*/adminhtml_walmart_listing/delete', array('back' => $backUrl)),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    protected function getGroupOrder()
    {
        return array(
            'products_actions' => Mage::helper('M2ePro')->__('Products'),
            'edit_actions'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'other'            => Mage::helper('M2ePro')->__('Other'),
        );
    }

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');
        $backUrl = $helper->makeBackUrlParam(
            '*/adminhtml_walmart_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING
            )
        );

        $actions = array(

            'editListingTitle' => array(
                'caption' => $helper->__('Title'),
                'group'   => 'edit_actions',
                'confirm' => $helper->__('Are you sure?'),
                'field'   => 'id',
                'onclick_action' => 'EditListingTitleObj.openPopup'
            ),

            'editConfiguration' => array(
                'caption' => $helper->__('Configuration'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_listing/edit',
                    'params' => array('back' => $backUrl)
                )
            ),

            'manageProducts' => array(
                'caption' => $helper->__('Manage'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_listing/view',
                    'params' => array('back' => $backUrl)
                )
            ),

            'addProductsFromProductsList' => array(
                'caption' => $helper->__('Add From Products List'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_listing_productAdd/index',
                    'params' => array(
                        'back' => $backUrl,
                        'step' => 2,
                        'source' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode::SOURCE_LIST
                    )
                )
            ),

            'addProductsFromCategories' => array(
                'caption' => $helper->__('Add From Categories'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_listing_productAdd/index',
                    'params' => array(
                        'back' => $backUrl,
                        'step' => 2,
                        'source' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode::SOURCE_CATEGORIES
                    )
                )
            ),

            'automaticActions' => array(
                'caption' => $helper->__('Auto Add/Remove Rules'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_listing/view',
                    'params' => array(
                        'back' => $backUrl,
                        'auto_actions' => 1
                    )
                )
            ),

            'viewLog' => array(
                'caption' => $helper->__('Logs & Events'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_walmart_log/listing',
                    'params' => array(
                        'back' => $backUrl
                    )
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

            'deleteListing' => array(
                'caption' => $helper->__('Delete Listing'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base' => '*/adminhtml_walmart_listing/delete',
                    'params' => array(
                        'back' => $backUrl
                    )
                )
            )
        );

        return $actions;
    }

    //########################################

    public function callbackColumnSoldProducts($value, $row, $column, $isExport)
    {
        return $this->getColumnValue($value);
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<span id="listing_title_'.$row->getId().'">' .
            Mage::helper('M2ePro')->escapeHtml($value) .
            '</span>';

        /** @var $row Ess_M2ePro_Model_Listing */
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
        $marketplace = Mage::helper('M2ePro')->__('Marketplace');
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

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR m.title LIKE ? OR a.title LIKE ?',
            '%'. $value .'%'
        );
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_walmart_listing/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_walmart_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING
            )
        );

        return $this->getUrl(
            '*/adminhtml_walmart_listing/view',
            array(
                'id' => $row->getId(),
                'back' => $backUrl
            )
        );
    }

    protected function _toHtml()
    {
        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
            'adminhtml_listing/saveTitle' => Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_listing/saveTitle')
            )
        );

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Cancel' => Mage::helper('M2ePro')->__('Cancel'),
            'Save' => Mage::helper('M2ePro')->__('Save'),
            'Edit Listing Title' => Mage::helper('M2ePro')->__('Edit Listing Title'),
            )
        );

        $uniqueTitleTxt = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('The specified Title is already used for other Listing. Listing Title must be unique.')
        );

        $constants = Mage::helper('M2ePro')->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Walmart');

        $ajax = (int)$this->getRequest()->isXmlHttpRequest();

        $javascripts = <<<HTML

<script type="text/javascript">

    var init = function () {
        M2ePro.url.add({$urls});
        M2ePro.translator.add({$translations});

        WalmartListingObj = new WalmartListing();

        M2ePro.text.title_not_unique_error = '{$uniqueTitleTxt}';

        M2ePro.php.setConstants(
            {$constants},
            'Ess_M2ePro_Helper_Component'
        );

        EditListingTitleObj = new ListingEditListingTitle('{$this->getId()}');
    };

    {$ajax} ? init() : Event.observe(window, 'load', init);

</script>

HTML;

        return parent::_toHtml() . $javascripts;
    }

    //########################################
}
