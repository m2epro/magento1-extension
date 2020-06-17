<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    /** @var Ess_M2ePro_Model_Abstract */
    protected $_model = null;

    //########################################

    public function setModel(Ess_M2ePro_Model_Abstract $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }

    //########################################

    /**
     * @param array $filters
     * @return Ess_M2ePro_Model_Component_Abstract[]
     */
    public function getObjects(array $filters = array())
    {
        return $this->loadCollection($filters)->getItems();
    }

    public function getData($columns = '*', array $filters = array())
    {
        $productCollection = $this->loadCollection($filters);

        if (is_array($columns) && !empty($columns)) {
            $productCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $columns && $productCollection->getSelect()->columns($columns);
        }

        return $productCollection->getData();
    }

    public function getIds(array $filters = array())
    {
        return $this->loadCollection($filters)->getAllIds();
    }

    //########################################

    /**
     * @param array $filters
     * @return Ess_M2ePro_Model_Resource_Collection_Component_Abstract
     */
    abstract public function loadCollection(array $filters = array());

    //########################################
}
