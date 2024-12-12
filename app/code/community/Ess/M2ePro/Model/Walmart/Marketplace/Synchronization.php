<?php

class Ess_M2ePro_Model_Walmart_Marketplace_Synchronization
{
    const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 1800; // 30 min

    /** @var Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceFactory */
    private $dictionaryMarketplaceFactory;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_Marketplace_Repository */
    private $dictionaryMarketplaceRepository;
    /** @var Ess_M2ePro_Model_Marketplace */
    private $marketplace = null;
    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    private $lockItemManager = null;
    /** @var Ess_M2ePro_Model_Lock_Item_Progress */
    private $progressManager = null;
    /** @var Ess_M2ePro_Model_Synchronization_Log  */
    private $synchronizationLog = null;

    public function __construct()
    {
        $this->dictionaryMarketplaceFactory = Mage::getModel('M2ePro/Walmart_Dictionary_MarketplaceFactory');
        $this->dictionaryMarketplaceRepository = Mage::getModel('M2ePro/Walmart_Dictionary_Marketplace_Repository');
    }

    /**
     * @return bool
     */
    public function isMarketplaceAllowed(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        return $marketplace->getChildObject()
                           ->isCanada();
    }

    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        if (!$this->isMarketplaceAllowed($marketplace)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace not allowed for synchronization.');
        }

        $this->marketplace = $marketplace;

        return $this;
    }

    public function isLocked()
    {
        if (!$this->getLockItemManager()->isExist()) {
            return false;
        }

        if ($this->getLockItemManager()->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->getLockItemManager()->remove();
            return false;
        }

        return true;
    }

    public function process()
    {
        $this->getLockItemManager()->create();

        $this->getProgressManager()->setPercentage(0);

        $this->processDetails();

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->getProgressManager()->setPercentage(100);

        $this->getLockItemManager()->remove();
    }

    /**
     * @return void
     */
    private function processDetails()
    {
        $dispatcherObj = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'marketplace', 'get', 'info',
            array(
                'include_details' => true,
                'marketplace' => $this->marketplace->getNativeId()
            ),
            'info', null
        );

        $dispatcherObj->process($connectorObj);
        $details = $connectorObj->getResponseData();
        if ($details === null) {
            return;
        }

        $this->dictionaryMarketplaceRepository->removeByMarketplace(
            (int)$this->marketplace->getId()
        );

        $marketplaceDictionary = $this->dictionaryMarketplaceFactory->createWithoutProductTypes(
            (int)$this->marketplace->getId(),
            Mage::helper('M2ePro/Data')->createCurrentGmtDateTime(),
            Mage::helper('M2ePro/Data')->createGmtDateTime($details['last_update'])
        );

        $this->dictionaryMarketplaceRepository->create($marketplaceDictionary);
    }

    public function getLockItemManager()
    {
        if ($this->lockItemManager !== null) {
            return $this->lockItemManager;
        }

        return $this->lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array(
                'nick' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK
            )
        );
    }

    public function getProgressManager()
    {
        if ($this->progressManager !== null) {
            return $this->progressManager;
        }

        return $this->progressManager = Mage::getModel(
            'M2ePro/Lock_Item_Progress', array(
                'lock_item_manager' => $this->getLockItemManager(),
                'progress_nick'     => $this->marketplace->getTitle() . ' Marketplace',
            )
        );
    }

    public function getLog()
    {
        if ($this->synchronizationLog !== null) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES);

        return $this->synchronizationLog;
    }
}
