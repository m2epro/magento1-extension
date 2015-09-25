<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * Used to set the initiator of the actions, performed on orders.
 */
class Ess_M2ePro_Model_Order_Log_Manager
{
    private $initiator = null;

    // ########################################

    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    // ########################################

    public function createLogRecord($componentMode, $orderId, $message, $type)
    {
        $initiator = $this->initiator ? $this->initiator : Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        Mage::getModel('M2ePro/Order_Log')->add($componentMode, $orderId, $message, $type, $initiator);
    }

    // ########################################
}