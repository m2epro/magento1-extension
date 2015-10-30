<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Order_Log extends Ess_M2ePro_Model_Log_Abstract
{
    protected $initiator = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    //########################################

    /**
     * @param int $initiator
     * @return $this
     */
    public function setInitiator($initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->initiator = (int)$initiator;
        return $this;
    }

    // ########################################

    public function addMessage($orderId, $description, $type, array $additionalData = array())
    {
        $dataForAdd = $this->makeDataForAdd($orderId, $description, $type, $additionalData);
        $this->createMessage($dataForAdd);
    }

    // ########################################

    protected function createMessage($dataForAdd)
    {
        $dataForAdd['initiator'] = $this->initiator ? $this->initiator : Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        $dataForAdd['component_mode'] = $this->getComponentMode();

        $this->setId(null)
            ->setData($dataForAdd)
            ->save();
    }

    protected function makeDataForAdd($orderId, $description, $type, array $additionalData = array())
    {
        $dataForAdd = array(
            'order_id'        => $orderId,
            'description'     => $description,
            'type'            => (int)$type,
            'additional_data' => json_encode($additionalData)
        );

        return $dataForAdd;
    }

    //########################################

    public function deleteInstance()
    {
        return parent::delete();
    }

    //########################################
}