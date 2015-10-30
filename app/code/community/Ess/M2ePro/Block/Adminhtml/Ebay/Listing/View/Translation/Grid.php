<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Translation_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewGridTranslation'.$listing->getId());
        // ---------------------------------------

        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    //########################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
        }
        return $this;
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        // ---------------------------------------
        // Get collection
        // ---------------------------------------
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        // ---------------------------------------

        // Join listing product tables
        // ---------------------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$listingData['id']
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'  => 'listing_product_id',
                'online_title'        => 'online_title',
                'online_sku'          => 'online_sku',
                'ebay_item_id'        => 'ebay_item_id',
                'translation_status'  => 'translation_status',
                'translation_service' => 'translation_service',
                'translated_date'     => 'translated_date',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            NULL,
            'left'
        );
        // ---------------------------------------

        // Set collection filters
        // ---------------------------------------
        $collection->addFieldToFilter('translation_status', array('neq' =>
            Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_NONE
        ));
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId'),
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'online_title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('language', array(
            'header'    => Mage::helper('M2ePro')->__('Source Language'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnSourceLanguage')
        ));

        $this->addColumn('service', array(
            'header'    => Mage::helper('M2ePro')->__('Translation Plan'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'translation_service',
            'options'   => Mage::helper('M2ePro/Component_Ebay')->getTranslationServices(),
            'frame_callback' => array($this, 'callbackColumnServices')
        ));

        $this->addColumn('translated_date', array(
            'header'    => Mage::helper('M2ePro')->__('Translated Date'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'translated_date',
            'frame_callback' => array($this, 'callbackColumnTranslatedTime')
        ));

        $this->addColumn('translation_status',
            array(
                'header'=> Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'translation_status',
                'filter_index' => 'translation_status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING =>
                        Mage::helper('M2ePro')->__('Pending'),
                    Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED =>
                        Mage::helper('M2ePro')->__('Payment Required'),
                    Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_IN_PROGRESS =>
                        Mage::helper('M2ePro')->__('In Progress'),
                    Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_TRANSLATED =>
                        Mage::helper('M2ePro')->__('Translated'),
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        // ---------------------------------------

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->getMassactionBlock()->addItem('startTranslate', array(
                'label'    => Mage::helper('M2ePro')->__('Translate'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ));

            $this->getMassactionBlock()->addItem('stopTranslate', array(
                'label'    => Mage::helper('M2ePro')->__('Stop Translation'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ));
        }

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = $row->getName();

        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $value = $onlineTitle;

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        if (is_null($sku = $row->getData('sku'))) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU').':</strong> '
            .Mage::helper('M2ePro')->escapeHtml($sku);
        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('translation_status')) {

            case Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_IN_PROGRESS:
                $value = '<span style="color: blue;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_TRANSLATED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value.$this->getViewLogIconHtml($row->getData('listing_product_id'));
    }

    public function callbackColumnSourceLanguage($value, $row, $column, $isExport)
    {
        $additionalData = json_decode($row->getData('additional_data'), true);
        if (empty($additionalData['translation_service'])) {
            return 'N/A';
        }

        $translationData = $additionalData['translation_service'];

        $label        = Mage::helper('M2ePro')->__('Show Info');
        $popupTitle   = Mage::helper('M2ePro')->__('Translation Info');
        $popupContent = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
            $this->getTranslationInfoHtml($row, $translationData['from'], $translationData['to'])));

        return <<<HTML
    {$translationData['from']['language']}
    &nbsp;<a href="javascript:void(0);"
           onclick="EbayListingTransferringInfoHandlerObj.showTranslationDetails('{$popupTitle}','{$popupContent}');"
           >{$label}</a>
HTML;
    }

    public function callbackColumnServices($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnTranslatedTime($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%')
            )
        );
    }

    // ---------------------------------------

    public function getViewLogIconHtml($listingProductId)
    {
        $listingProductId = (int)$listingProductId;

        // Get last messages
        // ---------------------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_product_id` = ?', $listingProductId)
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', array(
                Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT,
            ))
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $connRead->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {

            $row['description'] = Mage::helper('M2ePro')->escapeHtml($row['description']);
            $row['description'] = Mage::getModel('M2ePro/Log_Abstract')->decodeDescription($row['description']);

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
            'entity_id' => $listingProductId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'EbayListingTranslationGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'EbayListingTranslationGridHandlerObj.hideItemHelp',
        ));

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Translate');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
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

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascriptsMain = <<<HTML

