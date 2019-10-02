<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Vocabulary extends Ess_M2ePro_Helper_Module_Product_Variation_Vocabulary
{
    //########################################

    public function addAttribute($productAttribute, $channelAttribute)
    {
        if (!parent::addAttribute($productAttribute, $channelAttribute)) {
            return;
        }

        $affectedParentListingsProducts = $this->getParentListingsProductsAffectedToAttribute($channelAttribute);
        if (empty($affectedParentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    public function addOption($productOption, $channelOption, $channelAttribute)
    {
        if (!parent::addOption($productOption, $channelOption, $channelAttribute)) {
            return;
        }

        $affectedParentListingsProducts = $this->getParentListingsProductsAffectedToOption(
            $channelAttribute, $channelOption
        );

        if (empty($affectedParentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    //########################################

    protected function getParentListingsProductsAffectedToAttribute($channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $existListingProductCollection */
        $existListingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $existListingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $existListingProductCollection->addFieldToFilter('general_id', array('notnull' => true));

        $existListingProductCollection->getSelect()->where(
            'additional_data NOT REGEXP ?', '"variation_matched_attributes":{.+}'
        );
        $existListingProductCollection->addFieldToFilter(
            'additional_data',
            array('regexp'=> '"variation_channel_attributes_sets":.*"'.$channelAttribute.'":')
        );

        $affectedListingsProducts = $existListingProductCollection->getItems();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $newListingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $newListingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $newListingProductCollection->addFieldToFilter('is_general_id_owner', 1);
        $newListingProductCollection->addFieldToFilter('general_id', array('null' => true));

        $newListingProductCollection->getSelect()->where(
            'additional_data NOT REGEXP ?', '"variation_channel_theme":\s*".*"'
        );

        /** @var Ess_M2ePro_Model_Listing_Product[] $newListingsProducts */
        $newListingsProducts = $newListingProductCollection->getItems();

        if (empty($newListingsProducts)) {
            return $affectedListingsProducts;
        }

        $productRequirementsCache = array();

        foreach ($newListingsProducts as $newListingProduct) {
            if (isset($affectedListingsProducts[$newListingProduct->getId()])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct      = $newListingProduct->getChildObject();
            $amazonDescriptionTemplate = $amazonListingProduct->getAmazonDescriptionTemplate();

            $productAttributes = $amazonListingProduct->getVariationManager()->getTypeModel()->getProductAttributes();
            if (empty($productAttributes)) {
                continue;
            }

            if (isset($productRequirementsCache[$amazonDescriptionTemplate->getId()][count($productAttributes)])) {
                $affectedListingsProducts[$newListingProduct->getId()] = $newListingProduct;
                continue;
            }

            $marketplaceDetails = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
            $marketplaceDetails->setMarketplaceId($newListingProduct->getListing()->getMarketplaceId());

            $productDataNick = $amazonDescriptionTemplate->getProductDataNick();

            foreach ($marketplaceDetails->getVariationThemes($productDataNick) as $themeNick => $themeData) {
                $themeAttributes = $themeData['attributes'];

                if (count($themeAttributes) != count($productAttributes)) {
                    continue;
                }

                if (!in_array($channelAttribute, $themeAttributes)) {
                    continue;
                }

                $affectedListingsProducts[$newListingProduct->getId()] = $newListingProduct;
                $productRequirementsCache[$amazonDescriptionTemplate->getId()][count($productAttributes)] = true;

                break;
            }
        }

        return $affectedListingsProducts;
    }

    protected function getParentListingsProductsAffectedToOption($channelAttribute, $channelOption)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $listingProductCollection->addFieldToFilter('general_id', array('notnull' => true));

        $listingProductCollection->addFieldToFilter(
            'additional_data',
            array('regexp'=> '"variation_matched_attributes":{.+}')
        );
        $listingProductCollection->addFieldToFilter(
            'additional_data',
            array('regexp'=>
                  '"variation_channel_attributes_sets":.*"'.$channelAttribute.'":\s*[\[|{].*'.$channelOption.'.*[\]|}]'
            )
        );

        return $listingProductCollection->getItems();
    }

    //########################################
}
