<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
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

    //########################################

    /**
     * @return array
     */
    public function getAllModes()
    {
        return array_merge(
            parent::getAllModes(),
            array(self::MODE_EMPTY)
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function isEmptyMode()
    {
        return $this->mode == self::MODE_EMPTY;
    }

    /**
     * @return $this
     */
    public function setEmptyMode()
    {
        return $this->setMode(self::MODE_EMPTY);
    }

    //########################################

    /**
     * @return array
     */
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

    //########################################

    /**
     * @return bool
     */
    public function isAllAllowed()
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllAllowed();
    }

    /**
     * @return array
     */
    public function getAllowedDataTypes()
    {
        if ($this->isEmptyMode()) {
            return array();
        }

        return parent::getAllowedDataTypes();
    }

    //########################################

    /**
     * @param $dataType
     * @return bool
     */
    public function isAllowed($dataType)
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllowed($dataType);
    }

    /**
     * @param $dataType
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function allow($dataType)
    {
        $this->validateDataType($dataType);

        if ($this->isAllowed($dataType) || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = $dataType;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function isGeneralAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_GENERAL);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowGeneral()
    {
        return $this->allow(self::DATA_TYPE_GENERAL);
    }

    /**
     * @return $this
     */
    public function disallowGeneral()
    {
        return $this->disallow(self::DATA_TYPE_GENERAL);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowQty()
    {
        return $this->allow(self::DATA_TYPE_QTY);
    }

    /**
     * @return $this
     */
    public function disallowQty()
    {
        return $this->disallow(self::DATA_TYPE_QTY);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowPrice()
    {
        return $this->allow(self::DATA_TYPE_PRICE);
    }

    /**
     * @return $this
     */
    public function disallowPrice()
    {
        return $this->disallow(self::DATA_TYPE_PRICE);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_TITLE);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowTitle()
    {
        return $this->allow(self::DATA_TYPE_TITLE);
    }

    /**
     * @return $this
     */
    public function disallowTitle()
    {
        return $this->disallow(self::DATA_TYPE_TITLE);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isSubtitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SUBTITLE);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowSubtitle()
    {
        return $this->allow(self::DATA_TYPE_SUBTITLE);
    }

    /**
     * @return $this
     */
    public function disallowSubtitle()
    {
        return $this->disallow(self::DATA_TYPE_SUBTITLE);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isDescriptionAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DESCRIPTION);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    public function allowDescription()
    {
        return $this->allow(self::DATA_TYPE_DESCRIPTION);
    }

    /**
     * @return $this
     */
    public function disallowDescription()
    {
        return $this->disallow(self::DATA_TYPE_DESCRIPTION);
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return bool
     */
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

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function mergeData(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isEmptyMode()) {
            return $this;
        }

        return parent::mergeData($configurator);
    }

    //########################################
}