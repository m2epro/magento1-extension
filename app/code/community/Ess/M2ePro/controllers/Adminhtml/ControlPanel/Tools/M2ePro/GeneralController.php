<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Tools_M2ePro_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

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
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
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
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No Broken Tables'));
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

        return $this->getResponse()->setBody($html);
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

        return $this->getResponse()->setBody(
            '<pre>' .
             "<span>Broken Records '{$tableName}'<span><br>" .
            print_r($info, true)
        );
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
        $removedStoreIds = array_diff($usedStoresIds, $existsStoreIds);

        if (empty($removedStoreIds)) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No Removed Magento Stores'));
        }

        $html = $this->getStyleHtml();
        $removedStoresCount = count($removedStoreIds);

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

        return $this->getResponse()->setBody(str_replace('%count%', $removedStoresCount, $html));
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
            $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
        }

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

        // -- Listing_Product_Variation_Option to un-existed Listing_Product_Variation
        $deletedOptions = 0;

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mlpv' => $collection->getResource()->getTable('M2ePro/Listing_Product_Variation')),
            'main_table.listing_product_variation_id=mlpv.id',
            array()
        );
        $collection->addFieldToFilter('mlpv.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        /** @var $item Ess_M2ePro_Model_Listing_Product_Variation_Option */
        while ($item = $collection->fetchItem()) {
            $item->getResource()->delete($item);
            $deletedOptions++;
        }

        // --

        // -- Listing_Product_Variation to un-existed Listing_Product OR with no Listing_Product_Variation_Option
        $deletedVariations = 0;

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mlp' => $collection->getResource()->getTable('M2ePro/Listing_Product')),
            'main_table.listing_product_id=mlp.id',
            array()
        );
        $collection->getSelect()->joinLeft(
            array('mlpvo' => $collection->getResource()->getTable('M2ePro/Listing_Product_Variation_Option')),
            'main_table.id=mlpvo.listing_product_variation_id',
            array()
        );

        $collection->getSelect()->where('mlp.id IS NULL OR mlpvo.id IS NULL');
        $collection->getSelect()->group('main_table.id');

        /** @var $item Ess_M2ePro_Model_Listing_Product_Variation */
        while ($item = $collection->fetchItem()) {
            $item->getResource()->delete($item);
            $deletedVariations++;
        }

        // --

        // -- Listing_Product to un-existed Listing
        $deletedProducts = 0;

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->getSelect()->joinLeft(
            array('ml' => $collection->getResource()->getTable('M2ePro/Listing')),
            'main_table.listing_id=ml.id',
            array()
        );
        $collection->addFieldToFilter('ml.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        /** @var $item Ess_M2ePro_Model_Listing_Product */
        while ($item = $collection->fetchItem()) {
            $item->getResource()->delete($item);
            $deletedProducts++;
        }

        // --

        // -- Listing to un-existed Account
        $deletedListings = 0;

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->getSelect()->joinLeft(
            array('ma' => $collection->getResource()->getTable('M2ePro/Account')),
            'main_table.account_id=ma.id',
            array()
        );
        $collection->addFieldToFilter('ma.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        /** @var $item Ess_M2ePro_Model_Listing */
        while ($item = $collection->fetchItem()) {
            $item->getResource()->delete($item);
            $deletedListings++;
        }

        // --

        printf('Deleted options: %d <br/>', $deletedOptions);
        printf('Deleted variations: %d <br/>', $deletedVariations);
        printf('Deleted products: %d <br/>', $deletedProducts);
        printf('Deleted listings: %d <br/>', $deletedListings);

        printf('<br/>Please run repair broken tables feature.<br/>');
    }

    /**
     * @title "Repair OrderItem => Order Structure"
     * @description "OrderItem->getOrder() => remove OrderItem if is need"
     */
    public function repairOrderItemOrderStructureAction()
    {
        ini_set('display_errors', 1);

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Order_Item')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mo' => $collection->getResource()->getTable('M2ePro/Order')),
            'main_table.order_id=mo.id',
            array()
        );
        $collection->addFieldToFilter('mo.id', array('null' => true));

        $deletedOrderItems = 0;

        /** @var $item Ess_M2ePro_Model_Order_Item */
        while ($item = $collection->fetchItem()) {
            $item->deleteInstance() && $deletedOrderItems++;
        }

        printf('Deleted OrderItems records: %d', $deletedOrderItems);
    }

    /**
     * @title "Repair eBay ItemID N\A"
     * @description "Repair Item is Listed but have N\A Ebay Item ID"
     */
    public function repairEbayItemIdStructureAction()
    {
        ini_set('display_errors', 1);

        $items = 0;

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->joinLeft(
            array('ei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            '`second_table`.`ebay_item_id` = `ei`.`id`',
            array('item_id' => 'item_id')
        );
        $collection->addFieldToFilter(
            'status',
            array('nin' => array(Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN))
        );

        $collection->addFieldToFilter('item_id', array('null' => true));

        /** @var $item Ess_M2ePro_Model_Order_Item */
        while ($item = $collection->fetchItem()) {
            $item->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)->save();
            $items++;
        }

        printf('Processed items %d', $items);
    }

    /**
     * @title "Repair Amazon Products without variations"
     * @description "Repair Amazon Products without variations"
     * @new_line
     */
    public function repairAmazonProductWithoutVariationsAction()
    {
        ini_set('display_errors', 1);

        $items = 0;

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->joinLeft(
            array('mlpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
            '`second_table`.`listing_product_id` = `mlpv`.`listing_product_id`',
            array()
        );
        $collection->addFieldToFilter('is_variation_product', 1);
        $collection->addFieldToFilter('is_variation_product_matched', 1);
        $collection->addFieldToFilter('mlpv.id', array('null' => true));

        /** @var $item Ess_M2ePro_Model_Listing_Product */
        while ($item = $collection->fetchItem()) {
            $item->getChildObject()->setData('is_variation_product_matched', 0)->save();
            $items++;
        }

        printf('Processed items %d', $items);
    }

    //########################################

    /**
     * @title "Change the date of the last order synchronization"
     * @description "Change the date of the last order synchronization"
     * @new_line
     * @throws \Ess_M2ePro_Model_Exception_Logic
     * @throws \Exception
     */
    public function changeLastOrderSynchronizationAction()
    {
        ini_set('display_errors', 1);

        //########################################

        $walmartComponentTitle = \Mage::helper('M2ePro/Component_Walmart')->getTitle();
        $amazonComponentTitle = \Mage::helper('M2ePro/Component_Amazon')->getTitle();
        $eBayComponentTitle = \Mage::helper('M2ePro/Component_Ebay')->getTitle();
        $registryModel = \Mage::getModel('M2ePro/Registry');
        $dataHelper = \Mage::helper('M2ePro/Data');
        $logs = array();

        //########################################

        if (\Mage::app()->getRequest()->getMethod() === 'POST') {
            $accountData = $dataHelper->jsonDecode(
                $this->getRequest()->getParam('account_data', '')
            );

            if (empty($accountData)) {
                return $this->getResponse()->setBody(
                    $this->getEmptyResultsHtml('No account information is available.')
                );
            }

            foreach ($accountData as $data) {
                if (empty($data['time'])) {
                    $logs[] = "Account: <b>\"{$data['title']}\"</b> skipped. The date empty.";
                    continue;
                }

                $data['time'] = trim($data['time']);

                if (false === \DateTime::createFromFormat('Y-m-d H:i:s', $data['time'])) {
                    $logs[] = "Account: <b>\"{$data['title']}\"</b> skipped. The date or time was incorrect.";
                    continue;
                }

                /** @var Ess_M2ePro_Model_Account $account */
                $account = \Mage::getModel('M2ePro/Account')->load($data['id']);
                $componentTitle = $account->getComponentTitle();

                if ($amazonComponentTitle === $componentTitle) {
                    $key = "/amazon/orders/receive/{$account->getChildObject()->getMerchantId()}/from_update_date/";
                    $registry = $registryModel->load($key, 'key');
                    $registry->setData('key', $key);
                    $registry->setData('value', $data['time']);
                    $registry->save();
                    continue;
                }

                if ($eBayComponentTitle === $componentTitle || $walmartComponentTitle === $componentTitle) {
                    $accountChildObject = $account->getChildObject();
                    $accountChildObject->setData('orders_last_synchronization', $data['time']);
                    $accountChildObject->save();
                }
            }

            \Mage::helper('M2ePro/Magento')->clearCache();

            $logs[] = 'The date(s) of the last order synchronization was update successfully.';
            $logs[] = 'Magento cache was successfully cleared.';
        }

        //########################################

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = \Mage::getModel('M2ePro/Account')->getCollection();

        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = $accountCollection->getItems();

        if (empty($accounts)) {
            return $this->getResponse()->setBody(
                $this->getEmptyResultsHtml('There are no accounts.')
            );
        }

        $accountData = array();

        foreach ($accounts as $account) {
            $componentTitle = $account->getComponentTitle();
            $time = null;

            if ($amazonComponentTitle === $componentTitle) {
                $key = "/amazon/orders/receive/{$account->getChildObject()->getMerchantId()}/from_update_date/";
                $registryModel->load($key, 'key');
                $time = $registryModel->getValue();
            }

            if ($eBayComponentTitle === $componentTitle || $walmartComponentTitle === $componentTitle) {
                $time = $account->getChildObject()->getData('orders_last_synchronization');
            }

            $accountData[] = array(
                'component' => $componentTitle,
                'title'     => $account->getTitle(),
                'id'        => $account->getId(),
                'time'      => $time
            );
        }

        //########################################

        $currentActionURL = \Mage::helper('adminhtml')->getUrl('*/*/*');
        $formKey = \Mage::getSingleton('core/session')->getFormKey();

        /** @var Ess_M2ePro_Model_M2ePro_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = \Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('server', 'get', 'gmtTime');
        $dispatcherObject->process($connectorObj);

        $responseData = $connectorObj->getResponseData();

        $serverTime = new \DateTime($responseData['time'], new \DateTimeZone('UTC'));
        $dateTimeString = $serverTime->format('Y-m-d H:i:s');
        $accountData = $dataHelper->jsonEncode($accountData);
        $accountCounts = count($accounts);
        $log = implode('<br>', $logs);
        $title = $this->getEmptyResultsHtml('Change the date of the last order synchronization');

        $html = <<<HTML
<div style="/*max-width: 1280px; margin: 0 auto;*/">
    <div style="text-align: center; /*margin-bottom: 0; padding-top: 25px*/">
        {$title}
        <span style="color: #808080; font-size: 15px">(%count% entries)</span>
    </div>
<br/>
{$log}
<p>Current time by UTC: <b>{$dateTimeString}</b></p>

<form id="form" method="post" action="{$currentActionURL}">

    <div style="display: flex; margin-bottom: 14px">
        <div style="margin-right: 7px">
            <select id="accounts" multiple autofocus size="{$accountCounts}" style="padding: 14px"></select>
        </div>

        <div style="margin-right: 7px">
            <input type="text" id="last_synchronization" style="text-align: end"/>
            <p style="text-align: end">Last synchronization.</p>
        </div>
    </div>

    <div>
        <input type="hidden" id="form_key" name="form_key" value="{$formKey}"/>
        <input type="hidden" id="account_data" name="account_data"/>
        <input type="submit" id="edit" value="Edit(s)"/>
    </div>
</form>

<script type="text/javascript">

    var accountData = {$accountData},
        accountList = document.getElementById('accounts'),
        lastSynchronizationTime = document.getElementById('last_synchronization'),
        selected = [];

    accountData.forEach(function(account, index) {

        var option = document.createElement('option');

        option.innerHTML = '[ ' + account['component'] + ' ] ' + account['title'];
        option.id = account['id'];
        if (0 === index) {
            option.selected = true;
            lastSynchronizationTime.value = account['time'];
            selected.push(account['id']);
        }

        accountList.appendChild(option);
    });

    accountList.addEventListener('change', function() {

        selected = [];

        for (var i = 0; i < this.options.length; i++) {
            if (this.options[i].selected === true) {
                selected.push(this.options[i].id);
            }
        }

        if (1 === selected.length) {
            accountData.forEach(function(account) {
                if (account.id === selected[0]) {
                    lastSynchronizationTime.value = account['time'];
                }
            });
            return;
        }

        lastSynchronizationTime.value = null;
    });

    lastSynchronizationTime.addEventListener('input', function () {
        selected.forEach(function (value) {
            accountData.forEach(function(account) {
                if (account.id === value) {
                    account['time'] = lastSynchronizationTime.value;
                }
            });
        });
    });

    document.getElementById('edit').addEventListener('click', function() {

        var data = document.getElementById('account_data'),
            form = document.getElementById('form');

        data.value = JSON.stringify(accountData);
        form.submit();
    });
</script>
HTML;
        return $this->getResponse()->setBody(str_replace('%count%', $accountCounts, $html . '</div>'));
    }

    //########################################

    /**
     * @title "Check Server Connection"
     * @description "Send test request to server and check connection"
     */
    public function serverCheckConnectionAction()
    {
        $resultHtml = '';

        try {
            $response = Mage::helper('M2ePro/Server_Request')->single(
                array('timeout' => 30), null, null, false, false, true
            );
        } catch (Ess_M2ePro_Model_Exception_Connection $e) {
            $resultHtml .= "<h2>{$e->getMessage()}</h2><pre><br/>";
            $additionalData = $e->getAdditionalData();

            if (!empty($additionalData['curl_info'])) {
                $resultHtml .= '</pre><h2>Report</h2><pre>';
                $resultHtml .= print_r($additionalData['curl_info'], true);
                $resultHtml .= '</pre>';
            }

            if (!empty($additionalData['curl_error_number']) && !empty($additionalData['curl_error_message'])) {
                $resultHtml .= '<h2 style="color:red;">Errors</h2>';
                $resultHtml .= $additionalData['curl_error_number'] .': '
                               . $additionalData['curl_error_message'] . '<br/><br/>';
            }

            return $this->getResponse()->setBody($resultHtml);
        } catch (Exception $e) {
            return $this->getResponse()->setBody("<h2>{$e->getMessage()}</h2><pre><br/>");
        }

        $resultHtml .= '<h2>Response</h2><pre>';
        $resultHtml .= print_r($response['body'], true);
        $resultHtml .= '</pre>';

        $resultHtml .= '</pre><h2>Report</h2><pre>';
        $resultHtml .= print_r($response['curl_info'], true);
        $resultHtml .= '</pre>';

        return $this->getResponse()->setBody($resultHtml);
    }

    //########################################

    protected function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }

    //########################################
}
