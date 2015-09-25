<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product_AddUpdate_Before extends Ess_M2ePro_Model_Observer_Product_AddUpdate_Abstract
{
    public static $proxyStorage = array();

    /**
     * @var null|Ess_M2ePro_Model_Observer_Product_AddUpdate_Before_Proxy
     */
    private $proxy = NULL;

    //####################################

    public function beforeProcess()
    {
        parent::beforeProcess();
        $this->clearStoredProxy();
    }

    public function afterProcess()
    {
        parent::afterProcess();
        $this->storeProxy();
    }

    // ------------------------------------

    public function process()
    {
        if ($this->isAddingProductProcess()) {
            return;
        }

        $this->reloadProduct();

        $this->getProxy()->setData('name',$this->getProduct()->getName());

        $this->getProxy()->setWebsiteIds($this->getProduct()->getWebsiteIds());
        $this->getProxy()->setCategoriesIds($this->getProduct()->getCategoryIds());

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->getProxy()->setData('status',(int)$this->getProduct()->getStatus());
        $this->getProxy()->setData('price',(float)$this->getProduct()->getPrice());
        $this->getProxy()->setData('special_price',(float)$this->getProduct()->getSpecialPrice());
        $this->getProxy()->setData('special_price_from_date',$this->getProduct()->getSpecialFromDate());
        $this->getProxy()->setData('special_price_to_date',$this->getProduct()->getSpecialToDate());

        $this->getProxy()->setAttributes($this->getTrackingAttributesWithValues());
    }

    //####################################

    protected function isAddingProductProcess()
    {
        return $this->getProductId() <= 0 || (string)$this->getEvent()->getProduct()->getOrigData('sku') == '';
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Observer_Product_AddUpdate_Before_Proxy
     */
    private function getProxy()
    {
        if (!is_null($this->proxy)) {
            return $this->proxy;
        }

        /** @var Ess_M2ePro_Model_Observer_Product_AddUpdate_Before_Proxy $object */
        $object = Mage::getModel('M2ePro/Observer_Product_AddUpdate_Before_Proxy');

        $object->setProductId($this->getProductId());
        $object->setStoreId($this->getStoreId());

        return $this->proxy = $object;
    }

    // -----------------------------------

    private function clearStoredProxy()
    {
        $key = $this->getProductId().'_'.$this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        unset(self::$proxyStorage[$key]);
    }

    private function storeProxy()
    {
        $key = $this->getProductId().'_'.$this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        self::$proxyStorage[$key] = $this->getProxy();
    }

    //####################################

    private function getTrackingAttributes()
    {
        $attributes = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $tempAttributes = $listingProduct->getTrackingAttributes();
            $attributes = array_merge($attributes, $tempAttributes);
        }

        $tempAttributes = Mage::getModel('M2ePro/Ebay_Listing_Other_Source')->getTrackingAttributes();
        $attributes = array_merge($attributes, $tempAttributes);

        return array_values(array_unique($attributes));
    }

    private function getTrackingAttributesWithValues()
    {
        $attributes = array();

        foreach ($this->getTrackingAttributes() as $attributeCode) {
            $attributes[$attributeCode] = $this->getMagentoProduct()->getAttributeValue($attributeCode);
        }

        return $attributes;
    }

    //####################################
}