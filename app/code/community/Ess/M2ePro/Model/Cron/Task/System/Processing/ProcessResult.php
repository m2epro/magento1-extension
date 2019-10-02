<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_Processing_ProcessResult extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const SINGLE_PROCESSINGS_PER_CRON_COUNT = 5000;
    const PARTIAL_PROCESSINGS_PER_CRON_COUNT = 5;

    const NICK = 'system/processing/process_result';

    //########################################

    protected function performActions()
    {
        $this->processExpired();
        $this->processCompletedSingle();
        $this->processCompletedPartial();
    }

    //########################################

    protected function processExpired()
    {
        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->setOnlyExpiredItemsFilter();
        $processingCollection->addFieldToFilter('is_completed', 0);

        /** @var Ess_M2ePro_Model_Processing[] $processingObjects */
        $processingObjects = $processingCollection->getItems();

        foreach ($processingObjects as $processingObject) {
            $this->getLockItemManager()->activate();

            try {
                if (!class_exists(Mage::getConfig()->getModelClassName($processingObject->getModel()))) {
                    throw new Ess_M2ePro_Model_Exception(
                        sprintf('Responser runner model class "%s" does not exists', $processingObject->getModel())
                    );
                }

                /** @var Ess_M2ePro_Model_Processing_Runner $processingRunner */
                $processingRunner = Mage::getModel($processingObject->getModel());
                $processingRunner->setProcessingObject($processingObject);

                $processingRunner->processExpired();
                $processingRunner->complete();
            } catch (Exception $exception) {
                $processingObject->forceRemove();
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }
    }

    protected function processCompletedSingle()
    {
        /** @var Ess_M2ePro_Model_Resource_Processing_Collection $processingCollection */
        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->addFieldToFilter('is_completed', 1);
        $processingCollection->addFieldToFilter('type', Ess_M2ePro_Model_Processing::TYPE_SINGLE);
        $processingCollection->getSelect()->order('main_table.id ASC');
        $processingCollection->getSelect()->limit(self::SINGLE_PROCESSINGS_PER_CRON_COUNT);

        /** @var Ess_M2ePro_Model_Processing[] $processingObjects */
        $processingObjects = $processingCollection->getItems();
        if (empty($processingObjects)) {
            return;
        }

        $iteration = 0;
        $percentsForOneAction = 50 / count($processingObjects);

        foreach ($processingObjects as $processingObject) {
            $this->getLockItemManager()->activate();
            if ($iteration % 10 == 0) {
                Mage::dispatchEvent(
                    Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME,
                    array(
                        'progress_nick' => self::NICK,
                        'percentage'    => ceil($percentsForOneAction * $iteration),
                        'total'         => count($processingObjects)
                    )
                );
            }

            try {
                if (!class_exists(Mage::getConfig()->getModelClassName($processingObject->getModel()))) {
                    throw new Ess_M2ePro_Model_Exception(
                        sprintf('Responser runner model class "%s" does not exists', $processingObject->getModel())
                    );
                }

                /** @var Ess_M2ePro_Model_Processing_Runner $processingRunner */
                $processingRunner = Mage::getModel($processingObject->getModel());
                $processingRunner->setProcessingObject($processingObject);

                $processingRunner->processSuccess() && $processingRunner->complete();
            } catch (Exception $exception) {
                $processingObject->forceRemove();
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            $iteration++;
        }
    }

    protected function processCompletedPartial()
    {
        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->addFieldToFilter('is_completed', 1);
        $processingCollection->addFieldToFilter('type', Ess_M2ePro_Model_Processing::TYPE_PARTIAL);
        $processingCollection->getSelect()->order('main_table.id ASC');
        $processingCollection->getSelect()->limit(self::PARTIAL_PROCESSINGS_PER_CRON_COUNT);

        /** @var Ess_M2ePro_Model_Processing[] $processingObjects */
        $processingObjects = $processingCollection->getItems();
        if (empty($processingObjects)) {
            return;
        }

        $iteration = 0;
        $percentsForOneAction = 50 / count($processingObjects);

        foreach ($processingObjects as $processingObject) {
            $this->getLockItemManager()->activate();
            if ($iteration % 10 == 0) {
                Mage::dispatchEvent(
                    Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME,
                    array(
                        'progress_nick' => self::NICK,
                        'percentage'    => ceil($percentsForOneAction * $iteration),
                        'total'         => count($processingObjects)
                    )
                );
            }

            try {
                if (!class_exists(Mage::getConfig()->getModelClassName($processingObject->getModel()))) {
                    throw new Ess_M2ePro_Model_Exception(
                        sprintf('Responser runner model class "%s" does not exists', $processingObject->getModel())
                    );
                }

                /** @var Ess_M2ePro_Model_Processing_Runner $processingRunner */
                $processingRunner = Mage::getModel($processingObject->getModel());
                $processingRunner->setProcessingObject($processingObject);

                $processingRunner->processSuccess() && $processingRunner->complete();
            } catch (Exception $exception) {
                $processingObject->forceRemove();
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            $iteration++;
        }
    }

    //####################################
}
