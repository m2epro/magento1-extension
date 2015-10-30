<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Log_ErrorsSummary extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('logErrorsSummary');
        // ---------------------------------------

        $this->setTemplate('M2ePro/log/errors_summary.phtml');
    }

    protected function _beforeToHtml()
    {
        $tableName = $this->getData('table_name');
        $actionIdsString = $this->getData('action_ids');

        $countField = 'product_id';

        if ($this->getData('type_log') == 'listing') {
            $countField = 'product_id';
        } else if ($this->getData('type_log') == 'listing_other') {
            $countField = 'listing_other_id';
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $fields = new Zend_Db_Expr('COUNT(`'.$countField.'`) as `count_products`, `description`');
        $dbSelect = $connRead->select()
                             ->from($tableName,$fields)
                             ->where('`action_id` IN ('.$actionIdsString.')')
                             ->where('`type` = ?',Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)
                             ->group('description')
                             ->order(array('count_products DESC'))
                             ->limit(100);

        $newErrors = array();
        $tempErrors = $connRead->fetchAll($dbSelect);

        foreach ($tempErrors as $row) {
            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);
            $newErrors[] = $row;
        }

        $this->errors = $newErrors;

        return parent::_beforeToHtml();
    }

    //########################################
}