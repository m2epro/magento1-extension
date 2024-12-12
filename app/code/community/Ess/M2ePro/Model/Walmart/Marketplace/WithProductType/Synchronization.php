<?php

class Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_Synchronization
{
    const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 1800; // 30 min

    /** @var Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceService */
    private $marketplaceDictionaryService;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_CategoryService */
    private $categoryDictionaryService;
    /** @var Ess_M2ePro_Model_Marketplace */
    private $marketplace = null;
    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    protected $lockItemManager = null;
    /** @var Ess_M2ePro_Model_Lock_Item_Progress */
    private $progressManager = null;
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    private $synchronizationLog = null;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeService */
    private $productTypeDictionaryService;

    public function __construct()
    {
        $this->marketplaceDictionaryService = Mage::getModel('M2ePro/Walmart_Dictionary_MarketplaceService');
        $this->categoryDictionaryService = Mage::getModel('M2ePro/Walmart_Dictionary_CategoryService');
        $this->productTypeDictionaryService = Mage::getModel('M2ePro/Walmart_Dictionary_ProductTypeService');
    }

    /**
     * @return bool
     */
    public function isMarketplaceAllowed(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        return $marketplace->getChildObject()
                           ->isSupportedProductType();
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

        $this->marketplaceDictionaryService->update($this->marketplace);

        $this->getProgressManager()->setPercentage(30);

        $this->categoryDictionaryService->update($this->marketplace);

        $this->getProgressManager()->setPercentage(60);

        $this->productTypeDictionaryService->update($this->marketplace);

        $this->getProgressManager()->setPercentage(80);

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->getProgressManager()->setPercentage(100);

        $this->getLockItemManager()->remove();
    }

    public function getLockItemManager()
    {
        if ($this->lockItemManager !== null) {
            return $this->lockItemManager;
        }

        $nick = Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_WITH_PRODUCT_TYPE_SYNCHRONIZATION_LOCK_ITEM_NICK;
        return $this->lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => $nick));
    }

    public function getProgressManager()
    {
        if ($this->progressManager !== null) {
            return $this->progressManager;
        }

        return $this->progressManager = Mage::getModel(
            'M2ePro/Lock_Item_Progress', array(
                'lock_item_manager' => $this->getLockItemManager(),
                'progress_nick' => $this->marketplace->getTitle() . ' Marketplace',
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
