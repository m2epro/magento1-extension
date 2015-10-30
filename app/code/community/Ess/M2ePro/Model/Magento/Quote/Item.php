<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Quote_Item
{
    /** @var Mage_Sales_Model_Quote */
    private $quote = NULL;

    /** @var Ess_M2ePro_Model_Order_Item_Proxy */
    private $proxyItem = NULL;

    /** @var Mage_Catalog_Model_Product */
    private $product = NULL;

    /** @var Mage_GiftMessage_Model_Message */
    private $giftMessage = NULL;

    //########################################

    public function init(Mage_Sales_Model_Quote $quote, Ess_M2ePro_Model_Order_Item_Proxy $proxyItem)
    {
        $this->quote = $quote;
        $this->proxyItem = $proxyItem;

        return $this;
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product|null
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getProduct()
    {
        if (!is_null($this->product)) {
            return $this->product;
        }

        if ($this->proxyItem->getMagentoProduct()->isGroupedType()) {
            $this->product = $this->getAssociatedGroupedProduct();

            if (is_null($this->product)) {
                throw new Ess_M2ePro_Model_Exception('There is no associated Products found for Grouped Product.');
            }
        } else {
            $this->product = $this->proxyItem->getProduct();

            if ($this->proxyItem->getMagentoProduct()->isBundleType()) {
                $this->product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_PARENT);
            }
        }

        // tax class id should be set before price calculation
        $this->product->setTaxClassId($this->getProductTaxClassId());

        return $this->product;
    }

    // ---------------------------------------

    private function getAssociatedGroupedProduct()
    {
        $associatedProducts = $this->proxyItem->getAssociatedProducts();
        $associatedProductId = reset($associatedProducts);

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->quote->getStoreId())
            ->load($associatedProductId);

        return $product->getId() ? $product : null;
    }

    //########################################

    private function getProductTaxClassId()
    {
        $proxyOrder = $this->proxyItem->getProxyOrder();
        $itemTaxRate = $this->proxyItem->getTaxRate();
        $isOrderHasTax = $this->proxyItem->getProxyOrder()->hasTax();
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->hasRatesForCountry($this->quote->getShippingAddress()->getCountryId());
        $calculationBasedOnOrigin = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->isCalculationBasedOnOrigin($this->quote->getStore());

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
        $taxRuleBuilder->buildTaxRule(
            $itemTaxRate,
            $this->quote->getShippingAddress()->getCountryId(),
            $this->quote->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // ---------------------------------------

        return array_shift($productTaxClasses);
    }

    private function getProductTaxRate()
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        $request = $taxCalculator->getRateRequest(
            $this->quote->getShippingAddress(),
            $this->quote->getBillingAddress(),
            $this->quote->getCustomerTaxClassId(),
            $this->quote->getStore()
        );
        $request->setProductClassId($this->getProduct()->getTaxClassId());

        return $taxCalculator->getRate($request);
    }

    //########################################

    public function getRequest()
    {
        $request = new Varien_Object();
        $request->setQty($this->proxyItem->getQty());

        // grouped and downloadable products doesn't have options
        if ($this->proxyItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
            $this->proxyItem->getProduct()->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            return $request;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->getProduct());
        $options = $this->proxyItem->getOptions();

        if (empty($options)) {
            return $request;
        }

        if ($magentoProduct->isSimpleType()) {
            $request->setOptions($options);
        } else if ($magentoProduct->isBundleType()) {
            $request->setBundleOption($options);
        } else if ($magentoProduct->isConfigurableType()) {
            $request->setSuperAttribute($options);
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
        if (!is_null($this->giftMessage)) {
            return $this->giftMessage;
        }

        $giftMessageData = $this->proxyItem->getGiftMessage();

        if (!is_array($giftMessageData)) {
            return NULL;
        }

        $giftMessageData['customer_id'] = (int)$this->quote->getCustomerId();
        /** @var $giftMessage Mage_GiftMessage_Model_Message */
        $giftMessage = Mage::getModel('giftmessage/message')->addData($giftMessageData);

        if ($giftMessage->isMessageEmpty()) {
            return NULL;
        }

        $this->giftMessage = $giftMessage->save();

        return $this->giftMessage;
    }

    //########################################

    public function getAdditionalData(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $additionalData = $this->proxyItem->getAdditionalData();

        $existAdditionalData = $quoteItem->getAdditionalData();
        $existAdditionalData = is_string($existAdditionalData) ? @unserialize($existAdditionalData) : array();

        return serialize(array_merge((array)$existAdditionalData, $additionalData));
    }

    //########################################
}