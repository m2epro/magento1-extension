<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Currency
{
    //####################################

    public function isBase($currencyCode, $store)
    {
        $baseCurrency = Mage::app()->getStore($store)->getBaseCurrencyCode();
        return $baseCurrency == $currencyCode;
    }

    public function isAllowed($currencyCode, $store)
    {
        $allowedCurrencies = Mage::app()->getStore($store)->getAvailableCurrencyCodes();
        return in_array($currencyCode, $allowedCurrencies);
    }

    public function getConvertRateFromBase($currencyCode, $store, $precision = 2)
    {
        if (!$this->isAllowed($currencyCode, $store)) {
            return 0;
        }

        $precision = (int)$precision;

        if ($precision <= 0) {
            $precision = 2;
        }

        $rate = (float)Mage::app()->getStore($store)->getBaseCurrency()->getRate($currencyCode);

        return round($rate, $precision);
    }

    public function isConvertible($currencyCode, $store)
    {
        if ($this->isBase($currencyCode, $store)
            || !$this->isAllowed($currencyCode, $store)
            || $this->getConvertRateFromBase($currencyCode, $store) == 0
        ) {
            return false;
        }

        return true;
    }

    public function convertPrice($price, $currencyCode, $store)
    {
        if (!$this->isConvertible($currencyCode, $store)) {
            return $price;
        }

        return Mage::app()->getStore($store)->getBaseCurrency()->convert($price, $currencyCode);
    }

    public function convertPriceToBaseCurrency($price, $currencyCode, $store)
    {
        $store = Mage::app()->getStore($store);

        if (in_array($currencyCode, $store->getAvailableCurrencyCodes(true))) {
            $currencyConvertRate = $store->getBaseCurrency()->getRate($currencyCode);
            $currencyConvertRate == 0 && $currencyConvertRate = 1;
            $price = $price / $currencyConvertRate;
        }

        return $price;
    }

    public function formatPrice($currencyName, $priceValue)
    {
        return Mage::app()->getLocale()->currency($currencyName)->toCurrency($priceValue);
    }

    //####################################
}