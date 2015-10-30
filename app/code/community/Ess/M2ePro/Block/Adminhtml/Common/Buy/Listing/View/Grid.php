<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_View_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    public $hideMassactionColumn = false;

    /** @var $sellingFormatTemplate Ess_M2ePro_Model_Buy_Template_SellingFormat */
    private $sellingFormatTemplate = NULL;

    private $lockedDataCache = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingViewGrid'.$listingData['id']);
        // ---------------------------------------

        $this->showAdvancedFilterProductsOption = false;

        $this->sellingFormatTemplate = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Template_SellingFormat', $listingData['template_selling_format_id'], NULL,
            array('template')
        );
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection
        // ---------------------------------------
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty'),
                '{{table}}.stock_id=1',
                'left'
            );

        // ---------------------------------------

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'listing_id'      => 'listing_id',
                'component_mode'  => 'component_mode',
                'buy_status'      => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('blp' => 'M2ePro/Buy_Listing_Product'),
            'listing_product_id=id',
            array(
                'template_new_product_id'       => 'template_new_product_id',
                'general_id'                    => 'general_id',
                'buy_sku'                       => 'sku',
                'online_price'                  => 'online_price',
                'online_qty'                    => 'online_qty',
                'search_settings_status'        => 'search_settings_status',
                'search_settings_data'          => 'search_settings_data'
            ),
            null
        );
        // ---------------------------------------

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
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('Reference ID'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'buy_sku',
            'filter_index' => 'buy_sku',
            'frame_callback' => array($this, 'callbackColumnSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('Rakuten.com SKU'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => Mage::helper('M2ePro')->__('QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
        ));

        $this->addColumn('online_price', array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'online_price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '125px',
            'index' => 'buy_status',
            'filter_index' => 'buy_status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            $this->addColumn('developer_action', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '120px',
                'type'      => 'text',
                'renderer'  => 'M2ePro/adminhtml_listing_view_grid_column_renderer_developerAction',
                'index'     => 'value',
                'filter'    => false,
                'sortable'  => false,
                'js_handler' => 'ListingGridHandlerObj'
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $groups = array(
            'actions'    => Mage::helper('M2ePro')->__('Actions'),
            'rekuten_sku' => Mage::helper('M2ePro')->__('Rakuten.com SKU'),
            'other'      => Mage::helper('M2ePro')->__('Other')
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem('list', array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('newGeneralId', array(
                'label'    => Mage::helper('M2ePro')->__('Add New'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'rekuten_sku');

        $this->getMassactionBlock()->addItem('assignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'rekuten_sku');

        $this->getMassactionBlock()->addItem('unassignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Unassign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'rekuten_sku');

        $this->getMassactionBlock()->addItem('moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');

        $this->getMassactionBlock()->addItem('duplicate', array(
            'label'    => Mage::helper('M2ePro')->__('Duplicate'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');
        is_null($tempSku)
            && $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
                  ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $productOptions = $listingProduct->getChildObject()->getVariationManager()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
        }

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProductMatched()) {

            $popupTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                Mage::helper('M2ePro')->__('Manage "%product_title%" Options', $productTitle))
            );
            $linkTitle = Mage::helper('M2ePro')->__('Manage Options');
            $linkContent = '<img height="12" width="12" src="'.$this->getSkinUrl('M2ePro/images/add.png').'">';

            $value.= <<<HTML
<div style="clear: both"></div>
<div style="float: left; margin: 0 0 0 5px">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

            $linkContent = Mage::helper('M2ePro')->__('Manage Options');

            $value .= <<<HTML
<div style="float: left; margin: 0 0 0 5px">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

            return $value;
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if (!$hasInActionLock) {

            $popupTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
                Mage::helper('M2ePro')->__('Edit "%product_title%" Variation', $productTitle))
            );
            $linkTitle  = Mage::helper('M2ePro')->__('Edit Variation');
            $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/pencil.png').'">';

            $value .= <<<HTML
<div style="clear: both"></div>
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showEditPopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;
        }

        $popupTitle = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->escapeHtml(
            Mage::helper('M2ePro')->__('Add Another "%product_title%" Variations', $productTitle))
        );
        $linkTitle  = Mage::helper('M2ePro')->__('Add Another Variation(s)');
        $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/add.png').'">';

        $value.= <<<HTML
<div style="margin: 0 0 0 5px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

        return $value;
    }

    public function callbackColumnSku($value, $row, $column, $isExport)
    {
        if ($row->getData('buy_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }
        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('buy_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return '<i style="color:gray;">receiving...</i>';
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('buy_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return '<i style="color:gray;">receiving...</i>';
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        return Mage::app()->getLocale()->currency('USD')->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId = (int)$row->getData('id');

        $html = $this->getViewLogIconHtml($listingProductId);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$listingProductId);

        $synchNote = $listingProduct->getSetting('additional_data', 'synch_template_list_rules_note');
        if (!empty($synchNote)) {

            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage($synchNote);

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
            } else {
                $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        switch ($row->getData('buy_status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html .= '<span style="color: red;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'newsku_action':
                    $html .= '<br/><span style="color: #605fff">[Add New SKU in Progress...]</span>';
                    break;

                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%')
            )
        );
    }

    //########################################

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('buy_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return <<<HTML
<i style="color:gray;">receiving...</i>
HTML;
        }
        // ---------------------------------------

        // ---------------------------------------
        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        // ---------------------------------------

        // ---------------------------------------
        $lpId = $row->getData('id');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }
        $productTitle = Mage::helper('M2ePro')->__('Assign Rakuten.com SKU For &quot;%product_title%&quot;',
                                                   $productTitle);
        $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
        // ---------------------------------------

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        $na = Mage::helper('M2ePro')->__('N/A');

        $templateNewProductId = $row->getData('template_new_product_id');
        if (!empty($templateNewProductId)) {

            $newSkuTemplateUrl = $this->getUrl(
                '*/adminhtml_common_buy_template_newProduct/edit/',
                array(
                    'id' => $templateNewProductId,
                    'listing_product_id' => $lpId,
                    'save_and_assign' => 0,
                    'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_buy_listing/view',array(
                        'id' => $row->getData('listing_id'),
                    ))
                )
            );

            $newSkuTemplateTitle = Mage::getModel('M2ePro/Buy_Template_NewProduct')
                ->load($templateNewProductId)
                ->getData('title');

            if ($hasInActionLock) {
                return $newSkuTemplateTitle;
            }

            $tip = Mage::helper('M2ePro')->__('Unassign New SKU Policy');
            $iconSrc = $iconPath.'unassign.png';

            return <<<HTML
<a href="{$newSkuTemplateUrl}">{$newSkuTemplateTitle}</a>
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt('{$lpId}');">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
        }
        // ---------------------------------------

        // ---------------------------------------
        $searchSettingsData = $row->getData('search_settings_data');

        if (!is_null($searchSettingsData)) {
            $searchSettingsData = @json_decode($searchSettingsData,true);
        }
        // ---------------------------------------

        if (!empty($searchSettingsData['data'])) {

            $tip = Mage::helper('M2ePro')->__('Choose Rakuten.com SKU from the list');
            $iconSrc = $iconPath.'list.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
        }

        $searchSettingsStatus = $row->getData('search_settings_status');

        if ($searchSettingsStatus == Ess_M2ePro_Model_Buy_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND) {

            $tip = Mage::helper('M2ePro')->__(
                'There were no Products found on Rakuten.com according to the Listing Search Settings.'
            );
            $tip = Mage::helper('M2ePro')->escapeJs($tip);

            $iconSrc = $iconPath.'error.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript: void(0);" title="{$tip}"
    onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId},'{$tip}');">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
        }

        $tip = Mage::helper('M2ePro')->__('Search for Rakuten.com SKU');
        $iconSrc = $iconPath.'search.png';

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
    <img src="{$iconSrc}" alt="" width="16" height="16">
</a>
HTML;
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');

        $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($generalId);

        if ((int)$row->getData('buy_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            $conditionTitle = '';
            switch ((int)$row->getData('condition')) {
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_NEW:
                    $conditionTitle = Mage::helper('M2ePro')->__('New');
                    break;
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_USED_LIKE_NEW:
                    $conditionTitle = Mage::helper('M2ePro')->__('Used - Like New');
                    break;
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_USED_VERY_GOOD:
                    $conditionTitle = Mage::helper('M2ePro')->__('Used - Very Good');
                    break;
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_USED_GOOD:
                    $conditionTitle = Mage::helper('M2ePro')->__('Used - Good');
                    break;
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_USED_ACCEPTABLE:
                    $conditionTitle = Mage::helper('M2ePro')->__('Used - Acceptable');
                    break;
                case Ess_M2ePro_Model_Buy_Listing::CONDITION_REFURBISHED:
                    $conditionTitle = Mage::helper('M2ePro')->__('Refurbished');
                    break;
            }
            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a><br/>
<span style="color:gray;">{$conditionTitle}</span>
HTML;
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $generalIdSearchInfo = @json_decode($row->getData('general_id_search_info'), true);

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {

            $tip = Mage::helper('M2ePro')->__('Rakuten.com SKU was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;

        } else {

            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;

        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text;
        }

        $tip = Mage::helper('M2ePro')->__('Unassign Rakuten.com SKU');
        $iconSrc = $iconPath.'unassign.png';

        $id = $row->getData('id');

        $text .= <<<HTML
<a href="javascript:;" onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$id});"
                       title="{$tip}">
    <img src="{$iconSrc}" width="16" height="16"/>
</a>
HTML;

        return $text;
    }

    //########################################

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
            ->where('`listing_product_id` = ?',$listingProductId)
            ->where('`action_id` IS NOT NULL')
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
            'entity_id' => $listingProductId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'ListingGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'ListingGridHandlerObj.hideItemHelp',
        ));

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('New SKU');
                break;
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
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
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
        return $this->getUrl('*/adminhtml_common_buy_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    private function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load($listingProductId)->getObjectLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action' => !empty($objectLocks),
            );
            $this->lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->lockedDataCache[$listingProductId];
    }

    //########################################
}