<script type="text/javascript">
    EbayListingTranslationGridHandlerObj.afterInitPage();
    EbayListingTranslationGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');
</script>

HTML;
            return parent::_toHtml().$javascriptsMain;
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        // static routes
        $urls = array(
            'adminhtml_ebay_log/listing' => $this->getUrl(
                    '*/adminhtml_ebay_log/listing', array(
                        'id' =>$listingData['id']
                    )
                )
        );

        $path = 'adminhtml_ebay_listing/getTranslationHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
                'listing_id' => $listingData['id']
            ));

        $urls = json_encode($urls);

        $gridId = $component . 'ListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $logViewUrl = $this->getUrl('*/adminhtml_ebay_log/listing',array(
            'id'=>$listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_ebay_listing/view',array('id'=>$listingData['id']))
        ));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runStartTranslateProducts = $this->getUrl('*/adminhtml_ebay_listing/runStartTranslateProducts');
        $runStopTranslateProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopTranslateProducts');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage =
            $helper->escapeJs($helper->__('"%task_title%" Task has successfully completed.'));

        // M2ePro_TRANSLATIONS
        // %task_title%" Task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.
        $tempString = '"%task_title%" Task has completed with warnings.'
                     .' <a target="_blank" href="%url%">View Log</a> for details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($tempString));

        // M2ePro_TRANSLATIONS
        // "%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.
        $tempString = '"%task_title%" Task has completed with errors.'
                     .' <a target="_blank" href="%url%">View Log</a> for details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($tempString));

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% Product(s) data on eBay.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing is empty.')
        );

        $startTranslateSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Translation in Progress')
        );
        $stopTranslateSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping Translation')
        );

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select Items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $translations = json_encode(array(
            'Payment for Translation Service' => $helper->__('Payment for Translation Service'),
            'Payment for Translation Service. Help' => $helper->__('Payment for Translation Service'),
            'Specify a sum to be credited to an Account.' =>
                $helper->__('Specify a sum to be credited to an Account.'
                        .' If you are planning to order more Items for Translation in future,'
                        .' you can credit the sum greater than the one needed for current Translation.'
                        .' Click <a href="%url%" target="_blank">here</a> to find out more.',
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(NULL, NULL,
                    'x/BQAJAQ#SellonanothereBaySite-Account')
                ),
            'Amount to Pay.' => $helper->__('Amount to Pay'),
            'Insert amount to be credited to an Account' => $helper->__('Insert amount to be credited to an Account.'),
            'Confirm' => $helper->__('Confirm'),
        ));

        $javascriptsMain = <<<HTML

<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runStartTranslateProducts = '{$runStartTranslateProducts}';
    M2ePro.url.runStopTranslateProducts = '{$runStopTranslateProducts}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.start_translate_selected_items_message = '{$startTranslateSelectedItemsMessage}';
    M2ePro.text.stop_translate_selected_items_message = '{$stopTranslateSelectedItemsMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {

        EbayListingTranslationGridHandlerObj = new EbayListingTranslationGridHandler(
            '{$this->getId()}',
            {$listingData['id']}
        );
        EbayListingTranslationGridHandlerObj.afterInitPage();
        EbayListingTranslationGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');

        EbayListingTranslationGridHandlerObj.actionHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        EbayListingTransferringTranslateHandlerObj = new EbayListingTransferringTranslateHandler();

        EbayListingTransferringPaymentHandlerObj = new EbayListingTransferringPaymentHandler();
        EbayListingTransferringInfoHandlerObj = new EbayListingTransferringInfoHandler();

    });

</script>

HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    private function getGridIdsJson()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();

        $select->columns('elp.listing_product_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        return implode(',',$connRead->fetchCol($select));
    }

    //########################################

    private function getTranslationInfoHtml($row, $sourceData, $targetData)
    {
        $helper = Mage::helper('M2ePro');

        $sourceTitle = empty($sourceData['description']['title'])
            ? $helper->__('N/A')
            : $sourceData['description']['title'];
        $sourceSubtitle = empty($sourceData['description']['subtitle'])
            ? ''
            : <<<HTML
    <p style="margin-top: 2px;margin-left:20px;">
        <span style="display:inline-block;min-width:40px;text-decoration: underline;">
            From:</span> {$sourceData['description']['subtitle']}
    </p>
HTML;

        $sourceCategory = $sourceData['category']['path'] . ' ('. $sourceData['category']['primary_id'] . ')';

        $sourceSpecifics = '';
        foreach ($sourceData['item_specifics'] as $specific) {
            $specifics = implode(', ',$specific['value']);
            if (empty($specifics)) {
                continue;
            }
            $sourceSpecifics .= '<p style="margin-top:2px;margin-left:60px;">'.$specific['name'].': '.$specifics.'</p>';
        }

        $sourceSpecifics = empty($sourceSpecifics)
            ? ''
            : '<p style="margin-top: 2px;margin-left:20px; text-decoration: underline;">From:</p>'.$sourceSpecifics;

        if ($row->getData('translation_status')==Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_TRANSLATED) {

            $targetTitle = empty($targetData['title'])
                ? $helper->__('N/A')
                : $targetData['title'];
            $targetTitle = <<<HTML
    <p style="margin-top: 2px;margin-left:20px;">
        <span style="display:inline-block;min-width:40px;text-decoration: underline;">To:</span> {$targetTitle}
    </p>
HTML;
            if (!empty($sourceData['description']['subtitle'])) {
                $targetSubtitle = empty($targetData['subtitle'])
                    ? $helper->__('N/A')
                    : $targetData['subtitle'];
                $targetSubtitle = <<<HTML
    <p style="margin-top: 2px;margin-left:20px;">
        <span style="display:inline-block;min-width:40px;text-decoration: underline;">To:</span> {$targetSubtitle}
    </p>
HTML;
            } else {
                $targetSubtitle = '';
            }

            $targetCategory = $helper->__('N/A');
            if (!empty($targetData['category']['path']) && !empty($targetData['category']['primary_id'])) {
                $targetCategory = $targetData['category']['path'] . ' ('. $targetData['category']['primary_id'] . ')';
            }

            $targetCategory = <<<HTML
    <p style="margin-top: 2px;margin-left:20px;">
        <span style="display:inline-block;min-width:40px;text-decoration: underline;">To:</span> {$targetCategory}
    </p>
HTML;
            $targetSpecifics = '';
            foreach ($targetData['item_specifics'] as $specific) {
                $specifics = implode(', ',$specific['value']);
                if (empty($specifics)) {
                    continue;
                }
                $targetSpecifics .=
                    '<p style="margin-top:2px;margin-left:60px;">'.$specific['name'].': '.$specifics.'</p>';
            }

            $targetSpecifics = empty($targetSpecifics)
                ? ''
                : '<p style="margin-top: 2px;margin-left:20px; text-decoration: underline;">To:</p>'.$targetSpecifics;
        } else {
            $targetTitle     = '';
            $targetSubtitle  = '';
            $targetCategory  = '';
            $targetSpecifics = '';
        }

        $blockSubtitle = empty($sourceSubtitle) && empty($targetSubtitle)
            ? ''
            : <<<HTML
    <div style="margin: 2px 0 6px 0;">
        <span style="font-weight: bold">{$helper->__('Subtitle')}:</span>
        {$sourceSubtitle}
        {$targetSubtitle}
    </div>
HTML;

        $blockSpecifics = empty($sourceSpecifics) && empty($targetSpecifics)
            ? ''
            : <<<HTML
    <div style="margin: 2px 0;">
        <span style="font-weight: bold">{$helper->__('Specifics')}:</span>
        {$sourceSpecifics}
        {$targetSpecifics}
    </div>
HTML;

        return <<<HTML
    <div style="padding-top: 10px; height: 350px; overflow-x: auto;">
        <div style="margin: 2px 0;">
            <span style="font-weight: bold">{$helper->__('Title')}:</span>
            <p style="margin-top: 2px;margin-left:20px;">
                <span style="display:inline-block;min-width:40px;text-decoration:underline;">From:</span> {$sourceTitle}
            </p>
            {$targetTitle}
        </div>
        {$blockSubtitle}
        <div style="margin: 2px 0 6px 0;">
            <span style="font-weight: bold">{$helper->__('Category')}:</span>
            <p style="margin-top: 2px;margin-left:20px;">
                <span style="display:inline-block;min-width:40px;text-decoration:underline;">
                    From:</span> {$sourceCategory}
            </p>
            {$targetCategory}
        </div>
        $blockSpecifics
    </div>
    <div style="float: right; margin-top: 10px;">
        <a href="javascript:void(0);" onclick="Windows.getFocusedWindow().close()">{$helper->__('Close')}<a/>
    </div>
HTML;
    }

    //########################################
}