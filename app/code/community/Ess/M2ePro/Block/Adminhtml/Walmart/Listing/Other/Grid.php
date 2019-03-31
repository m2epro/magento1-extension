<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Other_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $cacheData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingOtherGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setSaveParametersInSession(true);
        $this->setPagerVisibility(false);
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setDefaultLimit(100);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $this->prepareCacheData();

        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->getSelect()->group(array('account_id','marketplace_id'));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('account', array(
            'header'    => Mage::helper('M2ePro')->__('Account'),
            'align'     => 'left',
            'type'      => 'text',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnAccount')
        ));

        $this->addColumn('products_total_count', array(
            'header'    => Mage::helper('M2ePro')->__('Total Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_total_count',
            'filter_index' => 'main_table.products_total_count',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnTotalProducts')
        ));

        $this->addColumn('products_active_count', array(
            'header'    => Mage::helper('M2ePro')->__('Active Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_active_count',
            'filter_index' => 'main_table.products_active_count',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnListedProducts')
        ));

        $this->addColumn('products_inactive_count', array(
            'header'    => Mage::helper('M2ePro')->__('Inactive Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_inactive_count',
            'filter_index' => 'main_table.products_inactive_count',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnInactiveProducts')
        ));

        $this->addColumnAfter('marketplace', array(
            'header'    => Mage::helper('M2ePro')->__('Marketplace'),
            'align'     => 'left',
            'type'      => 'text',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMarketplace')
        ), 'account');

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        $accountTitle = Mage::helper('M2ePro/Component')
            ->getCachedUnknownObject('Account',$row->getData('account_id'))
            ->getTitle();
        return Mage::helper('M2ePro')->escapeHtml($accountTitle);
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        $marketplaceTitle = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace',$row->getData('marketplace_id'))
            ->getTitle();
        return Mage::helper('M2ePro')->escapeHtml($marketplaceTitle);
    }

    public function callbackColumnTotalProducts($value, $row, $column, $isExport)
    {
        $accountId = $row->getAccountId();
        $marketplaceId = $row->getMarketplaceId();
        $key = $accountId . ',' . $marketplaceId;

        $value = $this->cacheData[$key]['total_items'];

        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnListedProducts($value, $row, $column, $isExport)
    {
        $accountId = $row->getAccountId();
        $marketplaceId = $row->getMarketplaceId();
        $key = $accountId . ',' . $marketplaceId;

        $value = $this->cacheData[$key]['active_items'];

        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnInactiveProducts($value, $row, $column, $isExport)
    {
        $accountId = $row->getAccountId();
        $marketplaceId = $row->getMarketplaceId();
        $key = $accountId . ',' . $marketplaceId;

        $value = $this->cacheData[$key]['inactive_items'];

        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_walmart_listing_other/view', array(
            'account' => $row->getData('account_id'),
            'marketplace' => $row->getData('marketplace_id'),
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_walmart_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING_OTHER,
            ))
        ));
    }

    //########################################

    protected function prepareCacheData()
    {
        $this->cacheData = array();

        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'count' => new \Zend_Db_Expr('COUNT(id)'),
            'account_id',
            'marketplace_id',
            'status'
        ));
        $collection->getSelect()->group(array('account_id','marketplace_id','status'));

        foreach ($collection->getItems() as $item) {

            $key = $item->getData('account_id') . ',' . $item->getData('marketplace_id');

            empty($this->cacheData[$key]) && ($this->cacheData[$key] = array(
                'total_items' => 0,
                'active_items' => 0,
                'inactive_items' => 0
            ));

            if ($item->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $this->cacheData[$key]['active_items'] += (int)$item['count'];
            } else {
                $this->cacheData[$key]['inactive_items'] += (int)$item['count'];
            }
            $this->cacheData[$key]['total_items'] += (int)$item['count'];
        }
    }

    //########################################
}