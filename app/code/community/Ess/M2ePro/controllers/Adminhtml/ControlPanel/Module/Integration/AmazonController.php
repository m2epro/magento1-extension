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
                              new Zend_Db_Expr('MIN(mlp.id) AS save_this_id'),
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

    /**
     * @title "Repricer Print Request"
     */
    public function repricerPrintRequestAction()
    {
        $listingProductId = $this->_request->getParam('listing_product_id', '');
        $html = $this->getRepricerPrintRequestForm($listingProductId);
        if (!empty($listingProductId)) {
            $html .= $this->getRepricerHtml($listingProductId);
        }

        return $this->getResponse()->setBody($html);
    }

    private function getRepricerHtml($listingProductId)
    {
        /** @var \Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection $amazonListingProductCollection */
        $amazonListingProductCollection = Mage::getModel('M2ePro/Amazon_Listing_Product')->getCollection();
        $amazonListingProductCollection->addFieldToFilter('listing_product_id', $listingProductId);

        /** @var \Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $amazonListingProductCollection->getFirstItem();
        if (!$amazonListingProduct->getId()) {
            return $this->printErrorMessage(
                sprintf('Listing product with ID "%s" not found.', $listingProductId)
            );
        }

        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Repricing_Collection $repricingListingProductCollection */
        $repricingListingProductCollection = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing')->getCollection();
        $repricingListingProductCollection->addFieldToFilter('listing_product_id', $listingProductId);

        /** @var \Ess_M2ePro_Model_Amazon_Listing_Product_Repricing $repricingListingProduct */
        $repricingListingProduct = $repricingListingProductCollection->getFirstItem();
        if (!$repricingListingProduct->getId()) {
            return $this->printErrorMessage(
                sprintf('No repricer is used for listing product with ID "%s"', $listingProductId)
            );
        }

        $repricingListingProduct->setListingProduct($amazonListingProduct->getParentObject());

        /** @var Ess_M2ePro_Model_Amazon_Repricing_Updating $repricingUpdating */
        $repricingUpdating = Mage::getModel(
            'M2ePro/Amazon_Repricing_Updating',
            $amazonListingProduct->getParentObject()->getAccount()
        );

        try {
            $result = $repricingUpdating->getChangeData($repricingListingProduct);
        } catch (\Ess_M2ePro_Model_Exception_Logic $exception) {
            $message = sprintf(
                '<h3>The product will not be sent to the repricer.</h3><p><strong>Product log text</strong>: %s</p><h3>Context:</h3>%s',
                $exception->getMessage(),
                $this->printJsonBlock(array(
                    'min_price' => $repricingListingProduct->getMinPrice(),
                    'regular_price' => $repricingListingProduct->getRegularPrice(),
                    'max_price' => $repricingListingProduct->getMaxPrice(),
                ))
            );
            return $this->printErrorMessage($message);
        } catch (\Exception $exception) {
            $message = sprintf(
                '<h3>Something went wrong.</h3><p><strong>Exception message</strong>: %s</p><h4>Exception Trace:</h4><pre>%s</pre>',
                $exception->getMessage(),
                $exception->getTraceAsString()
            );
            return $this->printErrorMessage($message);
        }

        if ($result === false) {
            $context = $this->printJsonBlock(array(
                'repricing_account_data' => $repricingListingProduct->getAccountRepricing()->getData(),
            ));

            return $this->printErrorMessage('<h3>No data will be sent to the repricer.</h3><h3>Context</h3>' . $context);
        }

        return $this->printJsonBlock($result);
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

    private function getRepricerPrintRequestForm($listingProductId)
    {
        return <<<HTML
<style>
pre {
    white-space: pre-wrap;       /* Since CSS 2.1 */
    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
    white-space: -pre-wrap;      /* Opera 4-6 */
    white-space: -o-pre-wrap;    /* Opera 7 */
    word-wrap: break-word;       /* Internet Explorer 5.5+ */
}
.form-wrap {
    color: #383d41;
    background-color: #e2e3e5;
    border: 1px solid #d6d8db;
    border-radius: .25rem;
    padding: .75rem 1.25rem;
    margin-bottom: 3px;
}
.form-wrap form { margin: 0}
.form-row:not(:last-child) {margin-bottom: 10px}
.btn {padding: .375rem .75rem; cursor: pointer}
.btn.primary {color: #fff;  background-color: #007bff; border: 1px solid #007bff}
.btn.primary:hover {background-color: #0069d9; border-color: #0062cc}
.form-wrap input {border: 1px solid #ced4da; color: #495057; border-radius: .25rem; padding: .375rem .75rem}
</style>

<div class="form-wrap">
<form>
<div class="form-row">
    <label for="listing_product_id">Listing Product ID:</label>
    <input id="listing_product_id" name="listing_product_id" value="$listingProductId" required>
</div>
<div class="form-row">
    <input type="submit" class="btn primary" value="Print Repricer Request">
</div>
</form>
</div>
HTML;

    }

    private function printErrorMessage($message)
    {
        return <<<HTML
<style>
.error-message {
    color: #721c24;
    padding: .75rem 1.25rem;
    background-color: #f8d7da; 
    border: 1px solid #f5c6cb; 
    border-radius: .25rem
}
</style>
<div class="error-message">
<p>$message</p>
</div>
HTML;

    }

    private function printJsonBlock(array $data)
    {
        $jsonEncodeFlags = 0;
        if (PHP_VERSION_ID >= 50400) {
            $jsonEncodeFlags = JSON_PRETTY_PRINT;
        }
        $dataHtml = json_encode($data, $jsonEncodeFlags);

        return <<<HTML
<style>
.json-code {
    color: #383d41;
    background-color: #e2e3e5;
    border: 1px solid #d6d8db;
    border-radius: .25rem;
    padding: .75rem 1.25rem;
    margin-bottom: 3px;
}
</style>
<div class="json-code">
    <pre>$dataHtml</pre>
</div>
HTML;

    }
}
