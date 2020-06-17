<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Marketplace as AmazonMarketplace;
use Ess_M2ePro_Model_Ebay_Marketplace as EbayMarketplace;
use Ess_M2ePro_Model_Walmart_Marketplace as WalmartMarketplace;

/**
 * @method AmazonMarketplace|EbayMarketplace|WalmartMarketplace getChildObject()
 * @method Ess_M2ePro_Model_Resource_Marketplace getResource()
 */
class Ess_M2ePro_Model_Marketplace extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Marketplace');
    }

    //########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        return true;
    }

    //########################################

    public function getIdByCode($code)
    {
        return $this->load($code, 'code')->getId();
    }

    /**
     * @return bool
     */
    public function isStatusEnabled()
    {
        return $this->getStatus() == self::STATUS_ENABLE;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getGroupTitle()
    {
        return $this->getData('group_title');
    }

    /**
     * @return int
     */
    public function getNativeId()
    {
        return (int)$this->getData('native_id');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::delete();
    }

    //########################################
}
