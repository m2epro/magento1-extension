<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $generalId = $this->getGeneralId($params);

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'list_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendIdentifiersData($data, $generalId);
        $data = $this->appendDetailsValues($data);
        $data = $this->appendIsStoppedManually($data, false);

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if (!$variationManager->isRelationParentType()) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO;

            $data = $this->appendQtyValues($data);
            $data = $this->appendRegularPriceValues($data);
            $data = $this->appendBusinessPriceValues($data);
            $data = $this->appendGiftSettingsStatus($data);
        }

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data);
        $this->setVariationData($generalId);

        $this->getListingProduct()->save();

        if (!$variationManager->isRelationParentType()) {
            $this->createAmazonItem();
        }
    }

    //########################################

    protected function appendIdentifiersData($data, $generalId)
    {
        $data['sku'] = $this->getRequestData()->getSku();

        $isGeneralIdOwner = $this->getIsGeneralIdOwner();
        if ($isGeneralIdOwner !== null) {
            $data['is_general_id_owner'] = $isGeneralIdOwner;
        }

        if (!empty($generalId)) {
            $data['general_id']         = $generalId;
            $data['is_isbn_general_id'] = Mage::helper('M2ePro')->isISBN($generalId);
        }

        return $data;
    }

    //########################################

    protected function setVariationData($generalId)
    {
        if (empty($generalId)) {
            return;
        }

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();
        if (!$variationManager->isRelationMode()) {
            return;
        }

        $typeModel = $variationManager->getTypeModel();

        if ($variationManager->isRelationParentType()) {
            $dictionary = $this->getAmazonListingProduct()->getProductTypeTemplate()->getDictionary();

            $channelAttributes = $dictionary->getVariationThemesAttributes((string)$typeModel->getChannelTheme());

            $typeModel->setChannelAttributesSets(array_fill_keys($channelAttributes, array()), false);

            return;
        }

        if (!$this->getRequestData()->hasVariationAttributes()) {
            return;
        }

        if ($typeModel->isVariationChannelMatched()) {
            return;
        }

        $realChannelOptions = $this->getRequestData()->getVariationAttributes();

        $parentTypeModel = $typeModel->getParentTypeModel();

        if ($parentTypeModel->getVirtualChannelAttributes()) {
            $typeModel->setChannelVariation(
                array_merge($realChannelOptions, $parentTypeModel->getVirtualChannelAttributes())
            );
        } else {
            $typeModel->setChannelVariation($realChannelOptions);
        }

        // add child variation to parent
        // ---------------------------------------
        $channelVariations = (array)$parentTypeModel->getRealChannelVariations();
        $channelVariations[$generalId] = $realChannelOptions;
        $parentTypeModel->setChannelVariations($channelVariations, false);
        // ---------------------------------------

        // update parent attributes sets
        // ---------------------------------------
        $channelAttributesSets = $parentTypeModel->getRealChannelAttributesSets();
        foreach ($realChannelOptions as $attribute => $value) {
            if (!isset($channelAttributesSets[$attribute])) {
                $channelAttributesSets[$attribute] = array();
            }

            if (in_array($value, $channelAttributesSets[$attribute])) {
                continue;
            }

            $channelAttributesSets[$attribute][] = $value;
        }

        $parentTypeModel->setChannelAttributesSets($channelAttributesSets, false);
        // ---------------------------------------

        $typeModel->getParentListingProduct()->save();
    }

    //########################################

    protected function getGeneralId(array $params)
    {
        if (!empty($params['general_id'])) {
            return $params['general_id'];
        }

        if ($this->getRequestData()->isTypeModeNew()) {
            return null;
        }

        return $this->getRequestData()->getProductId();
    }

    protected function getIsGeneralIdOwner()
    {
        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if ($variationManager->isRelationChildType()) {
            return null;
        }

        if ($variationManager->isRelationParentType()) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        }

        if ($this->getRequestData()->isTypeModeNew()) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        }

        return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO;
    }

    //########################################

    protected function createAmazonItem()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());

        $linkingObject->createAmazonItem();
    }

    //########################################
}
