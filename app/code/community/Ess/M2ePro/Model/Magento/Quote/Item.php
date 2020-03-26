<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Quote_Item
{
    /** @var Mage_Sales_Model_Quote */
    protected $_quote = null;

    /** @var Ess_M2ePro_Model_Order_Item_Proxy */
    protected $_proxyItem = null;

    /** @var Mage_Catalog_Model_Product */
    protected $_product = null;

    /** @var Mage_GiftMessage_Model_Message */
    protected $_giftMessage = null;

    //########################################

    public function init(Mage_Sales_Model_Quote $quote, Ess_M2ePro_Model_Order_Item_Proxy $proxyItem)
    {
        $this->_quote     = $quote;
        $this->_proxyItem = $proxyItem;

        return $this;
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product|null
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getProduct()
    {
        if ($this->_product !== null) {
            return $this->_product;
        }

        if ($this->_proxyItem->getMagentoProduct()->isGroupedType()) {
            $this->_product = $this->getAssociatedGroupedProduct();

            if ($this->_product === null) {
                throw new Ess_M2ePro_Model_Exception('There are no associated Products found for Grouped Product.');
            }
        } else {
            $this->_product = $this->_proxyItem->getProduct();

            if ($this->_proxyItem->getMagentoProduct()->isBundleType()) {
                $this->_product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_PARENT);
            }
        }

        // tax class id should be set before price calculation
        $this->_product->setTaxClassId($this->getProductTaxClassId());

        return $this->_product;
    }

    // ---------------------------------------

    protected function getAssociatedGroupedProduct()
    {
        $associatedProducts = $this->_proxyItem->getAssociatedProducts();
        $associatedProductId = reset($associatedProducts);

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->_quote->getStoreId())
            ->load($associatedProductId);

        return $product->getId() ? $product : null;
    }

    //########################################

    protected function getProductTaxClassId()
    {
        $proxyOrder = $this->_proxyItem->getProxyOrder();
        $itemTaxRate = $this->_proxyItem->getTaxRate();
        $isOrderHasTax = $this->_proxyItem->getProxyOrder()->hasTax();
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->hasRatesForCountry($this->_quote->getShippingAddress()->getCountryId());
        $calculationBasedOnOrigin = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->isCalculationBasedOnOrigin($this->_quote->getStore());

        if ($proxyOrder->isTaxModeNone()
            || ($proxyOrder->isTaxModeChannel() && $itemTaxRate <= 0)
            || ($proxyOrder->isTaxModeMagento() && !$hasRatesForCountry && !$calculationBasedOnOrigin)
            || ($proxyOrder->isTaxModeMixed() && $itemTaxRate <= 0 && $isOrderHasTax)
        ) {
            return Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE;
        }

        if ($proxyOrder->isTaxModeMagento()
            || $itemTaxRate <= 0
            || $itemTaxRate == $this->getProductTaxRate()
        ) {
            return $this->getProduct()->getTaxClassId();
        }

        // Create tax rule according to channel tax rate
        // ---------------------------------------
        /** @var $taxRuleBuilder Ess_M2ePro_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('M2ePro/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildProductTaxRule(
            $itemTaxRate,
            $this->_quote->getShippingAddress()->getCountryId(),
            $this->_quote->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // ---------------------------------------

        return array_shift($productTaxClasses);
    }

    protected function getProductTaxRate()
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        $request = $taxCalculator->getRateRequest(
            $this->_quote->getShippingAddress(),
            $this->_quote->getBillingAddress(),
            $this->_quote->getCustomerTaxClassId(),
            $this->_quote->getStore()
        );
        $request->setProductClassId($this->getProduct()->getTaxClassId());

        return $taxCalculator->getRate($request);
    }

    //########################################

    public function getRequest()
    {
        $request = new Varien_Object();
        $request->setQty($this->_proxyItem->getQty());

        // grouped product doesn't have options
        if ($this->_proxyItem->getMagentoProduct()->isGroupedType()) {
            return $request;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->getProduct());
        $options = $this->_proxyItem->getOptions();

        if (empty($options)) {
            return $request;
        }

        if ($magentoProduct->isSimpleType()) {
            $request->setOptions($options);
        } else if ($magentoProduct->isBundleType()) {
            $request->setBundleOption($options);
        } else if ($magentoProduct->isConfigurableType()) {
            $request->setSuperAttribute($options);
        } else if ($magentoProduct->isDownloadableType()) {
            $request->setLinks($options);
        }

        return $request;
    }

    //########################################

    public function getGiftMessageId()
    {
        $giftMessage = $this->getGiftMessage();

        return $giftMessage ? $giftMessage->getId() : null;
    }

    public function getGiftMessage()
    {
        if ($this->_giftMessage !== null) {
            return $this->_giftMessage;
        }

        $giftMessageData = $this->_proxyItem->getGiftMessage();

        if (!is_array($giftMessageData)) {
            return null;
        }

        $giftMessageData['customer_id'] = (int)$this->_quote->getCustomerId();
        /** @var $giftMessage Mage_GiftMessage_Model_Message */
        $giftMessage = Mage::getModel('giftmessage/message')->addData($giftMessageData);

        if ($giftMessage->isMessageEmpty()) {
            return null;
        }

        $this->_giftMessage = $giftMessage->save();

        return $this->_giftMessage;
    }

    //########################################

    public function getAdditionalData(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        return Mage::helper('M2ePro')->serialize(
            array_merge(
                Mage::helper('M2ePro')->unserialize($quoteItem->getAdditionalData()),
                $this->_proxyItem->getAdditionalData()
            )
        );
    }

    //########################################
}
