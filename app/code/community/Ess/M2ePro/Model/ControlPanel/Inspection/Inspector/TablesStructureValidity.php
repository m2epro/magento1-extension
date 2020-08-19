<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_TablesStructureValidity
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    const TABLE_MISSING = 'table_missing';

    const COLUMN_MISSING   = 'column_missing';
    const COLUMN_REDUNDANT = 'column_redundant';
    const COLUMN_DIFFERENT = 'column_different';

    const FIX_INDEX   = 'index';
    const FIX_COLUMN  = 'properties';
    const DROP_COLUMN = 'drop';
    const CRETE_TABLE = 'create_table';

    //########################################

    public function getTitle()
    {
        return 'Tables structure validity';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_STRUCTURE;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();

        try {
            $responseData = $this->getDiff();
        } catch (Exception $exception) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                $exception->getMessage()
            );

            return $issues;
        }

        if (!isset($responseData['diff'])) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'No info for this M2E Pro version.'
            );
        }

        if (!empty($responseData['diff'])) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Wrong tables structure',
                $this->renderMetadata($responseData)
            );
        }

        return $issues;
    }

    //########################################

    protected function getDiff()
    {
        $tablesInfo = Mage::helper('M2ePro/Module_Database_Structure')->getTablesInfo();

        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'tables', 'get', 'diff',
            array('tables_info' => Mage::helper('M2ePro/Data')->jsonEncode($tablesInfo))
        );

        $dispatcherObject->process($connectorObj);

        return $connectorObj->getResponseData();
    }

    //########################################

    protected function renderMetadata($data)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $currentUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_controlPanel_tools_m2ePro_install/fixColumn');
        $html = <<<HTML
    <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 400px">Table</th>
        <th>Problem</th>
        <th style="width: 300px">Info</th>
    </tr>
HTML;

        foreach ($data['diff'] as $tableName => $checkResult) {
            foreach ($checkResult as $resultRow) {
                $additionalInfo = '';

                if (!isset($resultRow['info'])) {
                    continue;
                }

                $resultInfo = $resultRow['info'];
                $diffData = isset($resultInfo['diff_data']) ? $resultInfo['diff_data'] : array();

                if (isset($resultInfo['diff_data'])) {
                    foreach ($resultInfo['diff_data'] as $diffCode => $diffValue) {
                        $additionalInfo .= "<b>{$diffCode}</b>: '{$diffValue}'. ";
                        $additionalInfo .= "<b>original:</b> '{$resultInfo['original_data'][$diffCode]}'.";
                        $additionalInfo .= "<br/>";
                    }
                }

                $columnInfo['table_name'] = $tableName;
                $columnInfo['column_info'] = $resultInfo['original_data'];

                if ($resultRow['problem'] === self::TABLE_MISSING) {
                    $columnInfo['repair_mode'] = self::CRETE_TABLE;
                } elseif ($resultRow['problem'] === self::COLUMN_MISSING) {
                    $columnInfo['repair_mode'] = self::FIX_COLUMN;
                } elseif ($resultRow['problem'] === self::COLUMN_REDUNDANT) {
                    $columnInfo['repair_mode'] = self::DROP_COLUMN;
                    $columnInfo['column_info'] = $resultInfo['current_data'];
                } elseif (isset($diffData['key'])) {
                    $columnInfo['repair_mode'] = self::FIX_INDEX;
                } elseif ($resultRow['problem'] === self::COLUMN_DIFFERENT) {
                    $columnInfo['repair_mode'] = self::FIX_COLUMN;
                }

                $repairInfo = Mage::helper('M2ePro')->jsonEncode($columnInfo);
                $input = "<input type='checkbox' name='repair_info[]' value='" . $repairInfo . "'>";
                $html .= <<<HTML
<tr>
    <td>{$input} {$tableName}</td>
    <td>{$resultRow['message']}</td>
    <td>{$additionalInfo}</td>
</tr>
HTML;
            }
        }

        $html .= '<button type="submit">Repair</button>
