<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
{
    //########################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::MARKETPLACES;
    }

    protected function getNick()
    {
        return NULL;
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

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

        if (!$marketplace->isComponentModeAmazon() || !$marketplace->isStatusEnabled()) {
            return false;
        }

        return true;
    }

    protected function configureLockItemBeforeStart()
    {
        parent::configureLockItemBeforeStart();

        $componentName = '';
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle() . ' ';
        }

        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualLockItem()->setTitle($componentName.Mage::helper('M2ePro')->__($marketplace->getTitle()));
    }

    /**
     * @return bool
     */
    public function performActions()
    {
        $result = true;

        $result = !$this->processTask('Marketplaces_Details') ? false : $result;
        $result = !$this->processTask('Marketplaces_Categories') ? false : $result;
        $result = !$this->processTask('Marketplaces_Specifics') ? false : $result;

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        return $result;
    }

    //########################################
}