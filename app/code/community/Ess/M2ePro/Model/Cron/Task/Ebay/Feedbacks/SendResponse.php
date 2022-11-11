<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_SendResponse extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/feedbacks/send_response';

    const ATTEMPT_INTERVAL = 86400;

    /** @var int (in seconds) */
    protected $_interval = 10800;
    /** @var Ess_M2ePro_Helper_Data */
    protected $_dataHelper;
    /** @var Ess_M2ePro_Model_Ebay_Feedback_Manager */
    protected $_ebayFeedbackManager;

    public function __construct()
    {
        $this->_dataHelper = Mage::helper('M2ePro');
        $this->_ebayFeedbackManager = Mage::getModel('M2ePro/Ebay_Feedback_Manager');
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function performActions()
    {
        $feedbacks = $this->getFeedbacksForAnswer(5);
        if (empty($feedbacks)) {
            return;
        }

        foreach ($feedbacks as $feedback) {
            $this->processFeedback($feedback);
        }
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getAccountsIds()
    {
        $result = array();

        /** @var Ess_M2ePro_Model_Resource_Ebay_Account_Collection $ebayAccountCollection */
        $ebayAccountCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Account');
        /** @var Ess_M2ePro_Model_Account $account */
        foreach ($ebayAccountCollection->getItems() as $account) {
            if (!$account->getChildObject()->isFeedbacksReceive()) {
                continue;
            }

            if ($account->getChildObject()->isFeedbacksAutoResponseDisabled()) {
                continue;
            }

            if (!$account->getChildObject()->hasFeedbackTemplate()) {
                continue;
            }

            $result[] = $account->getData('id');
        }

        return $result;
    }

    /**
     * @param int $daysAgo
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getFeedbacksForAnswer($daysAgo)
    {
        $accountsIds = $this->getAccountsIds();
        if (empty($accountsIds)) {
            return array();
        }

        $accountsIdsTemplate = implode(', ', $accountsIds);
        $feedbackTypePositive = Ess_M2ePro_Model_Ebay_Feedback::TYPE_POSITIVE;
        $minBuyerFeedbackDate = $this->_dataHelper->createCurrentGmtDateTime()
            ->modify("-{$daysAgo} days")
            ->format('Y-m-d H:i:s');
        $maxResponseAttemptDate = $this->_dataHelper->createCurrentGmtDateTime()
            ->modify('-' . self::ATTEMPT_INTERVAL . 'seconds')
            ->format('Y-m-d H:i:s');

        $ebayFeedbackTable = Mage::getResourceModel('M2ePro/Ebay_Feedback')->getMainTable();
        $sqlCondition = <<<SQL
(`main_table`.`seller_feedback_id` = 0)
AND (`main_table`.`is_critical_error_received` = 0)
AND (`main_table`.`buyer_feedback_date` > '{$minBuyerFeedbackDate}')
AND (`last_response_attempt_date` IS NULL OR `last_response_attempt_date` < '{$maxResponseAttemptDate}')
AND (
    `ea`.`feedbacks_auto_response_only_positive` = 0
    OR (`ea`.`feedbacks_auto_response_only_positive` = 1
        AND `main_table`.`buyer_feedback_type` = '{$feedbackTypePositive}'
    )
)
AND `main_table`.`buyer_name` NOT IN (
    SELECT `buyer_name`
    FROM `{$ebayFeedbackTable}`
    WHERE `seller_feedback_id` <> 0
    GROUP BY `buyer_name`
)
SQL;

        /** @var Ess_M2ePro_Model_Resource_Ebay_Feedback_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection();
        $collection->getSelect()
            ->join(
                array('ea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                "`ea`.`account_id` = `main_table`.`account_id` AND `ea`.`account_id` IN ($accountsIdsTemplate)",
                array()
            )
            ->where($sqlCondition)
            ->order(array('buyer_feedback_date ASC'));

        return $collection->getItems();
    }

    protected function processFeedback(Ess_M2ePro_Model_Ebay_Feedback $feedback)
    {
        /** @var $feedback Ess_M2ePro_Model_Ebay_Feedback */
        $account = $feedback->getAccount();

        if ($account->getChildObject()->isFeedbacksAutoResponseCycled()) {
            // Load is needed to get correct feedbacks_last_used_id
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $feedback->getData('account_id')
            );
        }

        if (($body = $this->getResponseBody($account)) === '') {
            return;
        }

        $result = $this->_ebayFeedbackManager
            ->sendResponse($feedback, $body, Ess_M2ePro_Model_Ebay_Feedback::TYPE_POSITIVE);
        if ($result) {
            $this->getOperationHistory()
                ->appendText('Send Feedback for "'.$feedback->getData('buyer_name').'"');
            $this->getOperationHistory()
                ->appendText(
                    'His feedback "'.$feedback->getData('buyer_feedback_text')
                    . '" ('.$feedback->getData('buyer_feedback_type').')'
                );
            $this->getOperationHistory()
                ->appendText('Our Feedback "'.$body.'"');
        } else {
            $this->getOperationHistory()
                ->appendText('Send Feedback for "'.$feedback->getData('buyer_name').'" was failed');
        }

        $this->getOperationHistory()->saveBufferString();
    }

    protected function getResponseBody(Ess_M2ePro_Model_Account $account)
    {
        if ($account->getChildObject()->isFeedbacksAutoResponseCycled()) {
            $lastUsedId = 0;
            if ($account->getChildObject()->getFeedbacksLastUsedId() != null) {
                $lastUsedId = (int)$account->getChildObject()->getFeedbacksLastUsedId();
            }

            $feedbackTemplatesIds = Mage::getModel('M2ePro/Ebay_Feedback_Template')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->setOrder('id', 'ASC')
                ->getAllIds();

            if (!count($feedbackTemplatesIds)) {
                return '';
            }

            $feedbackTemplate = Mage::getModel('M2ePro/Ebay_Feedback_Template');

            if (max($feedbackTemplatesIds) > $lastUsedId) {
                foreach ($feedbackTemplatesIds as $templateId) {
                    if ($templateId <= $lastUsedId) {
                        continue;
                    }

                    $feedbackTemplate->load($templateId);
                    break;
                }
            } else {
                $feedbackTemplate->load(min($feedbackTemplatesIds));
            }

            if (!$feedbackTemplate->getId()) {
                return '';
            }

            $account->setData('feedbacks_last_used_id', $feedbackTemplate->getId())->save();

            return $feedbackTemplate->getBody();
        }

        if ($account->getChildObject()->isFeedbacksAutoResponseRandom()) {
            $feedbackTemplatesIds = Mage::getModel('M2ePro/Ebay_Feedback_Template')->getCollection()
                ->addFieldToFilter('account_id', $account->getId())
                ->getAllIds();

            if (!count($feedbackTemplatesIds)) {
                return '';
            }

            $index = rand(0, count($feedbackTemplatesIds) - 1);
            $feedbackTemplate = Mage::getModel('M2ePro/Ebay_Feedback_Template')->load($feedbackTemplatesIds[$index]);

            if (!$feedbackTemplate->getId()) {
                return '';
            }

            return $feedbackTemplate->getBody();
        }

        return '';
    }
}
