<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Orders
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
{
    //########################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::ORDERS;
    }

    protected function getNick()
    {
        return NULL;
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Orders_Reserve_Cancellation') ? false : $result;
        $result = !$this->processTask('Orders_Receive') ? false : $result;
        $result = !$this->processTask('Orders_Refund') ? false : $result;
        $result = !$this->processTask('Orders_Cancel') ? false : $result;
        $result = !$this->processTask('Orders_Update') ? false : $result;

        return $result;
    }

    //########################################
}