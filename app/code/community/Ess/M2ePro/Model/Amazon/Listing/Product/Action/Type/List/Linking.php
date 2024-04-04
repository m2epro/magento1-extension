<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    protected $_generalId = null;

    protected $_sku = null;

    protected $_additionalData = array();

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
     * @param $generalId
     * @return $this
     */
    public function setGeneralId($generalId)
    {
        if (!Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) &&
            !Mage::helper('M2ePro')->isISBN10($generalId)
        ) {
            throw new InvalidArgumentException('General ID "'.$generalId.'" is invalid.');
        }

        $this->_generalId = $generalId;
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

    /**
     * @param array $data
     * @return bool
     */
    public function setAdditionalData(array $data)
    {
        $this->_additionalData = $data;
        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function link()
    {
        $this->validate();

        if (!$this->getVariationManager()->isRelationMode()) {
            return $this->linkSimpleOrIndividualProduct();
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            return $this->linkChildProduct();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return $this->linkParentProduct();
        }

        return false;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Item
     * @throws Ess_M2ePro_Model_Exception
     * @throws Exception
     */
    public function createAmazonItem()
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

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */
            $typeModel = $this->getVariationManager()->getTypeModel();
            $data['variation_product_options'] = $helper->jsonEncode($typeModel->getProductOptions());
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
            $typeModel = $this->getVariationManager()->getTypeModel();

            if ($typeModel->isVariationProductMatched()) {
                $data['variation_product_options'] = $helper->jsonEncode($typeModel->getRealProductOptions());
            }

            if ($typeModel->isVariationChannelMatched()) {
                $data['variation_channel_options'] = $helper->jsonEncode($typeModel->getRealChannelOptions());
            }
        }

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData = $this->getListingProduct()->getAdditionalData();
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode(array(
                'grouped_product_mode' => $additionalData['grouped_product_mode']
            ));
        }

        /** @var Ess_M2ePro_Model_Amazon_Item $object */
        $object = Mage::getModel('M2ePro/Amazon_Item');
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

    protected function linkSimpleOrIndividualProduct()
    {
        $this->getListingProduct()->addData(
            array(
            'general_id'          => $this->getGeneralId(),
            'is_isbn_general_id'  => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO,
            'sku'                 => $this->getSku(),
            'status'              => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
            )
        );
        $this->getListingProduct()->save();

        $this->createAmazonItem();

        return true;
    }

    protected function linkChildProduct()
    {
        $this->getListingProduct()->addData(
            array(
            'general_id'         => $this->getGeneralId(),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'sku'                => $this->getSku(),
            'status'             => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE
            )
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $typeModel->getParentListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel();

        $parentVariations = $parentTypeModel->getChannelVariations();
        if (!isset($parentVariations[$this->_generalId])) {
            return false;
        }

        $typeModel->setChannelVariation($parentVariations[$this->_generalId]);

        $this->createAmazonItem();

        try {
            $parentTypeModel->getProcessor()->process();
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
    }

    protected function linkParentProduct()
    {
        $data = $this->getAdditionalData();
        if (empty($data['parentage']) || $data['parentage'] != 'parent' || !empty($data['bad_parent'])) {
            return false;
        }

        $dataForUpdate = array(
            'general_id'         => $this->getGeneralId(),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'sku'                => $this->getSku(),
        );

        $descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        $listingProductSku = $this->getAmazonListingProduct()->getSku();

        // improve check is sku existence
        if (empty($listingProductSku) && !empty($descriptionTemplate) && $descriptionTemplate->isNewAsinAccepted()) {
            $dataForUpdate['is_general_id_owner'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        } else {
            $dataForUpdate['is_general_id_owner'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO;
        }

        $this->getListingProduct()->addData($dataForUpdate);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        $typeModel->setChannelAttributesSets($data['variations']['set'], false);

        $channelVariations = array();
        foreach ($data['variations']['asins'] as $generalId => $options) {
            $channelVariations[$generalId] = $options['specifics'];
        }

        $typeModel->setChannelVariations($channelVariations, false);

        $this->getListingProduct()->save();

        try {
            $typeModel->getProcessor()->process();
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    // ---------------------------------------

    protected function getGeneralId()
    {
        return $this->_generalId;
    }

    protected function getSku()
    {
        if ($this->_sku !== null) {
            return $this->_sku;
        }

        return $this->getAmazonListingProduct()->getSku();
    }

    protected function getAdditionalData()
    {
        if (!empty($this->_additionalData)) {
            return $this->_additionalData;
        }

        return $this->_additionalData = $this->getDataFromAmazon();
    }

    //########################################

    protected function getDataFromAmazon()
    {
        $params = array(
            'item' => $this->_generalId,
            'variation_child_modification' => 'none',
        );

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'product', 'search', 'byAsin',
            $params, 'item',
            $this->getListingProduct()->getListing()->getAccount()
        );

        $dispatcherObject->process($connectorObj);

        return $connectorObj->getResponseData();
    }

    //########################################
}
