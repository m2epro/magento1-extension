<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Listing_AbstractGrid extends Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid
{
    //#######################################

    abstract protected function getViewMode();

    abstract protected function getLogHash($type);

    abstract protected function getComponentMode();

    //#######################################

    protected function getActionName()
    {
        switch ($this->getEntityField()) {
            case self::LISTING_ID_FIELD:
                return 'listingGrid';

            case self::LISTING_PRODUCT_ID_FIELD:
                return 'listingProductGrid';
        }

        return 'listingGrid';
    }

    protected function addMaxAllowedLogsCountExceededNotification($date)
    {
        $notification = Mage::helper('M2ePro')->__(
            'Using a Grouped View Mode, the logs records which are not older than %date% are
            displayed here in order to prevent any possible Performance-related issues.',
            $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
        );

        $this->getMessagesBlock()->addNotice($notification);
    }

    protected function getMaxRecordsCount()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/logs/grouped/', 'max_records_count'
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/'.$this->getActionName(), array(
                '_current'=>true,
                'channel' => $this->getRequest()->getParam('channel')
            )
        );
    }
    //#######################################
}
