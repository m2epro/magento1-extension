<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Analytics_EntityManager
{
    protected $_component;
    protected $_entityType;

    /** @var Ess_M2ePro_Model_Servicing_Task_Analytics_Registry  */
    protected $_registry;

    //########################################

    public function __construct($args)
    {
        if (empty($args['component']) || empty($args['entityType'])) {
            throw new Exception('component or entityType was not provided.');
        }

        $this->_component  = $args['component'];
        $this->_entityType = $args['entityType'];

        $this->_registry = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry');

        $this->getLastId() === null && $this->initLastEntityId();
    }

    protected function initLastEntityId()
    {
        $lastIdCollection = $this->getCollection();
        $idFieldName = $lastIdCollection->getResource()->getIdFieldName();

        $lastIdCollection->getSelect()->order($idFieldName .' '. Zend_Db_Select::SQL_DESC);
        $lastIdCollection->getSelect()->limit(1);

        $this->setLastId($lastIdCollection->getFirstItem()->getId());
    }

    //########################################

    public function getEntities()
    {
        $collection = $this->getCollection();
        $idFieldName = $collection->getResource()->getIdFieldName();

        $collection->getSelect()->order($idFieldName .' '. Zend_Db_Select::SQL_ASC);
        $collection->getSelect()->limit($this->getLimit());
        $collection->addFieldToFilter($idFieldName, array('gt' => (int)$this->getLastProcessedId()));

        return $collection->getItems();
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Collection_Abstract $collection
     */
    protected function getCollection()
    {
        $model = Mage::getModel('M2ePro/'.$this->_entityType);
        if ($model instanceof Ess_M2ePro_Model_Component_Parent_Abstract) {
            $model = Mage::helper('M2ePro/Component')->getComponentModel($this->_component, $this->_entityType);
        }

        return $model->getCollection();
    }

    //########################################

    public function isCompleted()
    {
        return (int)$this->getLastProcessedId() >= (int)$this->getLastId();
    }

    // ---------------------------------------

    public function getLastProcessedId()
    {
        return $this->_registry->getProgressData($this->getEntityKey(), 'last_processed_id');
    }

    public function setLastProcessedId($id)
    {
        return $this->_registry->setProgressData($this->getEntityKey(), 'last_processed_id', (int)$id);
    }

    // ---------------------------------------

    public function getLastId()
    {
        return $this->_registry->getProgressData($this->getEntityKey(), 'last_id');
    }

    public function setLastId($id)
    {
        return $this->_registry->setProgressData($this->getEntityKey(), 'last_id', (int)$id);
    }

    // ---------------------------------------

    public function getLimit()
    {
        return 500;
    }

    //########################################

    public function getEntityType()
    {
        return $this->_entityType;
    }

    public function getComponent()
    {
        return $this->_component;
    }

    public function getEntityKey()
    {
        return $this->_component . '::' . $this->_entityType;
    }

    //########################################
}
