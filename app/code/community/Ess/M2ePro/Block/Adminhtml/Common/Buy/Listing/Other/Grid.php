<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Other_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('buyListingOtherGrid');
    }

    //########################################

    protected function _prepareCollection()
    {
        $this->prepareCacheData();

        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $collection->getSelect()->group(array('account_id','marketplace_id'));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_common_buy_listing_other/view', array(
            'account' => $row->getData('account_id'),
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_ManageListings::TAB_ID_LISTING_OTHER,
                'channel' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Other::TAB_ID_BUY
            ))
        ));
    }

    //########################################

    protected function prepareCacheData()
    {
        $this->cacheData = array();

        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array('account_id', 'status')
        );

        /* @var $item Ess_M2ePro_Model_Listing_Other */
        foreach ($collection->getItems() as $item) {

            $accountId = $item->getAccountId();
            $key = $accountId;

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