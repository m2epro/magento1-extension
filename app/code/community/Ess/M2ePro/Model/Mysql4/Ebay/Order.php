<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Order
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Order', 'order_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getOrdersContainingItemsFromOrder($accountId, array $items)
    {
        // Prepare item_id-transaction_id pairs for sql
        // ---------------------------------------
        $readConnection = $this->_getReadAdapter();

        $whereSql = array();
        foreach ($items as $orderItem) {
            $itemIdSql = $readConnection->quoteInto('?', $orderItem['item_id']);
            $transactionIdSql = $readConnection->quoteInto('?', $orderItem['transaction_id']);

            $whereSql[] = "(item_id = {$itemIdSql} AND transaction_id = {$transactionIdSql})";
        }
        $whereSql = implode(' OR ', $whereSql);
        // ---------------------------------------

        // Find orders which contains at least one order item from current order
        // ---------------------------------------
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection
            ->getSelect()
                ->distinct(true)
                ->join(
                    array('moi' => Mage::getResourceModel('M2ePro/Order_Item')->getMainTable()),
                    '(`moi`.`order_id` = `main_table`.`id`)',
                    array()
                )
                ->join(
                    array('meoi' => Mage::getResourceModel('M2ePro/Ebay_Order_Item')->getMainTable()),
                    '(`meoi`.`order_item_id` = `moi`.`id`)',
                    array()
                )
                ->where($whereSql)
                ->where('`main_table`.`account_id` = ?', $accountId)
                ->order(array('main_table.id ASC'));
        // ---------------------------------------

        return $collection->getItems();
    }

    //########################################

    public function getCancellationCandidatesChannelIds($accountId, DateTime $startDate, DateTime $endDate)
    {
        /** @var $collection Ess_M2ePro_Model_Mysql4_Order_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection->addFieldToFilter('account_id', $accountId);

        $collection->addFieldToFilter('payment_status', array(
            'neq' => Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED
        ));

        $collection->addFieldToFilter('purchase_create_date', array(
            'from' => $startDate->format('Y-m-d 00:00:00'),
            'to'   => $endDate->format('Y-m-d 23:59:59')
        ));

        $collection->setOrder('id', Varien_Data_Collection::SORT_ORDER_ASC);

        return $collection->getColumnValues('ebay_order_id');
    }

    //########################################
}