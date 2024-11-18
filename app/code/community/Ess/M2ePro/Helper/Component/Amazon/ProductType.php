<?php

class Ess_M2ePro_Helper_Component_Amazon_ProductType
{
    const SPECIFIC_KEY_NAME = 'item_name#array/value';
    const SPECIFIC_KEY_BRAND = 'brand#array/value';
    const SPECIFIC_KEY_MANUFACTURER = 'manufacturer#array/value';
    const SPECIFIC_KEY_DESCRIPTION = 'product_description#array/value';
    const SPECIFIC_KEY_COUNTRY_OF_ORIGIN = 'country_of_origin#array/value';
    const SPECIFIC_KEY_ITEM_PACKAGE_WEIGHT = 'item_package_weight#array/value';
    const SPECIFIC_KEY_MAIN_PRODUCT_IMAGE_LOCATOR = 'main_product_image_locator#array/media_location';
    const SPECIFIC_KEY_MAIN_OFFER_IMAGE_LOCATOR = 'main_offer_image_locator#array/media_location';
    const SPECIFIC_KEY_OTHER_OFFER_IMAGE_LOCATOR = 'other_offer_image_locator_1#array/media_location';
    const SPECIFIC_KEY_BULLET_POINT = 'bullet_point#array/value';

    /**
     * @return int
     */
    public static function getTimezoneShift()
    {
        $dateLocal = new DateTime(Mage::helper('M2ePro')->gmtDateToTimezone('2024-01-01'));
        $timestampUTC = Mage::helper('M2ePro')->timezoneDateToGmt($dateLocal->format('Y-m-d H:i:s'), true);

        return $timestampUTC - $dateLocal->getTimestamp();
    }

    public static function getMainImageSpecifics()
    {
        return array(
            self::SPECIFIC_KEY_MAIN_PRODUCT_IMAGE_LOCATOR,
            self::SPECIFIC_KEY_MAIN_OFFER_IMAGE_LOCATOR,
        );
    }

    public static function getOtherImagesSpecifics()
    {
        return array(
            'other_product_image_locator_1#array/media_location',
            'other_offer_image_locator_1#array/media_location',
        );
    }

    public static function getRecommendedBrowseNodesLink($marketplaceId)
    {
        $map = array(
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_UK
            => 'https://sellercentral.amazon.co.uk/help/hub/reference/G201742570',
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_IT
            => 'https://sellercentral.amazon.it/help/hub/reference/G201742570',
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_FR
            => 'https://sellercentral.amazon.fr/help/hub/reference/G201742570',
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_DE
            => 'https://sellercentral.amazon.de/help/hub/reference/G201742570',
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_ES
            => 'https://sellercentral.amazon.es/help/hub/reference/G201742570',
        );

        if (!array_key_exists($marketplaceId, $map)) {
            return '';
        }

        return Mage::helper('M2ePro')->__(
            '<a style="display: block; margin-top: -10px" href="%url%">View latest Browse Node ID List</a>',
            array('url' => $map[$marketplaceId])
        );
    }
}