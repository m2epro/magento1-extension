<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue_RemoveOld extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/stop_queue/remove_old';

    const MAX_PROCESSED_LIFETIME_HOURS_INTERVAL = 720; // 30 days

    //########################################

    public function performActions()
    {
        $minDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $minDateTime->modify('- '.self::MAX_PROCESSED_LIFETIME_HOURS_INTERVAL.' hours');

        $collection = Mage::getResourceModel('M2ePro/StopQueue_Collection');
        $collection->addFieldToFilter('is_processed', 1);
        $collection->addFieldToFilter('update_date', array('lt' => $minDateTime->format('Y-m-d H:i:s')));

        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $collection->getItems();

        foreach ($items as $item) {
            $item->deleteInstance();
        }
    }

    //########################################
}