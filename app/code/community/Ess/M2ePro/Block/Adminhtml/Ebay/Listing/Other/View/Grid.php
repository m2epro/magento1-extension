<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        /** @var $this->connRead Varien_Db_Adapter_Pdo_Mysql */
        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingOtherViewGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'mp.title')
        );

        $collection->getSelect()->joinLeft(
            array('mea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
            'mea.account_id = main_table.account_id',
            array('account_mode' => 'mea.mode')
        );

        // Add Filter By Account
        if ($accountId = $this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        // Add Filter By Marketplace
        if ($marketplaceId = $this->getRequest()->getParam('marketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'id'                    => 'main_table.id',
                'account_id'            => 'main_table.account_id',
                'marketplace_id'        => 'main_table.marketplace_id',
                'product_id'            => 'main_table.product_id',
                'title'                 => 'second_table.title',
                'sku'                   => 'second_table.sku',
                'item_id'               => 'second_table.item_id',
                'online_qty'            => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'       => 'second_table.online_qty_sold',
                'online_price'          => 'second_table.online_price',
                'status'                => 'main_table.status',
                'start_date'            => 'second_table.start_date',
                'end_date'              => 'second_table.end_date',
                'currency'              => 'second_table.currency',
                'account_mode'          => 'mea.mode'
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('M2ePro')->__('Product ID'),
            'align' => 'left',
            'type' => 'number',
            'width' => '80px',
            'index' => 'product_id',
            'filter_index' => 'main_table.product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('M2ePro')->__('Title / SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'filter_index' => 'second_table.title',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('item_id', array(
            'header' => Mage::helper('M2ePro')->__('Item ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'item_id',
            'filter_index' => 'second_table.item_id',
            'frame_callback' => array($this, 'callbackColumnItemId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('Available QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
        ));

        $this->addColumn('online_qty_sold', array(
            'header' => Mage::helper('M2ePro')->__('Sold QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_qty_sold',
            'filter_index' => 'second_table.online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
        ));

        $this->addColumn('online_price', array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'second_table.online_price',
            'frame_callback' => array($this, 'callbackColumnOnlinePrice')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED   => Mage::helper('M2ePro')->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN   => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_SOLD     => Mage::helper('M2ePro')->__('Sold'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED  => Mage::helper('M2ePro')->__('Stopped'),
                Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED => Mage::helper('M2ePro')->__('Finished'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED  => Mage::helper('M2ePro')->__('Pending')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        /*$this->addColumn('start_date', array(
             'header' => Mage::helper('M2ePro')->__('Start Date'),
             'align' => 'right',
             'width' => '130px',
             'type' => 'datetime',
             'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
             'index' => 'start_date',
             'filter_index' => 'second_table.start_date',
             'frame_callback' => array($this, 'callbackColumnStartTime')
        ));*/

        $this->addColumn('end_date', array(
           'header' => Mage::helper('M2ePro')->__('End Date'),
           'align' => 'right',
           'width' => '150px',
           'type' => 'datetime',
           'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
           'index' => 'end_date',
           'filter_index' => 'second_table.end_date',
           'frame_callback' => array($this, 'callbackColumnEndTime')
        ));

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing_other/view',array(
            'account' => $this->getRequest()->getParam('account'),
            'marketplace' => $this->getRequest()->getParam('marketplace'),
            'back' => $this->getRequest()->getParam('back')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View Log'),
                    'url'       => array('base'=> '*/adminhtml_ebay_log/listingOther'),
                    'target'    => '_blank',
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Clear Log'),
                    'url'       => array('base'=> '*/adminhtml_listing_other/clearLog/back/'.$back.'/'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups(array(
            'actions'            => Mage::helper('M2ePro')->__('Actions'),
            'mapping' => Mage::helper('M2ePro')->__('Mapping'),
            'other'                    => Mage::helper('M2ePro')->__('Other')
        ));

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('revise', array(
            'label'   => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('relist', array(
            'label'   => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stop', array(
            'label'   => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('autoMapping', array(
            'label'   => Mage::helper('M2ePro')->__('Map Item(s) Automatically'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'mapping');

        $this->getMassactionBlock()->addItem('moving', array(
            'label'   => Mage::helper('M2ePro')->__('Move Item(s) to Listing'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');
        $this->getMassactionBlock()->addItem('removing', array(
            'label'   => Mage::helper('M2ePro')->__('Remove Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');
        $this->getMassactionBlock()->addItem('unmapping', array(
            'label'   => Mage::helper('M2ePro')->__('Unmap Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'mapping');
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));
            $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                    onclick="EbayListingOtherMappingHandlerObj.openPopUp(\''.
                                        $productTitle.
                                        '\','.
                                        (int)$row->getId().
                                    ');">' . Mage::helper('M2ePro')->__('Map') . '</a>';

            if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
                $htmlValue .= '<br/>' . $row->getId();
            }
            return $htmlValue;
        }

        $htmlValue = '&nbsp<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $row->getData('product_id')))
            .'" target="_blank">'
            .$row->getData('product_id')
            .'</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            .' onclick="EbayListingOtherGridHandlerObj.movingHandler.getGridHtml('
            .json_encode(array((int)$row->getData('id')))
            .')">'
            .Mage::helper('M2ePro')->__('Move')
            .'</a>';

        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            $htmlValue .= '<br/>' . $row->getId();
        }

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';

        $tempSku = $row->getData('sku');

        if (is_null($tempSku)) {
            $tempSku = '<i style="color:gray;">receiving...</i>';
        } elseif ($tempSku == '') {
            $tempSku = '<i style="color:gray;">none</i>';
        } else {
            $tempSku = Mage::helper('M2ePro')->escapeHtml($tempSku);
        }

        $value .= '<br/><strong>'
                  .Mage::helper('M2ePro')->__('SKU')
                  .':</strong> '
                  .$tempSku;

        return $value;
    }

    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl($row->getData('item_id'),
                                                                 $row->getData('account_mode'),
                                                                 $row->getData('marketplace_id'));
        $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';

        return $value;
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            return '<span style="color: gray; text-decoration: line-through;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineQtySold($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnOnlinePrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        return Mage::app()->getLocale()->currency($row->getData('currency'))->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $value = '<span style="color: brown;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $value = '<span style="color: blue;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value.$this->getViewLogIconHtml($row->getId());
    }

    public function callbackColumnStartTime($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnEndTime($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%'.$value.'%');
    }

    //########################################

    public function getViewLogIconHtml($listingOtherId)
    {
        $listingOtherId = (int)$listingOtherId;

        // Get last messages
        // ---------------------------------------
        $dbSelect = $this->connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_other_id` = ?', $listingOtherId)
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $this->connRead->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {

            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (count($tempActionRows) > 0) {
                    $actionsRows[] = array(
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }
                $lastActionId = $row['action_id'];
            }
            $tempActionRows[] = $row;
        }

        if (count($tempActionRows) > 0) {
            $actionsRows[] = array(
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (count($actionsRows) <= 0) {
            return '';
        }

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock('M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => $listingOtherId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'EbayListingOtherGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'EbayListingOtherGridHandlerObj.hideItemHelp',
        ));

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Other_Log::ACTION_REVISE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['initiator']) {
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

    public function getMainTypeForActionId($actionRows)
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
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof EbayListingOtherGridHandlerObj != 'undefined') {
        EbayListingOtherGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            EbayListingOtherGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing_other/viewGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}