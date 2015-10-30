<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Feedbacks_Receive
    extends Ess_M2ePro_Model_Ebay_Synchronization_Feedbacks_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/receive/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Receive';
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
        return 50;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function intervalIsEnabled()
    {
        return true;
    }

    //########################################

    protected function performActions()
    {
        $accounts = $this->getPermittedAccounts();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Receive" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Receive" Action for eBay Account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'get'.$account->getId(),
                'Get feedbacks from eBay'
            );
            $this->processAccount($account);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());

            // M2ePro_TRANSLATIONS
            // The "Receive" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Receive" Action for eBay Account: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
                                    ->addFieldToFilter('feedbacks_receive',
                                                        Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_RECEIVE_YES);
        return $collection->getItems();
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableFeedbacks = Mage::getResourceModel('M2ePro/Ebay_Feedback')->getMainTable();

        $dbSelect = $connRead->select()
                             ->from($tableFeedbacks,new Zend_Db_Expr('MAX(`seller_feedback_date`)'))
                             ->where('`account_id` = ?',(int)$account->getId());
        $maxSellerDate = $connRead->fetchOne($dbSelect);
        if (strtotime($maxSellerDate) < strtotime('2001-01-02')) {
            $maxSellerDate = NULL;
        }

        $dbSelect = $connRead->select()
                             ->from($tableFeedbacks,new Zend_Db_Expr('MAX(`buyer_feedback_date`)'))
                             ->where('`account_id` = ?',(int)$account->getId());
        $maxBuyerDate = $connRead->fetchOne($dbSelect);
        if (strtotime($maxBuyerDate) < strtotime('2001-01-02')) {
            $maxBuyerDate = NULL;
        }

        $paramsConnector = array();
        !is_null($maxSellerDate) && $paramsConnector['seller_max_date'] = $maxSellerDate;
        !is_null($maxBuyerDate) && $paramsConnector['buyer_max_date'] = $maxBuyerDate;
        $result = $this->receiveFromEbay($account,$paramsConnector);

        $this->getActualOperationHistory()->appendText('Total received Feedback from eBay: '.$result['total']);
        $this->getActualOperationHistory()->appendText('Total only new Feedback from eBay: '.$result['new']);
        $this->getActualOperationHistory()->saveBufferString();
    }

    protected function receiveFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('feedback','get','entity',
                                                            $paramsConnector,'feedbacks',
                                                            NULL,$account->getId(),NULL);

        $feedbacks = $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj);

        is_null($feedbacks) && $feedbacks = array();

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

            if (!is_null($existFeedback->getId())) {

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

    private function processResponseMessages(Ess_M2ePro_Model_Connector_Protocol $connectorObj)
    {
        foreach ($connectorObj->getErrorMessages() as $message) {

            if (!$connectorObj->isMessageError($message) && !$connectorObj->isMessageWarning($message)) {
                continue;
            }

            $logType = $connectorObj->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                               : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################
}