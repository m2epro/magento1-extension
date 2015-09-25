<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_MigrationToV6 extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'intro',
        'sellingFormatCurrencies',
        'notifications'
    );

    protected static $currencyPreparedData = array();

    // ########################################

    public function getNick()
    {
        return Ess_M2ePro_Helper_Module::WIZARD_MIGRATION_NICK;
    }

    // ########################################

    public function removeEmptySteps()
    {
        $this->prepareCurrencyData();

        if (empty(self::$currencyPreparedData)) {
            $currencyStepIndex = array_search('sellingFormatCurrencies', $this->steps);

            if ($currencyStepIndex !== false) {
                unset($this->steps[$currencyStepIndex]);
                $this->steps = array_values($this->steps);
            }
        }
    }

    // ########################################

    public function getStepTitles()
    {
        return array(
            'intro' => Mage::helper('M2ePro')->__('Review'),
            'sellingFormatCurrencies' => Mage::helper('M2ePro')->__('Currency Conversion'),
            'notifications' => Mage::helper('M2ePro')->__('Notifications'),
        );
    }

    // ########################################

    public function getCurrencyPreparedData($componentMode)
    {
        if (empty(self::$currencyPreparedData)) {
            $this->prepareCurrencyData();
        }

        return isset(self::$currencyPreparedData[$componentMode])
            ? self::$currencyPreparedData[$componentMode] : array();
    }

    public function getMigrationData($group, $componentMode)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $migrationTable = Mage::getSingleton('core/resource')->getTableName('m2epro_migration_v6');

        $select = $connRead->select()
            ->from($migrationTable)
            ->where('`component` = \''.$componentMode.'\'')
            ->where('`group` = \''.$group.'\'');

        $fetchResult = $connRead->fetchAll($select);
        $data = array_shift($fetchResult);

        if (empty($data['data'])) {
            return array();
        }

        return json_decode($data['data'], true);
    }

    // ########################################

    protected function prepareCurrencyData()
    {
        $components = Mage::helper('M2ePro/Component')->getComponents();

        foreach ($components as $componentMode) {
            if (isset(self::$currencyPreparedData[$componentMode])) {
                continue;
            }

            $sellingFormatTemplates = Mage::helper('M2ePro/Component')
                ->getComponentCollection($componentMode, 'Template_SellingFormat')
                ->getItems();

            if (empty($sellingFormatTemplates)) {
                continue;
            }

            foreach ($sellingFormatTemplates as $template) {
                $getReceivedPricesMethod = 'get' . ucfirst($componentMode) . 'ReceivedPrices';
                $receivedPrices = $this->$getReceivedPricesMethod($template->getChildObject());
                if (empty($receivedPrices)) {
                    continue;
                }

                $currencyPairs = $this->getUniqueUsedCurrencyPairs($template->getId(), $componentMode);
                if (empty($currencyPairs)) {
                    continue;
                }

                $rates = array();
                foreach ($currencyPairs as $currencyPair) {
                    $rates[] = $currencyPair['rate'];
                }

                self::$currencyPreparedData[$componentMode][$template->getId()] = array(
                    'id' => $template->getId(),
                    'title' => $template->getTitle(),
                    'currencies' => $currencyPairs,
                    'prices' => $receivedPrices,
                    'rates' => $rates,
                );
            }
        }
    }

    // ---------------------------------------------

    protected function getEbayReceivedPrices(Ess_M2ePro_Model_Ebay_Template_SellingFormat $template)
    {
        if ($template->isListingTypeFixed()) {
            $priceIds = array(
                'buyitnow_price'
            );
        } else {
            $priceIds = array(
                'start_price',
                'reserve_price',
                'buyitnow_price',
            );
        }

        $migrationData = $this->getMigrationData('selling_format_currencies', Ess_M2ePro_Helper_Component_Ebay::NICK);
        if (!isset($migrationData[(int)$template->getId()])) {
            return array();
        }

        $receivedPrices = array();
        foreach ($priceIds as $priceId) {

            if ($template->getData($priceId . '_mode') ==
                Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
                continue;
            }

            $priceData = array(
                'coefficient' => $template->getData($priceId . '_coefficient')
            );

            if (isset($migrationData[(int)$template->getId()][$priceId . '_coefficient'])) {
                $priceData = array_merge(
                    $priceData, $migrationData[(int)$template->getId()][$priceId . '_coefficient']
                );
            }

            $receivedPrices[$priceId] = $priceData;
        }

        return $receivedPrices;
    }

    protected function getAmazonReceivedPrices(Ess_M2ePro_Model_Amazon_Template_SellingFormat $template)
    {
        $priceIds = array(
            'price',
            'sale_price'
        );

        $migrationData = $this->getMigrationData('selling_format_currencies', Ess_M2ePro_Helper_Component_Amazon::NICK);

        if (!isset($migrationData[(int)$template->getId()])) {
            return array();
        }

        $receivedPrices = array();
        foreach ($priceIds as $priceId) {

            if ($template->getData($priceId . '_mode') ==
                Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
                continue;
            }

            $receivedPrices[$priceId] = $template->getData($priceId . '_coefficient');
        }

        return $receivedPrices;
    }

    protected function getBuyReceivedPrices(Ess_M2ePro_Model_Buy_Template_SellingFormat $template)
    {
        $receivedPrices = array();

        $migrationData = $this->getMigrationData('selling_format_currencies', Ess_M2ePro_Helper_Component_Buy::NICK);
        if (!isset($migrationData[(int)$template->getId()])) {
            return array();
        }

        if (!$template->isPriceModeAttribute()) {
            $receivedPrices['price'] = $template->getPriceCoefficient();
        }

        return $receivedPrices;
    }

    // ---------------------------------------------

    protected function getEbayMarketplaceCurrency($marketplaceId)
    {
        return Mage::getModel('M2ePro/Ebay_Marketplace')->load((int)$marketplaceId)->getCurrency();
    }

    protected function getAmazonMarketplaceCurrency($marketplaceId)
    {
        return Mage::getModel('M2ePro/Amazon_Marketplace')->load((int)$marketplaceId)->getDefaultCurrency();
    }

    protected function getBuyMarketplaceCurrency($marketplaceId)
    {
        return 'USD';
    }

    // ---------------------------------------------

    protected function getUniqueUsedCurrencyPairs($templateId, $componentMode)
    {
        $listings = Mage::helper('M2ePro/Component')
            ->getComponentCollection($componentMode, 'Listing')
            ->addFieldToFilter('template_selling_format_id', (int)$templateId)
            ->getItems();

        if (empty($listings)) {
            return array();
        }

        $pairs = array();
        $tempPairs = array();
        $needConversion = false;
        $currencyModel = Mage::getModel('M2ePro/Currency');
        foreach ($listings as $listing) {
            $getMarketplaceCurrencyMethod = 'get' . ucfirst($componentMode) . 'MarketplaceCurrency';
            $marketplaceCurrencies = (array)$this->$getMarketplaceCurrencyMethod($listing->getMarketplaceId());

            $storeCurrency = Mage::app()->getStore($listing->getStoreId())->getBaseCurrencyCode();

            foreach ($marketplaceCurrencies as $marketplaceCurrency) {
                if (isset($tempPairs[$storeCurrency.'-'.$marketplaceCurrency])) {
                    continue;
                }

                $tempPairs[$storeCurrency.'-'.$marketplaceCurrency] = true;

                $rate = 0;
                if ($currencyModel->isConvertible($marketplaceCurrency, $listing->getStoreId())) {
                    $needConversion = true;
                    $rate = $currencyModel->getConvertRateFromBase($marketplaceCurrency, $listing->getStoreId());
                }

                $pairs[] = array(
                    'marketplace' => $marketplaceCurrency,
                    'store' => $storeCurrency,
                    'rate' => $rate,
                );
            }
        }

        if (!$needConversion) {
            return array();
        }

        return $pairs;
    }

    // ########################################
}