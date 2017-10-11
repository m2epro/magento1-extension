<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Quote_Total_WasteRecyclingFee extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    const TITLE = 'Waste Recycling Fee';

    /**
     * @var Mage_Weee_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var Mage_Tax_Model_Config
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_isTaxAffected;

    //########################################

    public function __construct()
    {
        $this->setCode('waste_recycling_fee');
        $this->_helper = Mage::helper('weee');
        $this->_config = Mage::getSingleton('tax/config');
    }

    //########################################

    /**
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Ess_M2ePro_Model_Magento_Quote_Total_WasteRecyclingFee
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if (!$address->getQuote()->getIsM2eProQuote() || !$address->getQuote()->getNeedProcessChannelTaxes()) {
            return $this;
        }

        $this->clearQuoteItemsCache($address);

        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);
        $this->_isTaxAffected = false;
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $address->setAppliedTaxesReset(true);
        $address->setAppliedTaxes(array());

        $this->_store = $address->getQuote()->getStore();

        foreach ($items as $item) {
            if (!$item->getWasteRecyclingFee() || $item->getParentItemId()) {
                continue;
            }
            $this->_resetItemData($item);
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_resetItemData($child);
                    $this->_process($address, $child);
                }
            } else {
                $this->_process($address, $item);
            }
        }

        if ($this->_isTaxAffected) {
            $address->unsSubtotalInclTax();
            $address->unsBaseSubtotalInclTax();
        }

        $this->clearQuoteItemsCache($address);

        return $this;
    }

    //########################################

    /**
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Ess_M2ePro_Model_Magento_Quote_Total_WasteRecyclingFee
     */
    protected function _process(Mage_Sales_Model_Quote_Address $address, $item)
    {
        $applied = array();
        $productTaxes = array();

        $totalExclTaxValue = 0;
        $baseTotalExclTaxValue = 0;
        $totalExclTaxRowValue = 0;
        $baseTotalExclTaxRowValue = 0;

        $baseValue = $item->getWasteRecyclingFee();
        $baseValueExclTax = $baseValue;

        $value = $this->_store->convertPrice($baseValue);
        $rowValue = $value * $item->getTotalQty();
        $baseRowValue = $baseValue * $item->getTotalQty();

        //Get the values excluding tax
        $valueExclTax = $this->_store->convertPrice($baseValueExclTax);
        $rowValueExclTax = $valueExclTax * $item->getTotalQty();
        $baseRowValueExclTax = $baseValueExclTax * $item->getTotalQty();

        //Calculate the Wee without tax
        $totalExclTaxValue += $valueExclTax;
        $baseTotalExclTaxValue += $baseValueExclTax;
        $totalExclTaxRowValue += $rowValueExclTax;
        $baseTotalExclTaxRowValue += $baseRowValueExclTax;

        /*
         * Note: including Tax does not necessarily mean it includes all the tax
         * *_incl_tax only holds the tax associated with Tax included products
         */

        $productTaxes[] = array(
            'title' => self::TITLE,
            'base_amount' => $baseValueExclTax,
            'amount' => $valueExclTax,
            'row_amount' => $rowValueExclTax,
            'base_row_amount' => $baseRowValueExclTax,
            /**
             * Tax value can't be presented as include/exclude tax
             */
            'base_amount_incl_tax' => $baseValue,
            'amount_incl_tax' => $value,
            'row_amount_incl_tax' => $rowValue,
            'base_row_amount_incl_tax' => $baseRowValue,
        );

        $applied[] = array(
            'id' => 'waste_recycling_fee',
            'percent' => null,
            'hidden' => $this->_helper->includeInSubtotal($this->_store),
            'rates' => array(array(
                'base_real_amount' => $baseRowValue,
                'base_amount' => $baseRowValue,
                'amount' => $rowValue,
                'code' => 'waste_recycling_fee',
                'title' => self::TITLE,
                'percent' => null,
                'position' => 1,
                'priority' => -1000 + 1,
            ))
        );

        //We set the TAX exclusive value
        $item->setWeeeTaxAppliedAmount($totalExclTaxValue);
        $item->setBaseWeeeTaxAppliedAmount($baseTotalExclTaxValue);
        $item->setWeeeTaxAppliedRowAmount($totalExclTaxRowValue);
        $item->setBaseWeeeTaxAppliedRowAmount($baseTotalExclTaxRowValue);

        $this->_processTaxSettings(
            $item, $totalExclTaxValue, $baseTotalExclTaxValue, $totalExclTaxRowValue, $baseTotalExclTaxRowValue
        )
            ->_processTotalAmount($address, $totalExclTaxRowValue, $baseTotalExclTaxRowValue);

        $this->_helper->setApplied($item, array_merge($this->_helper->getApplied($item), $productTaxes));
        if ($applied) {
            $this->_saveAppliedTaxes($address, $applied,
                $item->getWeeeTaxAppliedAmount(),
                $item->getBaseWeeeTaxAppliedAmount(),
                null
            );
        }
    }

    /**
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $value
     * @param   float $baseValue
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  Ess_M2ePro_Model_Magento_Quote_Total_WasteRecyclingFee
     */
    protected function _processTaxSettings($item, $value, $baseValue, $rowValue, $baseRowValue)
    {
        if ($rowValue) {
            $this->_isTaxAffected = true;
            $item->unsRowTotalInclTax()
                ->unsBaseRowTotalInclTax()
                ->unsPriceInclTax()
                ->unsBasePriceInclTax();
        }
        $item->setExtraTaxableAmount($value)
            ->setBaseExtraTaxableAmount($baseValue)
            ->setExtraRowTaxableAmount($rowValue)
            ->setBaseExtraRowTaxableAmount($baseRowValue);
        return $this;
    }

    /**
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  Ess_M2ePro_Model_Magento_Quote_Total_WasteRecyclingFee
     */
    protected function _processTotalAmount($address, $rowValue, $baseRowValue)
    {
        $address->setExtraTaxAmount($address->getExtraTaxAmount() + $rowValue);
        $address->setBaseExtraTaxAmount($address->getBaseExtraTaxAmount() + $baseRowValue);

        return $this;
    }

    /**
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     */
    protected function _resetItemData($item)
    {
        $this->_helper->setApplied($item, array());

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmount(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    private function clearQuoteItemsCache($address)
    {
        /** @var $address Mage_Sales_Model_Quote_Address */
        $address->unsetData('cached_items_all');
        $address->unsetData('cached_items_nominal');
        $address->unsetData('cached_items_nonnominal');
    }

    //########################################

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }

    public function processConfigArray($config, $store)
    {
        return $config;
    }

    public function getLabel()
    {
        return '';
    }

    //########################################
}
