<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_MatchedAttributes
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
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

        if ($this->getProcessor()->isGeneralIdSet()) {
            $channelAttributes = $this->getProcessor()->getTypeModel()->getChannelAttributes();

            if (count($channelAttributes) != count($matchedAttributes) ||
                array_diff($channelAttributes, array_values($matchedAttributes))
            ) {
                $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
            }

            return;
        }

        if (!$this->getProcessor()->isGeneralIdOwner()) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
            return;
        }

        $channelTheme = $this->getProcessor()->getTypeModel()->getChannelTheme();
        if (!$channelTheme) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
            return;
        }

        $possibleThemes = $this->getProcessor()->getPossibleThemes();
        $themeAttributes = $possibleThemes[$channelTheme]['attributes'];

        if ($this->getProcessor()->getTypeModel()->getVirtualChannelAttributes()) {
            $matchedAttributes = $this->getProcessor()->getTypeModel()->getRealMatchedAttributes();
        }

        $channelMatchedAttributes = array_values($matchedAttributes);

        sort($themeAttributes);
        sort($channelMatchedAttributes);

        if ($themeAttributes != $channelMatchedAttributes) {
            $this->getProcessor()->getTypeModel()->setMatchedAttributes(array(), false);
        }
    }

    protected function execute()
    {
        if ($this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            return;
        }

        if (!$this->getProcessor()->isGeneralIdOwner() && !$this->getProcessor()->isGeneralIdSet()) {
            return;
        }

        if (!$this->getProcessor()->isGeneralIdSet() && !$this->getProcessor()->getTypeModel()->getChannelTheme()) {
            return;
        }

        $additionalData = $this->getProcessor()->getListingProduct()->getAdditionalData();
        if (!empty($additionalData['running_migration_to_product_types'])) {
            return;
        }

        if ($this->getProcessor()->isGeneralIdSet()) {
            $this->processExistProduct();
            return;
        }

        $this->processNewProduct();
    }

    //########################################

    protected function processExistProduct()
    {
        $channelAttributes = $this->getProcessor()->getTypeModel()->getChannelAttributes();

        $this->getProcessor()
            ->getTypeModel()
            ->setMatchedAttributes($this->matchAttributes($channelAttributes), false);
    }

    protected function processNewProduct()
    {
        $channelThemes = $this->getProcessor()->getPossibleThemes();
        $themeAttributes = $channelThemes[$this->getProcessor()->getTypeModel()->getChannelTheme()]['attributes'];

        $this->getProcessor()
            ->getTypeModel()
            ->setMatchedAttributes($this->matchAttributes($themeAttributes), true);
    }

    // ---------------------------------------

    protected function matchAttributes($channelAttributes, $canUseDictionary = true)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $attributeMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());
        $attributeMatcher->setDestinationAttributes($channelAttributes);
        $attributeMatcher->canUseDictionary($canUseDictionary);

        if (!$attributeMatcher->isAmountEqual() || !$attributeMatcher->isFullyMatched()) {
            return array();
        }

        return $attributeMatcher->getMatchedAttributes();
    }

    //########################################
}
