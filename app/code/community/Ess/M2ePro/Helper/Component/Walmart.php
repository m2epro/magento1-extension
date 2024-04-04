<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Walmart extends Mage_Core_Helper_Abstract
{
    const NICK  = 'walmart';

    const MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK = 'walmart_marketplace_synchronization';

    const MARKETPLACE_US = 37;
    const MARKETPLACE_CA = 38;

    const MAX_ALLOWED_FEED_REQUESTS_PER_HOUR = 30;

    const SKU_MAX_LENGTH = 50;

    const PRODUCT_PUBLISH_STATUS_PUBLISHED        = 'PUBLISHED';
    const PRODUCT_PUBLISH_STATUS_UNPUBLISHED      = 'UNPUBLISHED';
    const PRODUCT_PUBLISH_STATUS_STAGE            = 'STAGE';
    const PRODUCT_PUBLISH_STATUS_IN_PROGRESS      = 'IN_PROGRESS';
    const PRODUCT_PUBLISH_STATUS_READY_TO_PUBLISH = 'READY_TO_PUBLISH';
    const PRODUCT_PUBLISH_STATUS_SYSTEM_PROBLEM   = 'SYSTEM_PROBLEM';

    const PRODUCT_LIFECYCLE_STATUS_ACTIVE   = 'ACTIVE';
    const PRODUCT_LIFECYCLE_STATUS_RETIRED  = 'RETIRED';
    const PRODUCT_LIFECYCLE_STATUS_ARCHIVED = 'ARCHIVED';

    const PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE = 'Reasonable Price Not Satisfied';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Walmart');
    }

    public function getChannelTitle()
    {
        return Mage::helper('M2ePro')->__('Walmart');
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

    public function getRegisterUrl($marketplaceId)
    {
        switch ($marketplaceId) {
            case self::MARKETPLACE_US:
                $domain = $this->getCachedObject('Marketplace', $marketplaceId)->getUrl();
                $url = 'https://developer.' . $domain . '/#/generateKey';
                break;

            case self::MARKETPLACE_CA:
                $url = 'https://seller.walmart.ca/';
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Marketplace ID.');
        }

        return $url;
    }

    public function getItemUrl($productItemId, $marketplaceId = null)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace', $marketplaceId)->getUrl();

        return 'https://www.'.$domain.'/ip/'.$productItemId;
    }

    public function getIdentifierForItemUrl($marketplaceId)
    {
        switch ($marketplaceId) {
            case Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US:
                return 'item_id';
            case Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA:
                return 'wpid';
            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Marketplace ID.');
        }
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
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/walmart/', 'application_name');
    }

    // ----------------------------------------

    public function getCarriers()
    {
        return array(
            'ups'      => 'UPS',
            'usps'     => 'USPS',
            'fedex'    => 'FedEx',
            'airborne' => 'Airborne',
            'ontrac'   => 'OnTrac',
            'dhl'      => 'DHL',
            'ng'       => 'NG',
            'ls'       => 'LS',
            'uds'      => 'UDS',
            'upsmi'    => 'UPSMI',
            'fdx'      => 'FDX'
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

    //########################################

    public function getResultProductStatus($publishStatus, $lifecycleStatus, $onlineQty)
    {
        if (!in_array(
            $publishStatus, array(self::PRODUCT_PUBLISH_STATUS_PUBLISHED,
            self::PRODUCT_PUBLISH_STATUS_STAGE)
        ) ||
            $lifecycleStatus != self::PRODUCT_LIFECYCLE_STATUS_ACTIVE
        ) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
        }

        return $onlineQty > 0
            ? Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
            : Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
    }

    //########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    //########################################
}
