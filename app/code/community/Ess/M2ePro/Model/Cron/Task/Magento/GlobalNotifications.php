<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Magento_GlobalNotifications extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'magento/global_notifications';

    //########################################

    protected function performActions()
    {
        /** @var Ess_M2ePro_Model_Issue_Notification_Channel_Magento_GlobalMessage $notificationChannel */
        $notificationChannel = Mage::getModel('M2ePro/Issue_Notification_Channel_Magento_GlobalMessage');
        $issueLocators = array(
            'M2ePro/Ebay_Account_Issue_AccessTokens',
        );

        foreach ($issueLocators as $locator) {
            /** @var Ess_M2ePro_Model_Issue_Locator_Abstract $locatorModel */
            $locatorModel = Mage::getModel($locator);

            foreach ($locatorModel->getIssues() as $issue) {
                $notificationChannel->addMessage($issue);
            }
        }
    }

    //########################################
}
