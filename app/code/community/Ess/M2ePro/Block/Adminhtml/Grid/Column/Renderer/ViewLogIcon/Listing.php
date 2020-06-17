<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Listing
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    protected $_jsHandler = 'ListingGridObj';

    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        !empty($args['js_handler']) && $this->_jsHandler = $args['js_handler'];
    }

    //########################################

    public function render(Varien_Object $row)
    {
        $actionsRows = $this->getGroupedLogRecords($row);
        if (count($actionsRows) <= 0) {
            return '';
        }

        $this->sortLogsRecords($actionsRows);

        $summary = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_log_grid_summary', '',
            array(
                'entity_id' => (int)$row->getData('id'),
                'rows'      => $actionsRows,
                'tips'      => array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => 'Last Action was completed with error(s).',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
                ),

                'icons' => array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => 'error',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
                ),
                'view_help_handler' => "{$this->_jsHandler}.viewItemHelp",
                'hide_help_handler' => "{$this->_jsHandler}.hideItemHelp",
            )
        );

        return $summary->toHtml();
    }

    //########################################

    protected function getAvailableActions()
    {
        return array(
            Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE
        );
    }

    protected function getLastMessages(Varien_Object $row)
    {
        $listingProductId  = (int)$row->getData('id');
        $isVariationParent = (bool)(int)$row->getData('is_variation_parent');

        $dbSelect = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id', 'action', 'type', 'description', 'create_date', 'initiator', 'listing_product_id')
            )
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
            ->order(array('id DESC'))
            ->limit(30);

        $isVariationParent
            ? $dbSelect->where('listing_product_id = ? OR parent_listing_product_id = ?', $listingProductId)
            : $dbSelect->where('listing_product_id = ?', $listingProductId);

        return Mage::getSingleton('core/resource')->getConnection('core_read')
            ->fetchAll($dbSelect);
    }

    protected function getGroupedLogRecords(Varien_Object $row)
    {
        $actionsRows    = array();
        $tempActionRows = array();
        $lastActionId   = null;

        foreach ($this->getLastMessages($row) as $row) {
            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (count($tempActionRows) > 0) {
                    $actionsRows[] = array(
                        'action_id' => $lastActionId,
                        'type'      => $this->getMainTypeForActionId($tempActionRows),
                        'date'      => $this->getMainDateForActionId($tempActionRows),
                        'action'    => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items'     => $tempActionRows
                    );
                    $tempActionRows = array();
                }

                $lastActionId = $row['action_id'];
            }

            $tempActionRows[] = $row;
        }

        if (count($tempActionRows) > 0) {
            $actionsRows[] = array(
                'action_id' => $lastActionId,
                'type'      => $this->getMainTypeForActionId($tempActionRows),
                'date'      => $this->getMainDateForActionId($tempActionRows),
                'action'    => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items'     => $tempActionRows
            );
        }

        return $actionsRows;
    }

    protected function sortLogsRecords($groupedLogsRecords)
    {
        foreach ($groupedLogsRecords as &$actionsRow) {
            usort(
                $actionsRow['items'], function($a, $b)
            {
                $sortOrder = array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 1,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => 2,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 3,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE  => 4,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
            }
            );
        }
    }

    //########################################

    protected function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            }

            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            }

            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
            }
        }

        return $type;
    }

    protected function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }


    protected function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('List');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING:
                $string = Mage::helper('M2ePro')->__('Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Remove from Channel');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Remove from Channel & Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Switch to AFN');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Switch to MFN');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Reset Inactive (Blocked) Item');
                break;
        }

        return $string;
    }

    protected function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    //########################################
}
