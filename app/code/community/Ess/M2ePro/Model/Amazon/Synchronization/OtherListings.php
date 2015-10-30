<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_OtherListings
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
{
    //########################################

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

        $result = !$this->processTask('OtherListings_Update') ? false : $result;
        $result = !$this->processTask('OtherListings_Title') ? false : $result;

        return $result;
    }

    //########################################
}