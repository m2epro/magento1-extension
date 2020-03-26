<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Module_Integration_EbayController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @title "Reset 3rd Party"
     * @description "Clear all 3rd party items for all Accounts"
     */
    public function resetOtherListingsAction()
    {
        $listingOther = Mage::getModel('M2ePro/Listing_Other');
        $ebayListingOther = Mage::getModel('M2ePro/Ebay_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other')->getSelect()->query();

        $itemIds = array();
        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $ebayListingOther->setData($row);

            $listingOther->setChildObject($ebayListingOther);
            $ebayListingOther->setParentObject($listingOther);
            $itemIds[] = $ebayListingOther->getItemId();

            $listingOther->deleteInstance();
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_ebay_item');
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach(array_chunk($itemIds, 1000) as $chunkItemIds) {
            $writeConnection->delete($tableName, array('item_id IN (?)' => $chunkItemIds));
        }

        foreach (Mage::helper('M2ePro/Component_Ebay')->getCollection('Account') as $account) {
            $account->setData('other_listings_last_synchronization', null)->save();
        }

        $this->_getSession()->addSuccess('Successfully removed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
    }

    /**
     * @title "Stop 3rd Party"
     * @description "[in order to resolve the problem with duplicates]"
     * @new_line
     */
    public function stop3rdPartyAction()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $collection->addFieldToFilter(
            'status', array('in' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN
            ))
        );

        $total       = 0;
        $groupedData = array();

        foreach ($collection->getItems() as $item) {
            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $item */

            $key = $item->getAccount()->getId() .'##'. $item->getMarketplace()->getId();
            $groupedData[$key][$item->getId()] = $item->getItemId();
            $total++;
        }

        foreach ($groupedData as $groupKey => $items) {
            list($accountId, $marketplaceId) = explode('##', $groupKey);

            foreach (array_chunk($items, 10, true) as $itemsPart) {

                /** @var $dispatcherObject Ess_M2ePro_Model_Ebay_Connector_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector(
                    'item', 'update', 'ends',
                    array('items' => $itemsPart), null, $marketplaceId, $accountId
                );

                $dispatcherObject->process($connectorObj);
                $response = $connectorObj->getResponseData();

                foreach ($response['result'] as $itemId => $iResp) {
                    $item = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Other', $itemId);
                    if ($item->getId() &&
                        ((isset($iResp['already_stop']) && $iResp['already_stop']) ||
                          isset($iResp['ebay_end_date_raw'])))
                    {
                        $item->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED)->save();
                    }
                }
            }
        }

        return $this->getResponse()->setBody("Processed {$total} products.");
    }

    /**
     * @title "Set EPS Images Mode"
     * @description "Set EPS Images Mode = true for listing products"
     * @prompt "Please enter Listing Product ID or `all` code for all products."
     * @prompt_var "listing_product_id"
     */
    public function setEpsImagesModeAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');

        $listingProducts = array();
        if (strtolower($listingProductId) == 'all') {
            $listingProducts = Mage::getModel('M2ePro/Listing_Product')->getCollection()
                ->addFieldToFilter('component_mode', 'ebay');
        } else {
            $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load((int)$listingProductId);
            $listingProduct && $listingProducts[] = $listingProduct;
        }

        if (empty($listingProducts)) {
            $this->_getSession()->addError('Failed to load Listing Product.');
            return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
        }

        $affected = 0;
        foreach ($listingProducts as $listingProduct) {
            $additionalData = $listingProduct->getAdditionalData();

            if (!isset($additionalData['is_eps_ebay_images_mode']) ||
                $additionalData['is_eps_ebay_images_mode'] == true) {
                continue;
            }

            $additionalData['is_eps_ebay_images_mode'] = true;
            $affected++;

            $listingProduct->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($additionalData))
                           ->save();
        }

        $this->_getSession()->addSuccess("Successfully set for {$affected} affected Products.");
        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
    }

    //########################################

    /**
     * @title "Show Nonexistent Templates"
     * @description "Show Nonexistent Templates"
     * @new_line
     */
    public function showNonexistentTemplatesAction()
    {
        if ($this->getRequest()->getParam('fix')) {
            $action       = $this->getRequest()->getParam('action');

            $template     = $this->getRequest()->getParam('template_nick');
            $currentMode  = $this->getRequest()->getParam('current_mode');
            $currentValue = $this->getRequest()->getParam('value');

            if ($action == 'set_null') {
                $field = $currentMode == 'template' ? "template_{$template}_id"
                                                    : "template_{$template}_custom_id";

                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
                $collection->addFieldToFilter($field, $currentValue);

                foreach ($collection->getItems() as $listingProduct) {
                    $listingProduct->setData($field, null)->save();
                }
            }

            if ($action == 'set_parent') {
                $field = $currentMode == 'template' ? "template_{$template}_id"
                                                    : "template_{$template}_custom_id";

                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
                $collection->addFieldToFilter($field, $currentValue);

                $data = array(
                    "template_{$template}_mode" => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT,
                    $field                      => null
                );

                foreach ($collection->getItems() as $listingProduct) {
                    $listingProduct->addData($data)->save();
                }
            }

            if ($action == 'set_template' && $this->getRequest()->getParam('template_id')) {
                $field = $currentMode == 'template' ? "template_{$template}_id"
                                                    : "template_{$template}_custom_id";

                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
                $collection->addFieldToFilter($field, $currentValue);

                $data = array(
                    "template_{$template}_mode" => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE,
                    $field                      => null,
                );
                $data["template_{$template}_id"] = (int)$this->getRequest()->getParam('template_id');

                foreach ($collection->getItems() as $listing) {
                    $listing->addData($data)->save();
                }
            }

            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/*'));
        }

        $nonexistentTemplates = array();

        $simpleTemplates = array('category', 'other_category');
        foreach ($simpleTemplates as $templateName) {
            $tempResult = $this->getNonexistentTemplatesBySimpleLogic($templateName);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        $difficultTemplates = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY,
        );
        foreach ($difficultTemplates as $templateName) {
            $tempResult = $this->getNonexistentTemplatesByDifficultLogic($templateName);
            !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        }

        if (empty($nonexistentTemplates)) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('There are no any nonexistent templates.'));
        }

        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Listing Product ID</th>
    <th>Policy ID</th>
    <th>My Mode</th>
    <th>Parent Mode</th>
    <th>Actions</th>
</tr>
HTML;

        $alreadyRendered = array();
        foreach ($nonexistentTemplates as $templateName => $items) {
            $tableContent .= <<<HTML
<tr>
    <td colspan="6" align="center"><b>{$templateName}</b></td>
</tr>
HTML;

            foreach ($items as $index => $itemInfo) {
                $myModeWord = '';
                $parentModeWord = '';
                $actionsHtml = '';

                if (!isset($itemInfo['my_mode']) && !isset($itemInfo['parent_mode'])) {
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/*/*', array(
                        'fix'           => '1',
                        'template_nick' => $templateName,
                        'current_mode'  => 'template',
                        'action'        => 'set_null',
                        'value'         => $itemInfo['my_needed_id'],
                        )
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set null</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 0) {
                    $myModeWord = 'parent';
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 1) {
                    $myModeWord = 'custom';
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/*/*', array(
                        'fix'           => '1',
                        'template_nick' => $templateName,
                        'current_mode'  => $myModeWord,
                        'action'        => 'set_parent',
                        'value'         => $itemInfo['my_needed_id'],
                        )
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 2) {
                    $myModeWord = 'template';
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/*/*', array(
                        'fix'           => '1',
                        'template_nick' => $templateName,
                        'current_mode'  => $myModeWord,
                        'action'        => 'set_parent',
                        'value'         => $itemInfo['my_needed_id'],
                        )
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 1) {
                    $parentModeWord = 'custom';
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/*/*', array(
                        'fix'           => '1',
                        'action'        => 'set_template',
                        'template_nick' => $templateName,
                        'current_mode'  => $parentModeWord,
                        'value'         => $itemInfo['my_needed_id'],
                        )
                    );
                    $onClick = <<<JS
var result = prompt('Enter Template ID');
if (result) {
    window.location.href = '{$url}' + '?template_id=' + result;
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 2) {
                    $parentModeWord = 'template';
                    $url = Mage::helper('adminhtml')->getUrl(
                        '*/*/*', array(
                        'fix'           => '1',
                        'action'        => 'set_template',
                        'template_nick' => $templateName,
                        'current_mode'  => $parentModeWord,
                        'value'         => $itemInfo['my_needed_id'],
                        )
                    );
                    $onClick = <<<JS
var result = prompt('Enter Template ID');
if (result) {
    window.location.href = '{$url}' + '?template_id=' + result;
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                $key = $templateName .'##'. $myModeWord .'##'. $itemInfo['listing_id'];
                if ($myModeWord == 'parent' && in_array($key, $alreadyRendered)) {
                    continue;
                }

                $alreadyRendered[] = $key;
                $tableContent .= <<<HTML
<tr>
    <td>{$itemInfo['listing_id']}</td>
    <td>{$itemInfo['my_id']}</td>
    <td>{$itemInfo['my_needed_id']}</td>
    <td>{$myModeWord}</td>
    <td>{$parentModeWord}</td>
    <td>
        {$actionsHtml}
    </td>
</tr>
HTML;
            }
        }

        $html = $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Nonexistent templates
            <span style="color: #808080; font-size: 15px;">(#count# entries)</span>
        </h2>
        <br/>
        <table class="grid" cellpadding="0" cellspacing="0">
            {$tableContent}
        </table>
    </body>
</html>
HTML;

        return $this->getResponse()->setBody(str_replace('#count#', count($alreadyRendered), $html));
    }

    protected function getNonexistentTemplatesByDifficultLogic($templateCode)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $subSelect = $connRead->select()
            ->from(
                array(
                    'melp' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_ebay_listing_product')
                ),
                array(
                    'my_id'          => 'listing_product_id',
                    'my_mode'        => "template_{$templateCode}_mode",
                    'my_template_id' => "template_{$templateCode}_id",
                    'my_custom_id'   => "template_{$templateCode}_custom_id",

                    'my_needed_id'   => new Zend_Db_Expr(
                        "CASE
                        WHEN melp.template_{$templateCode}_mode = 2 THEN melp.template_{$templateCode}_id
                        WHEN melp.template_{$templateCode}_mode = 1 THEN melp.template_{$templateCode}_custom_id
                        WHEN melp.template_{$templateCode}_mode = 0 THEN IF(mel.template_{$templateCode}_mode = 1,
                                                                            mel.template_{$templateCode}_custom_id,
                                                                            mel.template_{$templateCode}_id)
                    END"
                    ))
            )
            ->joinLeft(
                array(
                    'mlp' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_listing_product')
                ),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array(
                    'mel' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_ebay_listing')
                ),
                'mlp.listing_id = mel.listing_id',
                array(
                    'parent_mode'        => "template_{$templateCode}_mode",
                    'parent_template_id' => "template_{$templateCode}_id",
                    'parent_custom_id'   => "template_{$templateCode}_custom_id"
                )
            );

        $templateIdName = 'id';
        $horizontalTemplates = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
        );
        in_array($templateCode, $horizontalTemplates) && $templateIdName = "template_{$templateCode}_id";

        $result = $connRead->select()
        ->from(
            array('subselect' => new Zend_Db_Expr('('.$subSelect->__toString().')')),
            array(
                   'subselect.my_id',
                   'subselect.listing_id',
                   'subselect.my_mode',
                   'subselect.parent_mode',
                   'subselect.my_needed_id',
               )
        )
        ->joinLeft(
            array(
                   'template' => Mage::helper('M2ePro/Module_Database_Structure')
                       ->getTableNameWithPrefix("m2epro_ebay_template_{$templateCode}")
               ),
            "subselect.my_needed_id = template.{$templateIdName}",
            array()
        )
           ->where("template.{$templateIdName} IS NULL")
           ->query()->fetchAll();

        return $result;
    }

    protected function getNonexistentTemplatesBySimpleLogic($templateCode)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                array(
                    'melp' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_ebay_listing_product')
                ),
                array(
                    'my_id'        => 'listing_product_id',
                    'my_needed_id' => "template_{$templateCode}_id",
                )
            )
            ->joinLeft(
                array(
                    'mlp' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_listing_product')
                ),
                'melp.listing_product_id = mlp.id',
                array('listing_id' => 'listing_id')
            )
            ->joinLeft(
                array(
                    'template' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix("m2epro_ebay_template_{$templateCode}")
                ),
                "melp.template_{$templateCode}_id = template.id",
                array()
            )
            ->where("melp.template_{$templateCode}_id IS NOT NULL")
            ->where("template.id IS NULL");

        return $select->query()->fetchAll();
    }

    //########################################

    /**
     * @title "Show Duplicates [parse logs]"
     * @description "Show Duplicates According with Logs"
     */
    public function showDuplicatesByLogsAction()
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $queryObj = $resource->getConnection('core_read')
                             ->select()
                            ->from(
                                array(
                                     'mll' => Mage::helper('M2ePro/Module_Database_Structure')
                                         ->getTableNameWithPrefix('m2epro_listing_log')
                                 )
                            )
                            ->joinLeft(
                                array(
                                     'ml' => Mage::helper('M2ePro/Module_Database_Structure')
                                         ->getTableNameWithPrefix('m2epro_listing')
                                 ),
                                'mll.listing_id = ml.id',
                                array('marketplace_id')
                            )
                            ->joinLeft(
                                array(
                                    'mm' => Mage::helper('M2ePro/Module_Database_Structure')
                                        ->getTableNameWithPrefix('m2epro_marketplace')
                                ),
                                'ml.marketplace_id = mm.id',
                                array('marketplace_title' => 'title')
                            )
                            ->where(
                                "mll.description LIKE '%a duplicate of your item%' OR " . // ENG
                                     "mll.description LIKE '%ette annonce est identique%' OR " . // FR
                                     "mll.description LIKE '%ngebot ist identisch mit dem%' OR " .  // DE
                                     "mll.description LIKE '%un duplicato del tuo oggetto%' OR " . // IT
                                     "mll.description LIKE '%es un duplicado de tu art%'" // ESP
                            )
                             ->where("mll.component_mode = ?", 'ebay')
                             ->order('mll.id DESC')
                             ->group(array('mll.product_id', 'mll.listing_id'))
                             ->query();

        $duplicatesInfo = array();
        while ($row = $queryObj->fetch()) {
            preg_match('/.*\((\d*)\)/', $row['description'], $matches);
            $ebayItemId = !empty($matches[1]) ? $matches[1] : '';

            $duplicatesInfo[] = array(
                'date'               => $row['create_date'],
                'listing_id'         => $row['listing_id'],
                'listing_title'      => $row['listing_title'],
                'product_id'         => $row['product_id'],
                'product_title'      => $row['product_title'],
                'listing_product_id' => $row['listing_product_id'],
                'description'        => $row['description'],
                'ebay_item_id'       => $ebayItemId,
                'marketplace_title'  => $row['marketplace_title']
            );
        }

        if (empty($duplicatesInfo)) {
            return $this->getResponse()->setBody(
                $this->getEmptyResultsHtml('According to you logs there are no duplicates.')
            );
        }

        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Listing Title</th>
    <th>Product ID</th>
    <th>Product Title</th>
    <th>Listing Product ID</th>
    <th>eBay Item ID</th>
    <th>Marketplace</th>
    <th>Date</th>
</tr>
HTML;
        foreach ($duplicatesInfo as $row) {
            $tableContent .= <<<HTML
<tr>
    <td>{$row['listing_id']}</td>
    <td>{$row['listing_title']}</td>
    <td>{$row['product_id']}</td>
    <td>{$row['product_title']}</td>
    <td>{$row['listing_product_id']}</td>
    <td>{$row['ebay_item_id']}</td>
    <td>{$row['marketplace_title']}</td>
    <td>{$row['date']}</td>
</tr>
HTML;
        }

        $html = $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">eBay Duplicates
            <span style="color: #808080; font-size: 15px;">(#count# entries)</span>
        </h2>
        <br/>
        <table class="grid" cellpadding="0" cellspacing="0">
            {$tableContent}
        </table>
    </body>
</html>
HTML;
        return $this->getResponse()->setBody(str_replace('#count#', count($duplicatesInfo), $html));
    }

    /**
     * @title "Show Duplicates [product_id/listing_id]"
     * @description "[MIN(id) will be saved]"
     * @new_line
     */
    public function showDuplicatesAction()
    {
        /** @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $listingProduct = $structureHelper->getTableNameWithPrefix('m2epro_listing_product');
        $ebayListingProduct = $structureHelper->getTableNameWithPrefix('m2epro_ebay_listing_product');
        $ebayItem = $structureHelper->getTableNameWithPrefix('m2epro_ebay_item');

        $subQuery = $writeConnection
            ->select()
            ->from(
                array('melp' => $ebayListingProduct),
                array()
            )
            ->joinInner(
                array('mlp' => $listingProduct),
                'mlp.id = melp.listing_product_id',
                array('listing_id',
                              'product_id',
                              new Zend_Db_Expr('COUNT(product_id) - 1 AS count_of_duplicates'),
                              new Zend_Db_Expr('MIN(mlp.id) AS save_this_id'),
                )
            )
            ->group(array('mlp.product_id', 'mlp.listing_id'))
            ->having(new Zend_Db_Expr('count_of_duplicates > 0'));

        $query = $writeConnection
            ->select()
            ->from(
                array('melp' => $ebayListingProduct),
                array('listing_product_id', 'ebay_item_id')
            )
            ->joinInner(
                array('mlp' => $listingProduct),
                'mlp.id = melp.listing_product_id',
                array('status')
            )
            ->joinInner(
                array('templ_table' => $subQuery),
                'mlp.product_id = templ_table.product_id AND
                         mlp.listing_id = templ_table.listing_id'
            )
            ->where('melp.listing_product_id <> templ_table.save_this_id')
            ->query();

        $removed = 0;
        $stopped = 0;
        $duplicated = array();

        while ($row = $query->fetch()) {
            if ((bool)$this->getRequest()->getParam('remove', false)) {
                if ($row['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
                    $row['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
                    $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
                    $dispatcherObject->process(
                        Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                        array($row['listing_product_id'])
                    );

                    $stopped++;
                }

                $writeConnection->delete(
                    $listingProduct, array('id = ?' => $row['listing_product_id'])
                );

                $writeConnection->delete(
                    $ebayListingProduct, array('listing_product_id = ?' => $row['listing_product_id'])
                );

                $writeConnection->delete(
                    $ebayItem, array('id = ?' => $row['ebay_item_id'])
                );

                $removed++;
                continue;
            }

            $duplicated[$row['save_this_id']] = $row;
        }

        if (empty($duplicated)) {
            $message = 'There are no duplicates.';
            $removed > 0 && $message .= ' Removed: ' . $removed;
            $stopped > 0 && $message .= ' Stopped: ' . $stopped;

            return $this->getResponse()->setBody($this->getEmptyResultsHtml($message));
        }

        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Magento Product ID</th>
    <th>Count Of Copies</th>
</tr>
HTML;
        foreach ($duplicated as $row) {
            $tableContent .= <<<HTML
<tr>
    <td>{$row['listing_id']}</td>
    <td>{$row['product_id']}</td>
    <td>{$row['count_of_duplicates']}</td>
</tr>
HTML;
        }

        $url = Mage::helper('adminhtml')->getUrl('*/*/*', array('remove' => '1'));
        $html = $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">eBay Duplicates [group by product_id and listing_id]
            <span style="color: #808080; font-size: 15px;">(#count# entries)</span>
        </h2>
        <br/>
        <table class="grid" cellpadding="0" cellspacing="0">
            {$tableContent}
        </table>
        <form action="{$url}" method="get" style="margin-top: 1em;">
            <button type="submit">Remove</button>
        </form>
    </body>
</html>
HTML;
        return $this->getResponse()->setBody(str_replace('#count#', count($duplicated), $html));
    }

    //########################################

    /**
     * @title "Fix many same categories templates"
     * @description "[remove the same templates and set original templates to the settings of listings products]"
     * @new_line
     */
    public function fixManySameCategoriesTemplatesAction()
    {
        $affectedListingProducts = $removedTemplates = 0;

        $snapshots = array();
        $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder');

        foreach (Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection() as $template) {
            /**@var Ess_M2ePro_Model_Ebay_Template_Category $template */

            $snapshotBuilder->setModel($template);
            $shot = $snapshotBuilder->getSnapshot();
            unset($shot['id'], $shot['create_date'], $shot['update_date']);
            $key = sha1(Mage::helper('M2ePro')->jsonEncode($shot));

            if (!array_key_exists($key, $snapshots)) {
                $snapshots[$key] = $template;
                continue;
            }

            $affectedListingProducts = Mage::getModel('M2ePro/Ebay_Template_Category_AffectedListingsProducts');
            $affectedListingProducts->setModel($template);

            foreach ($affectedListingProducts->getObjects() as $listingsProduct) {
                /**@var Ess_M2ePro_Model_Listing_Product $listingsProduct */

                $originalTemplate = $snapshots[$key];
                $listingsProduct->setData('template_category_id', $originalTemplate->getId())
                                ->save();

                $affectedListingProducts++;
            }

            $template->deleteInstance();
            $removedTemplates++;
        }

        $this->getResponse()->setBody(
            <<<HTML
Templates were removed: {$removedTemplates}.<br>
Listings Product Affected: {$affectedListingProducts}.<br>
HTML
        );
    }

    //########################################

    /**
     * @title "Try to fix variation product"
     * @description "[]"
     */
    public function tryToFixVariationProductAction()
    {
        if ($this->getRequest()->getParam('fix')) {

            /** @var Ess_M2ePro_Model_Listing_Product $lp */
            $listingProductId = $this->getRequest()->getParam('listing_product_id');
            $lp = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Resolver $resolver */
            $resolver = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Resolver');
            $resolver->setListingProduct($lp);
            $resolver->setIsAllowedToSave((bool)$this->getRequest()->getParam('allowed_to_save'));
            $resolver->process();

            $errors = $warnings = $notices = array();
            foreach ($resolver->getMessagesSet()->getEntities() as $message) {
                $message->isError() && $errors[] = $message->getText();
                $message->isWarning() && $warnings[] = $message->getText();
                $message->isNotice() && $notices[] = $message->getText();
            }

            return $this->getResponse()->setBody(
                '<pre>' .
                sprintf('Listing Product ID: %s<br/><br/>', $listingProductId) .
                sprintf('Errors: %s<br/><br/>', print_r($errors, true)) .
                sprintf('Warnings: %s<br/><br/>', print_r($warnings, true)) .
                sprintf('Notices: %s<br/><br/>', print_r($notices, true)) .
                '</pre>'
            );
        }

        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

        return $this->getResponse()->setBody(
            <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Listing Product ID: </label>
        <input name="listing_product_id" style="width: 200px;" required>
    </div>

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Allowed to save Item: </label>
        <select name="allowed_to_save" style="width: 200px;" required>
            <option style="display: none;"></option>
            <option value="1">YES</option>
            <option value="0">NO</option>
        </select>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="fix" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Show</button>
    </div>

</form>
HTML
        );
    }

    //########################################

    protected function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl();

        return <<<HTML
    <h2 style="margin: 20px 0 0 10px">
        {$messageText} <span style="color: grey; font-size: 10px;">
        <a href="{$backUrl}">[back]</a>
    </h2>
HTML;
    }

    //########################################
}
