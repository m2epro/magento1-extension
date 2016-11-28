<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Task_RepricingSynchronizationGeneral extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'repricing_synchronization_general';
    const MAX_MEMORY_LIMIT = 512;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

    public function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $permittedAccount) {
            /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
            $repricingSynchronization = Mage::getModel(
                'M2ePro/Amazon_Repricing_Synchronization_General', $permittedAccount
            );
            $repricingSynchronization->run();
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Account[]
     */
    private function getPermittedAccounts()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountCollection->getSelect()->joinInner(
            array('aar' => Mage::getResourceModel('M2ePro/Amazon_Account_Repricing')->getMainTable()),
            'aar.account_id=main_table.id',
            array()
        );

        return $accountCollection->getItems();
    }

    //####################################
}