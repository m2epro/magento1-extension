<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid extends Mage_Adminhtml_Block_Widget_Grid
{
    const LISTING_ID_FIELD                = 'listing_id';
    const LISTING_PRODUCT_ID_FIELD        = 'listing_product_id';
    const LISTING_PARENT_PRODUCT_ID_FIELD = 'parent_listing_product_id';
    const ORDER_ID_FIELD                  = 'order_id';

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    protected $_messageCount = array();
    protected $_entityIdFieldName;
    protected $_logModelName;
    
    //########################################

    abstract protected function getComponentMode();

    //########################################

    protected function getEntityId()
    {
        if ($this->isListingLog()) {
            return $this->getRequest()->getParam(self::LISTING_ID_FIELD);
        }

        if ($this->isListingProductLog()) {
            return $this->getRequest()->getParam(self::LISTING_PRODUCT_ID_FIELD);
        }

        return null;
    }

    protected function getEntityField()
    {
        if ($this->isListingLog()) {
            return self::LISTING_ID_FIELD;
        }

        if ($this->isListingProductLog()) {
            return self::LISTING_PRODUCT_ID_FIELD;
        }

        return null;
    }

    //########################################

    public function isListingLog()
    {
        $id = $this->getRequest()->getParam(self::LISTING_ID_FIELD);
        return !empty($id);
    }

    public function isListingProductLog()
    {
        $listingProductId = $this->getRequest()->getParam(self::LISTING_PRODUCT_ID_FIELD);
        return !empty($listingProductId);
    }

    public function isSingleOrderLog()
    {
        return $this->getRequest()->getParam(self::ORDER_ID_FIELD);
    }

    public function isNeedCombineMessages()
    {
        return !$this->isListingProductLog() && !$this->isSingleOrderLog() &&
            $this->getRequest()->getParam('only_unique_messages', true);
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam(self::LISTING_PRODUCT_ID_FIELD, false);
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component')
                                         ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        // We need to sort by id to maintain the correct sequence of records
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex . ' ' . strtoupper($column->getDir()))->order('id DESC');
        }

        return $this;
    }

    //########################################

    protected function _getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE  => Mage::helper('M2ePro')->__('Notice'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => Mage::helper('M2ePro')->__('Error')
        );
    }

    protected function _getLogInitiatorList()
    {
        return array(
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN   => Mage::helper('M2ePro')->__('Unknown'),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION => Mage::helper('M2ePro')->__('Automatic'),
            Ess_M2ePro_Helper_Data::INITIATOR_USER      => Mage::helper('M2ePro')->__('Manual'),
        );
    }

    //########################################

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        switch ($row->getData('type')) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Synchronization_Log::TYPE_FATAL_ERROR:
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                 $value = '<span style="color: red; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        $initiator = $row->getData('initiator');

        switch ($initiator) {
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $message = "<span style=\"text-decoration: underline;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $message = "<span style=\"font-style: italic; color: gray;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
            default:
                $message = "<span>{$value}</span>";
                break;
        }

        return $message;
    }

    public function callbackColumnDescription($value, $row, $column, $isExport)
    {
        $fullDescription = Mage::helper('M2ePro/View')->getModifiedLogMessage($row->getData('description'));

        $renderedText = $this->stripTags($fullDescription, '<br>');
        if (strlen($renderedText) < 200) {
            $html = $fullDescription;
        } else {
            $renderedText =  Mage::helper('core/string')->truncate($renderedText, 200, '');

            $html = <<<HTML
{$renderedText}
<a href="javascript://" onclick="LogObj.showFullText(this);">
    {$this->__('more')}
</a>
<div class="no-display">{$fullDescription}</div>
HTML;
        }

        $countHtml = '';

        if (isset($this->_messageCount[$row[$this->_entityIdFieldName]])) {
            $colorMap = array(
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE  => 'gray',
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'green',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'orange',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR   => 'red',
            );

            $count = $this->_messageCount[$row[$this->_entityIdFieldName]][$row['description']]['count'];
            if ($count > 1) {
                $color = $colorMap[$row['type']];
                $countHtml = " <span style='color: {$color}; font-weight: bold'>({$count})</span>";
            }
        }

        return $html . $countHtml;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isAjax()) {
            $javascript = <<<JAVASCIRPT
<script type="text/javascript">
    LogObj.afterInitPage();
</script>
JAVASCIRPT;
            return $javascript . parent::_toHtml();
        }

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Description' => Mage::helper('M2ePro')->__('Description')
            )
        );

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        CommonObj = new Common();
        LogObj = new Log();
        LogObj.afterInitPage();
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml();
    }

    //########################################

    protected function prepareMessageCount(Mage_Core_Model_Resource_Db_Collection_Abstract $collection)
    {
        $select = clone $collection->getSelect();
        $select->columns(array('number' => 'COUNT(*)'));
        $stmt = $select->query();

        while ($log = $stmt->fetch()) {
            if ($log[$this->_entityIdFieldName]) {
                $this->_messageCount[$log[$this->_entityIdFieldName]][$log['description']]['count'] = $log['number'];
            }
        }
    }

    //########################################
}
