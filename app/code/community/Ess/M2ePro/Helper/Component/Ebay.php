<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay extends Mage_Core_Helper_Abstract
{
    const NICK  = 'ebay';

    const MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK = 'ebay_marketplace_synchronization';

    const MARKETPLACE_US     = 1;
    const MARKETPLACE_CA     = 2;
    const MARKETPLACE_UK     = 3;
    const MARKETPLACE_AU     = 4;
    const MARKETPLACE_BE_FR  = 6;
    const MARKETPLACE_FR     = 7;
    const MARKETPLACE_DE     = 8;
    const MARKETPLACE_MOTORS = 9;
    const MARKETPLACE_IT     = 10;
    const MARKETPLACE_BE_NL  = 11;
    const MARKETPLACE_ES     = 13;
    const MARKETPLACE_IN     = 16;

    const LISTING_DURATION_GTC = 100;

    const VARIATION_OPTION_LABEL_MAX_LENGTH = 50;
    const VARIATION_SKU_MAX_LENGTH          = 80;
    const ITEM_SKU_MAX_LENGTH               = 50;

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('eBay');
    }

    public function getChannelTitle()
    {
        return Mage::helper('M2ePro')->__('eBay');
    }

    //########################################

    public function getHumanTitleByListingProductStatus($status)
    {
        $statuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
            Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => Mage::helper('M2ePro')->__('Listed (Hidden)'),
            Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
        );

        if (!isset($statuses[$status])) {
            return null;
        }

        return $statuses[$status];
    }

    //########################################

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'mode');
    }

    public function isObject($modelName, $value, $field = null)
    {
        $mode = Mage::helper('M2ePro/Component')->getComponentMode($modelName, $value, $field);
        return $mode !== null && $mode == self::NICK;
    }

    // ---------------------------------------

    public function getModel($modelName)
    {
        return Mage::helper('M2ePro/Component')->getComponentModel(self::NICK, $modelName);
    }

    public function getObject($modelName, $value, $field = null)
    {
        return Mage::helper('M2ePro/Component')->getComponentObject(self::NICK, $modelName, $value, $field);
    }

    public function getCachedObject($modelName, $value, $field = null, array $tags = array())
    {
        return Mage::helper('M2ePro/Component')->getCachedComponentObject(
            self::NICK, $modelName, $value, $field, $tags
        );
    }

    /**
     * @param $modelName
     * @return Ess_M2ePro_Model_Resource_Collection_Abstract
     */
    public function getCollection($modelName)
    {
        return $this->getModel($modelName)->getCollection();
    }

    //########################################

    public function getItemUrl(
        $ebayItemId,
        $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION,
        $marketplaceId = null
    ) {
        $marketplaceId = (int)$marketplaceId;
        if ($marketplaceId <= 0 || $marketplaceId == self::MARKETPLACE_MOTORS) {
            $marketplaceId = self::MARKETPLACE_US;
        }

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = $this->getCachedObject('Marketplace', $marketplaceId);

        return $accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX
            ? $this->getSandboxItemUrl($ebayItemId, $marketplace)
            : 'http://www.' . $marketplace->getUrl() . '/itm/'.(double)$ebayItemId;
    }

    protected function getSandboxItemUrl($ebayItemId, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $domainParts = explode('.', $marketplace->getUrl());

        switch ($marketplace->getId()) {
            case self::MARKETPLACE_US:
                $subDomain = '';
                break;
            case self::MARKETPLACE_BE_FR:
            case self::MARKETPLACE_BE_NL:
                $subDomain = reset($domainParts) . '.';
                break;
            default:
                $subDomain = end($domainParts) . '.';
        }

        return 'https://www.' . $subDomain . 'sandbox.ebay.com/itm/' . (double)$ebayItemId;
    }

    public function getMemberUrl($ebayMemberId, $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION)
    {
        $domain = 'ebay.com';
        if ($accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            $domain = 'sandbox.'.$domain;
        }

        return 'http://myworld.'.$domain.'/'.(string)$ebayMemberId;
    }

    //########################################

    public function isShowTaxCategory()
    {
        return (bool)Mage::helper('M2ePro/Component_Ebay_Configuration')
            ->getViewTemplateSellingFormatShowTaxCategory();
    }

    public function getAvailableDurations()
    {
        $helper = Mage::helper('M2ePro');

        return array(
            '1' => $helper->__('1 day'),
            '3' => $helper->__('3 days'),
            '5' => $helper->__('5 days'),
            '7' => $helper->__('7 days'),
            '10' => $helper->__('10 days'),
            '30' => $helper->__('30 days'),
            self::LISTING_DURATION_GTC => $helper->__('Good Till Cancelled'),
        );
    }

    public function getListingProductByEbayItem($ebayItem, $accountId)
    {
        // Get listing product
        // ---------------------------------------
        $readConnection = Mage::getResourceModel('core/config')->getReadConnection();

        $ebayItem  = $readConnection->quoteInto('?', $ebayItem);
        $accountId = $readConnection->quoteInto('?', $accountId);

        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('mei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            "(second_table.ebay_item_id = mei.id AND mei.item_id = {$ebayItem}
                                                 AND mei.account_id = {$accountId})",
            array()
        );
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    // ---------------------------------------

    public function getCurrencies()
    {
        return array(
            'AUD' => 'Australian Dollar',
            'GBP' => 'British Pound',
            'CAD' => 'Canadian Dollar',
            'CNY' => 'Chinese Renminbi',
            'EUR' => 'Euro',
            'HKD' => 'Hong Kong Dollar',
            'INR' => 'Indian Rupees',
            'MYR' => 'Malaysian Ringgit',
            'PHP' => 'Philippines Peso',
            'PLN' => 'Polish Zloty',
            'SGD' => 'Singapore Dollar',
            'SEK' => 'Sweden Krona',
            'CHF' => 'Swiss Franc',
            'TWD' => 'Taiwanese Dollar',
            'USD' => 'US Dollar',
        );
    }

    // ---------------------------------------

    public function getCarriers()
    {
        return array(
            'usps'  => 'USPS',
            'ups'   => 'UPS',
            'fedex' => 'FedEx',
            'dhl'   => 'DHL',
        );
    }

    public function getCarrierTitle($carrierCode, $title)
    {
        $carriers = $this->getCarriers();
        $carrierCode = strtolower($carrierCode);

        if (isset($carriers[$carrierCode])) {
            return $carriers[$carrierCode];
        }

        if ($title == '' || filter_var($title, FILTER_VALIDATE_URL) !== false) {
            return 'Other';
        }

        return $title;
    }

    // ---------------------------------------

    public function prepareOptionsForVariations(array $options)
    {
        $set = array();
        foreach ($options['set'] as $optionTitle => $optionsSet) {
            foreach ($optionsSet as $singleOptionKey => $singleOption) {
                $set[trim($optionTitle)][$singleOptionKey] = trim(
                    Mage::helper('M2ePro')->reduceWordsInString(
                        $singleOption, self::VARIATION_OPTION_LABEL_MAX_LENGTH
                    )
                );
            }
        }

        $options['set'] = $set;

        foreach ($options['variations'] as &$variation) {
            foreach ($variation as &$singleOption) {
                $singleOption['option'] = trim(
                    Mage::helper('M2ePro')->reduceWordsInString(
                        $singleOption['option'], self::VARIATION_OPTION_LABEL_MAX_LENGTH
                    )
                );
                $singleOption['attribute'] = trim($singleOption['attribute']);
            }
        }

        unset($singleOption);
        unset($variation);

       if (isset($options['additional']['attributes'])) {
           foreach ($options['additional']['attributes'] as $code => &$title) {
               $title = trim($title);
           }

           unset($title);
       }

        return $options;
    }

    public function prepareOptionsForOrders(array $options)
    {
        foreach ($options as &$singleOption) {
            if ($singleOption instanceof Mage_Catalog_Model_Product) {
                $reducedName = trim(
                    Mage::helper('M2ePro')->reduceWordsInString(
                        $singleOption->getName(), self::VARIATION_OPTION_LABEL_MAX_LENGTH
                    )
                );
                $singleOption->setData('name', $reducedName);

                continue;
            }

            foreach ($singleOption['values'] as &$singleOptionValue) {
                foreach ($singleOptionValue['labels'] as &$singleOptionLabel) {
                    $singleOptionLabel = trim(
                        Mage::helper('M2ePro')->reduceWordsInString(
                            $singleOptionLabel, self::VARIATION_OPTION_LABEL_MAX_LENGTH
                        )
                    );
                }
            }
        }

        return $options;
    }

    //########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    //########################################

    public function timeToString($time)
    {
        return (string)$this->getEbayDateTimeObject($time)->format('Y-m-d H:i:s');
    }

    public function timeToTimeStamp($time)
    {
        return (int)$this->getEbayDateTimeObject($time)->format('U');
    }

    // -----------------------------------------

    protected function getEbayDateTimeObject($time)
    {
        $dateTime = null;

        if ($time instanceof DateTime) {
            $dateTime = clone $time;
            $dateTime->setTimezone(new DateTimeZone('UTC'));
        } else {
            is_int($time) && $time = '@'.$time;
            $dateTime = new DateTime($time, new DateTimeZone('UTC'));
        }

        if ($dateTime === null) {
            throw new Ess_M2ePro_Model_Exception('eBay DateTime object is null');
        }

        return $dateTime;
    }

    //########################################
}
