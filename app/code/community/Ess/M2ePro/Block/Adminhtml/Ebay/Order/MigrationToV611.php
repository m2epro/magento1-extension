<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_MigrationToV611 extends Mage_Adminhtml_Block_Widget
{
    const ORDERS_COUNT_PER_AJAX_REQUEST = 10000;

    private $notMigratedOrdersCount = 0;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/order/migration_to_v611.phtml');
    }

    //########################################

    public function setNotMigratedOrdersCount($ordersCount)
    {
        $this->notMigratedOrdersCount = $ordersCount;
        return $this;
    }

    public function getNotMigratedOrdersCount()
    {
        return $this->notMigratedOrdersCount;
    }

    public function getOrdersCountPerAjaxRequest()
    {
        return self::ORDERS_COUNT_PER_AJAX_REQUEST;
    }

    //########################################
}