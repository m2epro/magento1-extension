<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
    private $accountModel = NULL;

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
        $temp && $this->accountModel = NULL;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
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

            $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObj->getVirtualConnector('feedback', 'add', 'entity',
                                                                $paramsConnector, NULL, NULL,
                                                                $this->getAccount());

            $response = $dispatcherObj->process($connectorObj);

        } catch (Exception $e) {
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
        /** @var $collection Ess_M2ePro_Model_Mysql4_Order_Collection */
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

        return !is_null($order->getId()) ? $order : NULL;
    }

    //########################################
}