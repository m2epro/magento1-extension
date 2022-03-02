<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_BrokenTables
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    /**@var array */
    protected $_brokenTables = array();

    //########################################

    public function process()
    {
        $issues = array();
        $this->getBrokenTables();

        if (!empty($this->_brokenTables)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Has broken data in table',
                $this->renderMetadata($this->_brokenTables)
            );
        }

        return $issues;
    }

    //########################################

    public function getBrokenRecordsInfo($table, $returnOnlyCount = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $allTables = Mage::helper('M2ePro/Module_Database_Structure')->getHorizontalTables();

        $result = $returnOnlyCount ? 0 : array();

        foreach ($allTables as $parentTable => $childTables) {
            foreach ($childTables as $component => $childTable) {
                if (!in_array($table, array($parentTable, $childTable))) {
                    continue;
                }

                $parentTablePrefix = Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix($parentTable);
                $childTablePrefix = Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix($childTable);

                $parentIdColumn = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($parentTable);
                $childIdColumn = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($childTable);

                if ($table == $parentTable) {
                    $stmtQuery = $connRead->select()
                        ->from(
                            array('parent' => $parentTablePrefix),
                            $returnOnlyCount ? new Zend_Db_Expr('count(*) as `count_total`')
                                : array('id' => $parentIdColumn)
                        )
                        ->joinLeft(
                            array('child' => $childTablePrefix),
                            '`parent`.`' . $parentIdColumn . '` = `child`.`' . $childIdColumn . '`',
                            array()
                        )
                        ->where(
                            '`parent`.`component_mode` = \'' . $component . '\' OR
                                (`parent`.`component_mode` NOT IN (?) OR `parent`.`component_mode` IS NULL)',
                            Mage::helper('M2ePro/Component')->getComponents()
                        )
                        ->where('`child`.`' . $childIdColumn . '` IS NULL')
                        ->query();
                } else if ($table == $childTable) {
                    $stmtQuery = $connRead->select()
                        ->from(
                            array('child' => $childTablePrefix),
                            $returnOnlyCount ? new \Zend_Db_Expr('count(*) as `count_total`')
                                : array('id' => $childIdColumn)
                        )
                        ->joinLeft(
                            array('parent' => $parentTablePrefix),
                            "`child`.`{$childIdColumn}` = `parent`.`{$parentIdColumn}`",
                            array()
                        )
                        ->where('`parent`.`' . $parentIdColumn . '` IS NULL')
                        ->query();
                }

                if ($returnOnlyCount) {
                    $row = $stmtQuery->fetch();
                    $result += (int)$row['count_total'];
                } else {
                    while ($row = $stmtQuery->fetch()) {
                        $id = (int)$row['id'];
                        $result[$id] = $id;
                    }
                }
            }
        }

        if (!$returnOnlyCount) {
            $result = array_values($result);
        }

        return $result;
    }

   //########################################

    protected function renderMetadata($data)
    {
        $currentUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/deleteBrokenData');
        $infoUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/showBrokenTableIds');

        $html = <<<HTML
        <form method="GET" action="{$currentUrl}">
            <input type="hidden" name="action" value="repair" />
            <table style="width: 100%">
<tr>
    <td><div style="height:10px;"></div></td>
</tr>
<tr>
    <th style="width: 400px">Table</th>
    <th style="width: 50px">Count</th>
    <th style="width: 50px"></th>
</tr>
HTML;
            foreach ($data as $tableName => $brokenItemsCount) {
                $html .= <<<HTML
<tr>
    <td>
        <a href="{$infoUrl}?table[]={$tableName}"
           target="_blank" title="Show Ids" style="text-decoration:none;">{$tableName}</a>
    </td>
    <td>
        {$brokenItemsCount}
    </td>
    <td>
        <input type="checkbox" name="table[]" value="{$tableName}" />
    </td>
HTML;
            }

        $html .= <<<HTML
            </table>
            <button type="button" onclick="ControlPanelInspectionObj.removeRow(this)">Delete checked</button>
        </form>
HTML;
        return $html;
    }

    //########################################

    protected function getBrokenTables()
    {
        $horizontalTables = Mage::helper('M2ePro/Module_Database_Structure')->getHorizontalTables();

        foreach ($horizontalTables as $parentTable => $childrenTables) {
            if ($brokenItemsCount = $this->getBrokenRecordsInfo($parentTable, true)) {
                $this->_brokenTables[$parentTable] = $brokenItemsCount;
            }

            foreach ($childrenTables as $childrenTable) {
                if ($brokenItemsCount = $this->getBrokenRecordsInfo($childrenTable, true)) {
                    $this->_brokenTables[$childrenTable] = $brokenItemsCount;
                }
            }
        }
    }

    //########################################

    public function fix($tables)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($tables as $table) {
            $brokenIds = $this->getBrokenRecordsInfo($table);
            if (empty($brokenIds)) {
                continue;
            }

            $brokenIds = array_slice($brokenIds, 0, 50000);

            $tableWithPrefix = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($table);
            $idColumnName = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($table);

            foreach (array_chunk($brokenIds, 1000) as $brokenIdsPart) {
                if (empty($brokenIdsPart)) {
                    continue;
                }

                $connWrite->delete(
                    $tableWithPrefix,
                    '`' . $idColumnName . '` IN (' . implode(',', $brokenIdsPart) . ')'
                );
            }
        }
    }

    //########################################
}