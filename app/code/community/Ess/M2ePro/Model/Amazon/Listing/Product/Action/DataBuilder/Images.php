<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Images
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        $data = array();

        $this->searchNotFoundAttributes();

        $links = array();
        foreach ($this->getAmazonListingProduct()->getListingSource()->getGalleryImages() as $image) {

            if (!$image->getUrl()) {
                continue;
            }
            $links[] = $image->getUrl();
        }

        $images = array(
            'offer' => $links,
        );

        if ($this->getAmazonListingProduct()->isExistDescriptionTemplate()) {

            $amazonDescriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
            $definitionSource = $amazonDescriptionTemplate->getDefinitionTemplate()->getSource(
                $this->getAmazonListingProduct()->getActualMagentoProduct()
            );

            $links = array();
            foreach ($definitionSource->getGalleryImages() as $image) {

                if (!$image->getUrl()) {
                    continue;
                }
                $links[] = $image->getUrl();
            }
            $images['product'] = $links;

            if ($this->getVariationManager()->isRelationChildType()) {

                $links = array();
                foreach ($definitionSource->getVariationDifferenceImages() as $image) {

                    if (!$image->getUrl()) {
                        continue;
                    }
                    $links[] = $image->getUrl();
                }
                $images['variation_difference'] = $links;
            }
        }

        $this->processNotFoundAttributes('Images');

        if (!empty($images['offer'])) {
            $data['images_data']['offer'] = $images['offer'];
        }

        if (!empty($images['product'])) {
            $data['images_data']['product'] = $images['product'];
        }

        if (!empty($images['variation_difference'])) {
            $data['images_data']['variation_difference'] = $images['variation_difference'];
        }

        return $data;
    }

    //########################################
}