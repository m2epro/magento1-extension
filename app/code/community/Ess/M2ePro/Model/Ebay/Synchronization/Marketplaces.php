<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::MARKETPLACES;
    }

    protected function getNick()
    {
        return NULL;
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        $params = $this->getParams();

        if (empty($params['marketplace_id'])) {
            return false;
        }

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component')
                            ->getUnknownObject('Marketplace', (int)$params['marketplace_id']);

        if (!$marketplace->isComponentModeEbay() || !$marketplace->isStatusEnabled()) {
            return false;
        }

        return true;
    }

    protected function configureLockItemBeforeStart()
    {
        parent::configureLockItemBeforeStart();

        $componentName = '';
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle() . ' ';
        }

        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $marketplace->getNativeId() == 100 && $componentName = '';
        $this->getActualLockItem()->setTitle($componentName.Mage::helper('M2ePro')->__($marketplace->getTitle()));
    }

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Marketplaces_Details') ? false : $result;
        $result = !$this->processTask('Marketplaces_Categories') ? false : $result;
        $result = !$this->processTask('Marketplaces_MotorsSpecifics') ? false : $result;
        $result = !$this->processTask('Marketplaces_MotorsKtypes') ? false : $result;

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        return $result;
    }

    //####################################
}