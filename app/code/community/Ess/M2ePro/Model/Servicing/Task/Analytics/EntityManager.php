<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Analytics_EntityManager
{
    protected $component;
    protected $entityType;

    /** @var Ess_M2ePro_Model_Servicing_Task_Analytics_Registry  */
    protected $registry;

    //########################################

    public function __construct($args)
    {
        if (empty($args['component']) || empty($args['entityType'])) {
            throw new Exception('component or entityType was not provided.');
        }

        $this->component  = $args['component'];
        $this->entityType = $args['entityType'];

        $this->registry = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry');

        is_null($this->getLastId()) && $this->initLastEntityId();
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
     * @return Ess_M2ePro_Model_Mysql4_Collection_Abstract $collection
     */
    private function getCollection()
    {
        $model = Mage::getModel('M2ePro/'.$this->entityType);
        if ($model instanceof Ess_M2ePro_Model_Component_Parent_Abstract) {
            $model = Mage::helper('M2ePro/Component')->getComponentModel($this->component, $this->entityType);
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
        return $this->registry->getProgressData($this->getEntityKey(), 'last_processed_id');
    }

    public function setLastProcessedId($id)
    {
        return $this->registry->setProgressData($this->getEntityKey(), 'last_processed_id', (int)$id);
    }

    // ---------------------------------------

    public function getLastId()
    {
        return $this->registry->getProgressData($this->getEntityKey(), 'last_id');
    }

    public function setLastId($id)
    {
        return $this->registry->setProgressData($this->getEntityKey(), 'last_id', (int)$id);
    }

    // ---------------------------------------

    public function getLimit()
    {
        return 500;
    }

    //########################################

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function getComponent()
    {
        return $this->component;
    }

    public function getEntityKey()
    {
        return $this->component .'::'. $this->entityType;
    }

    //########################################
}