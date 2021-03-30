<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Order_Cancellation_Abstract
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    /** @var Ess_M2ePro_Model_Order */
    protected $_order;

    /** @var Ess_M2ePro_Model_Order_Change */
    protected $_orderChange;

    //########################################

    public function __construct(
        array $params = array(),
        Ess_M2ePro_Model_Marketplace $marketplace = null,
        Ess_M2ePro_Model_Account $account = null
    ) {
        $this->_order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', $params['order_id']);
        $this->_orderChange = Mage::getModel('M2ePro/Order_Change')->load($params['change_id']);

        parent::__construct($params, $marketplace, $account);
    }

    //########################################

    public function process()
    {
        parent::process();

        $this->processResponseData();
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        return true;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    abstract protected function processResponseData();

    //########################################
}
