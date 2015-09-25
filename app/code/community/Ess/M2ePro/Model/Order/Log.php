<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Log extends Mage_Core_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    // ########################################

    public function add($componentMode, $orderId, $message, $type,
                        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                        array $additionalData = array())
    {
        $validTypeValues = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE
        );

        if (!in_array($type, $validTypeValues)) {
            throw new InvalidArgumentException('Invalid Order Log type.');
        }

        $validInitiatorValues = array(
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION
        );

        if (!in_array($initiator, $validInitiatorValues)) {
            throw new InvalidArgumentException('Invalid Order Log initiator.');
        }

        $log = array(
            'component_mode'  => $componentMode,
            'order_id'        => $orderId,
            'message'         => $message,
            'type'            => (int)$type,
            'initiator'       => (int)$initiator,
            'additional_data' => json_encode($additionalData)
        );

        $this->setId(null)
             ->setData($log)
             ->save();
    }

    // ########################################

    public function deleteInstance()
    {
        return parent::delete();
    }

    // ########################################
}