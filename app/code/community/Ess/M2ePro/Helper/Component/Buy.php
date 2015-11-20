<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Buy extends Mage_Core_Helper_Abstract
{
    const NICK  = 'buy';

    const MARKETPLACE_ID = 33;
    const DEFAULT_CURRENCY = 'USD';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Rakuten.com (Beta)');
    }

    public function getChannelTitle()
    {
        return Mage::helper('M2ePro')->__('Rakuten.com');
    }

    //########################################

    public function getHumanTitleByListingProductStatus($status)
    {
        $statuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
        );

        if (!isset($statuses[$status])) {
            return NULL;
        }

        return $statuses[$status];
    }

    //########################################

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'mode');
    }

    public function isAllowed()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'allowed');
    }

    public function isActive()
    {
        return $this->isEnabled() && $this->isAllowed();
    }

    public function isObject($modelName, $value, $field = NULL)
    {
        $mode = Mage::helper('M2ePro/Component')->getComponentMode($modelName, $value, $field);
        return !is_null($mode) && $mode == self::NICK;
    }

    // ---------------------------------------

    public function getModel($modelName)
    {
        return Mage::helper('M2ePro/Component')->getComponentModel(self::NICK,$modelName);
    }

    public function getObject($modelName, $value, $field = NULL)
    {
        return Mage::helper('M2ePro/Component')->getComponentObject(self::NICK, $modelName, $value, $field);
    }

    public function getCachedObject($modelName, $value, $field = NULL, array $tags = array())
    {
        return Mage::helper('M2ePro/Component')->getCachedComponentObject(
            self::NICK, $modelName, $value, $field, $tags
        );
    }

    public function getCollection($modelName)
    {
        return $this->getModel($modelName)->getCollection();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getCachedObject('Marketplace', $this->getMarketplaceId());
    }

    public function getMarketplaceId()
    {
        return self::MARKETPLACE_ID;
    }

    public function getItemUrl($productId)
    {
        return 'http://'.$this->getMarketplace()->getUrl().'/prod/'.$productId.'.html';
    }

    //########################################

    public function isGeneralId($string)
    {
        if (empty($string)) {
            return false;
        }

        return preg_match('/^\d{8,9}$/', $string);
    }

    // ----------------------------------------

    public function getCarrierTitle($carrierCode, $title)
    {
        $carriers = $this->getCarriers();
        $carrierCode = strtolower($carrierCode);

        foreach ($carriers as $carrier) {
            if ($carrierCode == strtolower($carrier)) {
                return $carrier;
            }
        }

        if ($title == '' || filter_var($title, FILTER_VALIDATE_URL) !== false) {
            return 'Other';
        }

        return $title;
    }

    public function getCarriers()
    {
        return array(
            'UPS',
            'FedEx',
            'USPS',
            'DHL',
            'Other',
            'UPS-MI',
            'FedEx SmartPost',
            'DHL Global Mail',
            'LTL_A. Duie Pyle',
            'LTL_ABF',
            'LTL_AIM Trans',
            'LTL_AIT',
            'LTL_CEVA Logistics',
            'LTL_Conway',
            'LTL_Ensenda',
            'LTL_Estes',
            'LTL_FedEx Freight',
            'LTL_FedEx LTL Freight East',
            'LTL_Fox Brother',
            'LTL_Home Direct',
            'LTL_Lakeville Motor',
            'LTL_Manna',
            'LTL_New England Motor Freight',
            'LTL_Old Dominion',
            'LTL_Pilot',
            'LTL_Pitt Ohio',
            'LTL_R&L Global',
            'LTL_S&J Transportation',
            'LTL_SAIA',
            'LTL_UPS Freight',
            'LTL_USF Holland',
            'LTL_USF Reddaway',
            'LTL_Vitran Express',
            'LTL_Watkins Motor Line Freight Standard',
            'LTL_Wilson Trucking',
            'LTL_Yellow Freight'
        );
    }

    //########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    //########################################
}