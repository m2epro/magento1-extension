<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    //########################################

    /**
     * @return array
     */
    public function getActionData()
    {
        return array_merge(

            array(
                'sku' => $this->getEbayListingProduct()->getSku()
            ),

            $this->getRequestVariations()->getData(),
            $this->getRequestCategories()->getData(),

            $this->getRequestPayment()->getData(),
            $this->getRequestReturn()->getData(),
            $this->getRequestShipping()->getData(),

            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    //########################################

    public function resetVariations()
    {
        $variations = $this->getListingProduct()->getVariations(true);
        if (empty($variations)) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variations_that_can_not_be_deleted'] = array();
        $this->getListingProduct()->setSettings('additional_data', $additionalData)->save();

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
            if (!empty($additionalData['ebay_mpn_value'])) {
                unset($additionalData['ebay_mpn_value']);
                $variation->setSettings('additional_data', $additionalData);

                $needSave = true;
            }

            $needSave && $variation->save();
        }
    }

    public function getTheSameProductAlreadyListed()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/ebay/connector/listing/', 'check_the_same_product_already_listed');

        if (!is_null($config) && $config != 1) {
            return NULL;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('status',array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED))
            ->addFieldToFilter('product_id',$this->getListingProduct()->getProductId())
            ->addFieldToFilter('account_id',$this->getAccount()->getId())
            ->addFieldToFilter('marketplace_id',$this->getMarketplace()->getId());

        $theSameListingProduct = $listingProductCollection->getFirstItem();

        if (!$theSameListingProduct->getId()) {
            return NULL;
        }

        return $theSameListingProduct;
    }

    public function getVariationAttributesWithSpacesAroundName()
    {
        /** @var Ess_M2ePro_Model_Listing_Product_Variation[] $variations */
        $variations = $this->getListingProduct()->getVariations(true);

        $resultAttributes = array();

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option[] $options */
            $options = $variation->getOptions(true);

            foreach ($options as $option) {
                if ($option->getAttribute() != trim($option->getAttribute())) {
                    $resultAttributes[] = $option->getAttribute();
                }
            }
        }

        return $resultAttributes;
    }

    public function getVariationOptionsWithSpacesAroundName()
    {
        /** @var Ess_M2ePro_Model_Listing_Product_Variation[] $variations */
        $variations = $this->getListingProduct()->getVariations(true);

        $resultOptions = array();

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option[] $options */
            $options = $variation->getOptions(true);

            foreach ($options as $option) {
                if ($option->getOption() != trim($option->getOption())) {
                    $resultOptions[] = $option->getOption();
                }
            }
        }

        return $resultOptions;
    }

    //########################################

    protected function getIsEpsImagesMode()
    {
        return NULL;
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

            $attrCode  = $specific->getData('value_custom_attribute');
            $attrTitle = $specific->getData('attribute_title');

            if (!array_key_exists($attrCode, $confAttributes) || $confAttributes[$attrCode] == $attrTitle) {
                continue;
            }

            $replacements[$confAttributes[$attrCode]] = $attrTitle;
        }

        if (empty($replacements)) {
            return $data;
        }

        $data = $this->doReplaceVariationSpecifics($data, $replacements);

        $data['variations_specifics_replacements'] = $replacements;

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
}