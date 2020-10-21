<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_ArchiveOldOrders extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/archive_old_orders';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 3600;

    const MAX_ENTITIES_COUNT_FOR_ONE_TIME = 1000;

    const COUNT_EXCEEDS_TRIGGER = 100000;
    const DAYS_EXCEEDS_TRIGGER  = 180;

    //########################################

    protected function performActions()
    {
        $affectedOrders = $this->getAffectedOrdersGroupedByComponent();

        foreach (Mage::helper('M2ePro/Component')->getEnabledComponents() as $component) {
            if (empty($affectedOrders[$component])) {
                continue;
            }

            $this->processComponentEntities($component, $affectedOrders[$component]);
        }

        return true;
    }

    //########################################

    protected function getAffectedOrdersGroupedByComponent()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $firstAffectedId = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('M2ePro/Order'),
                array('id')
            )
            ->order('id DESC')
            ->limit(1, self::COUNT_EXCEEDS_TRIGGER)
            ->query()->fetchColumn();

        if ($firstAffectedId === false) {
            return array();
        }

        $archiveFromDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $archiveFromDate->modify('- ' .self::DAYS_EXCEEDS_TRIGGER. ' days');

        $queryStmt = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('M2ePro/Order'),
                array('id', 'component_mode')
            )
            ->where('id <= ?', (int)$firstAffectedId)
            ->where('create_date <= ?', $archiveFromDate->format('Y-m-d H:i:s'))
            ->limit(self::MAX_ENTITIES_COUNT_FOR_ONE_TIME)
            ->query();

        $orders = array();
        while ($row = $queryStmt->fetch()) {
            $orders[$row['component_mode']][] = (int)$row['id'];
        }

        return $orders;
    }

    protected function processComponentEntities($componentName, array $componentOrdersIds)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $mainOrderTable = $dbHelper->getTableNameWithPrefix('M2ePro/Order');
        $componentOrderTable = $dbHelper->getTableNameWithPrefix('M2ePro/' .ucfirst($componentName). '_Order');

        $queryStmt = $coreResource->getConnection('core_read')->select()
            ->from(array('main_table' => $mainOrderTable))
            ->joinInner(
                array('second_table' => $componentOrderTable),
                'second_table.order_id = main_table.id'
            )
            ->where('main_table.id IN (?)', $componentOrdersIds)
            ->query();

        $insertsData = array();

        while ($orderRow = $queryStmt->fetch()) {
            $insertsData[$orderRow['id']] = array(
                'name' => 'Order',
                'origin_id' => $orderRow['id'],
                'data' => array(
                    'order_data' => $orderRow
                ),
                'create_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
            );
        }

        $mainOrderItemTable = $dbHelper->getTableNameWithPrefix('M2ePro/Order_Item');
        $componentOrderItemTable = $dbHelper->getTableNameWithPrefix('M2ePro/' .ucfirst($componentName). '_Order_Item');

        $queryStmt = $coreResource->getConnection('core_read')->select()
            ->from(array('main_table' => $mainOrderItemTable))
            ->joinInner(
                array('second_table' => $componentOrderItemTable),
                'second_table.order_item_id = main_table.id'
            )
            ->where('main_table.order_id IN (?)', $componentOrdersIds)
            ->query();

        $orderItemsIds = array();

        while ($itemRow = $queryStmt->fetch()) {
            if (!isset($insertsData[$itemRow['order_id']])) {
                continue;
            }

            $insertsData[$itemRow['order_id']]['data']['order_item_data'][$itemRow['id']] = $itemRow;
            $orderItemsIds[] = (int)$itemRow['id'];
        }

        if (empty($insertsData)) {
            return;
        }

        foreach ($insertsData as $key => &$data) {
            $data['data'] = Mage::helper('M2ePro')->jsonEncode($data['data']);
        }

        unset($data);

        $connWrite = $coreResource->getConnection('core_write');

        foreach (array_chunk($insertsData, 200) as $dataPart) {
            $connWrite->insertMultiple($dbHelper->getTableNameWithPrefix('M2ePro/ArchivedEntity'), $dataPart);
        }

        $connWrite->delete($mainOrderTable, array('id IN (?)' => $componentOrdersIds));
        $connWrite->delete($componentOrderTable, array('order_id IN (?)' => $componentOrdersIds));

        $connWrite->delete($mainOrderItemTable, array('id IN (?)' => $orderItemsIds));
        $connWrite->delete($componentOrderItemTable, array('order_item_id IN (?)' => $orderItemsIds));
    }

    //########################################
}
