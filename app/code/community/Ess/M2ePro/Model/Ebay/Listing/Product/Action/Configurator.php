<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    const DATA_TYPE_IMAGES      = 'images';
    const DATA_TYPE_VARIATIONS  = 'variations';

    //########################################

    /**
     * @return array
     */
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

    //########################################

    /**
     * @return bool
     */
    public function isImagesAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    /**
     * @return $this
     */
    public function allowImages()
    {
        return $this->allow(self::DATA_TYPE_IMAGES);
    }

    /**
     * @return $this
     */
    public function disallowImages()
    {
        return $this->disallow(self::DATA_TYPE_IMAGES);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isVariationsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_VARIATIONS);
    }

    /**
     * @return $this
     */
    public function allowVariations()
    {
        return $this->allow(self::DATA_TYPE_VARIATIONS);
    }

    /**
     * @return $this
     */
    public function disallowVariations()
    {
        return $this->disallow(self::DATA_TYPE_VARIATIONS);
    }

    //########################################
}