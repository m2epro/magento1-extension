<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Images
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if (!$this->getConfigurator()->isImagesAllowed()) {
            return $data;
        }

        $this->searchNotFoundAttributes();

        $images = array(
            'offer' => $this->getAmazonListingProduct()->getListingSource()->getGalleryImages(),
        );

        if ($this->getAmazonListingProduct()->isExistDescriptionTemplate()) {
            $amazonDescriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
            $definitionSource = $amazonDescriptionTemplate->getDefinitionTemplate()->getSource(
                $this->getAmazonListingProduct()->getActualMagentoProduct()
            );

            $images['product'] = $definitionSource->getGalleryImages();

            if ($this->getVariationManager()->isRelationChildType()) {
                $images['variation_difference'] = $definitionSource->getVariationDifferenceImages();
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