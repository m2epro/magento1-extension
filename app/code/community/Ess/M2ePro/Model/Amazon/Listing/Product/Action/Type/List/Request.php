<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
{
    const LIST_TYPE_EXIST = 'exist';
    const LIST_TYPE_NEW   = 'new';

    const PARENTAGE_PARENT = 'parent';
    const PARENTAGE_CHILD  = 'child';

    //########################################

    protected function beforeBuildDataEvent()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData['grouped_product_mode'] = Mage::helper('M2ePro/Module_Configuration')
                ->getGroupedProductMode();
        }

        unset($additionalData['synch_template_list_rules_note']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->save();

        parent::beforeBuildDataEvent();
    }

    //########################################

    protected function getActionData()
    {
        $data = array(
            'sku'       => $this->_cachedData['sku'],
            'type_mode' => $this->_cachedData['list_type'],
        );

        if ($this->_cachedData['list_type'] == self::LIST_TYPE_NEW && $this->getVariationManager()->isRelationMode()) {
            $data = array_merge($data, $this->getRelationData());
        }

        $data = array_merge(
            $data,
            $this->getQtyData(),
            $this->getRegularPriceData(),
            $this->getBusinessPriceData(),
            $this->getDetailsData()
        );

        if ($this->getVariationManager()->isRelationParentType()) {
            return $data;
        }

        if ($this->_cachedData['list_type'] == self::LIST_TYPE_NEW) {
            $data = array_merge($data, $this->getNewProductIdentifierData());
        } else {
            $data = array_merge($data, $this->getExistProductIdentifierData());
        }

        return $data;
    }

    //########################################

    protected function getExistProductIdentifierData()
    {
        return array(
            'product_id' => $this->_cachedData['general_id'],
            'product_id_type' => Mage::helper('M2ePro')->isISBN($this->_cachedData['general_id']) ? 'ISBN' : 'ASIN',
        );
    }

    /**
     * @return array{product_id: string, product_id_type: string}|array
     */
    private function getNewProductIdentifierData()
    {
        $worldwideId = $this->getAmazonListingProduct()
                            ->getIdentifiers()
                            ->getWorldwideId();
        $data = array();
        if ($worldwideId !== null) {
            $data['product_id'] = $worldwideId->getIdentifier();
            $data['product_id_type'] = $worldwideId->isUPC() ? 'UPC' : 'EAN';
        }

        return $data;
    }

    // ---------------------------------------

    protected function getRelationData()
    {
        if (!$this->getVariationManager()->isRelationMode()) {
            return array();
        }

        $productTypeTemplate = $this->getAmazonListingProduct()->getProductTypeTemplate();

        $data = array(
            'product_type_nick' => $productTypeTemplate->getNick(),
            'variation_data'    => array(
                'theme' => $this->getChannelTheme(),
            ),
        );

        if ($this->getVariationManager()->isRelationParentType()) {
            $data['variation_data']['parentage'] = self::PARENTAGE_PARENT;
            return $data;
        }

        $typeModel = $this->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

        $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getMatchedAttributes();

        $virtualChannelAttributes = $typeModel->getParentTypeModel()->getVirtualChannelAttributes();

        $attributes = array();
        foreach ($typeModel->getProductOptions() as $attribute => $value) {
            if (isset($virtualChannelAttributes[$attribute])) {
                continue;
            }

            $attributes[$matchedAttributes[$attribute]] = $value;
        }

        $data['variation_data'] = array_merge(
            $data['variation_data'], array(
            'parentage'  => self::PARENTAGE_CHILD,
            'parent_sku' => $parentAmazonListingProduct->getSku(),
            'attributes' => $attributes,
            )
        );

        return $data;
    }

    //########################################

    protected function getChannelTheme()
    {
        if (!$this->getVariationManager()->isRelationMode()) {
            return null;
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return $this->getVariationManager()->getTypeModel()->getChannelTheme();
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $parentVariationManager */
        $parentVariationManager = $this->getVariationManager()
            ->getTypeModel()
            ->getParentListingProduct()
            ->getChildObject()
            ->getVariationManager();

        return $parentVariationManager->getTypeModel()->getChannelTheme();
    }

    //########################################
}
