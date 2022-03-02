<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_RemovedStores
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    /** @var array */
    protected $_removedStoresId = array();

    //########################################

    protected function getRemovedStores()
    {
        $collection = Mage::getModel('core/store')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('store_id');

        $existsStoreIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        foreach ($collection as $item) {
            $existsStoreIds[] = (int)$item->getStoreId();
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $storeRelatedColumns = Mage::helper('M2ePro/Module_Database_Structure')->getStoreRelatedColumns();

        $usedStoresIds = array();

        foreach ($storeRelatedColumns as $tableName => $columnsInfo) {
            foreach ($columnsInfo as $columnInfo) {
                $tempResult = $connection->select()
                    ->distinct()
                    ->from(
                        Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix($tableName),
                        array($columnInfo['name'])
                    )
                    ->where("{$columnInfo['name']} IS NOT NULL")
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);

                if ($columnInfo['type'] == 'int') {
                    $usedStoresIds = array_merge($usedStoresIds, $tempResult);
                    continue;
                }

                // json
                foreach ($tempResult as $itemRow) {
                    preg_match_all('/"(store|related_store)_id":"?([\d]+)"?/', $itemRow, $matches);
                    !empty($matches[2]) && $usedStoresIds = array_merge($usedStoresIds, $matches[2]);
                }
            }
        }

        $usedStoresIds = array_values(array_unique(array_map('intval', $usedStoresIds)));
        $this->_removedStoresId = array_diff($usedStoresIds, $existsStoreIds);
    }

    //########################################

    public function process()
    {
        $issues = array();
        $this->getRemovedStores();

        if (!empty($this->_removedStoresId)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Some data have nonexistent magento stores',
                $this->renderMetadata($this->_removedStoresId)
            );
        }

        return $issues;
    }

    protected function renderMetadata($data)
    {
        $removedStoreIds = implode(', ', $data);
        $repairStoresAction = Mage::helper('adminhtml')
            ->getUrl('M2ePro/adminhtml_controlPanel_tools_m2ePro_general/repairRemovedMagentoStore');

        $html = <<<HTML
<div style="margin:0 0 10px">Removed Store IDs: {$removedStoreIds}</div>
<form action="{$repairStoresAction}" method="get">
    <input name="replace_from" value="" type="text" placeholder="replace from id" required/>
    <input name="replace_to" value="" type="text" placeholder="replace to id" required />
    <button type="submit">Repair</button>
</form>
HTML;
        return $html;
    }

    public function fix($ids)
    {
        foreach ($ids as $replaceIdFrom => $replaceIdTo) {
            $this->replaceId($replaceIdFrom, $replaceIdTo);
        }
    }

    protected function replaceId($replaceIdFrom, $replaceIdTo)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $storeRelatedColumns = Mage::helper('M2ePro/Module_Database_Structure')->getStoreRelatedColumns();

        foreach ($storeRelatedColumns as $tableName => $columnsInfo) {
            foreach ($columnsInfo as $columnInfo) {
                if ($columnInfo['type'] == 'int') {
                    $connection->update(
                        Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName),
                        array($columnInfo['name'] => $replaceIdTo),
                        "`{$columnInfo['name']}` = {$replaceIdFrom}"
                    );
                    continue;
                }

                // json
                $bind = array($columnInfo['name'] => new Zend_Db_Expr(
                    "REPLACE(
                        REPLACE(
                            `{$columnInfo['name']}`,
                            'store_id\":{$replaceIdFrom}',
                            'store_id\":{$replaceIdTo}'
                        ),
                        'store_id\":\"{$replaceIdFrom}\"',
                        'store_id\":\"{$replaceIdTo}\"'
                    )"
                ));

                $connection->update(
                    Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName),
                    $bind,
                    "`{$columnInfo['name']}` LIKE '%store_id\":\"{$replaceIdFrom}\"%' OR
                     `{$columnInfo['name']}` LIKE '%store_id\": {$replaceIdFrom}%'"
                );
            }
        }
    }

    //########################################
}