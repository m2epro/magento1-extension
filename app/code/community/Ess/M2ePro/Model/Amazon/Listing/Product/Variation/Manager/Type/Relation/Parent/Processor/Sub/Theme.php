<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Theme
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

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistDescriptionTemplate() ||
            !$this->getProcessor()->getAmazonDescriptionTemplate()->isNewAsinAccepted()
        ) {
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

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistDescriptionTemplate() ||
            !$this->getProcessor()->getAmazonDescriptionTemplate()->isNewAsinAccepted() ||
            empty($possibleThemes)
        ) {
            return;
        }

        if ($this->getProcessor()->isGeneralIdSet()) {
            $this->processExistProduct();
            return;
        }

        $this->processNewProduct();
    }

    //########################################

    private function processExistProduct()
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
        if (is_null($matchedTheme)) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false, false);
    }

    private function processNewProduct()
    {
        $possibleThemes = $this->getProcessor()->getPossibleThemes();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme $themeMatcher */
        $themeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Theme');
        $themeMatcher->setThemes($possibleThemes);
        $themeMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());

        $matchedTheme = $themeMatcher->getMatchedTheme();
        if (is_null($matchedTheme)) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false, false);
    }

    //########################################
}