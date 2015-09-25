<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_OrderItem_Add_Dispute
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    // M2ePro_TRANSLATIONS
    // Dispute cannot be opened. Reason: Dispute explanation is not defined.
    // Dispute cannot be opened. Reason: Dispute reason is not defined.
    // Unpaid Item Process was not open for Item #%id%. Reason: %msg%
    // Unpaid Item Process was not open for Item #%id%. Reason: eBay failure. Please try again later.
    // Unpaid Item Process for Item #%id% has been initiated.

    const DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID = 'BuyerNotPaid';

    /** @var $orderItem Ess_M2ePro_Model_Order_Item */
    private $orderItem;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order_Item $orderItem)
    {
        parent::__construct($params, null, $orderItem->getOrder()->getAccount(), null);

        $this->orderItem = $orderItem;
    }

    protected function getCommand()
    {
        return array('dispute', 'add', 'entity');
    }

    protected function isNeedSendRequest()
    {
        if (empty($this->params['explanation'])) {
            $this->orderItem->getOrder()->addErrorLog(
                'Dispute cannot be opened. Reason: Dispute explanation is not defined.'
            );

            return false;
        }

        if (empty($this->params['reason'])) {
            $this->orderItem->getOrder()->addErrorLog(
                'Dispute cannot be opened. Reason: Dispute reason is not defined.'
            );

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $requestData = array(
            'item_id'        => $this->orderItem->getChildObject()->getItemId(),
            'transaction_id' => $this->orderItem->getChildObject()->getTransactionId(),
            'explanation'    => $this->params['explanation'],
            'reason'         => $this->params['reason']
        );

        return $requestData;
    }

    protected function validateResponseData($response)
    {
        return true;
    }

    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            return false;
        }

        $result = parent::process();

        foreach ($this->messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] != parent::MESSAGE_TYPE_ERROR) {
                continue;
            }

            $this->orderItem->getOrder()->addErrorLog(
                'Unpaid Item Process was not open for Item #%id%. Reason: %msg%', array(
                    '!id' => $this->orderItem->getChildObject()->getItemId(),
                    'msg' => $message[parent::MESSAGE_TEXT_KEY]
                )
            );

            if (isset($message[parent::MESSAGE_CODE_KEY])
                && (in_array($message[parent::MESSAGE_CODE_KEY], array(16207, 16212)))
            ) {
                $this->orderItem->setData(
                    'unpaid_item_process_state', Ess_M2ePro_Model_Ebay_Order_Item::UNPAID_ITEM_PROCESS_OPENED
                );
                $this->orderItem->save();
            }
        }

        return $result;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return;
        }

        if (empty($response['dispute_id'])) {
            $log = 'Unpaid Item Process was not open for Item #%id%. Reason: eBay failure. Please try again later.';
            $this->orderItem->getOrder()->addErrorLog($log, array(
                '!id' => $this->orderItem->getChildObject()->getItemId()
            ));
            return;
        }

        $this->orderItem->setData(
            'unpaid_item_process_state', Ess_M2ePro_Model_Ebay_Order_Item::UNPAID_ITEM_PROCESS_OPENED
        );
        $this->orderItem->save();

        $this->orderItem->getOrder()->addSuccessLog('Unpaid Item Process for Item #%id% has been initiated.', array(
            '!id' => $this->orderItem->getChildObject()->getItemId()
        ));
    }

    // ########################################
}