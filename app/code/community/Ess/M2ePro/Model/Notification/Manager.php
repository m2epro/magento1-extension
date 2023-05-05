<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Notification_Manager
{
    public function getMessages()
    {
        $messages = array();
        /** @var Ess_M2ePro_Model_Notification_MessageBuilderInterface $builder */
        foreach ($this->getMessageBuilders() as $builder) {
            $messages[] = $builder->buildMessage();
        }

        return array_values(array_filter($messages));
    }

    private function getMessageBuilders()
    {
        return array(
            Mage::getModel('M2ePro/Notification_OrderNotCreated'),
            Mage::getModel('M2ePro/Notification_OrderVatChanged'),
        );
    }
}
