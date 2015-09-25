<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Change extends Ess_M2ePro_Model_Abstract
{
    const ACTION_UPDATE_PAYMENT  = 'update_payment';
    const ACTION_UPDATE_SHIPPING = 'update_shipping';
    const ACTION_CANCEL          = 'cancel';
    const ACTION_REFUND          = 'refund';

    const CREATOR_TYPE_OBSERVER = 1;

    const MAX_ALLOWED_PROCESSING_ATTEMPTS = 3;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Change');
    }

    //####################################

    public function getOrderId()
    {
        return (int)$this->getData('order_id');
    }

    public function getAction()
    {
        return $this->getData('action');
    }

    public function getCreatorType()
    {
        return (int)$this->getData('creator_type');
    }

    public function getComponent()
    {
        return $this->getData('component');
    }

    public function getParams()
    {
        $params = json_decode($this->getData('params'), true);

        return is_array($params) ? $params : array();
    }

    public function getHash()
    {
        return $this->getData('hash');
    }

    //####################################

    public static function getAllowedActions()
    {
        return array(
            self::ACTION_UPDATE_PAYMENT,
            self::ACTION_UPDATE_SHIPPING,
            self::ACTION_CANCEL,
            self::ACTION_REFUND,
        );
    }

    //####################################

    public function isPaymentUpdateAction()
    {
        return $this->getAction() == self::ACTION_UPDATE_PAYMENT;
    }

    public function isShippingUpdateAction()
    {
        return $this->getAction() == self::ACTION_UPDATE_SHIPPING;
    }

    public function isCancelAction()
    {
        return $this->getAction() == self::ACTION_CANCEL;
    }

    public function isRefundAction()
    {
        return $this->getAction() == self::ACTION_CANCEL;
    }

    //####################################

    public static function create($orderId, $action, $creatorType, $component, array $params)
    {
        if (!is_numeric($orderId)) {
            throw new InvalidArgumentException('Order ID is invalid.');
        }

        if (!in_array($action, self::getAllowedActions())) {
            throw new InvalidArgumentException('Action is invalid.');
        }

        if (!in_array($creatorType, array(self::CREATOR_TYPE_OBSERVER))) {
            throw new InvalidArgumentException('Creator is invalid.');
        }

        $hash = self::generateHash($orderId, $action, $params);

        /** @var Ess_M2ePro_Model_Order_Change $change */
        $change = Mage::getModel('M2ePro/Order_Change')
            ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('component', $component)
                ->addFieldToFilter('hash', $hash)
                ->getFirstItem();

        if ($change->getId()) {
            return;
        }

        $change->addData(array(
            'order_id'     => $orderId,
            'action'       => $action,
            'params'       => json_encode($params),
            'creator_type' => $creatorType,
            'component'    => $component,
            'hash'         => $hash
        ));
        $change->save();
    }

    //####################################

    public static function generateHash($orderId, $action, array $params)
    {
        return sha1($orderId.'-'.$action.'-'.serialize($params));
    }

    //####################################
}