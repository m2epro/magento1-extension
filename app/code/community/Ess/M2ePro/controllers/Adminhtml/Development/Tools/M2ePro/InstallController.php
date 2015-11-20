<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_InstallController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Show Installation History"
     * @description "Show History of Install/Upgrade Module"
     * @new_line
     */
    public function showInstallationVersionHistoryAction()
    {
        $history = Mage::getModel('M2ePro/Registry')->load('/installation/versions_history/', 'key')
                                                    ->getValueFromJson();
        if (count($history) <= 0) {
            echo $this->getEmptyResultsHtml('Installation History is not available.');
            return;
        }

        $history = array_reverse($history);
        $html = $this->getStyleHtml();

        $html .= <<<HTML
<style>
    .grid td.color-first  { background-color: rgba(136, 227, 53, 0); }
    .grid td.color-second { background-color: rgba(255, 217, 97, 0.27); }
    .grid td  { text-align: center; }
</style>

<h2 style="margin: 20px 0 0 10px">Installation History
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 100px">Version From</th>
        <th style="width: 100px">Version To</th>
        <th style="width: 200px">Date</th>
    </tr>
HTML;
        $tdClass = 'color-first';
        $previousItemDate = $history[0]['date'];

        foreach ($history as $item) {

            !$item['from'] && $item['from'] = '--';

            if ((strtotime($previousItemDate) - strtotime($item['from'])) > 360) {
                $tdClass = $tdClass != 'color-second' ? 'color-second' : 'color-first';
            }
            $previousItemDate = $item['date'];

            $html .= <<<HTML
<tr>
    <td class="{$tdClass}">{$item['from']}</td>
    <td class="{$tdClass}">{$item['to']}</td>
    <td class="{$tdClass}">{$item['date']}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%', count($history), $html);
    }

    //########################################

    /**
     * @title "Repeat Upgrade > 4.1.0"
     * @description "Repeat Upgrade From Certain Version"
     * @new_line
     */
    public function recurringUpdateAction()
    {
        if ($this->getRequest()->getParam('upgrade')) {

            $version = $this->getRequest()->getParam('version');
            $version = str_replace(array(','),'.',$version);

            if (!version_compare('3.2.0',$version,'<=')) {
                $this->_getSession()->addError('Extension upgrade can work only from 3.2.0 version.');
                $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

            $connWrite->update(
                Mage::getSingleton('core/resource')->getTableName('core_resource'),
                array(
                    'version'      => $version,
                    'data_version' => $version
                ),
                array('code = ?' => 'M2ePro_setup')
            );

            Mage::helper('M2ePro/Magento')->clearCache();

            $this->_getSession()->addSuccess('Extension upgrade was successfully completed.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());

            return;
        }

        $urlPhpInfo = Mage::helper('adminhtml')->getUrl('*/*/*', array('upgrade' => 'yes'));

        echo '<form method="GET" action="'.$urlPhpInfo.'">
                From version: <input type="text" name="version" value="3.2.0" />
                <input type="submit" title="Upgrade Now!" onclick="return confirm(\'Are you sure?\');" />
              </form>';
    }

    //########################################

    /**
     * @title "Check Files Validity"
     * @description "Check Files Validity"
     */
    public function checkFilesValidityAction()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('files','get','info');
        $responseData = $dispatcherObject->process($connectorObj);

        if (count($responseData) <= 0) {
            echo $this->getEmptyResultsHtml('No files info for this M2E Pro version on server.');
            return;
        }

        $problems = array();

        $baseDir = Mage::getBaseDir() . '/';
        foreach ($responseData['files_info'] as $info) {

            if (!is_file($baseDir . $info['path'])) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'File is missing'
                );
                continue;
            }

            $fileContent = trim(file_get_contents($baseDir . $info['path']));
            $fileContent = str_replace(array("\r\n","\n\r",PHP_EOL), chr(10), $fileContent);

            if (md5($fileContent) != $info['hash']) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'Hash mismatch'
                );
                continue;
            }
        }

        if (count($problems) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">All files are valid.</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 600px">Path</th>
        <th>Reason</th>
        <th>Action</th>
    </tr>
HTML;
        foreach ($problems as $item) {
            $url = Mage::helper('adminhtml')->getUrl('*/*/filesDiff',
                                                     array('filePath' => base64_encode($item['path'])));

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
    <td style="text-align: center;">
        <a href="{$url}" target="_blank">Diff</a>
    </td>
</tr>

HTML;
        }

        $html .= '</table>';
        print str_replace('%count%',count($problems),$html);
    }

    /**
     * @title "Check Tables Structure Validity"
     * @description "Check Tables Structure Validity"
     */
    public function checkTablesStructureValidityAction()
    {
        $tablesInfo = Mage::helper('M2ePro/Module_Database_Structure')->getTablesInfo();

        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('tables','get','diff',
                                                               array('tables_info' => json_encode($tablesInfo)));

        $responseData = $dispatcherObject->process($connectorObj);

        if (!isset($responseData['diff'])) {
            echo $this->getEmptyResultsHtml('No Tables info for this M2E Pro version on Server.');
            return;
        }

        if (count($responseData['diff']) <= 0) {
            echo $this->getEmptyResultsHtml('All Tables are valid.');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Tables Structure Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 400px">Table</th>
        <th>Problem</th>
        <th style="width: 300px">Info</th>
        <th style="width: 100px">Actions</th>
    </tr>
HTML;

        foreach ($responseData['diff'] as $tableName => $checkResult) {
            foreach ($checkResult as $resultRow) {

                $additionalInfo = '';
                $actionsHtml    = '';

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

                $urlParams = array(
                    'table_name'  => $tableName,
                    'column_info' => json_encode($resultInfo['original_data'])
                );

                if (empty($resultInfo['current_data']) ||
                    (isset($diffData['type']) || isset($diffData['default']) || isset($diffData['null']))) {

                    $urlParams['mode'] = 'properties';
                    $url = $this->getUrl('*/*/fixColumn', $urlParams);
                    $actionsHtml .= "<a href=\"{$url}\">Fix Properties</a>";
                }

                if (isset($diffData['key'])) {

                    $urlParams['mode'] = 'index';
                    $url = $this->getUrl('*/*/fixColumn', $urlParams);
                    $actionsHtml .= "<a href=\"{$url}\">Fix Index</a>";
                }

                if (empty($resultInfo['original_data']) && !empty($resultInfo['current_data'])) {

                    $urlParams['mode'] = 'drop';
                    $urlParams['column_info'] = json_encode($resultInfo['current_data']);
                    $url = $this->getUrl('*/*/fixColumn', $urlParams);
                    $actionsHtml .= "<a href=\"{$url}\">Drop</a>";
                }

                $html .= <<<HTML
<tr>
    <td>{$tableName}</td>
    <td>{$resultRow['message']}</td>
    <td>&nbsp;{$additionalInfo}&nbsp;</td>
    <td>&nbsp;{$actionsHtml}&nbsp;</td>
</tr>
HTML;
            }
        }

        $html .= '</table>';
        print str_replace('%count%',count($responseData['diff']),$html);
    }

    /**
     * @title "Check Configs Validity"
     * @description "Check Configs Validity"
     */
    public function checkConfigsValidityAction()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('configs','get','info');
        $responseData = $dispatcherObject->process($connectorObj);

        if (!isset($responseData['configs_info'])) {
            echo $this->getEmptyResultsHtml('No configs info for this M2E Pro version on server.');
            return;
        }

        $originalData = $responseData['configs_info'];
        $currentData = array();

        foreach ($originalData as $tableName => $configInfo) {

            $currentData[$tableName] = Mage::helper('M2ePro/Module_Database_Structure')
                                                ->getConfigSnapshot($tableName);
        }

        $differenses = array();

        foreach ($originalData as $tableName => $configInfo) {
            foreach ($configInfo as $codeHash => $item) {

                if (array_key_exists($codeHash, $currentData[$tableName])) {
                    continue;
                }

                $differenses[] = array('table'    => $tableName,
                                       'item'     => $item,
                                       'solution' => 'insert');
            }
        }

        foreach ($currentData as $tableName => $configInfo) {
            foreach ($configInfo as $codeHash => $item) {

                if (array_key_exists($codeHash, $originalData[$tableName])) {
                    continue;
                }

                $differenses[] = array('table'    => $tableName,
                                       'item'     => $item,
                                       'solution' => 'drop');
            }
        }

        if (count($differenses) <= 0) {
            echo $this->getEmptyResultsHtml('All Configs are valid.');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Configs Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0" style="width: 100%;">
    <tr>
        <th style="width: 400px">Table</th>
        <th style="width: 200px">Group</th>
        <th style="width: 200px">Key</th>
        <th style="width: 150px">Value</th>
        <th style="width: 50px">Action</th>
    </tr>
HTML;

        foreach ($differenses as $index => $row) {

            if ($row['solution'] == 'insert') {

                $url = $this->getUrl('*/adminhtml_development_database/addTableRow', array(
                    'table'  => $row['table'],
                    'model'  => Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($row['table']),
                ));

            } else {

                $url = $this->getUrl('*/adminhtml_development_database/deleteTableRows', array(
                    'table'  => $row['table'],
                    'model'  => Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($row['table']),
                    'ids'    => $row['item']['id']
                ));
            }

            $actionWord = $row['solution'] == 'insert' ? 'Insert' : 'Drop';
            $styles = $row['solution'] == 'insert' ? '' : 'color: red;';

            $onclickAction = <<<JS
var elem     = $(this.id),
    formData = Form.serialize(elem.up('tr').down('form'));

elem.up('tr').remove();

new Ajax.Request( '{$url}' , {
    method: 'get',
    asynchronous : true,
    parameters : formData
});
JS;
        $html .= <<<HTML
<tr>
    <td>{$row['table']}</td>
    <td>{$row['item']['group']}</td>
    <td>{$row['item']['key']}</td>
    <td>
        <form style="margin-bottom: 0;">
            <input type="checkbox" name="cells[]" value="group" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="key" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="value" style="display: none;" checked="checked">

            <input type="hidden" name="value_group" value="{$row['item']['group']}">
            <input type="hidden" name="value_key" value="{$row['item']['key']}">
            <input type="text" name="value_value" value="{$row['item']['value']}">
        </form>
    </td>
    <td align="center">
        <a id="insert_id_{$index}" style= "{$styles}"
           onclick="{$onclickAction}" href="javascript:void(0);">{$actionWord}</a>
    </td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%',count($differenses),$html);
    }

    // ---------------------------------------

    /**
     * @hidden
     */
    public function fixColumnAction()
    {
        $tableName  = $this->getRequest()->getParam('table_name');
        $columnInfo = $this->getRequest()->getParam('column_info');
        $columnInfo = (array)json_decode($columnInfo, true);

        $repairMode = $this->getRequest()->getParam('mode');

        if (!$tableName || !$repairMode) {
            $this->_redirect('*/*/checkTablesStructureValidity');
            return;
        }

        $helper = Mage::helper('M2ePro/Module_Database_Repair');
        $repairMode == 'index' && $helper->fixColumnIndex($tableName, $columnInfo);
        $repairMode == 'properties' && $helper->fixColumnProperties($tableName, $columnInfo);
        $repairMode == 'drop' && $helper->dropColumn($tableName, $columnInfo);

        $this->_redirect('*/*/checkTablesStructureValidity');
    }

    /**
     * @title "Files Diff"
     * @description "Files Diff"
     * @hidden
     */
    public function filesDiffAction()
    {
        $filePath     = base64_decode($this->getRequest()->getParam('filePath'));
        $originalPath = base64_decode($this->getRequest()->getParam('originalPath'));

        $params = array(
            'content' => file_get_contents(Mage::getBaseDir() . '/' . $filePath),
            'path'    => $originalPath ? $originalPath : $filePath
        );

        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('files','get','diff',
                                                               $params);

        $responseData = $dispatcherObject->process($connectorObj);

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Difference
    <span style="color: #808080; font-size: 15px;">({$filePath})</span>
</h2>
<br/>
HTML;

        if (isset($responseData['html'])) {
            $html .= $responseData['html'];
        } else {
            $html .= '<h1>&nbsp;&nbsp;No file on server</h1>';
        }

        echo $html;
    }

    /**
     * @title "Show UnWritable Directories"
     * @description "Show UnWritable Directories"
     * @new_line
     */
    public function showUnWritableDirectoriesAction()
    {
        $unWritableDirectories = Mage::helper('M2ePro/Module')->getUnWritableDirectories();

        if (count ($unWritableDirectories) <= 0) {
            echo $this->getEmptyResultsHtml('No UnWritable Directories');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">UnWritable Directories
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 800px">Path</th>
    </tr>
HTML;
        foreach ($unWritableDirectories as $item) {

            $html .= <<<HTML
<tr>
    <td>{$item}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%',count($unWritableDirectories),$html);
    }

    //########################################

    /**
     * @title "Reset Module (Clear Installation)"
     * @description "Clear all M2ePro data tables, reset wizards"
     * @confirm "This will remove all M2e Pro data. Are you sure?"
     * @non-production
     */
    public function fullResetModuleStateAction()
    {
        $this->truncateModuleTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_primary_config'),
            array('value' => null),
            "`group` LIKE '%license%'"
        );
        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_config'),
            array('value' => 1),
            "`key` = 'mode' AND `group` LIKE '/component/%'"
        );

        $skipWizards = array('migrationToV6','migrationNewAmazon','removedPay','ebayProductDetails',
                             'fullAmazonCategories','amazonShippingOverridePolicy');

        array_walk($skipWizards, function(&$el, $key) { $el = "'{$el}'"; });
        $skipWizards = implode(',', $skipWizards);

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_wizard'),
            array('status' => 0, 'step' => null),
            "nick NOT IN ({$skipWizards})"
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess('Full Reset Module State was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Reset Module (Without Wizards)"
     * @description "Clear all M2ePro data tables, set wizards as skipped"
     * @confirm "This will remove all M2e Pro data. Are you sure?"
     * @non-production
     */
    public function resetModuleStateAndSkippingWizardsAction()
    {
        $this->truncateModuleTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_config'),
            array('value' => 1),
            "`key` = 'mode' AND `group` LIKE '/component/%'"
        );
        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_wizard'),
            array('status' => 3, 'step' => null)
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess('Reset Module State was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    // ---------------------------------------

    private function truncateModuleTables()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $moduleTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();

        $excludeTables = array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_synchronization_config',

            'm2epro_marketplace',
            'm2epro_amazon_marketplace',
            'm2epro_buy_marketplace',
            'm2epro_ebay_marketplace',

            'm2epro_wizard'
        );

        $tablesForTruncate = array_diff($moduleTables, $excludeTables);
        foreach ($tablesForTruncate as $table) {
            $connWrite->delete(Mage::getSingleton('core/resource')->getTableName($table));
        }
    }

    //########################################

    private function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }

    //########################################
}