<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Log_Abstract as LogModel;

abstract class Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions extends Mage_Adminhtml_Block_Widget
{
    const VIEW_LOG_LINK_SHOW = 0;
    const VIEW_LOG_LINK_HIDE = 1;

    const ACTIONS_COUNT  = 3;
    const PRODUCTS_LIMIT = 100;

    protected $_tip     = null;
    protected $_iconSrc = null;
    protected $_rows    = array();

    public static $actionsSortOrder = array(
        LogModel::TYPE_SUCCESS  => 1,
        LogModel::TYPE_ERROR    => 2,
        LogModel::TYPE_WARNING  => 3,
        LogModel::TYPE_INFO   => 4,
    );

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('logGridLastActions');

        $this->setTemplate('M2ePro/log/last_actions.phtml');
    }

    //########################################

    public function getTip()
    {
        return $this->_tip;
    }

    public function getIconSrc()
    {
        return $this->_iconSrc;
    }

    public function getEncodedRows()
    {
        return base64_encode(Mage::helper('M2ePro')->jsonEncode($this->_rows));
    }

    public function getEntityId()
    {
        if (!$this->hasData('entity_id') || !is_int($this->getData('entity_id'))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Entity ID is not set.');
        }

        return $this->getData('entity_id');
    }

    public function getViewHelpHandler()
    {
        if (!$this->hasData('view_help_handler') || !is_string($this->getData('view_help_handler'))) {
            throw new Ess_M2ePro_Model_Exception_Logic('View help handler is not set.');
        }

        return $this->getData('view_help_handler');
    }

    public function getCloseHelpHandler()
    {
        if (!$this->hasData('hide_help_handler') || !is_string($this->getData('hide_help_handler'))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Close help handler is not set.');
        }

        return $this->getData('hide_help_handler');
    }

    public function getHideViewLogLink()
    {
        if ($this->hasData('hide_view_log_link')) {
            return self::VIEW_LOG_LINK_HIDE;
        }

        return self::VIEW_LOG_LINK_SHOW;
    }

    //########################################

    protected function getInitiator(array $actionLogs)
    {
        if (empty($actionLogs)) {
            return '';
        }

        $log = reset($actionLogs);

        if (!isset($log['initiator'])) {
            return '';
        }

        switch ($log['initiator']) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                return '';
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                return $this->__('Manual');
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                return $this->__('Automatic');
        }

        return '';
    }

    protected function getActionTitle(array $actionLogs)
    {
        if (empty($actionLogs)) {
            return '';
        }

        $log = reset($actionLogs);

        if (!isset($log['action'])) {
            return '';
        }

        $availableActions = $this->getAvailableActions();
        $action = $log['action'];

        if (isset($availableActions[$action])) {
            return $availableActions[$action];
        }

        return '';
    }

    protected function getMainType(array $actionLogs)
    {
        $types = array();
        foreach ($actionLogs as $log) {
            $types[] = $log['type'];
        }

        return empty($types) ? 0 : max($types);
    }

    protected function getMainDate(array $actionLogs)
    {
        if (count($actionLogs) > 1) {
            $row = array_reduce(
                $actionLogs, function ($a, $b) {
                return ($a === null || strtotime($a['create_date']) < strtotime($b['create_date'])) ? $b : $a;
                }
            );
        } else {
            $row = reset($actionLogs);
        }

        return $row['create_date'];
    }

    //----------------------------------------

    abstract protected function getActions(array $logs);

    protected function sortActionLogs(array &$actions)
    {
        $sortOrder = self::$actionsSortOrder;

        foreach ($actions as &$actionLogs) {
            usort(
                $actionLogs['items'], function ($a, $b) use ($sortOrder) {
                    return $sortOrder[$a['type']] > $sortOrder[$b['type']];
                }
            );
        }
    }

    protected function sortActions(array &$actions)
    {
        usort(
            $actions, function ($a, $b) {
                return strtotime($a['date']) < strtotime($b['date']);
            }
        );
    }

    protected function getRows()
    {
        if (!$this->hasData('logs') || !is_array($this->getData('logs'))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Logs are not set.');
        }

        $logs = $this->getData('logs');

        if (empty($logs)) {
            return array();
        }

        return $this->getActions($logs);
    }

    //----------------------------------------

    protected function getAvailableActions()
    {
        if (!$this->hasData('available_actions') || !is_array($this->getData('available_actions'))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Available actions are not set.');
        }

        return $this->getData('available_actions');
    }

    protected function getTips()
    {
        if (!$this->hasData('tips') || !is_array($this->getData('tips'))) {
            return array(
                LogModel::TYPE_SUCCESS  => 'Last Action was completed.',
                LogModel::TYPE_ERROR    => 'Last Action was completed with error(s).',
                LogModel::TYPE_WARNING  => 'Last Action was completed with warning(s).',
                LogModel::TYPE_INFO   => 'Last Action was completed with info(s).'
            );
        }

        return $this->getData('tips');
    }

    protected function getIcons()
    {
        if (!$this->hasData('icons') || !is_array($this->getData('icons'))) {
            return array(
                LogModel::TYPE_SUCCESS  => 'success',
                LogModel::TYPE_ERROR    => 'error',
                LogModel::TYPE_WARNING  => 'warning',
                LogModel::TYPE_INFO   => 'info',
            );
        }

        return $this->getData('icons');
    }

    protected function getDefaultTip()
    {
        return $this->__('Last Action was completed.');
    }

    protected function getTipByType($type)
    {
        foreach ($this->getTips() as $tipType => $tip) {
            if ($tipType == $type) {
                return $this->__($tip);
            }
        }

        return $this->getDefaultTip();
    }

    protected function getDefaultIcon()
    {
        return 'success';
    }

    protected function getIconByType($type)
    {
        foreach ($this->getIcons() as $iconType => $icon) {
            if ($iconType == $type) {
                return $icon;
            }
        }

        return $this->getDefaultIcon();
    }

    //----------------------------------------

    protected function _beforeToHtml()
    {
        $rows = $this->getRows();

        if (empty($rows)) {
            return parent::_beforeToHtml();
        }

        $lastActionRow = $rows[0];
        // ---------------------------------------

        // Get log icon
        // ---------------------------------------
        $icon = $this->getDefaultIcon();
        $tip = $this->getDefaultTip();

        if (isset($lastActionRow['type'])) {
            $tip = $this->getTipByType($lastActionRow['type']);
            $icon = $this->getIconByType($lastActionRow['type']);
        }

        $this->_tip = Mage::helper('M2ePro')->escapeHtml($tip);
        $this->_iconSrc = $this->getSkinUrl('M2ePro/images/log_statuses/' . $icon . '.png');
        $this->_rows = $rows;
        // ---------------------------------------

        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Model_Log_Abstract'),
            'Ess_M2ePro_Model_Log_Abstract'
        );

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        if (empty($this->_rows)) {
            return '';
        }

        return parent::_toHtml();
    }

    //########################################
}
