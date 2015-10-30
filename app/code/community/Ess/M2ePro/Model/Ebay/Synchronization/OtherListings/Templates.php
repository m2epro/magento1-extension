<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    private $runner = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Synchronization_Templates_Inspector
     */
    private $inspector = NULL;

    private $cache = array();

    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/templates/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Inventory';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 50;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function beforeStart()
    {
        parent::beforeStart();

        $this->runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');

        $this->runner->setConnectorModel('Connector_Ebay_OtherItem_Dispatcher');
        $this->runner->setMaxProductsPerStep(10);

        $this->runner->setLockItem($this->getActualLockItem());
        $this->runner->setPercentsStart($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->runner->setPercentsEnd($this->getPercentsEnd());

        $this->inspector = Mage::getModel('M2ePro/Ebay_Synchronization_OtherListings_Templates_Inspector');
    }

    protected function afterEnd()
    {
        $this->cache = array();
        $this->executeRunner();

        parent::afterEnd();
    }

    // ---------------------------------------

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Templates_Revise') ? false : $result;
        $result = !$this->processTask('Templates_Relist') ? false : $result;
        $result = !$this->processTask('Templates_Stop') ? false : $result;

        return $result;
    }

    protected function makeTask($taskPath)
    {
        $task = parent::makeTask($taskPath);

        $task->setRunner($this->runner);
        $task->setInspector($this->inspector);
        $task->setCache($this->cache);

        return $task;
    }

    //########################################

    private function executeRunner()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Apply Products changes on eBay');

        $result = $this->runner->execute();
        $this->affectResultRunner($result);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function affectResultRunner($result)
    {
        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {

            $resultString = 'errors';
            $resultType = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
            $resultPriority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;

        } else if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {

            $resultString = 'warnings';
            $resultType = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            $resultPriority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

        } else {
            return;
        }

        // M2ePro_TRANSLATIONS
        // Task "Update 3rd Party Listings" has completed with %result%. View Listings Log for details.
        $this->getLog()->addMessage(
            Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Task "Update 3rd Party Listings" has completed with %result%. View Listings Log for details.',
                array('!result'=>$resultString)
            ), $resultType, $resultPriority
        );

        $this->getActualOperationHistory()->addText('Updating Products on eBay ended with '.$resultString.'.');
    }

    //########################################
}