<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Feedback extends Ess_M2ePro_Model_Component_Abstract
{
    const ROLE_BUYER  = 'Buyer';
    const ROLE_SELLER = 'Seller';

    const TYPE_NEUTRAL  = 'Neutral';
    const TYPE_POSITIVE = 'Positive';
    const TYPE_NEGATIVE = 'Negative';

    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Feedback');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_accountModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $this->getData('account_id')
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->_accountModel = $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //########################################

    public function isNeutral()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEUTRAL;
    }

    public function isNegative()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEGATIVE;
    }

    public function isPositive()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_POSITIVE;
    }

    //########################################

    public function sendResponse($text, $type = self::TYPE_POSITIVE)
    {
        $paramsConnector = array(
            'item_id'        => $this->getData('ebay_item_id'),
            'transaction_id' => $this->getData('ebay_transaction_id'),
            'text'           => $text,
            'type'           => $type,
            'target_user'    => $this->getData('buyer_name')
        );

        $this->setData('last_response_attempt_date', Mage::helper('M2ePro')->getCurrentGmtDate())->save();

        try {

            /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connectorObj */
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObj->getVirtualConnector(
                'feedback', 'add', 'entity',
                $paramsConnector, null, null,
                $this->getAccount()
            );

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($connectorObj->getResponse()->getMessages()->hasErrorEntities()) {
                throw new Ess_M2ePro_Model_Exception(
                    $connectorObj->getResponse()->getMessages()->getCombinedErrorsString()
                );
            }
        } catch (Exception $e) {
            $synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
            $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
            $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER);

            $synchronizationLog->addMessage(
                Mage::helper('M2ePro')->__($e->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            Mage::helper('M2ePro/Module_Exception')->process($e);
            return false;
        }

        if (!isset($response['feedback_id'])) {
            return false;
        }

        $this->setData('seller_feedback_id', $response['feedback_id']);
        $this->setData('seller_feedback_type', $type);
        $this->setData('seller_feedback_text', $text);
        $this->setData('seller_feedback_date', $response['feedback_date']);

        $this->save();

        return true;
    }

    /**
     * @return Ess_M2ePro_Model_Order|null
     */
    public function getOrder()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Order_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection->getSelect()
            ->join(
                array('oi' => Mage::getResourceModel('M2ePro/Order_Item')->getMainTable()),
                '`oi`.`order_id` = `main_table`.`id`',
                array()
            )
            ->join(
                array('eoi' => Mage::getResourceModel('M2ePro/Ebay_Order_Item')->getMainTable()),
                '`eoi`.`order_item_id` = `oi`.`id`',
                array()
            );

        $collection->addFieldToFilter('account_id', $this->getData('account_id'));
        $collection->addFieldToFilter('eoi.item_id', $this->getData('ebay_item_id'));
        $collection->addFieldToFilter('eoi.transaction_id', $this->getData('ebay_transaction_id'));

        $collection->getSelect()->limit(1);

        $order = $collection->getFirstItem();

        return $order->getId() !== null ? $order : null;
    }

    //########################################
}
