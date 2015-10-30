<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Feedbacks
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::FEEDBACKS;
    }

    /**
     * @return null
     */
    protected function getNick()
    {
        return NULL;
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
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Feedbacks_Receive') ? false : $result;
        $result = !$this->processTask('Feedbacks_Response') ? false : $result;

        return $result;
    }

    //########################################
}