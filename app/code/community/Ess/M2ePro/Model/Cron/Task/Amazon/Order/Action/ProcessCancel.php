<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessCancel
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/action/process_cancel';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 18000;

    //####################################

    public function performActions()
    {
        $actionsProcessor = Mage::getModel(
            'M2ePro/Amazon_Order_Action_Processor',
            array('action_type' => Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_CANCEL)
        );
        $actionsProcessor->process();
    }

    //####################################
}
