<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Linking
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    protected $_productIdentifiers = array();

    protected $_sku = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @param array $productIdentifiers
     * @return $this
     */
    public function setProductIdentifiers(array $productIdentifiers)
    {
        $this->_productIdentifiers = $productIdentifiers;
        return $this;
    }

    /**
     * @param $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->_sku = $sku;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function link()
    {
        $this->validate();

        if (!$this->getVariationManager()->isRelationMode()) {
            $this->linkSimpleOrIndividualProduct();
            return true;
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            $this->linkChildProduct();
            return true;
        }

        return false;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Item
     * @throws Ess_M2ePro_Model_Exception
     * @throws Exception
     */
    public function createWalmartItem()
    {
        $data = array(
            'account_id'     => $this->getListingProduct()->getListing()->getAccountId(),
            'marketplace_id' => $this->getListingProduct()->getListing()->getMarketplaceId(),
            'sku'            => $this->getSku(),
            'product_id'     => $this->getListingProduct()->getProductId(),
            'store_id'       => $this->getListingProduct()->getListing()->getStoreId(),
        );

        $helper = Mage::helper('M2ePro/Data');

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */
            $typeModel = $this->getVariationManager()->getTypeModel();
            $data['variation_product_options'] = $helper->jsonEncode($typeModel->getProductOptions());
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
            $typeModel = $this->getVariationManager()->getTypeModel();

            if ($typeModel->isVariationProductMatched()) {
                $data['variation_product_options'] = $helper->jsonEncode($typeModel->getRealProductOptions());
            }
        }

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData = $this->getListingProduct()->getAdditionalData();
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode(array(
                'grouped_product_mode' => $additionalData['grouped_product_mode']
            ));
        }

        /** @var Ess_M2ePro_Model_Walmart_Item $object */
        $object = Mage::getModel('M2ePro/Walmart_Item');
        $object->setData($data);
        $object->save();

        return $object;
    }

    //########################################

    protected function validate()
    {
        $listingProduct = $this->getListingProduct();
        if (empty($listingProduct)) {
            throw new InvalidArgumentException('Listing Product was not set.');
        }

        $generalId = $this->getProductIdentifiers();
        if (empty($generalId)) {
            throw new InvalidArgumentException('Product identifiers were not set.');
        }

        $sku = $this->getSku();
        if (empty($sku)) {
            throw new InvalidArgumentException('SKU was not set.');
        }
    }

    //########################################

    protected function linkSimpleOrIndividualProduct()
    {
        $this->getListingProduct()->addData(
            array('status' => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE)
        );

        $productIdentifiers = $this->getProductIdentifiers();

        $this->getListingProduct()->addData(
            array(
                'wpid'    => $productIdentifiers['wpid'],
                'item_id' => $productIdentifiers['item_id'],
                'gtin'    => $productIdentifiers['gtin'],
                'upc'     => isset($productIdentifiers['upc']) ? $productIdentifiers['upc'] : null,
                'ean'     => isset($productIdentifiers['ean']) ? $productIdentifiers['ean'] : null,
                'isbn'    => isset($productIdentifiers['isbn']) ? $productIdentifiers['isbn'] : null,
                'sku'     => $this->getSku(),
            )
        );
        $this->getListingProduct()->save();

        $this->createWalmartItem();
    }

    protected function linkChildProduct()
    {
        $this->linkSimpleOrIndividualProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $typeModel->getParentListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel();

        $parentTypeModel->getProcessor()->process();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    protected function getWalmartListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getWalmartListingProduct()->getVariationManager();
    }

    // ---------------------------------------

    protected function getProductIdentifiers()
    {
        return $this->_productIdentifiers;
    }

    protected function getSku()
    {
        if ($this->_sku !== null) {
            return $this->_sku;
        }

        return $this->getWalmartListingProduct()->getSku();
    }

    //########################################
}
