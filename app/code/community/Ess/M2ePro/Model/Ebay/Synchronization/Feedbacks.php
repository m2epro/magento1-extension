<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Feedbacks
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::FEEDBACKS;
    }

    protected function getNick()
    {
        return NULL;
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Feedbacks_Receive') ? false : $result;
        $result = !$this->processTask('Feedbacks_Response') ? false : $result;

        return $result;
    }

    //####################################
}