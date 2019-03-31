<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Issue_Notification_Channel_Magento_Session
    implements Ess_M2ePro_Model_Issue_Notification_Channel_Interface
{
    //########################################

    public function addMessage(Ess_M2ePro_Model_Issue_Object $issue)
    {
        switch ($issue->getType()) {

            case Mage_Core_Model_Message::NOTICE:
                Mage::getSingleton('adminhtml/session')->addNotice($issue->getText());
                break;

            case Mage_Core_Model_Message::SUCCESS:
                Mage::getSingleton('adminhtml/session')->addSuccess($issue->getText());
                break;

            case Mage_Core_Model_Message::WARNING:
                Mage::getSingleton('adminhtml/session')->addWarning($issue->getText());
                break;

            case Mage_Core_Model_Message::ERROR:
                Mage::getSingleton('adminhtml/session')->addError($issue->getText());
                break;

            default;
                throw new Ess_M2ePro_Model_Exception_Logic(sprintf(
                    'Unsupported message type [%s]', $issue->getType()
                ));
        }
    }

    //########################################
}