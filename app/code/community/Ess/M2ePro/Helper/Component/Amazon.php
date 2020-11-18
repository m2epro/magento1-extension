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
    const MARKETPLACE_US = 29;
    const MARKETPLACE_JP = 27;
    const MARKETPLACE_CN = 32;

    const MAX_ALLOWED_FEED_REQUESTS_PER_HOUR = 30;

    const SKU_MAX_LENGTH = 40;

    //########################################

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
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Inactive'),
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Inactive (Blocked)')
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

    public function getRegisterUrl($marketplaceId = null)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace', $marketplaceId)->getUrl();
        $applicationName = Mage::helper('M2ePro/Component_Amazon')->getApplicationName();

        return 'https://sellercentral.'.
                $domain.
                '/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0&applicationName='.
                rawurlencode($applicationName).'&appDevMWSAccountId='.
                $this->getCachedObject('Marketplace', $marketplaceId)->getDeveloperKey();
    }

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

    public function getApplicationName()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/amazon/', 'application_name');
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
                    ->addFieldToFilter('developer_key', array('notnull' => true))
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

    //########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    //########################################
}
