<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_DownloadNew extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/feedbacks/download_new';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 10800;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    protected function performActions()
    {
        $accounts = $this->getPermittedAccounts();

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'get'.$account->getId(),
                'Get feedbacks from eBay'
            );

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Receive" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $collection->addFieldToFilter('feedbacks_receive', 1);

        return $collection->getItems();
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableFeedbacks = Mage::getResourceModel('M2ePro/Ebay_Feedback')->getMainTable();

        $dbSelect = $connRead->select()
                             ->from($tableFeedbacks, new Zend_Db_Expr('MAX(`seller_feedback_date`)'))
                             ->where('`account_id` = ?', (int)$account->getId());
        $maxSellerDate = $connRead->fetchOne($dbSelect);
        if (strtotime($maxSellerDate) < strtotime('2001-01-02')) {
            $maxSellerDate = null;
        }

        $dbSelect = $connRead->select()
                             ->from($tableFeedbacks, new Zend_Db_Expr('MAX(`buyer_feedback_date`)'))
                             ->where('`account_id` = ?', (int)$account->getId());
        $maxBuyerDate = $connRead->fetchOne($dbSelect);
        if (strtotime($maxBuyerDate) < strtotime('2001-01-02')) {
            $maxBuyerDate = null;
        }

        $paramsConnector = array();
        $maxSellerDate !== null && $paramsConnector['seller_max_date'] = $maxSellerDate;
        $maxBuyerDate !== null && $paramsConnector['buyer_max_date'] = $maxBuyerDate;
        $result = $this->receiveFromEbay($account, $paramsConnector);

        $this->getOperationHistory()->appendText('Total received Feedback from eBay: '.$result['total']);
        $this->getOperationHistory()->appendText('Total only new Feedback from eBay: '.$result['new']);
        $this->getOperationHistory()->saveBufferString();
    }

    protected function receiveFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'feedback', 'get', 'entity',
            $paramsConnector, 'feedbacks',
            null, $account->getId()
        );

        $dispatcherObj->process($connectorObj);
        $feedbacks = $connectorObj->getResponseData();
        $this->processResponseMessages($connectorObj->getResponseMessages());

        $feedbacks === null && $feedbacks = array();

        $countNewFeedbacks = 0;
        foreach ($feedbacks as $feedback) {
            $dbFeedback = array(
                'account_id' => $account->getId(),
                'ebay_item_id' => $feedback['item_id'],
                'ebay_transaction_id' => $feedback['transaction_id']
            );

            if ($feedback['item_title'] != '') {
                $dbFeedback['ebay_item_title'] = $feedback['item_title'];
            }

            if ($feedback['from_role'] == Ess_M2ePro_Model_Ebay_Feedback::ROLE_BUYER) {
                $dbFeedback['buyer_name'] = $feedback['user_sender'];
                $dbFeedback['buyer_feedback_id'] = $feedback['id'];
                $dbFeedback['buyer_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['buyer_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['buyer_feedback_type'] = $feedback['info']['type'];
            } else {
                $dbFeedback['seller_feedback_id'] = $feedback['id'];
                $dbFeedback['seller_feedback_text'] = $feedback['info']['text'];
                $dbFeedback['seller_feedback_date'] = $feedback['info']['date'];
                $dbFeedback['seller_feedback_type'] = $feedback['info']['type'];
            }

            $existFeedback = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('ebay_item_id', $feedback['item_id'])
                ->addFieldToFilter('ebay_transaction_id', $feedback['transaction_id'])
                ->getFirstItem();

            if ($existFeedback->getId() !== null) {
                if ($feedback['from_role'] == Ess_M2ePro_Model_Ebay_Feedback::ROLE_BUYER &&
                    !$existFeedback->getData('buyer_feedback_id')) {
                    $countNewFeedbacks++;
                }

                if ($feedback['from_role'] == Ess_M2ePro_Model_Ebay_Feedback::ROLE_SELLER &&
                    !$existFeedback->getData('seller_feedback_id')) {
                    $countNewFeedbacks++;
                }
            } else {
                $countNewFeedbacks++;
            }

            $existFeedback->addData($dbFeedback)->save();
        }

        return array(
            'total' => count($feedbacks),
            'new'   => $countNewFeedbacks
        );
    }

    protected function processResponseMessages(array $messages)
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($messages);

        foreach ($messagesSet->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType
            );
        }
    }

    //########################################
}
