<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid_LastActions extends Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions
{
    //########################################

    protected function _construct()
    {
        parent::_construct();

        $this->setId('lastProductActions');
    }

    //########################################

    protected function getActions(array $logs)
    {
        $actions = $this->getGroupedActions($logs);

        $this->sortActions($actions);
        $this->sortActionLogs($actions);

        return array_slice($actions, 0, self::ACTIONS_COUNT);
    }

    protected function getGroupedActions(array $logs)
    {
        $groupedLogsByAction = array();

        foreach ($logs as $log) {
            $log['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($log['description']);
            $groupedLogsByAction[$log['action_id']][] = $log;
        }

        $actions = array();

        foreach ($groupedLogsByAction as $actionLogs) {
            $actions[] = array(
                'type'           => $this->getMainType($actionLogs),
                'date'           => $date = $this->getMainDate($actionLogs),
                'localized_date' => $this->formatDate($date, IntlDateFormatter::MEDIUM, true),
                'action'         => $this->getActionTitle($actionLogs),
                'initiator'      => $this->getInitiator($actionLogs),
                'items'          => $actionLogs
            );
        }

        return $actions;
    }

    //########################################
}
