<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const MODE_FULL    = 'full';
    const MODE_PARTIAL = 'partial';

    // ########################################

    protected $mode = self::MODE_FULL;

    protected $allowedDataTypes = array();

    protected $params = array();

    // ########################################

    public function getAllModes()
    {
        return array(
            self::MODE_FULL,
            self::MODE_PARTIAL,
        );
    }

    // ########################################

    public function setMode($mode)
    {
        if (!in_array($mode, $this->getAllModes())) {
            throw new InvalidArgumentException('Mode is invalid.');
        }

        $this->mode = $mode;
        $this->allowedDataTypes = array();

        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    // ########################################

    public function isFullMode()
    {
        return $this->mode == self::MODE_FULL;
    }

    public function setFullMode()
    {
        return $this->setMode(self::MODE_FULL);
    }

    // ----------------------------------------

    public function isPartialMode()
    {
        return $this->mode == self::MODE_PARTIAL;
    }

    public function setPartialMode()
    {
        return $this->setMode(self::MODE_PARTIAL);
    }

    // ########################################

    abstract public function getAllDataTypes();

    // ########################################

    public function isAllAllowed()
    {
        if ($this->isFullMode()) {
            return true;
        }

        return !array_diff($this->getAllDataTypes(), $this->getAllowedDataTypes());
    }

    public function getAllowedDataTypes()
    {
        if ($this->isFullMode()) {
            return $this->getAllDataTypes();
        }

        return $this->allowedDataTypes;
    }

    // ########################################

    public function isAllowed($dataType)
    {
        $this->validateDataType($dataType);

        if ($this->isFullMode()) {
            return true;
        }

        return in_array($dataType, $this->allowedDataTypes);
    }

    public function allow($dataType)
    {
        $this->validateDataType($dataType);

        if ($this->isAllowed($dataType)) {
            return $this;
        }

        $this->allowedDataTypes[] = $dataType;
        return $this;
    }

    public function disallow($dataType)
    {
        $this->validateDataType($dataType);

        if (!$this->isAllowed($dataType)) {
            return $this;
        }

        if ($this->isFullMode()) {
            $this->setPartialMode();
            $this->allowedDataTypes = array_diff($this->getAllDataTypes(), array($dataType));

            return $this;
        }

        $this->allowedDataTypes = array_diff($this->allowedDataTypes, array($dataType));
        return $this;
    }

    // ########################################

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    // ########################################

    public function isDataConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($this->isAllAllowed()) {
            return true;
        }

        if ($configurator->isAllAllowed()) {
            return false;
        }

        return !array_diff($configurator->getAllowedDataTypes(), $this->getAllowedDataTypes());
    }

    public function isParamsConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        return !array_diff_assoc($configurator->getParams(), $this->getParams());
    }

    // ----------------------------------------

    public function mergeData(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($this->isAllAllowed()) {
            return $this;
        }

        if ($configurator->isAllAllowed()) {
            $this->setFullMode();
            return $this;
        }

        if (!$this->isPartialMode()) {
            $this->setPartialMode();
        }

        $this->allowedDataTypes = array_unique(array_merge(
            $this->getAllowedDataTypes(), $configurator->getAllowedDataTypes()
        ));

        return $this;
    }

    public function mergeParams(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        $this->params = array_unique(array_merge(
            $this->getParams(), $configurator->getParams()
        ));

        return $this;
    }

    // ########################################

    public function getData()
    {
        return array(
            'mode'               => $this->mode,
            'allowed_data_types' => $this->allowedDataTypes,
            'params'             => $this->params,
        );
    }

    public function setData(array $data)
    {
        if (!empty($data['mode'])) {
            if (!in_array($data['mode'], $this->getAllModes())) {
                throw new InvalidArgumentException('Mode is invalid.');
            }

            $this->mode = $data['mode'];
        }

        if (!empty($data['allowed_data_types'])) {
            if (!is_array($data['allowed_data_types']) ||
                array_diff($data['allowed_data_types'], $this->getAllDataTypes())
            ) {
                throw new InvalidArgumentException('Allowed data types are invalid.');
            }

            $this->allowedDataTypes = $data['allowed_data_types'];
        }

        if (!empty($data['params'])) {
            if (!is_array($data['params'])) {
                throw new InvalidArgumentException('Params has invalid format.');
            }

            $this->params = $data['params'];
        }

        return $this;
    }

    // ########################################

    protected function validateDataType($dataType)
    {
        if (!in_array($dataType, $this->getAllDataTypes())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Data type is invalid');
        }
    }

    // ########################################
}