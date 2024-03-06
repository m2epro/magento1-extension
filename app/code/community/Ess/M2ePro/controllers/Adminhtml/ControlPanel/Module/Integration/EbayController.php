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
     * @title "Stop Unmanaged"
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
                        $item->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE)->save();
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
            return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
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

        $this->_getSession()->addSuccess("Set for {$affected} affected Products.");
        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
    }

    //########################################

    /**
     * @hidden
     */
    public function repairNonexistentTemplatesAction()
    {
        $fixData = array(
            'field'       => $this->getRequest()->getParam('field'),
            'template'    => $this->getRequest()->getParam('template'),
            'field_value' => $this->getRequest()->getParam('field_value'),
            'action'      => $this->getRequest()->getParam('action'),
            'template_id' => $this->getRequest()->getParam('template_id', false)
        );

        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_NonexistentTemplates $inspector */
        $inspector = Mage::getModel(
            Mage::getSingleton('M2ePro/ControlPanel_Inspection_Repository')->getDefinition('NonexistentTemplates')
                ->getHandler()
        );

        $inspector->fix($fixData);
    }

    //########################################

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
            $resolver->setIsAllowedToProcessVariationsWhichAreNotExistInTheModule(true);
            $resolver->setIsAllowedToProcessExistedVariations(true);
            $resolver->setIsAllowedToProcessVariationMpnErrors(true);
            $resolver->resolve();

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

    /**
     * @title "View variations_that_can_not_be_deleted"
     * @description "[View or delete array variations_that_can_not_be_deleted]"
     * @new_line
     */
    public function viewVariationsThatCanNotBeDeletedAction()
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = \Mage::getModel('M2ePro/Listing_Product');
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = $listingProduct->getCollection();
        $collection->addFieldToFilter('component_mode', 'ebay');
        $collection->getSelect()->where("additional_data LIKE '%variations_that_can_not_be_deleted%'");
        $collection->getSelect()->where('additional_data NOT LIKE "%\"variations_that_can_not_be_deleted\":[]%"');

        // Delete All action
        if ($this->getRequest()->getParam('delete_all')) {
            foreach ($collection->getItems() as $item) {
                $item->setSetting('additional_data', 'variations_that_can_not_be_deleted', array());
                $item->save();
            }

            $this->_getSession()->addNotice('Cleared all <code>variations_that_can_not_be_deleted</code> arrays');
            return $this->_redirect('*/*/viewVariationsThatCanNotBeDeleted', array('_query' => ''));
        }
        // Delete by id action
        $deleteId = $this->getRequest()->getParam('delete_id');
        if (!empty($deleteId)) {
            $collection->addFieldToFilter('id', $deleteId);
            $item = $collection->getFirstItem();
            $item->setSetting('additional_data', 'variations_that_can_not_be_deleted', array());
            $item->save();

            $message = sprintf(
                'Array <code>variations_that_can_not_be_deleted</code> cleared for listing_product_id %s.',
                $deleteId
            );
            $this->_getSession()->addNotice($message);
            return $this->_redirect('*/*/viewVariationsThatCanNotBeDeleted', array('_query' => ''));
        }

        $html = '';
        foreach ($this->_getSession()->getMessages(true)->getItems() as $message) {
            $html .= '<p>' . $message->getText() . '</p>';
        }

        if ($collection->getSize() === 0) {
            $html .= '<p>All products have an empty array <code>variations_that_can_not_be_deleted</code></p>';

            return $this->getResponse()->setBody($html);
        }

        $jsonEncodeFlags = 0;
        if (PHP_VERSION_ID >= 50400) {
            $jsonEncodeFlags = JSON_PRETTY_PRINT;
        }

        $addTableColumnFunction = function ($content) {
            return '<td style="border: 1px solid black; padding: 3px 5px">' . $content . '</td>';
        };

        $tableHtml = '<div style="padding: 7px 0"><a href="?delete_all=1">Delete All</a></div>';
        $tableHtml .= '<table style="width: 100%; border-collapse: collapse; border: 1px solid black">';
        $tableHtml .= '<tr>'
            . $addTableColumnFunction('listing_product_id')
            . $addTableColumnFunction('product_id')
            . $addTableColumnFunction('variations_that_can_not_be_deleted')
            . $addTableColumnFunction('action')
            . '</tr>';
        /** @var  $item */
        foreach ($collection->getItems() as $item) {
            $additionalData = $item->getSettings('additional_data');
            if (!array_key_exists('variations_that_can_not_be_deleted', $additionalData)) {
                continue;
            }
            $prettyVariations = json_encode($additionalData['variations_that_can_not_be_deleted'], $jsonEncodeFlags);

            $tableHtml .= '<tr>';
            $tableHtml .= $addTableColumnFunction($item->getId());
            $tableHtml .= $addTableColumnFunction($item->getProductId());
            $tableHtml .= $addTableColumnFunction(
                '<textarea rows="5" style="width: 100%">'
                . $prettyVariations
                . '</textarea>'
            );
            $tableHtml .= $addTableColumnFunction("<a href='?delete_id={$item->getId()}'>Delete</a>");
            $tableHtml .= '</tr>';
        }
        $tableHtml .= '</table>';

        return $this->getResponse()->setBody($html . $tableHtml);
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
