<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Clear Cache"
     * @description "Clear extension cache"
     * @confirm "Are you sure?"
     */
    public function clearExtensionCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        $this->_getSession()->addSuccess('Extension cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear Config Cache"
     * @description "Clear config cache"
     * @confirm "Are you sure?"
     */
    public function clearConfigCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearConfigCache();
        $this->_getSession()->addSuccess('Config cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear Variables Dir"
     * @description "Clear Variables Dir"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function clearVariablesDirAction()
    {
        Mage::getModel('M2ePro/VariablesDir')->removeBaseForce();
        $this->_getSession()->addSuccess('Variables dir was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //########################################

    /**
     * @title "Repair Broken Tables"
     * @description "Command for show and repair broken horizontal tables"
     */
    public function checkTablesAction()
    {
        $tableNames = $this->getRequest()->getParam('table', array());

        if (!empty($tableNames)) {
            Mage::helper('M2ePro/Module_Database_Repair')->repairBrokenTables($tableNames);
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/checkTables/'));
        }

        $brokenTables = Mage::helper('M2ePro/Module_Database_Repair')->getBrokenTablesInfo();

        if ($brokenTables['total_count'] <= 0) {
            echo $this->getEmptyResultsHtml('No Broken Tables');
            return;
        }

        $currentUrl = Mage::helper('adminhtml')->getUrl('*/*/*');
        $infoUrl = Mage::helper('adminhtml')->getUrl('*/*/showBrokenTableIds');

        $html = <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Broken Tables
            <span style="color: #808080; font-size: 15px;">({$brokenTables['total_count']} entries)</span>
        </h2>
        <br/>
        <form method="GET" action="{$currentUrl}">
            <input type="hidden" name="action" value="repair" />
            <table class="grid" cellpadding="0" cellspacing="0">
HTML;
        if (count($brokenTables['parent'])) {

            $html .= <<<HTML
<tr bgcolor="#E7E7E7">
    <td colspan="4">
        <h4 style="margin: 0 0 0 10px">Parent Tables</h4>
    </td>
</tr>
<tr>
    <th style="width: 400">Table</th>
    <th style="width: 50">Count</th>
    <th style="width: 50"></th>
    <th style="width: 50"></th>
</tr>
HTML;
            foreach ($brokenTables['parent'] as $parentTable => $brokenItemsCount) {

                $html .= <<<HTML
<tr>
    <td>
        <a href="{$infoUrl}?table[]={$parentTable}"
           target="_blank" title="Show Ids" style="text-decoration: none;">{$parentTable}</a>
    </td>
    <td>
        {$brokenItemsCount}
    </td>
    <td>
        <input type='button' value="Repair" onclick ="location.href='{$currentUrl}?table[]={$parentTable}'" />
    </td>
    <td>
        <input type="checkbox" name="table[]" value="{$parentTable}" />
    </td>
HTML;
            }
        }

        if (count($brokenTables['children'])) {

            $html .= <<<HTML
<tr height="100%">
    <td><div style="height: 10px;"></div></td>
</tr>
<tr bgcolor="#E7E7E7">
    <td colspan="4">
        <h4 style="margin: 0 0 0 10px">Children Tables</h4>
    </td>
</tr>
<tr>
    <th style="width: 400">Table</th>
    <th style="width: 50">Count</th>
    <th style="width: 50"></th>
    <th style="width: 50"></th>
</tr>
HTML;
            foreach ($brokenTables['children'] as $childrenTable => $brokenItemsCount) {

                $html .= <<<HTML
<tr>
    <td>
        <a href="{$infoUrl}?table[]={$childrenTable}"
           target="_blank" title="Show Ids" style="text-decoration: none;">{$childrenTable}</a>
    </td>
    <td>
        {$brokenItemsCount}
    </td>
    <td>
        <input type='button' value="Repair" onclick ="location.href='{$currentUrl}?table[]={$childrenTable}'" />
    </td>
    <td>
        <input type="checkbox" name="table[]" value="{$childrenTable}" />
    </td>
HTML;
            }
        }

        $html .= <<<HTML
                <tr>
                    <td colspan="4"><hr/></td>
                </tr>
                <tr>
                    <td colspan="4" align="right">
                        <input type="submit" value="Repair Checked">
                    <td>
                </tr>
            </table>
        </form>
    </body>
</html>
HTML;

        echo $html;
    }

    /**
     * @title "Show Broken Table IDs"
     * @hidden
     */
    public function showBrokenTableIdsAction()
    {
        $tableNames = $this->getRequest()->getParam('table', array());

        if (empty($tableNames)) {
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/checkTables/'));
        }

        $tableName = array_pop($tableNames);

        $info = Mage::helper('M2ePro/Module_Database_Repair')->getBrokenRecordsInfo($tableName);
        return '<pre>' . print_r($info);
    }

    // ---------------------------------------

    /**
     * @title "Repair Removed Stores"
     * @description "Command for show and repair removed magento stores"
     */
    public function showRemovedMagentoStoresAction()
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
                    ->from(Mage::getSingleton('core/resource')->getTableName($tableName), array($columnInfo['name']))
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
                    !empty($matches[2]) && $usedStoresIds = array_merge($usedStoresIds,$matches[2]);
                }
            }
        }

        $usedStoresIds = array_values(array_unique(array_map('intval',$usedStoresIds)));
        $removedStoreIds = array_diff($usedStoresIds, $existsStoreIds);

        if (count($removedStoreIds) <= 0) {
            echo $this->getEmptyResultsHtml('No Removed Magento Stores');
            return;
        }

        $html = $this->getStyleHtml();

        $removedStoreIds = implode(', ', $removedStoreIds);
        $repairStoresAction = Mage::helper('adminhtml')->getUrl('*/*/repairRemovedMagentoStore');

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Removed Magento Stores
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>

<span style="display:inline-block; margin: 20px 20px 20px 10px;">
    Removed Store IDs: {$removedStoreIds}
</span>

<form action="{$repairStoresAction}" method="get">
    <input name="replace_from" value="" type="text" placeholder="replace from id" required/>
    <input name="replace_to" value="" type="text" placeholder="replace to id" required />
    <button type="submit">Repair</button>
</form>
HTML;

        print str_replace('%count%', count($removedStoreIds), $html);
    }

    /**
     * @title "Repair Removed Store"
     * @hidden
     */
    public function repairRemovedMagentoStoreAction()
    {
        $replaceIdFrom = $this->getRequest()->getParam('replace_from');
        $replaceIdTo = $this->getRequest()->getParam('replace_to');

        if (!$replaceIdFrom || !$replaceIdTo) {
            $this->_getSession()->addError('Required params are not presented.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $storeRelatedColumns = Mage::helper('M2ePro/Module_Database_Structure')->getStoreRelatedColumns();

        foreach ($storeRelatedColumns as $tableName => $columnsInfo) {
            foreach ($columnsInfo as $columnInfo) {

                if ($columnInfo['type'] == 'int') {

                    $connection->update(
                        Mage::getSingleton('core/resource')->getTableName($tableName),
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
                    Mage::getSingleton('core/resource')->getTableName($tableName),
                    $bind,
                    "`{$columnInfo['name']}` LIKE '%store_id\":\"{$replaceIdFrom}\"%' OR
                     `{$columnInfo['name']}` LIKE '%store_id\":{$replaceIdFrom}%'"
                );
            }
        }

        $this->_redirect('*/*/showRemovedMagentoStores');
    }

    // ---------------------------------------

    /**
     * @title "Repair Listing Product Structure"
     * @description "Listing -> Listing Product -> Option -> Variation"
     */
    public function repairListingProductStructureAction()
    {
        ini_set('display_errors', 1);

        foreach (array('Ebay', 'Amazon', 'Buy') as $component) {

            $collection = Mage::helper("M2ePro/Component_{$component}")
                    ->getCollection('Listing_Product_Variation_Option');

            $deletedOptions = $deletedVariations = $deletedProducts = 0;

            /* @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            while ($option = $collection->fetchItem()) {

                try {
                    $variation = $option->getListingProductVariation();
                } catch (Ess_M2ePro_Model_Exception_Logic $e) {

                    $tempOption = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->load($option->getId());
                    if (!is_null($tempOption->getId())) {
                        $option->deleteInstance() && $deletedOptions++;
                    }
                    continue;
                }

                try {
                    $listingProduct = $variation->getListingProduct();
                } catch (Ess_M2ePro_Model_Exception_Logic $e) {
                    $variation->deleteInstance() && $deletedVariations++;
                    continue;
                }

                try {
                    $listing = $listingProduct->getListing();
                } catch (Ess_M2ePro_Model_Exception_Logic $e) {
                    $listingProduct->deleteInstance() && $deletedProducts++;
                    continue;
                }
            }

            printf('Deleted options on %s count = %d <br/>', $component, $deletedOptions);
            printf('Deleted variations on %s count = %d <br/>', $component, $deletedVariations);
            printf('Deleted products on %s count = %d <br/>', $component, $deletedProducts);
        }
    }

    /**
     * @title "Repair OrderItem => Order Structure"
     * @description "OrderItem->getOrder() => remove OrderItem if is need"
     */
    public function repairOrderItemOrderStructureAction()
    {
        ini_set('display_errors', 1);

        $deletedOrderItems = 0;
        $collection = Mage::getModel('M2ePro/Order_Item')->getCollection();

        /* @var $item Ess_M2ePro_Model_Order_Item */
        while ($item = $collection->fetchItem()) {

            try {
                $order = $item->getOrder();
            } catch (Ess_M2ePro_Model_Exception_Logic $e) {

                $item->deleteInstance() && $deletedOrderItems++;
            }
        }

        printf('Deleted OrderItems records %d', $deletedOrderItems);
    }

    //########################################

    /**
     * @title "Remove Config Duplicates"
     * @description "Remove Configuration Duplicates"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function removeConfigsDuplicatesAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');
        $installerInstance->removeConfigsDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();

        $this->_getSession()->addSuccess('Remove duplicates was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Check Server Connection"
     * @description "Send test request to server and check connection"
     */
    public function serverCheckConnectionAction()
    {
        $curlObject = curl_init();

        // set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, Mage::helper('M2ePro/Server')->getEndpoint());

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query(array(),'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlObject, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($curlObject);

        echo '<h1>Response</h1><pre>';
        print_r($response);
        echo '</pre><h1>Report</h1><pre>';
        print_r(curl_getinfo($curlObject));
        echo '</pre>';

        echo '<h2 style="color:red;">Errors</h2>';
        echo curl_errno($curlObject) . ' ' . curl_error($curlObject) . '<br/><br/>';

        curl_close($curlObject);
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