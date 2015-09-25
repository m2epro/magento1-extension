<?php
/*
* @copyright  Copyright (c) 2014 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Magento_Store_Website
{
    private $defaultWebsite = NULL;

    // ########################################

    public function isExists($entity)
    {
        if ($entity instanceof Mage_Core_Model_Website) {
            return (bool)$entity->getCode();
        }

        try {
            Mage::app()->getWebsite($entity);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    // ########################################

    public function getDefault()
    {
        if (!is_null($this->defaultWebsite)) {
            return $this->defaultWebsite;
        }

        $this->defaultWebsite = Mage::getModel('core/website')->load(1,'is_default');

        if (is_null($this->defaultWebsite->getId())) {
            $this->defaultWebsite = Mage::getModel('core/website')->load(0);

            if (is_null($this->defaultWebsite->getId())) {
                throw new Ess_M2ePro_Model_Exception('Getting default website is failed');
            }
        }

        return $this->defaultWebsite;
    }

    public function getDefaultId()
    {
        return (int)$this->getDefault()->getId();
    }

    // ########################################

    public function getWebsite($storeId)
    {
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            return NULL;
        }

        return $store->getWebsite();
    }

    public function getName($storeId)
    {
        $website = $this->getWebsite($storeId);
        return $website ? $website->getName() : '';
    }

    // ########################################

    public function addWebsite($name, $code)
    {
       $website = Mage::app()->getWebsite()->load($code, 'code');

       if ($website->getId()) {
           $error = Mage::helper('M2ePro')->__('Website with code %value% already exists', $code);
           throw new Ess_M2ePro_Model_Exception($error);
       }

       $website->setCode($code);
       $website->setName($name);
       $website->setId(null)->save();

       return $website;
    }

    // ########################################
}