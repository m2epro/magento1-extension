<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList as ProcessingList;

class Ess_M2ePro_Model_Mysql4_Walmart_Listing_Product_Action_ProcessingList
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Listing_Product_Action_ProcessingList', 'id');
    }

    // ########################################

    public function markAsRelistQtyReady($listingProductsIds)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'stage' => ProcessingList::STAGE_RELIST_QTY_READY,
            ),
            array('listing_product_id IN (?)' => $listingProductsIds)
        );

        return $this;
    }

    public function markAsRelistQtyWaitingResult($listingProductsIds, $requestPendingSingleId)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'stage' => ProcessingList::STAGE_RELIST_QTY_WAITING_RESULT,
                'relist_request_pending_single_id' => $requestPendingSingleId,
            ),
            array('listing_product_id IN (?)' => $listingProductsIds)
        );

        return $this;
    }

    // ########################################

    public function getUniqueRelistRequestPendingSingleIds()
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->distinct(true)
            ->from(
                $this->getMainTable(),
                new Zend_Db_Expr('DISTINCT `relist_request_pending_single_id`')
            )
            ->where('relist_request_pending_single_id IS NOT NULL')
            ->where('stage = ?' , ProcessingList::STAGE_RELIST_QTY_WAITING_RESULT);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    // ########################################
}