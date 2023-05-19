<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Connector_Connection_Response_Message as Message;

class Ess_M2ePro_Model_Order_Log extends Ess_M2ePro_Model_Log_Abstract
{
    /** @var int|null  */
    protected $_initiator = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    //########################################

    /**
     * @param int $initiator
     * @return $this
     */
    public function setInitiator($initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->_initiator = (int)$initiator;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getInitiator()
    {
        return $this->_initiator;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order|int $order
     * @param string $description
     * @param int $type
     * @param array $additionalData
     * @param bool $isUnique
     * 
     * @return bool
     */
    public function addMessage($order, $description, $type, array $additionalData = array(), $isUnique = false)
    {
        if (!($order instanceof Ess_M2ePro_Model_Order)) {
            $order = Mage::getModel('M2ePro/Order')->load($order);
        }

        if ($isUnique && $this->isExist($order->getId(), $description)) {
            return false;
        }

        $dataForAdd = array(
            'account_id'      => $order->getAccountId(),
            'marketplace_id'  => $order->getMarketplaceId(),
            'order_id'        => $order->getId(),
            'description'     => $description,
            'type'            => (int)$type,
            'additional_data' => Mage::helper('M2ePro')->jsonEncode($additionalData),

            'initiator'      => $this->_initiator ? $this->_initiator : Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            'component_mode' => $this->getComponentMode()
        );

        Mage::getModel('M2ePro/Order_Log')
            ->setData($dataForAdd)
            ->save();

        return true;
    }

    /**
     * @param Ess_M2ePro_Model_Order|int $order
     * @param Message $message
     */
    public function addServerResponseMessage($order, Message $message)
    {
        if (!($order instanceof Ess_M2ePro_Model_Order)) {
            $order = Mage::getModel('M2ePro/Order')->load($order);
        }

        $this->addMessage(
            $order,
            $message->getText(),
            $this->convertServerMessageTypeToExtensionMessageType((string)$message->getType())
        );
    }

    //########################################

    public function deleteInstance()
    {
        return parent::delete();
    }

    //########################################

    public function isExist($orderId, $message)
    {
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->addFieldToFilter('description', $message);

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $type
     * @return int
     */
    public function convertServerMessageTypeToExtensionMessageType($type)
    {
        $map = array(
            Message::TYPE_NOTICE => self::TYPE_INFO,
            Message::TYPE_SUCCESS => self::TYPE_SUCCESS,
            Message::TYPE_WARNING => self::TYPE_WARNING,
            Message::TYPE_ERROR => self::TYPE_ERROR,
        );

        return isset($map[$type]) ? $map[$type] : self::TYPE_ERROR;
    }
}
