<?php

class Ess_M2ePro_Model_Amazon_Account_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Account_CollectionFactory */
    private $collectionFactory;
    /** @var bool */
    private $isLoaded = false;
    /** @var array<int, Ess_M2ePro_Model_Amazon_Account> */
    private $entitiesById = array();

    public function __construct()
    {
        $this->collectionFactory = Mage::getResourceModel('M2ePro/Amazon_Account_CollectionFactory');
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isExists($id)
    {
        return $this->find($id) !== null;
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Amazon_Account|null
     */
    public function find($id)
    {
        $this->load();

        return !empty($this->entitiesById[$id])
            ? $this->entitiesById[$id]
            : null;
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function get($id)
    {
        $entity = $this->find($id);
        if ($entity === null) {
            throw new LogicException('Account not found by id: ' . $id);
        }

        return $entity;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account[]
     */
    public function getAll()
    {
        $this->load();

        return array_values($this->entitiesById);
    }

    /**
     * @return void
     */
    private function load()
    {
        if ($this->isLoaded) {
            return;
        }

        $this->entitiesById = array();

        $collection = $this->collectionFactory->create();
        /** @var Ess_M2ePro_Model_Amazon_Account $entity */
        foreach ($collection->getItems() as $entity) {
            $this->entitiesById[$entity->getId()] = $entity;
        }

        $this->isLoaded = true;
    }
}
