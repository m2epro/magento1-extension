<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $cacheData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingOtherGrid');
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

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
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

        $this->addColumn('marketplace', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Site'),
            'align'     => 'left',
            'type'      => 'text',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMarketplace')
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

        $this->addColumn('items_sold_count', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'items_sold_count',
            'filter_index' => 'second_table.items_sold_count',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnSoldQTY')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        $accountTitle = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Account',$row->getData('account_id'))
            ->getTitle();
        return Mage::helper('M2ePro')->escapeHtml($accountTitle);
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        $marketplaceTitle = Mage::helper('M2ePro/Component_Ebay')
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

    public function callbackColumnSoldQTY($value, $row, $column, $isExport)
    {
        $accountId = $row->getAccountId();
        $marketplaceId = $row->getMarketplaceId();
        $key = $accountId . ',' . $marketplaceId;

        $value = $this->cacheData[$key]['sold_qty'];

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
        return $this->getUrl('*/adminhtml_ebay_listing_other/view', array(
            'account' => $row->getData('account_id'),
            'marketplace' => $row->getData('marketplace_id'),
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING_OTHER
            ))
        ));
    }

    //########################################

    private function prepareCacheData()
    {
        $this->cacheData = array();

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array('account_id', 'marketplace_id', 'status', 'second_table.online_qty_sold')
        );

        /* @var $item Ess_M2ePro_Model_Listing_Other */
        foreach ($collection->getItems() as $item) {

            $accountId = $item->getAccountId();
            $marketplaceId = $item->getMarketplaceId();
            $key = $accountId . ',' . $marketplaceId;

            empty($this->cacheData[$key]) && ($this->cacheData[$key] = array(
                'total_items' => 0,
                'active_items' => 0,
                'inactive_items' => 0,
                'sold_qty' => 0
            ));

            ++$this->cacheData[$key]['total_items'];

            if ($item->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                ++$this->cacheData[$key]['active_items'];
            } else {
                ++$this->cacheData[$key]['inactive_items'];
            }

            $this->cacheData[$key]['sold_qty'] += $item->getData('online_qty_sold');
        }
    }

    //########################################
}