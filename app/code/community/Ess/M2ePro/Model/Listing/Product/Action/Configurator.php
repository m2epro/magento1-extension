<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const MODE_INCLUDING = 'including';
    const MODE_EXCLUDING = 'excluding';

    //########################################

    protected $mode = self::MODE_EXCLUDING;

    protected $allowedDataTypes = array();

    protected $params = array();

    //########################################

    public function __construct()
    {
        $this->enableAll();
    }

    //########################################

    abstract public function getAllDataTypes();

    //########################################

    public function enableAll()
    {
        $this->mode             = self::MODE_EXCLUDING;
        $this->allowedDataTypes = $this->getAllDataTypes();

        return $this;
    }

    public function disableAll()
    {
        $this->mode             = self::MODE_INCLUDING;
        $this->allowedDataTypes = array();

        return $this;
    }

    //########################################

    public function getMode()
    {
        return $this->mode;
    }

    public function isExcludingMode()
    {
        return $this->mode == self::MODE_EXCLUDING;
    }

    public function isIncludingMode()
    {
        return $this->mode == self::MODE_INCLUDING;
    }

    // ---------------------------------------

    public function setModeExcluding()
    {
        $this->mode = self::MODE_EXCLUDING;
        return $this;
    }

    public function setModeIncluding()
    {
        $this->mode = self::MODE_INCLUDING;
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getAllowedDataTypes()
    {
        return $this->allowedDataTypes;
    }

    //########################################

    public function isAllowed($dataType)
    {
        $this->validateDataType($dataType);
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

        $this->allowedDataTypes = array_diff($this->allowedDataTypes, array($dataType));
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return bool
     */
    public function isDataConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        return !array_diff($configurator->getAllowedDataTypes(), $this->getAllowedDataTypes());
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return bool
     */
    public function isParamsConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        return !array_diff_assoc($configurator->getParams(), $this->getParams());
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function mergeData(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isExcludingMode()) {
            $this->mode = self::MODE_EXCLUDING;
        }

        $this->allowedDataTypes = array_unique(array_merge(
            $this->getAllowedDataTypes(), $configurator->getAllowedDataTypes()
        ));

        return $this;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function mergeParams(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        $this->params = array_unique(array_merge(
            $this->getParams(), $configurator->getParams()
        ));

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'mode'               => $this->mode,
            'allowed_data_types' => $this->allowedDataTypes,
            'params'             => $this->params,
        );
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->mode = $data['mode'];

        if (!empty($data['allowed_data_types'])) {
            if (!is_array($data['allowed_data_types']) ||
                array_diff($data['allowed_data_types'], $this->getAllDataTypes())
            ) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'Allowed data types are invalid.',
                    array('allowed_data_types' => $data['allowed_data_types'])
                );
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

    //########################################

    protected function validateDataType($dataType)
    {
        if (!in_array($dataType, $this->getAllDataTypes())) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Data type is invalid', array('data_type' => $dataType)
            );
        }
    }

    //########################################
}