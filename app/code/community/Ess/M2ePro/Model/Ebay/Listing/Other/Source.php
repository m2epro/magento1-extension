<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Source
{
    const QTY_SOURCE_NONE = 0;
    const QTY_SOURCE_PRODUCT = 1;
    const QTY_SOURCE_ATTRIBUTE = 2;
    const QTY_SOURCE_PRODUCT_FIXED = 3;

    const PRICE_SOURCE_NONE = 0;
    const PRICE_SOURCE_PRODUCT = 1;
    const PRICE_SOURCE_SPECIAL = 2;
    const PRICE_SOURCE_ATTRIBUTE = 3;

    const TITLE_SOURCE_NONE = 0;
    const TITLE_SOURCE_PRODUCT = 1;
    const TITLE_SOURCE_ATTRIBUTE = 2;

    const SUB_TITLE_SOURCE_NONE = 0;
    const SUB_TITLE_SOURCE_ATTRIBUTE = 1;

    const DESCRIPTION_SOURCE_NONE = 0;
    const DESCRIPTION_SOURCE_PRODUCT_MAIN = 1;
    const DESCRIPTION_SOURCE_PRODUCT_SHORT = 2;
    const DESCRIPTION_SOURCE_ATTRIBUTE = 3;

    // ########################################

    public function getSource($sourceId)
    {
        $value = Mage::helper('M2ePro/Module')
                     ->getSynchronizationConfig()
                     ->getGroupValue('/ebay/other_listing/source/', $sourceId);

        return $value;
    }

    public function getAttributes()
    {
        $attributes = Mage::helper('M2ePro/Module')
            ->getSynchronizationConfig()
            ->getAllGroupValues('/ebay/other_listing/source/attribute/');

        return $attributes;
    }

    // ########################################

    public function getQtySource()
    {
        return (int)$this->getSource('qty');
    }

    public function isQtySourceNone()
    {
        return $this->getQtySource() == self::QTY_SOURCE_NONE;
    }

    public function isQtySourceProduct()
    {
        return $this->getQtySource() == self::QTY_SOURCE_PRODUCT;
    }

    public function isQtySourceAttribute()
    {
        return $this->getQtySource() == self::QTY_SOURCE_ATTRIBUTE;
    }

    public function isQtySourceProductFixed()
    {
        return $this->getQtySource() == self::QTY_SOURCE_PRODUCT_FIXED;
    }

    //------------------------------------------

    public function getPriceSource()
    {
        return (int)$this->getSource('price');
    }

    public function isPriceSourceNone()
    {
        return $this->getPriceSource() == self::PRICE_SOURCE_NONE;
    }

    public function isPriceSourceProduct()
    {
        return $this->getPriceSource() == self::PRICE_SOURCE_PRODUCT;
    }

    public function isPriceSourceSpecial()
    {
        return $this->getPriceSource() == self::PRICE_SOURCE_SPECIAL;
    }

    public function isPriceSourceAttribute()
    {
        return $this->getPriceSource() == self::PRICE_SOURCE_ATTRIBUTE;
    }

    //------------------------------------------

    public function getTitleSource()
    {
        return (int)$this->getSource('title');
    }

    public function isTitleSourceNone()
    {
        return $this->getTitleSource() == self::TITLE_SOURCE_NONE;
    }

    public function isTitleSourceProduct()
    {
        return $this->getTitleSource() == self::TITLE_SOURCE_PRODUCT;
    }

    public function isTitleSourceAttribute()
    {
        return $this->getTitleSource() == self::TITLE_SOURCE_ATTRIBUTE;
    }

    //------------------------------------------

    public function getSubTitleSource()
    {
        return (int)$this->getSource('sub_title');
    }

    public function isSubTitleSourceNone()
    {
        return $this->getSubTitleSource() == self::SUB_TITLE_SOURCE_NONE;
    }

    public function isSubTitleSourceAttribute()
    {
        return $this->getSubTitleSource() == self::SUB_TITLE_SOURCE_ATTRIBUTE;
    }

    //------------------------------------------

    public function getDescriptionSource()
    {
        return (int)$this->getSource('description');
    }

    public function isDescriptionSourceNone()
    {
        return $this->getDescriptionSource() == self::DESCRIPTION_SOURCE_NONE;
    }

    public function isDescriptionSourceProductMain()
    {
        return $this->getDescriptionSource() == self::DESCRIPTION_SOURCE_PRODUCT_MAIN;
    }

    public function isDescriptionSourceProductShort()
    {
        return $this->getDescriptionSource() == self::DESCRIPTION_SOURCE_PRODUCT_SHORT;
    }

    public function isDescriptionSourceAttribute()
    {
        return $this->getDescriptionSource() == self::DESCRIPTION_SOURCE_ATTRIBUTE;
    }

    // ########################################

    public function getQtyAttribute()
    {
        $attributes = $this->getAttributes();

        if (isset($attributes['qty'])) {
            return $attributes['qty'];
        } else {
            return NULL;
        }
    }

    public function getPriceAttribute()
    {
        $attributes = $this->getAttributes();

        if (isset($attributes['price'])) {
            return $attributes['price'];
        } else {
            return NULL;
        }
    }

    public function getTitleAttribute()
    {
        $attributes = $this->getAttributes();

        if (isset($attributes['title'])) {
            return $attributes['title'];
        } else {
            return NULL;
        }
    }

    public function getSubTitleAttribute()
    {
        $attributes = $this->getAttributes();

        if (isset($attributes['sub_title'])) {
            return $attributes['sub_title'];
        } else {
            return NULL;
        }
    }

    public function getDescriptionAttribute()
    {
        $attributes = $this->getAttributes();

        if (isset($attributes['description'])) {
            return $attributes['description'];
        } else {
            return NULL;
        }
    }

    // ########################################

    public function getTrackingAttributes()
    {
        $tempArray = array();

        $temp = $this->getTitleAttribute();
        if ($this->isTitleSourceAttribute() && !is_null($temp)) {
            $tempArray[] = $temp;
        } else if ($this->isTitleSourceProduct()) {
            $tempArray[] = 'name';
        }

        $temp = $this->getSubTitleAttribute();
        if ($this->isSubTitleSourceAttribute() && !is_null($temp)) {
            $tempArray[] = $temp;
        }

        $temp = $this->getDescriptionAttribute();
        if ($this->isDescriptionSourceAttribute() && !is_null($temp)) {
            $tempArray[] = $temp;
        } else if ($this->isDescriptionSourceProductMain()) {
            $tempArray[] = 'description';
        } else if ($this->isDescriptionSourceProductShort()) {
            $tempArray[] = 'short_description';
        }

        $temp = $this->getPriceAttribute();
        if ($this->isPriceSourceAttribute() && !is_null($temp)) {
            $tempArray[] = $temp;
        }

        $temp = $this->getQtyAttribute();
        if ($this->isQtySourceAttribute() && !is_null($temp)) {
            $tempArray[] = $temp;
        }

        return array_unique($tempArray);
    }

    // ########################################
}