<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Tax_Rule_Builder
{
    const TAX_CLASS_NAME_PRODUCT   = 'M2E Pro Product Tax Class';
    const TAX_CLASS_NAME_CUSTOMER  = 'M2E Pro Customer Tax Class';
    const TAX_CLASS_NAME_SHIPPING  = 'M2E Pro Shipping Tax Class';

    const TAX_RATE_CODE_PRODUCT    = 'M2E Pro Tax Rate';
    const TAX_RULE_CODE_PRODUCT    = 'M2E Pro Tax Rule';

    const TAX_RATE_CODE_SHIPPING   = 'M2E Pro Shipping Tax Rate';
    const TAX_RULE_CODE_SHIPPING   = 'M2E Pro Shipping Tax Rule';

    /** @var $rule Mage_Tax_Model_Calculation_Rule */
    private $rule = NULL;

    //########################################

    public function getRule()
    {
        return $this->rule;
    }

    //########################################

    public function buildProductTaxRule($rate = 0, $countryId, $customerTaxClassId = NULL)
    {
        $this->buildTaxRule(
            $rate,
            $countryId,
            $customerTaxClassId,
            self::TAX_RATE_CODE_PRODUCT,
            self::TAX_RULE_CODE_PRODUCT,
            self::TAX_CLASS_NAME_PRODUCT
        );
    }

    public function buildShippingTaxRule($rate = 0, $countryId, $customerTaxClassId = NULL)
    {
        $this->buildTaxRule(
            $rate,
            $countryId,
            $customerTaxClassId,
            self::TAX_RATE_CODE_SHIPPING,
            self::TAX_RULE_CODE_SHIPPING,
            self::TAX_CLASS_NAME_SHIPPING
        );
    }

    private function buildTaxRule(
        $rate = 0,
        $countryId,
        $customerTaxClassId = NULL,
        $taxRateCode,
        $taxRuleCode,
        $taxClassName
    )
    {
        // Init product tax class
        // ---------------------------------------
        $productTaxClass = Mage::getModel('tax/class')->getCollection()
            ->addFieldToFilter('class_name', $taxClassName)
            ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
            ->getFirstItem();

        if (is_null($productTaxClass->getId())) {
            $productTaxClass->setClassName($taxClassName)
                ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
            $productTaxClass->save();
        }
        // ---------------------------------------

        // Init customer tax class
        // ---------------------------------------
        if (is_null($customerTaxClassId)) {
            $customerTaxClass = Mage::getModel('tax/class')->getCollection()
                ->addFieldToFilter('class_name', self::TAX_CLASS_NAME_CUSTOMER)
                ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
                ->getFirstItem();

            if (is_null($customerTaxClass->getId())) {
                $customerTaxClass->setClassName(self::TAX_CLASS_NAME_CUSTOMER)
                    ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
                $customerTaxClass->save();
            }

            $customerTaxClassId = $customerTaxClass->getId();
        }
        // ---------------------------------------

        // Init tax rate
        // ---------------------------------------
        $taxCalculationRate = Mage::getModel('tax/calculation_rate')->load($taxRateCode, 'code');

        $taxCalculationRate->setCode($taxRateCode)
            ->setRate((float)$rate)
            ->setTaxCountryId((string)$countryId)
            ->setTaxPostcode('*')
            ->setTaxRegionId(0);
        $taxCalculationRate->save();
        // ---------------------------------------

        // Combine tax classes and tax rate in tax rule
        // ---------------------------------------
        $this->rule = Mage::getModel('tax/calculation_rule')->load($taxRuleCode, 'code');

        $this->rule->setCode($taxRuleCode)
            ->setTaxCustomerClass(array($customerTaxClassId))
            ->setTaxProductClass(array($productTaxClass->getId()))
            ->setTaxRate(array($taxCalculationRate->getId()));
        $this->rule->save();
        // ---------------------------------------
    }

    //########################################
}