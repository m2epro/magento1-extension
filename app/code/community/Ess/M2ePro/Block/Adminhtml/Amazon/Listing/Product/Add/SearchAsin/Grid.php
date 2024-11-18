<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SearchAsin_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    const SEARCH_SETTINGS_STATUS_NONE = 'none';
    const SEARCH_SETTINGS_STATUS_COMPLETED = 'completed';

    protected $_lockedDataCache = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingData = $this->getListing()->getData();
        $this->setId('searchAsinForListingProductsGrid'.$listingData['id']);

        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');
        $listingData = $this->getListing()->getData();

        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->setListingProductModeOn()
                   ->setStoreId($listingData['store_id'])
                   ->addAttributeToSelect('name')
                   ->addAttributeToSelect('sku');

        $collection->joinStockItem();

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'status'   => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_regular_price'           => 'online_regular_price',
                'online_regular_sale_price'      => 'online_regular_sale_price',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->getSelect()->where('lp.id IN (?)', $listingProductsIds);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'       => Mage::helper('M2ePro')->__('Product ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id'     => $this->getListing()->getStoreId(),
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'                    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'name',
                'filter_index'              => 'name',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '140px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'frame_callback' => array($this, 'callbackColumnGeneralId')
            )
        );

        if ($this->getListing()->getChildObject()->isGeneralIdAttributeMode() ||
            $this->getListing()->getChildObject()->isWorldwideIdAttributeMode()) {
            $this->addColumn(
                'settings', array(
                    'header'         => Mage::helper('M2ePro')->__('Product Identifiers Values'),
                    'align'          => 'left',
                    'width'          => '240px',
                    'filter'         => false,
                    'sortable'       => false,
                    'type'           => 'text',
                    'index'          => 'id',
                    'frame_callback' => array($this, 'callbackColumnSettings')
                )
            );
        }

        $this->addColumn(
            'status', array(
            'header'                    => Mage::helper('M2ePro')->__('Status'),
            'width'                     => '200px',
            'index'                     => 'search_settings_status',
            'filter_index'              => 'search_settings_status',
            'sortable'                  => false,
            'type'                      => 'options',
            'options'                   => array(
                self::SEARCH_SETTINGS_STATUS_NONE => Mage::helper('M2ePro')->__('None'),
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS =>
                    Mage::helper('M2ePro')->__('In Progress'),
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_IDENTIFIER_INVALID =>
                    $this->__('Product ID not found'),
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND =>
                    Mage::helper('M2ePro')->__('Not Found'),
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED =>
                    Mage::helper('M2ePro')->__('Action Required'),
                self::SEARCH_SETTINGS_STATUS_COMPLETED => Mage::helper('M2ePro')->__('Completed')
            ),
            'frame_callback'            => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus'))
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'assignGeneralId', array(
                'label'   => Mage::helper('M2ePro')->__('Search ASIN/ISBN automatically'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'unassignGeneralId', array(
                'label'   => Mage::helper('M2ePro')->__('Reset ASIN/ISBN information'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))
                                                               ->getSku();
        }

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isRelationParentType()) {
            return $value;
        }

        $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();

        $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
        $value .= implode(', ', $productAttributes);
        $value .= '</div>';

        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnSettings($id, $row, $column, $isExport)
    {
        $result = '';
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = Mage::helper('M2ePro/Component_Amazon')
                              ->getObject('Listing_Product', $id)
                              ->getChildObject();
        $identifiers = $amazonListingProduct->getIdentifiers();

        $generalIdValue = Mage::helper('M2ePro')->__('Not set');
        if ($generalId = $identifiers->getGeneralId()) {
            $generalIdValue = $generalId->hasResolvedType()
                ? $generalId->getIdentifier()
                : Mage::helper('M2ePro')->__('Inappropriate value');
        }
        $result .= '<b>' . Mage::helper('M2ePro')->__('ASIN/ISBN') . '</b>: ' . $generalIdValue . '<br/>';

        $worldwideIdValue = Mage::helper('M2ePro')->__('Not Set');
        if ($worldwideId = $identifiers->getWorldwideId()) {
            $worldwideIdValue = $worldwideId->hasResolvedType()
                ? $worldwideId->getIdentifier()
                : Mage::helper('M2ePro')->__('Inappropriate value');
        }
        $result .= '<b>' . Mage::helper('M2ePro')->__('UPC/EAN') . '</b>: ' . $worldwideIdValue;

        return $result;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $generalId = $row->getData('general_id');
        $searchSettingsStatus = $row->getData('search_settings_status');
        $skinUrl = $this->getSkinUrl('M2ePro');
        $style = 'display: inline-block; vertical-align: middle;';

        if (empty($generalId) && empty($searchSettingsStatus)) {
            $msg = Mage::helper('M2ePro')->__('None');
            $tip = Mage::helper('M2ePro')->__('The Search of Product was not performed yet');

            return <<<HTML
<span style="color: gray; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span><br/>
HTML;
        }

        switch ($searchSettingsStatus) {
            case Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS:
                $searchData = Mage::helper('M2ePro')->jsonDecode($row->getData('search_settings_data'));

                $msg = Mage::helper('M2ePro')->__('In Progress');
                $tip = Mage::helper('M2ePro')->__(
                    'The Search is being performed now by %type% "%value%"',
                    $this->prepareSearchType($searchData['type']), $searchData['value']
                );

                return <<<HTML
<span style="color: orange; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span><br/>
HTML;
            case Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_IDENTIFIER_INVALID:
                $msg = Mage::helper('M2ePro')->__('Invalid Product ID');
                $tip = Mage::helper('M2ePro')->__('Product ID is missing or has invalid format');

                return <<<HTML
<span style="color: orange; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span><br/>
HTML;

            case Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND:

                $msg = Mage::helper('M2ePro')->__('Product was not found');
                $tip = Mage::helper('M2ePro')->__(
                    'There are no Products found on Amazon after the Automatic Search
                                                   was performed according to Listing Product Identifiers.'
                );

                return <<<HTML
<span style="color: red; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span><br/>
HTML;
            case Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED:

                $searchData = Mage::helper('M2ePro')->jsonDecode($row->getData('search_settings_data'));

                $lpId = $row->getData('id');

                $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
                if (strlen($productTitle) > 60) {
                    $productTitle = substr($productTitle, 0, 60) . '...';
                }

                $productTitle = Mage::helper('M2ePro')->__(
                    'Search ASIN/ISBN For &quot;%product_title%&quot;',
                    $productTitle
                );
                $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);

                $linkTxt = Mage::helper('M2ePro')->__('choose one of the Results');

                $linkHtml = <<<HTML
<a href="javascript:void(0)"
    onclick="ListingGridObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">{$linkTxt}</a>
HTML;

                $msg = Mage::helper('M2ePro')->__('Action Required');
                $tip = Mage::helper('M2ePro')->__(
                    'Please %link% that were found by %type% "%value%"',
                    $linkHtml, $this->prepareSearchType($searchData['type']), $searchData['value']
                );

                return <<<HTML
<span style="color: orange; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span>
HTML;
        }

        $searchInfo = Mage::helper('M2ePro')->jsonDecode($row->getData('general_id_search_info'));

        $msg = Mage::helper('M2ePro')->__('Completed');
        $tip = Mage::helper('M2ePro')->__(
            'Product was found by %type% "%value%"',
            $this->prepareSearchType($searchInfo['type']), $searchInfo['value']
        );

        return <<<HTML
<span style="color: green; {$style}">{$msg}</span>&nbsp;
<img class="tool-tip-image" style="{$style}" src="{$skinUrl}/images/tool-tip-icon.png">
<span class="tool-tip-message tip-left" style="left: 528px; top: 249px; display: none; min-width: 230px;">
    <img src="{$skinUrl}/images/help.png">
    <span>{$tip}</span>
</span><br/>
HTML;
    }

    protected function prepareSearchType($searchType)
    {
        if ($searchType == 'string') {
            return 'query';
        }

        return strtoupper($searchType);
    }

    //########################################

    protected function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        // ---------------------------------------

        // ---------------------------------------
        $lpId = $row->getData('id');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }

        $productTitle = Mage::helper('M2ePro')->__('Search ASIN/ISBN For &quot;%product_title%&quot;', $productTitle);
        $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
        // ---------------------------------------

        // ---------------------------------------

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {
            $tip = Mage::helper('M2ePro')->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $iconPath.'processing.gif';

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt=""></a>
HTML;
        }

        // ---------------------------------------

        // ---------------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED) {
            $linkTxt = Mage::helper('M2ePro')->__('Choose ASIN/ISBN');

            return <<<HTML
