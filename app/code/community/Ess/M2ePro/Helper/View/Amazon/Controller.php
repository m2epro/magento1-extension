<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Amazon_Controller extends Mage_Core_Helper_Abstract
{
    //########################################

    public function addMessages()
    {
        /** @var Ess_M2ePro_Model_Issue_Notification_Channel_Magento_Session $notificationChannel */
        $notificationChannel = Mage::getModel('M2ePro/Issue_Notification_Channel_Magento_Session');
        $issueLocators = array(
            'M2ePro/Amazon_Marketplace_Issue_NotUpdated',
            'M2ePro/Amazon_Repricing_Issue_InvalidToken',
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
