<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_MatchedAttributes
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        if (!$this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            return;
        }

        $productAttributes = $this->getProcessor()->getTypeModel()->getProductAttributes();
        $matchedAttributes = $this->getProcessor()->getTypeModel()->getMatchedAttributes();

        if (count($productAttributes) != count($matchedAttributes) ||
            array_diff($productAttributes, array_keys($matchedAttributes))
        ) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
            return;
        }

        $channelAttributes = $this->getProcessor()->getTypeModel()->getChannelAttributes();

        if (count($channelAttributes) != count($matchedAttributes) ||
            array_diff($channelAttributes, array_values($matchedAttributes))
        ) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
            return;
        }

        if ($this->getProcessor()->getTypeModel()->getVirtualChannelAttributes()) {
            $matchedAttributes = $this->getProcessor()->getTypeModel()->getRealMatchedAttributes();
        }

        $channelMatchedAttributes = array_values($matchedAttributes);
        $walmartListingProduct = $this->getProcessor()->getWalmartListingProduct();
        $possibleChannelAttributes = $walmartListingProduct->isExistsProductType()
            ? $walmartListingProduct
                ->getProductType()
                ->getVariationAttributes()
            : array();

        if (array_diff($channelMatchedAttributes, $possibleChannelAttributes)) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
        }
    }

    protected function execute()
    {
        if ($this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            return;
        }

        if (!$this->getProcessor()->getTypeModel()->hasChannelAttributes()) {
            return;
        }

        $channelAttributes = $this->getProcessor()->getTypeModel()->getChannelAttributes();

        $this->getProcessor()
            ->getTypeModel()
            ->setMatchedAttributes($this->matchAttributes($channelAttributes), false);
    }

    //########################################

    protected function matchAttributes($channelAttributes)
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Matcher_Attribute');
        $attributeMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());
        $attributeMatcher->setDestinationAttributes($channelAttributes);
        $attributeMatcher->canUseDictionary(true);

        if (!$attributeMatcher->isAmountEqual() || !$attributeMatcher->isFullyMatched()) {
            return array();
        }

        return $attributeMatcher->getMatchedAttributes();
    }

    //########################################
}
