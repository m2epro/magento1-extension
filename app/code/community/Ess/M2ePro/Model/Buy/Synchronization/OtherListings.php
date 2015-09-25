<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

final class Ess_M2ePro_Model_Buy_Synchronization_OtherListings
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::OTHER_LISTINGS;
    }

    protected function getNick()
    {
        return NULL;
    }

    protected function getTitle()
    {
        return '3rd Party Listings';
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

        $result = !$this->processTask('OtherListings_Update') ? false : $result;
        $result = !$this->processTask('OtherListings_Title') ? false : $result;

        return $result;
    }

    //####################################
}