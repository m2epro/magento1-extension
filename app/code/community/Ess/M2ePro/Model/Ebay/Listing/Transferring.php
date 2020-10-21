<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Transferring
{
    const PARAM_LISTING_ID_DESTINATION_CREATE_NEW = 'create-new';

    protected $_sessionPrefix = 'ebay_listing_transferring';

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->_listing = $listing;
    }

    public function getListing()
    {
        return $this->_listing;
    }

    //########################################

    public function setProductsIds($products)
    {
        $this->setSessionValue('products_ids', $products);
        return $this;
    }

    public function setTargetListingId($listingId)
    {
        $this->setSessionValue('to_listing_id', $listingId);
        return $this;
    }

    public function setErrorsCount($count)
    {
        $this->setSessionValue('errors_count', $count);
        return $this;
    }

    //----------------------------------------

    public function getProductsIds()
    {
        return $this->getSessionValue('products_ids');
    }

    public function getTargetListingId()
    {
        return $this->getSessionValue('to_listing_id');
    }

    public function getErrorsCount()
    {
        return (int)$this->getSessionValue('errors_count');
    }

    public function isTargetListingNew()
    {
        return $this->getTargetListingId() === self::PARAM_LISTING_ID_DESTINATION_CREATE_NEW;
    }

    //########################################

    public function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();

        if ($key === null) {
            $sessionData = $value;
        } else {
            $sessionData[$key] = $value;
        }

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionPrefix . $this->_listing->getId(), $sessionData);
        return $this;
    }

    public function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionPrefix . $this->_listing->getId());

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    public function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionPrefix . $this->_listing->getId(), true);
    }

    //########################################
}