</table>
</form>';
        return $html;
    }

    public function fix($data)
    {
        switch ($data['repair_mode']) {
            case self::FIX_COLUMN:
                $this->fixColumnProperties($data['table_name'], $data['column_info']);
                break;
            case self::FIX_INDEX:
                $this->fixColumnIndex($data['table_name'], $data['column_info']);
                break;
            case self::DROP_COLUMN:
                $this->dropColumn($data['table_name'], $data['column_info']);
                break;
            case self::CRETE_TABLE:
                $this->creteTable($data['table_name'], $data['column_info']);
                break;
        }
    }

    protected function fixColumnIndex($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'], $columnInfo['key'])) {
            return;
        }

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        if (empty($columnInfo['key'])) {
            $writeConnection->dropIndex($tableName, $columnInfo['name']);
            return;
        }

        $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY;
        $columnInfo['key'] == 'mul' && $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX;
        $columnInfo['key'] == 'uni' && $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;

        $writeConnection->addIndex($tableName, $columnInfo['name'], $columnInfo['name'], $indexType);
    }

    protected function fixColumnProperties($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $definition = "{$columnInfo['type']} ";
        $columnInfo['null'] == 'no' && $definition .= 'NOT NULL ';
        $columnInfo['default'] != '' && $definition .= "DEFAULT '{$columnInfo['default']}' ";
        ($columnInfo['null'] == 'yes' && $columnInfo['default'] == '') && $definition .= 'DEFAULT NULL ';
        $columnInfo['extra'] == 'auto_increment' && $definition .= 'AUTO_INCREMENT ';
        !empty($columnInfo['after']) && $definition .= "AFTER `{$columnInfo['after']}`";

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        if ($writeConnection->tableColumnExists($tableName, $columnInfo['name']) === false) {
            $writeConnection->addColumn($tableName, $columnInfo['name'], $definition);
            return;
        }

        $writeConnection->changeColumn($tableName, $columnInfo['name'], $columnInfo['name'], $definition);
    }

    protected function dropColumn($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        $writeConnection->dropColumn($tableName, $columnInfo['name']);
    }

    protected function creteTable($tableName, $columnsInfo)
    {
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_setup');
        $ddlTable = $connection->newTable(
            Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix($tableName)
        );

        foreach ($columnsInfo as $columnInfo) {
            $columnDefinition = $this->parseColumnInfo($columnInfo);
            $ddlTable->addColumn(
                $columnDefinition['name'],
                $columnDefinition['type'],
                $columnDefinition['size'],
                $columnDefinition['options']
            );
            if (isset($columnDefinition['key'])) {
                $ddlTable->addIndex(
                    $columnDefinition['name'],
                    $columnDefinition['name'],
                    array('type' => $columnDefinition['key'])
                );
            }
        }

        $connection->createTable($ddlTable);
    }

    protected function parseColumnInfo($columnInfo)
    {
        $result['name'] = $columnInfo['name'];

        $pattern = "#^(?P<type>[a-z]+(?:\([\d\s,]+\))?)";
        $pattern .= "(?:(?P<unsigned>\sUNSIGNED)?(?P<nullable>\s(?:NOT\s)?NULL)?)?#i";

        $matches = array();
        $result['type'] = $columnInfo['type'];
        if (preg_match($pattern, $columnInfo['type'], $matches) !== false && isset($matches['type'])) {
            $size = null;
            $type = $matches['type'];
            if (strpos($type, '(') !== false) {
                $result['size'] = str_replace(array('(', ')'), '', substr($type, strpos($type, '(')));
                $result['type'] = substr($type, 0, strpos($type, '('));
                if ($result['type'] === 'int') {
                    $result['type'] = Varien_Db_Ddl_Table::TYPE_INTEGER;
                }
            }

            if (!empty($matches['unsigned'])) {
                $result['options']['unsigned'] = true;
            }
        }

        if (isset($columnInfo['null'])) {
            if ($columnInfo['null'] === 'no') {
                $result['options']['nullable'] = false;
            } else {
                $result['options']['nullable'] = true;
            }
        }

        if (isset($columnInfo['default']) && $columnInfo['default'] !== '') {
            $result['options']['default'] = $columnInfo['default'];
        }

        if (isset($columnInfo['key'])) {
            if ($columnInfo['key'] === 'pri') {
                $result['options']['primary'] = true;
            } else {
                $columnInfo['key'] == 'mul' && $result['key'] = Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX;
                $columnInfo['key'] == 'uni' && $result['key'] = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
            }
        }

        return $result;
    }

    //########################################
}