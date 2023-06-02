<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    protected $_isVerifyCall = false;

    //########################################

    protected function beforeBuildDataEvent()
    {
        if ($this->_isVerifyCall) {
            parent::beforeBuildDataEvent();
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData['grouped_product_mode'] = Mage::helper('M2ePro/Module_Configuration')
                ->getGroupedProductMode();
        }

        unset($additionalData['synch_template_list_rules_note']);
        unset($additionalData['item_duplicate_action_required']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->setData('is_duplicate', 0);

        $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @return array
     */
    public function getActionData()
    {
        if (!$uuid = $this->getEbayListingProduct()->getItemUUID()) {
            $uuid = $this->getEbayListingProduct()->generateItemUUID();
            $this->getEbayListingProduct()->setData('item_uuid', $uuid)->save();
        }

        $data = array_merge(
            array(
                'sku'       => $this->getSku(),
                'item_uuid' => $uuid,
            ),
            $this->getGeneralData(),
            $this->getQtyData(),
            $this->getPriceData(),
            $this->getTitleData(),
            $this->getSubtitleData(),
            $this->getDescriptionData(),
            $this->getImagesData(),
            $this->getCategoriesData(),
            $this->getPartsData(),
            $this->getPaymentData(),
            $this->getReturnData(),
            $this->getShippingData(),
            $this->getVariationsData(),
            $this->getOtherData()
        );

        $this->_isVerifyCall && $data['verify_call'] = true;

        return $data;
    }

    //########################################

    protected function initializeVariations()
    {
        if (!$this->getEbayListingProduct()->isVariationMode()) {
            foreach ($this->getListingProduct()->getVariations(true) as $variation) {
                $variation->deleteInstance();
            }
        }

        parent::initializeVariations();

        if (!$this->getEbayListingProduct()->isVariationMode()) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variations_that_can_not_be_deleted'] = array();
        $this->getListingProduct()->setSettings('additional_data', $additionalData)->save();

        $variations = $this->getListingProduct()->getVariations(true);

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            if ($ebayVariation->isDelete()) {
                $variation->deleteInstance();
                continue;
            }

            $needSave = false;

            if ($ebayVariation->isAdd()) {
                $variation->setData('add', 0);
                $needSave = true;
            }

            if ($ebayVariation->isNotListed()) {
                $variation->setData('online_sku', null);
                $variation->setData('online_price', null);
                $variation->setData('online_qty', null);
                $variation->setData('online_qty_sold', null);

                $needSave = true;
            }

            $additionalData = $variation->getAdditionalData();
            if (!empty($additionalData['online_product_details'])) {
                unset($additionalData['online_product_details']);
                $variation->setSettings('additional_data', $additionalData);

                $needSave = true;
            }

            if ($needSave) {
                $variation->save();
            }
        }
    }

    //########################################

    protected function getIsEpsImagesMode()
    {
        return null;
    }

    //########################################

    protected function replaceVariationSpecificsNames(array $data)
    {
        if (!$this->getIsVariationItem() || !$this->getMagentoProduct()->isConfigurableType() ||
            empty($data['variations_sets']) || !is_array($data['variations_sets'])) {
            return $data;
        }

        $confAttributes = array();
        $additionalData = $this->getListingProduct()->getAdditionalData();
        if (!empty($additionalData['configurable_attributes'])) {
            $confAttributes = $additionalData['configurable_attributes'];
        }

        if (empty($confAttributes)) {
            return $data;
        }

        $replacements = array();

        foreach ($this->getEbayListingProduct()->getCategoryTemplate()->getSpecifics(true) as $specific) {
            if (!$specific->isItemSpecificsMode() || !$specific->isCustomAttributeValueMode()) {
                continue;
            }

            $attrCode  = trim($specific->getData('value_custom_attribute'));
            $attrTitle = trim($specific->getData('attribute_title'));

            if (!array_key_exists($attrCode, $confAttributes) || $confAttributes[$attrCode] == $attrTitle) {
                continue;
            }

            $replacements[$confAttributes[$attrCode]] = $attrTitle;
        }

        if (empty($replacements)) {
            return $data;
        }

        $data = $this->doReplaceVariationSpecifics($data, $replacements);
        $this->addMetaData('variations_specifics_replacements', $replacements);

        return $data;
    }

    protected function resolveVariationMpnIssue(array $data)
    {
        if (!$this->getIsVariationItem()) {
            return $data;
        }

        $data['without_mpn_variation_issue'] = true;

        return $data;
    }

    //########################################

    public function setIsVerifyCall($value)
    {
        $this->_isVerifyCall = $value;
        return $this;
    }

    //########################################
}
