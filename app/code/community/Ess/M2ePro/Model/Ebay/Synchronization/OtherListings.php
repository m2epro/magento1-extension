<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::OTHER_LISTINGS;
    }

    /**
     * @return null
     */
    protected function getNick()
    {
        return NULL;
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return '3rd Party Listings';
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

        $result = !$this->processTask('OtherListings_Update') ? false : $result;
        $result = !$this->processTask('OtherListings_Sku') ? false : $result;
        $result = !$this->processTask('OtherListings_Templates') ? false : $result;

        return $result;
    }

    //########################################
}