<a href="javascript:;" title="{$linkTxt}"
   onclick="ListingGridObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">{$linkTxt}</a>
HTML;
        }

        // ---------------------------------------

        $na = Mage::helper('M2ePro')->__('N/A');
        $tip = Mage::helper('M2ePro')->__('Search for ASIN/ISBN');
        $iconSrc = $iconPath.'search.png';

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridObj.productSearchHandler.showSearchManualPrompt('{$productTitle}',{$lpId});">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
    }

    protected function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');
        $marketplaceId = $this->getListing()->getMarketplaceId();

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $marketplaceId
        );

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = Mage::helper('M2ePro')->jsonDecode($generalIdSearchInfo);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {
            $tip = Mage::helper('M2ePro')->__('ASIN/ISBN was found automatically');

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

        $listingProductId = (int)$row->getData('id');

        $tip = Mage::helper('M2ePro')->__('Unassign ASIN/ISBN');
        $iconSrc = $iconPath.'unassign.png';

        $text .= <<<HTML
<a href="javascript:;"
    onclick="ListingGridObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
    title="{$tip}">
    <img src="{$iconSrc}" width="16" height="16"/></a>
HTML;

        return $text;
    }

    //########################################

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

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if ($value == self::SEARCH_SETTINGS_STATUS_NONE) {
            $collection->addFieldToFilter('general_id', array('null' => null));
            $collection->addFieldToFilter('search_settings_status', array('null' => null));
            return;
        }

        if ($value == self::SEARCH_SETTINGS_STATUS_COMPLETED) {
            $collection->addFieldToFilter(
                array(
                    array('attribute'=>'general_id', 'notnull' => null)
                )
            );

            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'search_settings_status', 'eq' => $value)
            )
        );
    }

    //########################################

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->_lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load(
                $listingProductId
            )->getProcessingLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action'    => !empty($objectLocks),
            );
            $this->_lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->_lockedDataCache[$listingProductId];
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $disableMassactionSearch = '';
        $startSearch = '';

        $listing = $this->getListing();

        if (!$listing->getChildObject()->isGeneralIdAttributeMode() &&
            !$listing->getChildObject()->isWorldwideIdAttributeMode()) {
                $gridId = $this->getId();

                $disableMassactionSearch = <<<JS
var mmassActionEl = $("{$gridId}_massaction-select");

if (mmassActionEl &&  mmassActionEl.select('option[value="assignGeneralId"]').length > 0) {
    var assignGeneralIdOption = mmassActionEl.select('option[value="assignGeneralId"]')[0];
    assignGeneralIdOption.disabled = true;

    mmassActionEl.insert({bottom: assignGeneralIdOption.remove()});
}
JS;
        } else {
            $autoSearchSetting = $listing->getSetting('additional_data', 'auto_search_was_performed');

            if (!$autoSearchSetting) {
                $listing->setSetting('additional_data', 'auto_search_was_performed', 1);
                $listing->save();

                $startSearch = <<<JS
ListingGridObj.getGridMassActionObj().selectAll();
ListingGridObj.productSearchHandler.searchGeneralIdAuto(ListingGridObj.getSelectedProductsString());
JS;
            }
        }

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
        {$disableMassactionSearch}
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridObj.afterInitPage();
            {$disableMassactionSearch}
            {$startSearch}
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################
}
