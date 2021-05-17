<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract */
    protected $_model;

    /** @var array */
    protected $_rawData;

    //########################################

    /**
     * @param Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract $model
     * @param array $rawData
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract
     * @throws Exception
     */
    public function build($model, array $rawData)
    {
        if (empty($rawData)) {
            return $model;
        }

        $this->_model   = $model;
        $this->_rawData = $rawData;

        $prepareData = $this->prepareData();
        $this->_model->addData($prepareData);
        $this->_model->save();

        return $this->_model;
    }

    //########################################

    /**
     * @return array
     */
    abstract protected function prepareData();

    /**
     * @return array
     */
    abstract public function getDefaultData();

    //########################################

    public function getModel()
    {
        return $this->_model;
    }

    //########################################
}
