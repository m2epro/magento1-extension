<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Theme
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        $currentTheme = $this->getProcessor()->getTypeModel()->getChannelTheme();
        if (empty($currentTheme)) {
            return;
        }

        if (!$this->getProcessor()->isGeneralIdOwner()) {
            $this->getProcessor()->getTypeModel()->resetChannelTheme(false);
            return;
        }

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistProductTypeTemplate()) {
            $this->getProcessor()->getTypeModel()->resetChannelTheme(false);
            return;
        }

        $possibleThemes = $this->getProcessor()->getPossibleThemes();
        if (empty($possibleThemes[$currentTheme])) {
            $this->getProcessor()->getTypeModel()->resetChannelTheme(false);
            return;
        }

        if (!$this->getProcessor()->getTypeModel()->isActualChannelTheme()) {
            $this->getProcessor()->getTypeModel()->resetChannelTheme(false);
        }
    }

    protected function execute()
    {
        if ($this->getProcessor()->getTypeModel()->getChannelTheme() || !$this->getProcessor()->isGeneralIdOwner()) {
            return;
        }

        $possibleThemes = $this->getProcessor()->getPossibleThemes();

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistProductTypeTemplate() ||
            empty($possibleThemes)
        ) {
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
        $possibleThemes = $this->getProcessor()->getPossibleThemes();
        $channelAttributes = array_keys(
            $this->getProcessor()->getTypeModel()->getRealChannelAttributesSets()
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme $themeMatcher */
        $themeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Theme');
        $themeMatcher->setThemes($possibleThemes);
        $themeMatcher->setSourceAttributes($channelAttributes);

        $matchedTheme = $themeMatcher->getMatchedTheme();
        if ($matchedTheme === null) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false, false);
    }

    protected function processNewProduct()
    {
        $possibleThemes = $this->getProcessor()->getPossibleThemes();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme $themeMatcher */
        $themeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Theme');
        $themeMatcher->setThemes($possibleThemes);
        $themeMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());

        $matchedTheme = $themeMatcher->getMatchedTheme();
        if ($matchedTheme === null) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false, false);
    }

    //########################################
}
