<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_UpdateAccountsPreferences extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/update_accounts_preferences';

    //########################################

    public function performActions()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = $accountCollection->getItems();

        foreach ($accounts as $account) {
            $account->getChildObject()->updateUserPreferences();
        }
    }

    //########################################
}