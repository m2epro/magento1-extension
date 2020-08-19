<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Log_Grid_LastActions extends Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions
{
    //########################################

    protected function _construct()
    {
        parent::_construct();

        $this->setId('lastOrderActions');
    }

    //########################################

    protected function getActions(array $logs)
    {
        $actions = $this->getGroupedActions($logs);

        $this->sortActions($actions);

        return $actions;
    }

    protected function getGroupedActions(array $logs)
    {
        $actions = array();

        foreach ($logs as $log) {
            $actions[] = array(
                'type'           => $log->getData('type'),
                'text'           => Mage::helper('M2ePro/View')->getModifiedLogMessage($log->getData('description')),
                'initiator'      => $this->getInitiator(array($log)),
                'date'           => $date = $log->getData('create_date'),
                'localized_date' => $this->formatDate($date, IntlDateFormatter::MEDIUM, true)
            );
        }

        return $actions;
    }

    //########################################
}
