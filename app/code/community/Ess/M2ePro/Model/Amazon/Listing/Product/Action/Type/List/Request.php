<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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

    protected function getActionData()
    {
        $data = array(
            'sku'       => $this->validatorsData['sku'],
            'type_mode' => $this->validatorsData['list_type'],
        );

        if ($this->validatorsData['list_type'] == self::LIST_TYPE_NEW &&
            $this->getVariationManager()->isRelationMode()) {

                $data = array_merge($data, $this->getRelationData());
        }

        $data = array_merge(
            $data,
            $this->getRequestDetails()->getData(),
            $this->getRequestImages()->getData()
        );

        if ($this->getVariationManager()->isRelationParentType()) {
            return $data;
        }

        if ($this->validatorsData['list_type'] == self::LIST_TYPE_NEW) {
            $data = array_merge($data, $this->getNewProductIdentifierData());
        } else {
            $data = array_merge($data, $this->getExistProductIdentifierData());
        }

        $data = array_merge(
            $data,
            $this->getRequestQty()->getData(),
            $this->getRequestPrice()->getData(),
            $this->getRequestShippingOverride()->getData()
        );

        return $data;
    }

    //########################################

    private function getExistProductIdentifierData()
    {
        return array(
            'product_id' => $this->validatorsData['general_id'],
            'product_id_type' => Mage::helper('M2ePro')->isISBN($this->validatorsData['general_id']) ? 'ISBN' : 'ASIN',
        );
    }

    private function getNewProductIdentifierData()
    {
        $data = array();

        $worldwideId = $this->getAmazonListingProduct()->getDescriptionTemplateSource()->getWorldwideId();

        if (!empty($worldwideId)) {
            $data['product_id']      = $worldwideId;
            $data['product_id_type'] = Mage::helper('M2ePro')->isUPC($worldwideId) ? 'UPC' : 'EAN';
        }

        $registeredParameter = $this->getAmazonListingProduct()
            ->getAmazonDescriptionTemplate()
            ->getRegisteredParameter();

        if (!empty($registeredParameter)) {
            $data['registered_parameter'] = $registeredParameter;
        }

        return $data;
    }

    // ---------------------------------------

    private function getRelationData()
    {
        if (!$this->getVariationManager()->isRelationMode()) {
            return array();
        }

        $descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();

        $data = array(
            'product_data_nick' => $descriptionTemplate->getProductDataNick(),
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

        $data['variation_data'] = array_merge($data['variation_data'], array(
            'parentage'  => self::PARENTAGE_CHILD,
            'parent_sku' => $parentAmazonListingProduct->getSku(),
            'attributes' => $attributes,
        ));

        return $data;
    }

    //########################################

    private function getChannelTheme()
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