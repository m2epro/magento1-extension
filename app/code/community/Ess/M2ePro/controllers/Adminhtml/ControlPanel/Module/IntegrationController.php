<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Module_IntegrationController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @title "Print Request Data"
     * @description "Print [List/Relist/Revise] Request Data"
     */
    public function getRequestDataAction()
    {
        if ($this->getRequest()->getParam('print')) {

            /** @var Ess_M2ePro_Model_Listing_Product $lp */
            $listingProductId = $this->getRequest()->getParam('listing_product_id');
            $lp = Mage::helper('M2ePro/Component')->getUnknownObject('Listing_Product', $listingProductId);

            $componentMode    = $lp->getComponentMode();
            $requestType      = $this->getRequest()->getParam('request_type');

            if ($componentMode == 'ebay') {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */
                $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request $request */
                $request = Mage::getModel("M2ePro/Ebay_Listing_Product_Action_Type_{$requestType}_Request");
                $request->setListingProduct($lp);
                $request->setConfigurator($configurator);

                return $this->getResponse()->setBody('<pre>'.print_r($request->getData(), true).'</pre>');
            }

            if ($componentMode == 'amazon') {

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
                $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request $request */
                $request = Mage::getModel("M2ePro/Amazon_Listing_Product_Action_Type_{$requestType}_Request");
                $request->setParams(array());
                $request->setListingProduct($lp);
                $request->setConfigurator($configurator);

                if ($requestType == 'List') {
                    $request->setCachedData(
                        array(
                            'sku'        => 'placeholder',
                            'general_id' => 'placeholder',
                            'list_type'  => 'placeholder'
                        )
                    );
                }

                return $this->getResponse()->setBody('<pre>'.print_r($request->getData(), true).'</pre>');
            }

            if ($componentMode == 'walmart') {

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
                $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request $request */
                $request = Mage::getModel("M2ePro/Walmart_Listing_Product_Action_Type_{$requestType}_Request");
                $request->setParams(array());
                $request->setListingProduct($lp);
                $request->setConfigurator($configurator);

                return $this->getResponse()->setBody('<pre>'.print_r($request->getData(), true).'</pre>');
            }

            return;
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
        <label style="width: 170px; display: inline-block;">Request Type: </label>
        <select name="request_type" style="width: 200px;" required>
            <option style="display: none;"></option>
            <option value="List">List</option>
            <option value="Relist">Relist</option>
            <option value="Revise">Revise</option>
        </select>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Show</button>
    </div>

</form>
HTML
        );
    }

    //########################################

    /**
     * @title "Print Inspector Data"
     * @description "Print Inspector Data"
     * @new_line
     */
    public function getInspectorDataAction()
    {
        if (!$this->getRequest()->getParam('print')) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

            return $this->getResponse()->setBody(
                <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Listing Product ID: </label>
        <input name="listing_product_id" style="width: 200px;" required>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Show</button>
    </div>

</form>
HTML
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $listingProduct = Mage::helper('M2ePro/Component')->getUnknownObject('Listing_Product', $listingProductId);

        $checkerInput = Mage::getModel('M2ePro/Listing_Product_Instruction_SynchronizationTemplate_Checker_Input');
        $checkerInput->setListingProduct($listingProduct);

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction_Collection $collection */
        $instructionCollection = Mage::getResourceModel('M2ePro/Listing_Product_Instruction_Collection');
        $instructionCollection->applySkipUntilFilter();
        $instructionCollection->addFieldToFilter('listing_product_id', $listingProduct->getId());

        $instructions = array();
        foreach ($instructionCollection->getItems() as $instruction) {
            /**@var Ess_M2ePro_Model_Listing_Product_Instruction $instruction */
            $instruction->setListingProduct($listingProduct);
            $instructions[$instruction->getId()] = $instruction;
        }

        $checkerInput->setInstructions($instructions);

        if ($listingProduct->getComponentMode() == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $html = '<pre>';

            //--
            $checker = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>NotListed</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetList: '.json_encode($checker->isMeetListRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Inactive'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Inactive</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetRelist: '.json_encode($checker->isMeetRelistRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Active</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetStop: '.json_encode($checker->isMeetStopRequirements()).'<br><br>';

            $html .= 'isMeetReviseQty: '.json_encode($checker->isMeetReviseQtyRequirements()).'<br>';
            $html .= 'isMeetRevisePriceReg: '.json_encode($checker->isMeetRevisePriceRegularRequirements()).'<br>';
            $html .= 'isMeetRevisePriceBus: '.json_encode($checker->isMeetRevisePriceBusinessRequirements()).'<br>';
            $html .= 'isMeetReviseDetails: '.json_encode($checker->isMeetReviseDetailsRequirements()).'<br>';
            $html .= 'isMeetReviseImages: '.json_encode($checker->isMeetReviseImagesRequirements()).'<br><br>';
            //--

            //--
            $magentoProduct = $listingProduct->getMagentoProduct();
            $html .= 'isStatusEnabled: '.json_encode($magentoProduct->isStatusEnabled()).'<br>';
            $html .= 'isStockAvailability: '.json_encode($magentoProduct->isStockAvailability()).'<br>';
            //--

            return $this->getResponse()->setBody($html);
        }

        if ($listingProduct->getComponentMode() == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $html = '<pre>';

            //--
            $checker = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>NotListed</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetList: '.json_encode($checker->isMeetListRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Inactive'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Inactive</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetRelist: '.json_encode($checker->isMeetRelistRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Active</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetStop: '.json_encode($checker->isMeetStopRequirements()).'<br><br>';

            $html .= 'isMeetReviseQty: '.json_encode($checker->isMeetReviseQtyRequirements()).'<br>';
            $html .= 'isMeetRevisePrice: '.json_encode($checker->isMeetRevisePriceRequirements()).'<br>';
            $html .= 'isMeetReviseTitle: '.json_encode($checker->isMeetReviseTitleRequirements()).'<br>';
            $html .= 'isMeetReviseSubtitle: '.json_encode($checker->isMeetReviseSubtitleRequirements()).'<br>';
            $html .= 'isMeetReviseDescription: '.json_encode($checker->isMeetReviseDescriptionRequirements()).'<br>';
            $html .= 'isMeetReviseImages: '.json_encode($checker->isMeetReviseImagesRequirements()).'<br>';
            $html .= 'isMeetReviseCategories: '.json_encode($checker->isMeetReviseCategoriesRequirements()).'<br>';
            $html .= 'isMeetRevisePayment: '.json_encode($checker->isMeetRevisePaymentRequirements()).'<br>';
            $html .= 'isMeetReviseShipping: '.json_encode($checker->isMeetReviseShippingRequirements()).'<br>';
            $html .= 'isMeetReviseReturn: '.json_encode($checker->isMeetReviseReturnRequirements()).'<br>';
            $html .= 'isMeetReviseOther: '.json_encode($checker->isMeetReviseOtherRequirements()).'<br><br>';

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $elp */
            $elp = $listingProduct->getChildObject();
            $html .= 'isSetCategoryTemplate: ' .json_encode($elp->isSetCategoryTemplate()).'<br>';
            $html .= 'isInAction: ' .json_encode($listingProduct->isSetProcessingLock('in_action')). '<br><br>';

            $magentoProduct = $listingProduct->getMagentoProduct();
            $html .= 'isStatusEnabled: ' .json_encode($magentoProduct->isStatusEnabled()).'<br>';
            $html .= 'isStockAvailability: ' .json_encode($magentoProduct->isStockAvailability()).'<br>';
            //--

            return $this->getResponse()->setBody($html);
        }

        if ($listingProduct->getComponentMode() == Ess_M2ePro_Helper_Component_Walmart::NICK) {
            $html = '<pre>';

            //--
            $checker = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>NotListed</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetList: '.json_encode($checker->isMeetListRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Inactive'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Inactive</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetRelist: '.json_encode($checker->isMeetRelistRequirements()).'<br><br>';
            //--

            //--
            $checker = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active'
            );
            $checker->setInput($checkerInput);

            $html .= '<b>Active</b><br>';
            $html .= 'isAllowed: '.json_encode($checker->isAllowed()).'<br>';
            $html .= 'isMeetStop: '.json_encode($checker->isMeetStopRequirements()).'<br><br>';

            $html .= 'isMeetReviseQty: '.json_encode($checker->isMeetReviseQtyRequirements()).'<br>';
            $html .= 'isMeetRevisePrice: '.json_encode($checker->isMeetRevisePriceRequirements()).'<br>';
            $html .= 'isMeetRevisePromotions: '.json_encode($checker->isMeetRevisePromotionsRequirements()).'<br>';
            $html .= 'isMeetReviseDetails: '.json_encode($checker->isMeetReviseDetailsRequirements()).'<br>';
            //--

            //--
            $magentoProduct = $listingProduct->getMagentoProduct();
            $html .= 'isStatusEnabled: '.json_encode($magentoProduct->isStatusEnabled()).'<br>';
            $html .= 'isStockAvailability: '.json_encode($magentoProduct->isStockAvailability()).'<br>';
            //--

            return $this->getResponse()->setBody($html);
        }

        return $this->getResponse()->setBody('Is not supported');
    }

    //########################################

    /**
     * @title "Build Order Quote"
     * @description "Print Order Quote Data"
     * @new_line
     */
    public function getPrintOrderQuoteDataAction()
    {
        if ($this->getRequest()->getParam('print')) {

            /** @var Ess_M2ePro_Model_Order $order */
            $orderId = $this->getRequest()->getParam('order_id');
            $order =  Mage::helper('M2ePro/Component')->getUnknownObject('Order', $orderId);

            if (!$order->getId()) {
                $this->_getSession()->addError('Unable to load order instance.');
                $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
                return;
            }

            // Store must be initialized before products
            // ---------------------------------------
            $order->associateWithStore();
            $order->associateItemsWithProducts();
            // ---------------------------------------

            $proxy = $order->getProxy()->setStore($order->getStore());

            $magentoQuote = Mage::getModel('M2ePro/Magento_Quote', $proxy);
            $magentoQuote->buildQuote();
            $magentoQuote->getQuote()->setIsActive(false)->save();

            $shippingAddressData = $magentoQuote->getQuote()->getShippingAddress()->getData();
            unset(
                $shippingAddressData['cached_items_all'],
                $shippingAddressData['cached_items_nominal'],
                $shippingAddressData['cached_items_nonnominal']
            );
            $billingAddressData  = $magentoQuote->getQuote()->getBillingAddress()->getData();
            unset(
                $billingAddressData['cached_items_all'],
                $billingAddressData['cached_items_nominal'],
                $billingAddressData['cached_items_nonnominal']
            );

            $quote = $magentoQuote->getQuote();

            $resultHtml = '';

            $resultHtml .= '<pre><b>Grand Total:</b> ' .$quote->getGrandTotal(). '<br>';
            $resultHtml .= '<pre><b>Shipping Amount:</b> ' .$quote->getShippingAddress()->getShippingAmount(). '<br>';

            $resultHtml .= '<pre><b>Quote Data:</b> ' .print_r($quote->getData(), true). '<br>';
            $resultHtml .= '<pre><b>Shipping Address Data:</b> ' .print_r($shippingAddressData, true). '<br>';
            $resultHtml .= '<pre><b>Billing Address Data:</b> ' .print_r($billingAddressData, true). '<br>';

            return $this->getResponse()->setBody($resultHtml);
        }

        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

        return $this->getResponse()->setBody(
            <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Order ID: </label>
        <input name="order_id" style="width: 200px;" required>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Build</button>
    </div>

</form>
HTML
        );
    }

    //########################################

    /**
     * @title "Search Troubles With Parallel Execution"
     * @description "By operation history table"
     * @new_line
     */
    public function searchTroublesWithParallelExecutionAction()
    {
        if (!$this->getRequest()->getParam('print')) {
            $formKey = Mage::getSingleton('core/session')->getFormKey();
            $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

            $collection = Mage::getModel('M2ePro/OperationHistory')->getCollection();
            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns(array('nick'));
            $collection->getSelect()->order('nick ASC');
            $collection->getSelect()->distinct();

            $optionsHtml = '';
            foreach ($collection->getItems() as $item) {
                $optionsHtml .= <<<HTML
<option value="{$item->getData('nick')}">{$item->getData('nick')}</option>
HTML;
            }

            $html = <<<HTML
<form method="get" enctype="multipart/form-data" action="{$actionUrl}">

    <div style="margin: 5px 0; width: 400px;">
        <label style="width: 170px; display: inline-block;">Search by nick: </label>
        <select name="nick" style="width: 200px;" required>
            <option value="" style="display: none;"></option>
            {$optionsHtml}
        </select>
    </div>

    <input name="form_key" value="{$formKey}" type="hidden" />
    <input name="print" value="1" type="hidden" />

    <div style="margin: 10px 0; width: 365px; text-align: right;">
        <button type="submit">Search</button>
    </div>

</form>
HTML;
            return $this->getResponse()->setBody($html);
        }

        $searchByNick = (string)$this->getRequest()->getParam('nick');

        $collection = Mage::getModel('M2ePro/OperationHistory')->getCollection();
        $collection->addFieldToFilter('nick', $searchByNick);
        $collection->getSelect()->order('id ASC');

        $results = array();
        $prevItem = null;

        foreach ($collection->getItems() as $item) {
            /** @var Ess_M2ePro_Model_OperationHistory $item */
            /** @var Ess_M2ePro_Model_OperationHistory $prevItem */

            if ($item->getData('end_date') === null) {
                continue;
            }

            if ($prevItem === null) {
                $prevItem = $item;
                continue;
            }

            $prevEnd   = new DateTime($prevItem->getData('end_date'), new \DateTimeZone('UTC'));
            $currStart = new DateTime($item->getData('start_date'), new \DateTimeZone('UTC'));

            if ($currStart->getTimeStamp() < $prevEnd->getTimeStamp()) {
                $results[$item->getId().'##'.$prevItem->getId()] = array(
                    'curr' => array(
                        'id'    => $item->getId(),
                        'start' => $item->getData('start_date'),
                        'end'   => $item->getData('end_date')
                    ),
                    'prev' => array(
                        'id'    => $prevItem->getId(),
                        'start' => $prevItem->getData('start_date'),
                        'end'   => $prevItem->getData('end_date')
                    ),
                );
            }

            $prevItem = $item;
        }

        if (empty($results)) {
            return $this->getResponse()->setBody(
                $this->getEmptyResultsHtml(
                    'There are no troubles with a parallel work of crons.'
                )
            );
        }

        $tableContent = <<<HTML
<tr>
    <th>Num</th>
    <th>Type</th>
    <th>ID</th>
    <th>Started</th>
    <th>Finished</th>
    <th>Total</th>
    <th>Delay</th>
</tr>
HTML;
        $index = 1;
        $results = array_reverse($results, true);

        foreach ($results as $key => $row) {
            $currStart = new \DateTime($row['curr']['start'], new \DateTimeZone('UTC'));
            $currEnd   = new \DateTime($row['curr']['end'], new \DateTimeZone('UTC'));
            $currTime = $currEnd->diff($currStart);
            $currTime = $currTime->format('%H:%I:%S');

            $currUrlUp = $this->getUrl(
                '*/adminhtml_controlPanel_database/showOperationHistoryExecutionTreeUp',
                array('operation_history_id' => $row['curr']['id'])
            );
            $currUrlDown = $this->getUrl(
                '*/adminhtml_controlPanel_database/showOperationHistoryExecutionTreeDown',
                array('operation_history_id' => $row['curr']['id'])
            );

            $prevStart = new \DateTime($row['prev']['start'], new \DateTimeZone('UTC'));
            $prevEnd   = new \DateTime($row['prev']['end'], new \DateTimeZone('UTC'));
            $prevTime = $prevEnd->diff($prevStart);
            $prevTime = $prevTime->format('%H:%I:%S');

            $prevUrlUp = $this->getUrl(
                '*/adminhtml_controlPanel_database/showOperationHistoryExecutionTreeUp',
                array('operation_history_id' => $row['prev']['id'])
            );
            $prevUrlDown = $this->getUrl(
                '*/adminhtml_controlPanel_database/showOperationHistoryExecutionTreeDown',
                array('operation_history_id' => $row['prev']['id'])
            );

            $delayTime = $currStart->diff($prevStart);
            $delayTime = $delayTime->format('%H:%I:%S');

            $tableContent .= <<<HTML
<tr>
    <td rowspan="2">{$index}</td>
    <td>Previous</td>
    <td>
        {$row['prev']['id']}&nbsp;
        <a style="color: green;" href="{$prevUrlUp}" target="_blank"><span>&uarr;</span></a>&nbsp;
        <a style="color: green;" href="{$prevUrlDown}" target="_blank"><span>&darr;</span></a>
    </td>
    <td><span>{$row['prev']['start']}</span></td>
    <td><span>{$row['prev']['end']}</span></td>
    <td><span>{$prevTime}</span></td>
    <td rowspan="2"><span>{$delayTime}</span>
</tr>
<tr>
    <td>Current</td>
    <td>
        {$row['curr']['id']}&nbsp;
        <a style="color: green;" href="{$currUrlUp}" target="_blank"><span>&uarr;</span></a>&nbsp;&nbsp;
        <a style="color: green;" href="{$currUrlDown}" target="_blank"><span>&darr;</span></a>
        </td>
    <td><span>{$row['curr']['start']}</span></td>
    <td><span>{$row['curr']['end']}</span></td>
    <td><span>{$currTime}</span></td>
</tr>
HTML;
            $index++;
        }

        $html = $this->getStyleHtml() . <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Parallel work of [{$searchByNick}]
            <span style="color: #808080; font-size: 15px;">(#count# entries)</span>
        </h2>
        <br/>
        <table class="grid" cellpadding="0" cellspacing="0">
            {$tableContent}
        </table>
    </body>
</html>
HTML;
        return $this->getResponse()->setBody(str_replace('#count#', count($results), $html));
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
