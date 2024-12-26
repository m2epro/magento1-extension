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

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_SkuResolver $skuResolver */
                $skuResolver = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Type_List_SkuResolver');
                $skuResolver->setListingProduct($lp);

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request $request */
                $request = Mage::getModel("M2ePro/Walmart_Listing_Product_Action_Type_{$requestType}_Request");
                $request->setParams(array('sku' => $skuResolver->resolve()));
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
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active $checker */
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
     */
    public function getPrintOrderQuoteDataAction()
    {
        if ($this->getRequest()->getParam('print')) {

            /** @var Ess_M2ePro_Model_Order $order */
            $orderId = $this->getRequest()->getParam('order_id');
            $order =  Mage::helper('M2ePro/Component')->getUnknownObject('Order', $orderId);

            if (!$order->getId()) {
                $this->_getSession()->addError('Unable to load order instance.');
                $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
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
            $items = array();
            foreach ($quote->getAllItems() as $item) {
                $items[] = $item->getData();
            }

            return $this->getResponse()->setBody(print_r(json_decode(json_encode(array(
                'Grand Total'           => $quote->getGrandTotal(),
                'Shipping Amount'       => $quote->getShippingAddress()->getShippingAmount(),
                'Quote Data'            => $quote->getData(),
                'Shipping Address Data' => $shippingAddressData,
                'Billing Address Data'  => $billingAddressData,
                'Items'                 => $items
            )), true), true));
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
