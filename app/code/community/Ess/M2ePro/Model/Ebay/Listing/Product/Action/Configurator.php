<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    const DATA_TYPE_IMAGES      = 'images';
    const DATA_TYPE_VARIATIONS  = 'variations';

    // ########################################

    public function getAllDataTypes()
    {
        return array_merge(
            parent::getAllDataTypes(),
            array(
                self::DATA_TYPE_IMAGES,
                self::DATA_TYPE_VARIATIONS,
            )
        );
    }

    // ########################################

    public function isImagesAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    public function allowImages()
    {
        return $this->allow(self::DATA_TYPE_IMAGES);
    }

    public function disallowImages()
    {
        return $this->disallow(self::DATA_TYPE_IMAGES);
    }

    // ----------------------------------------

    public function isVariationsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_VARIATIONS);
    }

    public function allowVariations()
    {
        return $this->allow(self::DATA_TYPE_VARIATIONS);
    }

    public function disallowVariations()
    {
        return $this->disallow(self::DATA_TYPE_VARIATIONS);
    }

    // ########################################
}