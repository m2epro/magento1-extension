<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Log_View_Grouped_Grid
    extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    protected $_nestedLogs = array();

    //########################################

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('description')->setData('sortable', false);

        return $this;
    }

    protected function _prepareCollection()
    {
        $logCollection = Mage::getModel('M2ePro/Listing_Log')->getCollection();

        $this->applyFilters($logCollection);

        $logCollection->getSelect()
            ->order(new \Zend_Db_Expr('main_table.id DESC'))
            ->limit(1, $this->getMaxRecordsCount() - 1);

        $lastAllowedLog = $logCollection->getFirstItem();

        if ($lastAllowedLog->getId() !== null) {
            $logCollection->getSelect()->limit($this->getMaxRecordsCount());
            $this->addMaxAllowedLogsCountExceededNotification($lastAllowedLog->getCreateDate());
        } else {
            $logCollection->getSelect()
                ->reset(\Zend_Db_Select::ORDER)
                ->reset(\Zend_Db_Select::LIMIT_COUNT)
                ->reset(\Zend_Db_Select::LIMIT_OFFSET);
        }

        $groupedCollection = new \Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $groupedCollection->getSelect()->reset()->from(
            array('main_table' => $logCollection->getSelect()),
            array(
                'id'              => 'main_table.id',
                self::LISTING_PRODUCT_ID_FIELD        => 'main_table.' . self::LISTING_PRODUCT_ID_FIELD,
                self::LISTING_PARENT_PRODUCT_ID_FIELD => 'main_table.' . self::LISTING_PARENT_PRODUCT_ID_FIELD,
                self::LISTING_ID_FIELD                => 'main_table.' . self::LISTING_ID_FIELD,
                'product_id'      => 'main_table.product_id',
                'action_id'       => 'main_table.action_id',
                'action'          => 'main_table.action',
                'listing_title'   => 'main_table.listing_title',
                'product_title'   => 'main_table.product_title',
                'initiator'       => 'main_table.initiator',
                'component_mode'  => 'main_table.component_mode',
                'additional_data' => 'main_table.additional_data',
                'create_date'     => new \Zend_Db_Expr('MAX(main_table.create_date)'),
                'description'     => new \Zend_Db_Expr('GROUP_CONCAT(main_table.description)'),
                'type'            => new \Zend_Db_Expr('MAX(main_table.type)'),
                'nested_log_ids'  => new \Zend_Db_Expr('GROUP_CONCAT(main_table.id)'),
            )
        );

        $groupedCollection->getSelect()->where(new \Zend_Db_Expr('action_id IS NOT NULL'));
        $groupedCollection->getSelect()->group(array('listing_product_id', 'action_id'));

        $resultCollection = new \Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $resultCollection->getSelect()->from(
            array('main_table' => $groupedCollection->getSelect())
        );

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        if (!$this->getCollection()->getSize()) {
            return parent::_afterLoadCollection();
        }

        $logCollection = Mage::getModel('M2ePro/Listing_Log')->getCollection();

        $logCollection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'id',
                    self::LISTING_PRODUCT_ID_FIELD,
                    self::LISTING_ID_FIELD,
                    'action_id',
                    'description',
                    'type',
                    'create_date'
                )
            )
            ->order(new \Zend_Db_Expr('id DESC'));

        $nestedLogsIds = array();

        foreach ($this->getCollection()->getItems() as $log) {
            $nestedLogsIds[] = new \Zend_Db_Expr($log->getNestedLogIds());
        }

        $logCollection->getSelect()->where(
            new \Zend_Db_Expr('main_table.id IN (?)'), $nestedLogsIds
        );

        foreach ($logCollection->getItems() as $log) {
            $this->_nestedLogs[$this->getLogHash($log)][] = $log;
        }

        $sortOrder = Ess_M2ePro_Block_Adminhtml_Log_Grid_Summary::$actionsSortOrder;

        foreach ($this->_nestedLogs as &$logs) {
            usort(
                $logs, function($a, $b) use ($sortOrder) {
                    return $sortOrder[$a['type']] > $sortOrder[$b['type']];
                }
            );
        }

        return parent::_afterLoadCollection();
    }

    //########################################

    public function callbackDescription($value, $row, $column, $isExport)
    {
        $description = '';

        $nestedLogs = $this->_nestedLogs[$this->getLogHash($row)];

        /** @var Ess_M2ePro_Model_Listing_Log $log */
        foreach ($nestedLogs as $log) {
            $messageType = '';
            $createDate = '';

            if (count($nestedLogs) > 1) {
                $logTypeList = $this->_getLogTypeList();
                $messageType = $this->callbackColumnType(
                    '[' . $logTypeList[$log->getType()] . ']', $log, $column, $isExport
                );
                $createDate = $this->formatDate($log->getCreateDate(), IntlDateFormatter::MEDIUM, true);
            }

            $logDescription = parent::callbackDescription($log->getData($column->getIndex()), $log, $column, $isExport);

            $description .= <<<HTML
<div class="log-description-group">
    <span class="log-description">
        <span class="log-type">{$messageType}</span>
        {$logDescription}
    </span>
    <div class="log-date">{$createDate}</div>
</div>
HTML;
        }

        return $description;
    }

    //########################################
}
