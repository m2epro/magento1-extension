<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Module_Integration_AmazonController
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
        $amazonListingOther = Mage::getModel('M2ePro/Amazon_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other')->getSelect()->query();

        $SKUs = array();
        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $amazonListingOther->setData($row);

            $listingOther->setChildObject($amazonListingOther);
            $amazonListingOther->setParentObject($listingOther);
            $SKUs[] = $amazonListingOther->getSku();

            $listingOther->deleteInstance();
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_item');
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach (array_chunk($SKUs, 1000) as $chunkSKUs) {
            $writeConnection->delete($tableName, array('sku IN (?)' => $chunkSKUs));
        }

        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization', 1);

        foreach ($accountsCollection->getItems() as $account) {
            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
            unset(
                $additionalData['is_amazon_other_listings_full_items_data_already_received'],
                $additionalData['last_other_listing_products_synchronization']
            );
            $account->setSettings('additional_data', $additionalData)->save();
        }

        $this->_getSession()->addSuccess('Successfully removed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
    }

    /**
     * @title "Show Duplicates [listing_id/sku]"
     * @description "[MAX(id) will be saved]"
     */
    public function showAmazonDuplicatesAction()
    {
        /** @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $lp = $structureHelper->getTableNameWithPrefix('m2epro_listing_product');
        $alp = $structureHelper->getTableNameWithPrefix('m2epro_amazon_listing_product');
        $alpr = $structureHelper->getTableNameWithPrefix('m2epro_amazon_listing_product_repricing');

        $subQuery = $writeConnection
            ->select()
            ->from(
                array('malp' => $alp),
                array('general_id','sku')
            )
            ->joinInner(
                array('mlp' => $lp),
                'mlp.id = malp.listing_product_id',
                array('listing_id',
                              'product_id',
                              new Zend_Db_Expr('COUNT(product_id) - 1 AS count_of_duplicates'),
                              new Zend_Db_Expr('MAX(mlp.id) AS save_this_id'),
                )
            )
            ->group(array('mlp.listing_id', 'malp.sku'))
            ->having(new Zend_Db_Expr('count_of_duplicates > 0'));

        $query = $writeConnection
            ->select()
            ->from(
                array('malp' => $alp),
                array('listing_product_id')
            )
            ->joinInner(
                array('mlp' => $lp),
                'mlp.id = malp.listing_product_id',
                array('status')
            )
            ->joinInner(
                array('templ_table' => $subQuery),
                'malp.sku = templ_table.sku AND mlp.listing_id = templ_table.listing_id'
            )
            ->where('malp.listing_product_id <> templ_table.save_this_id')
            ->query();

        $removed = 0;
        $duplicated = array();

        while ($row = $query->fetch()) {
            if ((bool)$this->getRequest()->getParam('remove', false)) {
                $writeConnection->delete(
                    $lp, array('id = ?' => $row['listing_product_id'])
                );

                $writeConnection->delete(
                    $alp, array('listing_product_id = ?' => $row['listing_product_id'])
                );

                $writeConnection->delete(
                    $alpr, array('listing_product_id = ?' => $row['listing_product_id'])
                );

                $removed++;
                continue;
            }

            $duplicated[$row['save_this_id']] = $row;
        }

        if (empty($duplicated)) {
            $message = 'There are no duplicates.';
            $removed > 0 && $message .= ' Removed: ' . $removed;

            return $this->getResponse()->setBody($this->getEmptyResultsHtml($message));
        }

        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Magento Product ID</th>
    <th>SKU</th>
    <th>Count Of Copies</th>
</tr>
HTML;
        foreach ($duplicated as $row) {
            $tableContent .= <<<HTML
<tr>
    <td>{$row['listing_id']}</td>
    <td>{$row['product_id']}</td>
    <td>{$row['sku']}</td>
    <td>{$row['count_of_duplicates']}</td>
</tr>
HTML;
        }

        $url = Mage::helper('adminhtml')->getUrl('*/*/*', array('remove' => '1'));
        $html = $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Amazon Duplicates [group by SKU and listing_id]
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
