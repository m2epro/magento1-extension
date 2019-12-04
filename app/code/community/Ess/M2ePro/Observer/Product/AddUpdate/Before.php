<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Product_AddUpdate_Before extends Ess_M2ePro_Observer_Product_AddUpdate_Abstract
{
    public static $proxyStorage = array();

    /**
     * @var null|Ess_M2ePro_Observer_Product_AddUpdate_Before_Proxy
     */
    protected $_proxy = null;

    //########################################

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

    // ---------------------------------------

    public function process()
    {
        if ($this->isAddingProductProcess()) {
            return;
        }

        $this->reloadProduct();

        $this->getProxy()->setData('name', $this->getProduct()->getName());

        $this->getProxy()->setWebsiteIds($this->getProduct()->getWebsiteIds());
        $this->getProxy()->setCategoriesIds($this->getProduct()->getCategoryIds());

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->getProxy()->setData('status', (int)$this->getProduct()->getStatus());
        $this->getProxy()->setData('price', (float)$this->getProduct()->getPrice());
        $this->getProxy()->setData('special_price', (float)$this->getProduct()->getSpecialPrice());
        $this->getProxy()->setData('special_price_from_date', $this->getProduct()->getSpecialFromDate());
        $this->getProxy()->setData('special_price_to_date', $this->getProduct()->getSpecialToDate());
        $this->getProxy()->setData('tier_price', $this->getProduct()->getTierPrice());

        $this->getProxy()->setAttributes($this->getTrackingAttributesWithValues());
    }

    //########################################

    protected function isAddingProductProcess()
    {
        return $this->getProductId() <= 0 || (string)$this->getEvent()->getProduct()->getOrigData('sku') == '';
    }

    //########################################

    /**
     * @return Ess_M2ePro_Observer_Product_AddUpdate_Before_Proxy
     */
    protected function getProxy()
    {
        if ($this->_proxy !== null) {
            return $this->_proxy;
        }

        /** @var Ess_M2ePro_Observer_Product_AddUpdate_Before_Proxy $object */
        $object = Mage::getModel('M2ePro_Observer/Product_AddUpdate_Before_Proxy');

        $object->setProductId($this->getProductId());
        $object->setStoreId($this->getStoreId());

        return $this->_proxy = $object;
    }

    // ---------------------------------------

    protected function clearStoredProxy()
    {
        $key = $this->getProductId().'_'.$this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        unset(self::$proxyStorage[$key]);
    }

    protected function storeProxy()
    {
        $key = $this->getProductId().'_'.$this->getStoreId();
        if ($this->isAddingProductProcess()) {
            $key = $this->getProduct()->getSku();
        }

        self::$proxyStorage[$key] = $this->getProxy();
    }

    //########################################

    protected function getTrackingAttributes()
    {
        $attributes = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract $changeProcessor */
            $changeProcessor = Mage::getModel(
                'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Magento_Product_ChangeProcessor'
            );
            $changeProcessor->setListingProduct($listingProduct);

            $attributes = array_merge($attributes, $changeProcessor->getTrackingAttributes());
        }

        return array_values(array_unique($attributes));
    }

    protected function getTrackingAttributesWithValues()
    {
        $attributes = array();

        foreach ($this->getTrackingAttributes() as $attributeCode) {
            $attributes[$attributeCode] = $this->getMagentoProduct()->getAttributeValue($attributeCode);
        }

        return $attributes;
    }

    //########################################
}
