<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Other_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingOtherGrid');
    }

    //########################################

    protected function _prepareCollection()
    {
        $this->prepareCacheData();

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->getSelect()->group(array('account_id','marketplace_id'));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
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

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        $marketplaceTitle = Mage::helper('M2ePro/Component_Amazon')
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
        return $this->getUrl('*/adminhtml_common_amazon_listing_other/view', array(
            'account' => $row->getData('account_id'),
            'marketplace' => $row->getData('marketplace_id'),
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_ManageListings::TAB_ID_LISTING_OTHER,
                'channel' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Other::TAB_ID_AMAZON
            ))
        ));
    }

    //########################################

    protected function prepareCacheData()
    {
        $this->cacheData = array();

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array('account_id', 'marketplace_id', 'status')
        );

        /* @var $item Ess_M2ePro_Model_Listing_Other */
        foreach ($collection->getItems() as $item) {

            $accountId = $item->getAccountId();
            $marketplaceId = $item->getMarketplaceId();
            $key = $accountId . ',' . $marketplaceId;

            empty($this->cacheData[$key]) && ($this->cacheData[$key] = array(
                'total_items' => 0,
                'active_items' => 0,
                'inactive_items' => 0
            ));

            ++$this->cacheData[$key]['total_items'];

            if ($item->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                ++$this->cacheData[$key]['active_items'];
            } else {
                ++$this->cacheData[$key]['inactive_items'];
            }
        }
    }

    //########################################
}