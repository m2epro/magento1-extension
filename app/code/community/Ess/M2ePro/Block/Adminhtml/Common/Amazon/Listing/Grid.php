<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingGrid');
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getResourceModel('M2ePro/Amazon_Listing')->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing');

        // Set global filters
        // ---------------------------------------
        $filterSellingFormatTemplate = $this->getRequest()->getParam('filter_amazon_selling_format_template');
        $filterSynchronizationTemplate = $this->getRequest()->getParam('filter_amazon_synchronization_template');

        if ($filterSellingFormatTemplate != 0) {
            $collection->addFieldToFilter(
                'second_table.template_selling_format_id', (int)$filterSellingFormatTemplate
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
            ->join(array('a'=>Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                '(`a`.`id` = `main_table`.`account_id`)',
                array('account_title'=>'title'))
            ->join(array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                '(`m`.`id` = `main_table`.`marketplace_id`)',
                array('marketplace_title'=>'title'));
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
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_ManageListings::TAB_ID_LISTING,
                'channel' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            )
        );

        $this->getMassactionBlock()->addItem('clear_logs', array(
            'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
            'url'      => $this->getUrl('*/adminhtml_listing/clearLog', array('back' => $backUrl)),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        // ---------------------------------------

        // Set remove listings action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('delete_listings', array(
            'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
            'url'      => $this->getUrl('*/adminhtml_common_amazon_listing/delete', array('back' => $backUrl)),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');
        $backUrl = $helper->makeBackUrlParam(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_ManageListings::TAB_ID_LISTING,
                'channel' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            )
        );

        $actions = array(
            'manageProducts' => array(
                'caption' => $helper->__('Manage'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_amazon_listing/view',
                    'params' => array('back' => $backUrl)
                )
            ),

            'addProductsFromProductsList' => array(
                'caption' => $helper->__('Add From Products List'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_listing_productAdd/index',
                    'params' => array(
                        'back' => $backUrl,
                        'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                        'step' => 2,
                        'source' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_SourceMode::SOURCE_LIST
                    )
                )
            ),

            'addProductsFromCategories' => array(
                'caption' => $helper->__('Add From Categories'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_listing_productAdd/index',
                    'params' => array(
                        'back' => $backUrl,
                        'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                        'step' => 2,
                        'source' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_SourceMode::SOURCE_CATEGORIES
                    )
                )
            ),

            'automaticActions' => array(
                'caption' => $helper->__('Auto Add/Remove Rules'),
                'group'   => 'products_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_amazon_listing/view',
                    'params' => array(
                        'back' => $backUrl,
                        'auto_actions' => 1
                    )
                )
            ),

            'viewLog' => array(
                'caption' => $helper->__('View Log'),
                'group'   => 'other',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_log/listing',
                    'params' => array(
                        'back' => $backUrl,
                        'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_AMAZON
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
                    'base' => '*/adminhtml_common_amazon_listing/delete',
                    'params' => array(
                        'back' => $backUrl
                    )
                )
            ),

            'editListingTitle' => array(
                'caption' => $helper->__('Title'),
                'group'   => 'edit_actions',
                'confirm' => $helper->__('Are you sure?'),
                'field'   => 'id',
                'onclick_action' => 'editListingTitle'
            ),

            'sellingSetting' => array(
                'caption' => $helper->__('Selling'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_amazon_listing/edit',
                    'params' => array(
                        'back' => $backUrl,
                        'tab' => 'selling'
                    )
                )
            ),

            'searchSetting' => array(
                'caption' => $helper->__('Search'),
                'group'   => 'edit_actions',
                'field'   => 'id',
                'url'     => array(
                    'base'   => '*/adminhtml_common_amazon_listing/edit',
                    'params' => array(
                        'back' => $backUrl,
                        'tab' => 'search'
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

        $collection->getSelect()->where('main_table.title LIKE ? OR m.title LIKE ? OR a.title LIKE ?',
                                        '%'. $value .'%');
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_listing/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_ManageListings::TAB_ID_LISTING,
                'channel' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            )
        );

        return $this->getUrl(
            '*/adminhtml_common_amazon_listing/view',
            array(
                'id' => $row->getId(),
                'back' => $backUrl
            )
        );
    }

    //########################################
}