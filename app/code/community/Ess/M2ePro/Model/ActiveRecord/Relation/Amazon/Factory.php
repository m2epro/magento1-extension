<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */


class Ess_M2ePro_Model_ActiveRecord_Relation_Amazon_Factory
    implements Ess_M2ePro_Model_ActiveRecord_Relation_FactoryInterface
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Relation_Factory  */
    protected $_relationFactory;

    //########################################

    public function __construct()
    {
        $this->_relationFactory = Mage::getSingleton('M2ePro/ActiveRecord_Relation_Factory');
    }

    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################

    /**
     * @param $modelName
     * @return false|Ess_M2ePro_Model_ActiveRecord_Relation
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObject($modelName)
    {
        return $this->_relationFactory->getObject($this->getComponentMode(), $modelName);
    }

    /**
     * @param $modelName
     * @return Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectCollection($modelName)
    {
        return $this->_relationFactory->getObjectCollection($this->getComponentMode(), $modelName);
    }

    /**
     * @param $modelName
     * @param $value
     * @param null $field
     * @param bool $throwException
     * @return Ess_M2ePro_Model_ActiveRecord_Relation|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        return $this->_relationFactory->getObjectLoaded(
            $this->getComponentMode(),
            $modelName,
            $value,
            $field,
            $throwException
        );
    }

    /**
     * @param $modelName
     * @param $value
     * @param null $field
     * @param bool $throwException
     * @return Ess_M2ePro_Model_ActiveRecord_Relation|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCachedObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        return $this->_relationFactory->getCachedObjectLoaded(
            $this->getComponentMode(),
            $modelName,
            $value,
            $field,
            $throwException
        );
    }

    //########################################
}
