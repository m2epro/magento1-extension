<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Marketplace extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Marketplace');
    }

    // ########################################

    public function getAmazonItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Amazon_Item','marketplace_id',$asObjects,$filters);
    }

    public function getDescriptionTemplates($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Amazon_Template_Description','marketplace_id',$asObjects,$filters);
    }

    // ########################################

    public function getCurrency()
    {
        return $this->getData('default_currency');
    }

    // ########################################

    public function getDeveloperKey()
    {
        return $this->getData('developer_key');
    }

    public function getDefaultCurrency()
    {
        return $this->getData('default_currency');
    }

    public function isAsinAvailable()
    {
        return (bool)$this->getData('is_asin_available');
    }

    // ########################################

    public function isNewAsinAvailable()
    {
        $newAsinNotImplementedMarketplaces = array(
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_CA,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_JP,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_CN,
        );

        return !in_array((int)$this->getId(),$newAsinNotImplementedMarketplaces);
    }

    // ########################################

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

    // ########################################
}