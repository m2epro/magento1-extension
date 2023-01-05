<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Configuration_LogsClearingController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //########################################

    public function saveAction()
    {
        // Save settings
        // ---------------------------------------
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            Mage::getModel('M2ePro/Log_Clearing')->saveSettings(
                Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS,
                $post['listings_log_mode'],
                $post['listings_log_days']
            );
            Mage::getModel('M2ePro/Log_Clearing')->saveSettings(
                Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS,
                $post['synchronizations_log_mode'],
                $post['synchronizations_log_days']
            );
            Mage::getModel('M2ePro/Log_Clearing')->saveSettings(
                Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS,
                $post['orders_log_mode'],
                90
            );

            /** @var Ess_M2ePro_Helper_Order_Notification $orderNotification */
            $orderNotification = Mage::helper('M2ePro/Order_Notification');
            $orderNotification->setNotificationMode($this->getRequest()->getParam('order_notification_mode'));

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('The clearing Settings has been saved.')
            );
        }

        // ---------------------------------------

        // Get actions
        // ---------------------------------------
        $task = $this->getRequest()->getParam('task');
        $log = $this->getRequest()->getParam('log');

        if ($task !== null) {
            $title = ucwords(str_replace('_', ' ', $log));

            switch ($task) {
                case 'run_now':
                    Mage::getModel('M2ePro/Log_Clearing')->clearOldRecords($log);
                    $tempString = Mage::helper('M2ePro')->__(
                        'Log for %title% has been cleared.', $title
                    );
                    $this->_getSession()->addSuccess($tempString);
                    break;

                case 'clear_all':
                    Mage::getModel('M2ePro/Log_Clearing')->clearAllLog($log);
                    $tempString = Mage::helper('M2ePro')->__(
                        'All Log for %title% has been cleared.', $title
                    );
                    $this->_getSession()->addSuccess($tempString);
                    break;
            }
        }

        // ---------------------------------------

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}
