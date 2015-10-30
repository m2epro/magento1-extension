<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Linking
{
    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $generalId = null;

    private $sku = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @param $generalId
     * @return $this
     */
    public function setGeneralId($generalId)
    {
        $this->generalId = $generalId;
        return $this;
    }

    /**
     * @param $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    //########################################

    public function link()
    {
        $this->validate();

        $this->getListingProduct()->addData(array(
            'general_id' => $this->getGeneralId(),
            'sku'        => $this->getSku(),
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        ));
        $this->getListingProduct()->save();

        $this->createBuyItem();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Item
     * @throws Exception
     */
    public function createBuyItem()
    {
        $data = array(
            'account_id'     => $this->getListingProduct()->getListing()->getAccountId(),
            'marketplace_id' => $this->getListingProduct()->getListing()->getMarketplaceId(),
            'sku'            => $this->getSku(),
            'product_id'     => $this->getListingProduct()->getProductId(),
            'store_id'       => $this->getListingProduct()->getListing()->getStoreId(),
        );

        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()
        ) {
            $data['variation_product_options'] = json_encode($this->getVariationManager()->getProductOptions());
        }

        /** @var Ess_M2ePro_Model_Buy_Item $object */
        $object = Mage::getModel('M2ePro/Buy_Item');
        $object->setData($data);
        $object->save();

        return $object;
    }

    //########################################

    private function validate()
    {
        $listingProduct = $this->getListingProduct();
        if (empty($listingProduct)) {
            throw new InvalidArgumentException('Listing Product was not set.');
        }

        $generalId = $this->getGeneralId();
        if (empty($generalId)) {
            throw new InvalidArgumentException('General ID was not set.');
        }

        $sku = $this->getSku();
        if (empty($sku)) {
            throw new InvalidArgumentException('SKU was not set.');
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    private function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    private function getBuyListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager
     */
    private function getVariationManager()
    {
        return $this->getBuyListingProduct()->getVariationManager();
    }

    // ---------------------------------------

    private function getGeneralId()
    {
        return $this->generalId;
    }

    private function getSku()
    {
        if (!is_null($this->sku)) {
            return $this->sku;
        }

        return $this->getBuyListingProduct()->getSku();
    }

    //########################################
}