<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Notification_OrderVatChanged
    implements Ess_M2ePro_Model_Notification_MessageBuilderInterface
{
    const VAT_WAS_CHANGED_TEXT = 'VAT was changed.';

    /** @var array */
    private $components = array();
    /** @var int */
    private $count = 0;
    /** @var string */
    private $minCreateDate;
    /** @var string */
    private $maxCreateDate;
    /** @var bool  */
    private $loaded = false;

    //########################################

    public function getMessageType()
    {
        return Mage_Core_Model_Message::WARNING;
    }

    public function buildMessage()
    {
        if (!$this->hasNotification()) {
            return '';
        }

        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::getSingleton('core/layout');
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        /** @var Ess_M2ePro_Block_Adminhtml_Notification_OrderVatChanged $block */
        $block = $layout->createBlock('M2ePro/Adminhtml_Notification_OrderVatChanged');
        $block->setSinceDate($this->minCreateDate);
        $block->setFailOrderCount($this->count);
        $block->setComponents($this->components);
        $filters = array(
            'order_create_date' => array(
                'from' => $coreHelper->formatDate(
                    Mage::app()->getLocale()->storeDate(null, $this->minCreateDate)
                ),
                'from_origin' => $this->minCreateDate,
            ),
            'orders_with_modified_vat' => true,
            'initiator' => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION
        );
        $block->setLogLinkFilters($filters);

        $block->setSkipUrl(Mage::helper('adminhtml')->getUrl(
            'M2ePro/adminhtml_order/skipLogNotificationVatChanged',
            array('last_order_vat_changed_date' => $this->maxCreateDate)
        ));

        return $block->toHtml();
    }

    private function hasNotification()
    {
        if (!$this->loaded) {
            $this->loadNotificationData();
        }

        return $this->count > 0;
    }

    private function loadNotificationData()
    {
        if ($this->loaded) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Log_Collection $collection */
        $collection = Mage::getModel('M2ePro/Resource_Order_Log_Collection');

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array(
            'components' => new Zend_Db_Expr('IFNULL(GROUP_CONCAT(DISTINCT main_table.component_mode), "")'),
            'failed_orders_count' => new Zend_Db_Expr('COUNT(DISTINCT main_table.order_id)'),
            'min_create_date' => new Zend_Db_Expr('IFNULL(MIN(create_date), "")'),
            'max_create_date' => new Zend_Db_Expr('IFNULL(MAX(create_date), "")'),
        ));
        $collection->addFieldToFilter('main_table.initiator', Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        $collection->addFieldToFilter(
            'main_table.additional_data',
            array('like' => '%' . \Ess_M2ePro_Model_Order::ADDITIONAL_DATA_KEY_VAT_REVERSE_CHARGE . '%')
        );
        $collection->addFieldToFilter('main_table.type', Ess_M2ePro_Model_Log_Abstract::TYPE_INFO);

        /** @var Ess_M2ePro_Helper_Order_Notification $notificationHelper */
        $notificationHelper = Mage::helper('M2ePro/Order_Notification');
        $lastFromDate = $notificationHelper->getOrderChangedVatLastDate();
        if (!empty($lastFromDate)) {
            $collection->addFieldToFilter('main_table.create_date', array('gteq' => $lastFromDate));
        }

        $notificationData = $collection
            ->getResource()
            ->getReadConnection()
            ->fetchRow($collection->getSelect());

        $this->components = explode(',', (string)$notificationData['components']);
        $this->count = (int)$notificationData['failed_orders_count'];
        $this->minCreateDate = (string)$notificationData['min_create_date'];
        $this->maxCreateDate = (string)$notificationData['max_create_date'];
    }
}
