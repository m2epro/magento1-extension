<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Order_Notification extends Ess_M2ePro_Observer_Abstract
{
    const NOTIFICATION_MESSAGE_IDENTIFIER = 'm2epro_order_message';

    protected $_isProcessed = false;

    //########################################

    public function process()
    {
        $this->_isProcessed = true;

        /** @var Mage_Adminhtml_Model_Session $session */
        $session = Mage::getSingleton('adminhtml/session');
        /** @var Mage_Core_Model_Message $messageFactory */
        $messageFactory = Mage::getSingleton('core/message');
        /** @var Ess_M2ePro_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('M2ePro');

        /** @var Ess_M2ePro_Model_Notification_Manager $manager */
        $manager = Mage::getModel('M2ePro/Notification_Manager');

        foreach ($manager->getMessages() as $messageText) {
            $message = $messageFactory
                ->warning($dataHelper->__($messageText))
                ->setIdentifier(self::NOTIFICATION_MESSAGE_IDENTIFIER);

            $session->addMessage($message);
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canProcess()
    {
        if ($this->_isProcessed) {
            return false;
        }

        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        $request = Mage::app()->getRequest();

        if (!$session->isLoggedIn() || $request->isPost() || $request->isAjax()) {
            return false;
        }

        if (strripos($request->getActionName(), 'Grid')) {
            return false;
        }

        if (Mage::app()->getResponse()->isRedirect()) {
            return false;
        }

        if (Mage::getSingleton('core/layout')->getBlock('head') === false) {
            return false;
        }

        /** @var Ess_M2ePro_Helper_Order_Notification $notificationHelper */
        $notificationHelper = Mage::helper('M2ePro/Order_Notification');

        if ($notificationHelper->isNotificationDisabled()) {
            return false;
        }

        if ($notificationHelper->isNotificationExtensionPages() && $request->getModuleName() !== 'M2ePro') {
            return false;
        }

        if ($this->isNeedToSkipByControllerName($request)) {
            return false;
        }

        // do not show on own controllers
        if ($request->getControllerModule() === 'Ess_M2ePro_Adminhtml') {
            return false;
        }

        // after redirect message can be added twice
        foreach ($session->getMessages()->getItems() as $message) {
            if ($message->getIdentifier() === self::NOTIFICATION_MESSAGE_IDENTIFIER) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return bool
     */
    protected function isNeedToSkipByControllerName(Mage_Core_Controller_Request_Http $request)
    {
        $controllersToSkip = array(
            'system_config',
            'system_convert_profile',
            'system_convert_gui'
        );

        return in_array(strtolower($request->getControllerName()), $controllersToSkip, true);
    }

    //########################################
}
