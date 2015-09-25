<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const MODE_EMPTY = 'empty';

    const DATA_TYPE_GENERAL     = 'general';
    const DATA_TYPE_QTY         = 'qty';
    const DATA_TYPE_PRICE       = 'price';
    const DATA_TYPE_TITLE       = 'title';
    const DATA_TYPE_SUBTITLE    = 'subtitle';
    const DATA_TYPE_DESCRIPTION = 'description';

    // ########################################

    public function getAllModes()
    {
        return array_merge(
            parent::getAllModes(),
            array(self::MODE_EMPTY)
        );
    }

    // ########################################

    public function isEmptyMode()
    {
        return $this->mode == self::MODE_EMPTY;
    }

    public function setEmptyMode()
    {
        return $this->setMode(self::MODE_EMPTY);
    }

    // ########################################

    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_GENERAL,
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_TITLE,
            self::DATA_TYPE_SUBTITLE,
            self::DATA_TYPE_DESCRIPTION,
        );
    }

    // ########################################

    public function isAllAllowed()
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllAllowed();
    }

    public function getAllowedDataTypes()
    {
        if ($this->isEmptyMode()) {
            return array();
        }

        return parent::getAllowedDataTypes();
    }

    // ########################################

    public function isAllowed($dataType)
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllowed($dataType);
    }

    public function allow($dataType)
    {
        $this->validateDataType($dataType);

        if ($this->isAllowed($dataType) || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = $dataType;
        return $this;
    }

    // ########################################

    public function isGeneralAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_GENERAL);
    }

    public function allowGeneral()
    {
        return $this->allow(self::DATA_TYPE_GENERAL);
    }

    public function disallowGeneral()
    {
        return $this->disallow(self::DATA_TYPE_GENERAL);
    }

    // ----------------------------------------

    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    public function allowQty()
    {
        return $this->allow(self::DATA_TYPE_QTY);
    }

    public function disallowQty()
    {
        return $this->disallow(self::DATA_TYPE_QTY);
    }

    // ----------------------------------------

    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    public function allowPrice()
    {
        return $this->allow(self::DATA_TYPE_PRICE);
    }

    public function disallowPrice()
    {
        return $this->disallow(self::DATA_TYPE_PRICE);
    }

    // ----------------------------------------

    public function isTitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_TITLE);
    }

    public function allowTitle()
    {
        return $this->allow(self::DATA_TYPE_TITLE);
    }

    public function disallowTitle()
    {
        return $this->disallow(self::DATA_TYPE_TITLE);
    }

    // ----------------------------------------

    public function isSubtitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SUBTITLE);
    }

    public function allowSubtitle()
    {
        return $this->allow(self::DATA_TYPE_SUBTITLE);
    }

    public function disallowSubtitle()
    {
        return $this->disallow(self::DATA_TYPE_SUBTITLE);
    }

    // ----------------------------------------

    public function isDescriptionAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DESCRIPTION);
    }

    public function allowDescription()
    {
        return $this->allow(self::DATA_TYPE_DESCRIPTION);
    }

    public function disallowDescription()
    {
        return $this->disallow(self::DATA_TYPE_DESCRIPTION);
    }

    // ########################################

    public function isDataConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isEmptyMode()) {
            return true;
        }

        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isDataConsists($configurator);
    }

    // -----------------------------------------

    public function mergeData(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isEmptyMode()) {
            return $this;
        }

        return parent::mergeData($configurator);
    }

    // ########################################
}