<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::MARKETPLACES;
    }

    /**
     * @return null
     */
    protected function getNick()
    {
        return NULL;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    /**
     * @return bool
     */
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
        $result = !$this->processTask('Marketplaces_MotorsEpids') ? false : $result;
        $result = !$this->processTask('Marketplaces_MotorsKtypes') ? false : $result;

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        return $result;
    }

    //########################################
}