<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon extends Mage_Core_Helper_Abstract
{
    const NICK  = 'amazon';

    const MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK = 'amazon_marketplace_synchronization';

    const MARKETPLACE_CA = 24;
    const MARKETPLACE_DE = 25;
    const MARKETPLACE_FR = 26;
    const MARKETPLACE_UK = 28;
    const MARKETPLACE_US = 29;
    const MARKETPLACE_ES = 30;
    const MARKETPLACE_IT = 31;
    const MARKETPLACE_CN = 32;
    const MARKETPLACE_MX = 34;
    const MARKETPLACE_AU = 35;
    const MARKETPLACE_NL = 39;
    const MARKETPLACE_TR = 40;
    const MARKETPLACE_SE = 41;
    const MARKETPLACE_JP = 42;
    const MARKETPLACE_PL = 43;
    const MARKETPLACE_BR = 44;
    const MARKETPLACE_SG = 45;
    const MARKETPLACE_IN = 46;
    const MARKETPLACE_AE = 47;
    const MARKETPLACE_BE = 48;
    const MARKETPLACE_ZA = 49;

    const MAX_ALLOWED_FEED_REQUESTS_PER_HOUR = 30;
    const SKU_MAX_LENGTH = 40;

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Amazon');
    }

    public function getChannelTitle()
    {
        return Mage::helper('M2ePro')->__('Amazon');
    }

    //########################################

    public function getHumanTitleByListingProductStatus($status)
    {
        $statuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN    => Mage::helper('M2ePro')->__('Unknown'),
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Active'),
            Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Incomplete')
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

    public function getItemUrl($productId, $marketplaceId = null)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace', $marketplaceId)->getUrl();

        return 'http://'.$domain.'/gp/product/'.$productId;
    }

    public function getOrderUrl($orderId, $marketplaceId = null)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace', $marketplaceId)->getUrl();

        return 'https://sellercentral.'.$domain.'/orders-v3/order/'.$orderId;
    }

    //########################################

    public function isASIN($string)
    {
        if (strlen($string) != 10) {
            return false;
        }

        if (!preg_match('/^B[A-Z0-9]{9}$/', $string)) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

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

        return $title;
    }

    // ----------------------------------------

    public function getMarketplacesAvailableForApiCreation()
    {
        return $this->getCollection('Marketplace')
                    ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                    ->setOrder('sorder', 'ASC');
    }

    public function getMarketplacesAvailableForAsinCreation()
    {
        $collection = $this->getMarketplacesAvailableForApiCreation();
        return $collection->addFieldToFilter('is_new_asin_available', 1);
    }

    //########################################

    public function getStatesList()
    {
        $collection = Mage::getResourceModel('directory/region_collection');
        $collection->addCountryFilter('US');

        $collection->addFieldToFilter(
            'default_name',
            array(
                'nin' => array(
                    'Armed Forces Africa',
                    'Armed Forces Americas',
                    'Armed Forces Canada',
                    'Armed Forces Europe',
                    'Armed Forces Middle East',
                    'Armed Forces Pacific',
                    'Federated States Of Micronesia',
                    'Marshall Islands',
                    'Palau'
                )
            )
        );

        $states = array();

        foreach ($collection->getItems() as $state) {
            $states[$state->getCode()] = $state->getName();
        }

        return $states;
    }

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    /**
     * @return string[]
     */
    public function getEEACountryCodes()
    {
        return array(
            'AT', 'BE', 'BG', 'HR', 'CY',
            'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IS', 'IE',
            'IT', 'LV', 'LI', 'LT', 'LU',
            'MT', 'NL', 'NO', 'PL', 'PT',
            'RO', 'SK', 'SI', 'ES', 'SE',
        );
    }

    /**
     * @return array
     */
    public function getEEACountriesList()
    {
        $collection = Mage::getModel('directory/country')
            ->getCollection()
            ->addFieldToSelect(array('iso2_code'))
            ->addFieldToFilter(
                'iso2_code',
                array('in' => $this->getEEACountryCodes())
            );

        $tempData = array();
        /** @var Mage_Directory_Model_Country $item */
        foreach ($collection->getItems() as $item) {
            $tempData[] = array(
                'name' => $item->getName(),
                'code' => $item->getData('iso2_code')
            );
        }

        $compare = function ($a, $b) {
            if ($a['name'] === $b['name']) {
                return 0;
            }

            return ($a['name'] < $b['name']) ? -1 : 1;
        };
        uasort($tempData, $compare);

        $data = array();
        foreach ($tempData as $value) {
            $data[$value['code']] = $value['name'];
        }

        return $data;
    }

    /**
     * @return int[]
     */
    private function getMarketplaceWithoutData()
    {
        return array(
            self::MARKETPLACE_JP,
            self::MARKETPLACE_BR,
            self::MARKETPLACE_SG,
            self::MARKETPLACE_IN,
            self::MARKETPLACE_AE,
            self::MARKETPLACE_BE,
            self::MARKETPLACE_ZA,
        );
    }

    /**
     * @param $marketplaceId
     * @return bool
     */
    public function isMarketplacesWithoutData($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getMarketplaceWithoutData(), true);
    }
}
