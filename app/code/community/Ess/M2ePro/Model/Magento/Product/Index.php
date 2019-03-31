<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Index
{
    /** @var Mage_Index_Model_Indexer */
    private $indexer = null;

    //########################################

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexer()
    {
        if (is_null($this->indexer)) {
            $this->indexer = Mage::getSingleton('index/indexer');
        }
        return $this->indexer;
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return array(
            'cataloginventory_stock'
        );
    }

    //########################################

    public function disableReindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->getIndexer()->getProcessByCode($code);

        if ($process === false) {
            return false;
        }

        if ($process->getMode() == Mage_Index_Model_Process::MODE_MANUAL) {
            return false;
        }

        $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();

        return true;
    }

    public function enableReindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->getIndexer()->getProcessByCode($code);

        if ($process === false) {
            return false;
        }

        if ($process->getMode() == Mage_Index_Model_Process::MODE_REAL_TIME) {
            return false;
        }

        $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();

        return true;
    }

    // ---------------------------------------

    public function requireReindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->getIndexer()->getProcessByCode($code);

        if ($process === false) {
            return false;
        }

        /** @var $eventsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $eventsCollection = Mage::getResourceModel('index/event_collection')
            ->addProcessFilter($process, Mage_Index_Model_Process::EVENT_STATUS_NEW);

        return (bool)$eventsCollection->getSize();
    }

    public function executeReindex($code)
    {
        /** @var $process Mage_Index_Model_Process */
        $process = $this->getIndexer()->getProcessByCode($code);

        if ($process === false || $process->getStatus() == Mage_Index_Model_Process::STATUS_RUNNING) {
            return false;
        }

        $process->reindexEverything();

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function isIndexManagementEnabled()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                            ->getGroupValue('/product/index/', 'mode');
    }

    public function isDisabledIndex($code)
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                            ->getGroupValue('/product/index/'.$code.'/', 'disabled');
    }

    // ---------------------------------------

    public function rememberDisabledIndex($code)
    {
        Mage::helper('M2ePro/Module')->getConfig()
            ->setGroupValue('/product/index/'.$code.'/', 'disabled', 1);
    }

    public function forgetDisabledIndex($code)
    {
        Mage::helper('M2ePro/Module')->getConfig()
            ->setGroupValue('/product/index/'.$code.'/', 'disabled', 0);
    }

    //########################################
}