<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Variation_Product_Manage_Tabs_Variations_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_lockedDataCache = array();

    protected $_childListingProducts;
    protected $_currentProductVariations;
    protected $_usedProductVariations;

    protected $_listingProductId;
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Amazon')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonVariationProductManageGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()->where("`second_table`.`variation_parent_id` = ?", (int)$this->getListingProductId());

        $collection->getSelect()->columns(
            array(
                'amazon_sku' => 'second_table.sku',
                'online_current_price' => new Zend_Db_Expr(
                    '
                    IF (
                        `second_table`.`online_regular_price` IS NULL,
                        `second_table`.`online_business_price`,
                        IF (
                            `second_table`.`online_regular_sale_price` IS NOT NULL AND
                            `second_table`.`online_regular_sale_price_end_date` IS NOT NULL AND
                            `second_table`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                            `second_table`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                            `second_table`.`online_regular_sale_price`,
                            `second_table`.`online_regular_price`
                        )
                    )
                '
                )
            )
        );

        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr(
                '(
                SELECT
                    mlpv.listing_product_id,
                    GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`product_id` SEPARATOR \'||\') as products_ids
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` as mlpv
                INNER JOIN `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable() .
                    '` AS `mlpvo` ON (`mlpvo`.`listing_product_variation_id`=`mlpv`.`id`)
                WHERE `mlpv`.`component_mode` = \'amazon\'
                GROUP BY `mlpv`.`listing_product_id`
            )'
            ),
            'main_table.id=t.listing_product_id',
            array(
                'products_ids' => 'products_ids',
            )
        );

        $collection->getSelect()->joinLeft(
            array('malpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            '(`second_table`.`listing_product_id` = `malpr`.`listing_product_id`)',
            array(
                'is_repricing_disabled'   => 'is_online_disabled',
                'is_repricing_inactive'   => 'is_online_inactive',
            )
        );

        if ($this->getParam($this->getVarNameFilter()) == 'searched_by_child'){
            $collection->addFieldToFilter(
                'second_table.listing_product_id',
                array('in' => explode(',', $this->getRequest()->getParam('listing_product_id_filter')))
            );
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
        $parentType = $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel();

        $channelAttributesSets = $parentType->getChannelAttributesSets();
        $productAttributes = $parentType->getProductAttributes();

        if ($parentType->hasMatchedAttributes()) {
            $productAttributes = array_keys($parentType->getMatchedAttributes());
            $channelAttributes = array_values($parentType->getMatchedAttributes());
        } else if (!empty($channelAttributesSets)) {
            $channelAttributes = array_keys($channelAttributesSets);
        } else {
            $channelAttributes = array();
        }

        $this->addColumn(
            'product_options', array(
                'header'                    => Mage::helper('M2ePro')->__('Magento Variation'),
                'align'                     => 'left',
                'width'                     => '210px',
                'sortable'                  => false,
                'index'                     => 'additional_data',
                'filter_index'              => 'additional_data',
                'frame_callback'            => array($this, 'callbackColumnProductOptions'),
                'filter'                    => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
                'options'                   => $productAttributes,
                'filter_condition_callback' => array($this, 'callbackProductOptions')
            )
        );

        $this->addColumn(
            'channel_options', array(
                'header'                    => Mage::helper('M2ePro')->__('Amazon Variation'),
                'align'                     => 'left',
                'width'                     => '210px',
                'sortable'                  => false,
                'index'                     => 'additional_data',
                'filter_index'              => 'additional_data',
                'frame_callback'            => array($this, 'callbackColumnChannelOptions'),
                'filter'                    => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
                'options'                   => $channelAttributes,
                'filter_condition_callback' => array($this, 'callbackChannelOptions')
            )
        );

        $this->addColumn(
            'amazon_sku', array(
                'header'       => Mage::helper('M2ePro')->__('SKU'),
                'align'        => 'left',
                'type'         => 'text',
                'index'        => 'amazon_sku',
                'filter_index' => 'second_table.sku',
                'renderer'     => 'M2ePro/adminhtml_amazon_grid_column_renderer_sku',
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '100px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'frame_callback' => array($this, 'callbackColumnGeneralId')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '70px',
                'type'                      => 'number',
                'index'                     => 'online_qty',
                'filter_index'              => 'online_qty',
                'show_receiving'            => false,
                'renderer'                  => 'M2ePro/adminhtml_amazon_grid_column_renderer_qty',
                'filter'                    => 'M2ePro/adminhtml_amazon_grid_column_filter_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_current_price',
            'filter_index' => 'online_current_price',
            'marketplace_id' => $this->getListingProduct()->getListing()->getMarketplaceId(),
            'account_id' => $this->getListingProduct()->getListing()->getAccountId(),
            'renderer' => 'M2ePro/adminhtml_amazon_grid_column_renderer_price',
            'filter_condition_callback' => array($this, 'callbackFilterPrice'),
            'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_price',
        );

        $this->addColumn('online_regular_price', $priceColumn);

        $this->addColumn(
            'status', array(
                'header'        => Mage::helper('M2ePro')->__('Status'),
                'width'         => '100px',
                'index'         => 'status',
                'filter_index'  => 'status',
                'type'          => 'options',
                'sortable'      => false,
                'options'       => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Incomplete')
                ),
                'renderer'     => 'M2ePro/adminhtml_amazon_grid_column_renderer_status'
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->addItem(
            'list', array(
                'label'   => Mage::helper('M2ePro')->__('List Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'revise', array(
                'label'   => Mage::helper('M2ePro')->__('Revise Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'relist', array(
                'label'   => Mage::helper('M2ePro')->__('Relist Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'stop', array(
                'label'   => Mage::helper('M2ePro')->__('Stop Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'stopAndRemove', array(
                'label'   => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'deleteAndRemove', array(
                'label'   => Mage::helper('M2ePro')->__('Remove from Channel & Listing'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductOptions($additionalData, $row, $column, $isExport)
    {
        $html = '';

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $row->getChildObject()->getVariationManager()->getTypeModel();

        $html .= '<div class="product-options-main" style="font-size: 11px; color: grey; margin-left: 7px">';
        $productOptions = $typeModel->getProductOptions();
        if (!empty($productOptions)) {
            $productsIds = $this->parseGroupedData($row->getData('products_ids'));
            $uniqueProductsIds = count(array_unique($productsIds)) > 1;

            $matchedAttributes = $typeModel->getParentTypeModel()->getMatchedAttributes();
            if (!empty($matchedAttributes)) {
                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    if (isset($productOptions[$magentoAttr])) {
                        $sortedOptions[$magentoAttr] = $productOptions[$magentoAttr];
                    }
                }

                $productOptions = $sortedOptions;
            }

            $virtualProductAttributes = array_keys($typeModel->getParentTypeModel()->getVirtualProductAttributes());

            $html .= '<div class="m2ePro-variation-attributes product-options-list">';
            if (!$uniqueProductsIds) {
                $data['id'] = reset($productsIds);
                if ($this->getListingProduct()->getListing()->getStoreId() !== null) {
                    $data['store'] = $this->getListingProduct()->getListing()->getStoreId();
                }
                $url = $this->getUrl('adminhtml/catalog_product/edit', $data);
                $html .= '<a href="' . $url . '" target="_blank">';
            }

            foreach ($productOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualProductAttributes, true)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                if ($option === '' || $option === null) {
                    $option = '--';
                }

                $optionHtml = '<span class="attribute-row" style="' . $style . '"><span class="attribute"><strong>' .
                    Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong></span>:&nbsp;<span class="value">' . Mage::helper('M2ePro')->escapeHtml($option) .
                    '</span></span>';

                if ($uniqueProductsIds && $option !== '--' && !in_array($attribute, $virtualProductAttributes, true)) {
                    $data['id'] = $productsIds[$attribute];
                    if ($this->getListingProduct()->getListing()->getStoreId() !== null) {
                        $data['store'] = $this->getListingProduct()->getListing()->getStoreId();
                    }
                    $url = $this->getUrl('adminhtml/catalog_product/edit', $data);
                    $html .= '<a href="' . $url . '" target="_blank">' . $optionHtml . '</a><br/>';
                } else {
                    $html .= $optionHtml . '<br/>';
                }
            }

            if (!$uniqueProductsIds) {
                $html .= '</a>';
            }

            $html .= '</div>';
        }

        if ($this->canChangeProductVariation($row)) {
            $listingProductId = $row->getId();
            $attributes = array_keys($typeModel->getParentTypeModel()->getMatchedAttributes());
            $variationsTree = $this->getProductVariationsTree($row, $attributes);

            $linkTitle = Mage::helper('M2ePro')->__('Change Variation');
            $linkContent = Mage::helper('M2ePro')->__('Change Variation');

            $attributes = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->jsonEncode($attributes));
            $variationsTree = Mage::helper('M2ePro')->escapeHtml(json_encode($variationsTree, JSON_FORCE_OBJECT));

            $html .= <<<HTML
<form action="javascript:void(0);" class="product-options-edit"></form>
<a href="javascript:"
    onclick="ListingGridObj.editProductOptions(this, {$attributes}, {$variationsTree}, {$listingProductId})"
    title="{$linkTitle}">{$linkContent}</a>
HTML;
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnChannelOptions($additionalData, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $row->getChildObject();

        $typeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

        $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getMatchedAttributes();

        if (!$typeModel->isVariationChannelMatched()) {
            if (!$typeModel->isVariationProductMatched() || !$amazonListingProduct->isGeneralIdOwner()) {
                return '';
            }

            if (empty($matchedAttributes)) {
                return '';
            }

            $options = array();

            foreach ($typeModel->getProductOptions() as $attribute => $value) {
                $options[$matchedAttributes[$attribute]] = $value;
            }
        } else {
            $options = $typeModel->getChannelOptions();

            if (!empty($matchedAttributes)) {
                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    if (isset($options[$amazonAttr])) {
                        $sortedOptions[$amazonAttr] = $options[$amazonAttr];
                    }
                }

                $options = $sortedOptions;
            }
        }

        if (empty($options)) {
            return '';
        }

        $generalId = $amazonListingProduct->getGeneralId();

        $virtualChannelAttributes = array_keys($typeModel->getParentTypeModel()->getVirtualChannelAttributes());

        $html = '<div class="m2ePro-variation-attributes" style="color: grey; margin-left: 7px">';

        if (!empty($generalId)) {
            $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
                $generalId,
                $this->getListingProduct()->getListing()->getMarketplaceId()
            );

            $html .= '<a href="' . $url . '" target="_blank" title="' . $generalId . '" >';
        }

        foreach ($options as $attribute => $option) {
            $style = '';
            if (in_array($attribute, $virtualChannelAttributes, true)) {
                $style = 'border-bottom: 2px dotted grey';
            }

            if ($option === '' || $option === null) {
                $option = '--';
            }

            $attrName = Mage::helper('M2ePro')->escapeHtml($attribute);
            $optionName = Mage::helper('M2ePro')->escapeHtml($option);

            if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                $html .= <<<HTML
<span style="{$style}">{$attrName}:&nbsp;{$optionName}</span><br/>
HTML;
            } else {
                $html .= <<<HTML
<span style="{$style}"><b>{$attrName}</b>:&nbsp;{$optionName}</span><br/>
HTML;
            }
        }

        if (!empty($generalId)) {
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if ($generalId === null || $generalId === '') {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $this->getListingProduct()->getChildObject();
            if ($amazonListingProduct->isGeneralIdOwner()) {
                return Mage::helper('M2ePro')->__('New ASIN/ISBN');
            }

            return Mage::helper('M2ePro')->__('N/A');
        }

        if (
            Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) ||
            Mage::helper('M2ePro')->isISBN($generalId)
        ) {
            return $this->getGeneralIdLink($generalId);
        }

        $tooltip = $this->getTooltipHtml($this->__('Amazon returned UPC/EAN as the product ID'));
        $result = <<<HTML
<div style="display: flex; align-items: center;">
    <span>{$generalId}</span>
    {$tooltip}
</div>
HTML;

        return $result;
    }

    public function callbackProductOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_product_options":[^}]*' .
                        $value['attr'] . '[[:space:]]*":"[[:space:]]*' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value']).'[[:space:]]*'))
                );
            }
        }
    }

    public function callbackChannelOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_channel_options":[^}]*' .
                        $value['attr'] . '[[:space:]]*":"[[:space:]]*' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value']).'[[:space:]]*'))
                );
            }
        }
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_qty >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'online_qty <= ' . (int)$value['to'];
        }

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            $where .= 'is_afn_channel = ' . (int)$value['afn'];
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) || isset($value['to'])) {
            if (isset($value['from']) && $value['from'] != '') {
                $condition = 'second_table.online_regular_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'second_table.online_regular_price <= \''.(float)$value['to'].'\'';
            }

            $condition = '(' . $condition . ' AND
            (
                second_table.online_regular_price IS NOT NULL AND
                ((second_table.online_regular_sale_price_start_date IS NULL AND
                second_table.online_regular_sale_price_end_date IS NULL) OR
                second_table.online_regular_sale_price IS NULL OR
                second_table.online_regular_sale_price_start_date > CURRENT_DATE() OR
                second_table.online_regular_sale_price_end_date < CURRENT_DATE())
            )) OR (';

            if (isset($value['from']) && $value['from'] != '') {
                $condition .= 'second_table.online_regular_sale_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'second_table.online_regular_sale_price <= \''.(float)$value['to'].'\'';
            }

            $condition .= ' AND
            (
                second_table.online_regular_price IS NOT NULL AND
                (second_table.online_regular_sale_price_start_date IS NOT NULL AND
                second_table.online_regular_sale_price_end_date IS NOT NULL AND
                second_table.online_regular_sale_price IS NOT NULL AND
                second_table.online_regular_sale_price_start_date < CURRENT_DATE() AND
                second_table.online_regular_sale_price_end_date > CURRENT_DATE())
            )) OR (';

            if (isset($value['from']) && $value['from'] != '') {
                $condition .= 'online_business_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'second_table.online_business_price <= \''.(float)$value['to'].'\'';
            }

            $condition .= ' AND (second_table.online_regular_price IS NULL))';
        }

        if (isset($value['is_repricing']) && $value['is_repricing'] !== '') {
            if (!empty($condition)) {
                $condition = '(' . $condition . ') AND ';
            }

            $condition .= 'is_repricing = ' . (int)$value['is_repricing'];
        }

        $collection->getSelect()->where($condition);
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html.= $this->getAddNewChildButtonsHtml();
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }

        return $html;
    }

    public function getMassactionBlockHtml()
    {
        if ($this->isNewChildAllowed()) {
            $blockName = 'adminhtml_amazon_listing_variation_product_manage_tabs_variations_child_form';
            $form = $this->getLayout()->createBlock('M2ePro/'.$blockName);
            $form->setListingProductId($this->getListingProductId());

            return $form->toHtml() . parent::getMassactionBlockHtml();
        }

        return parent::getMassactionBlockHtml();
    }

    protected function getAddNewChildButtonsHtml()
    {
        if ($this->isNewChildAllowed()) {
            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('adminhtml')->__('Add New Child Product'),
                'onclick' => 'ListingGridObj.showNewChildForm('. !$this->hasUnusedChannelVariations() .')',
                'class'   => 'task',
                'id'      => 'add_new_child_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('add_new_child_button', $buttonBlock);
            // ---------------------------------------
        }

        return $this->getChildHtml('add_new_child_button');
    }

    protected function isNewChildAllowed()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();

        if (!$amazonListingProduct->getGeneralId()) {
            return false;
        }

        if (!$amazonListingProduct->getVariationManager()->getTypeModel()->hasMatchedAttributes()) {
            return false;
        }

        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        if ($this->hasChildWithEmptyProductOptions()) {
            return false;
        }

        if (!$this->isGeneralIdOwner() && !$this->hasUnusedChannelVariations()) {
            return false;
        }

        if (!$this->isGeneralIdOwner() && $this->hasChildWithEmptyChannelOptions()) {
            return false;
        }

        return true;
    }

    public function isGeneralIdOwner()
    {
        return $this->getListingProduct()->getChildObject()->isGeneralIdOwner();
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelVariations();
    }

    public function hasUnusedProductVariation()
    {
        return (bool)$this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedProductOptions();
    }

    public function hasUnusedChannelVariations()
    {
        return (bool)$this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedChannelOptions();
    }

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    public function hasChildWithEmptyChannelOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                return true;
            }
        }

        return false;
    }

    public function getUsedChannelVariations()
    {
        return (bool)$this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUsedChannelOptions();
    }

    // ---------------------------------------

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/viewVariationsGridAjax', array(
                '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $listingId = $this->getListingProduct()->getListingId();

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        // ---------------------------------------
        $urls = array();

        $path = 'adminhtml_amazon_log/listingProduct';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
                'channel' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'back'    => $helper->makeBackUrlParam('*/adminhtml_amazon_listing/view', array('id' => $listingId))
            )
        );

        $urls['adminhtml_amazon_listing_variation_product_manage/createNewChild'] = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/createNewChild'
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_amazon_log/listing', array(
                'listing_id' => $listingId,
                'back' => $helper->makeBackUrlParam('*/adminhtml_amazon_listing/view', array('id' => $listingId))
            )
        );

        $checkLockListing = $this->getUrl('*/adminhtml_listing/checkLockListing', array('component' => $component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing/lockListingNow', array('component' => $component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing/unlockListingNow', array('component' => $component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_amazon_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_amazon_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_amazon_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_amazon_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_amazon_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_amazon_listing/runDeleteAndRemoveProducts');

        $setChildListingProductOptions = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/setChildListingProductOptions'
        );

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Amazon request(s) are being processed now.'));
        $sendingDataToAmazonMessage = $helper->escapeJs(
            $helper->__(
                'Sending %product_title% Product(s) data on Amazon.'
            )
        );
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing All Items On Amazon')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing Selected Items On Amazon')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Revising Selected Items On Amazon')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Relisting Selected Items On Amazon')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping Selected Items On Amazon')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(
                Mage::helper('M2ePro')->__('Stopping On Amazon And Removing From Listing Selected Items')
            );
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(
                Mage::helper('M2ePro')->__('Removing From Amazon And Listing Selected Items')
            );

        $errorChangingProductOptions = $helper->escapeJs($helper->__('Please Select Product Options.'));

        $mapToTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_amazon_listing/validateProductsForTemplateDescriptionAssign'
        );
        $viewTemplateDescriptionsGrid = $this->getUrl('*/adminhtml_amazon_listing/viewTemplateDescriptionsGrid');
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy for Products'));

        $getUpdatedRepricingPriceBySkus = $this->getUrl(
            '*/adminhtml_amazon_listing_repricing/getUpdatedPriceBySkus'
        );

        $generalId = $this->getListingProduct()->getGeneralId();
        $hideMassAction = empty($generalId) ?
            '$(\'amazonVariationProductManageGrid_massaction-form\').hide();' : '';

        $javascriptMain = <<<HTML
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.url.add({$urls});

    M2ePro.url.logViewUrl = '{$logViewUrl}';

    M2ePro.url.checkLockListing = '{$checkLockListing}';
    M2ePro.url.lockListingNow = '{$lockListingNow}';
    M2ePro.url.unlockListingNow = '{$unlockListingNow}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.runDeleteAndRemoveProducts = '{$runDeleteAndRemoveProducts}';
    M2ePro.url.setChildListingProductOptions = '{$setChildListingProductOptions}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.url.getUpdatedRepricingPriceBySkus = '{$getUpdatedRepricingPriceBySkus}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.deleting_and_removing_selected_items_message = '{$deletingAndRemovingSelectedItemsMessage}';

    M2ePro.text.error_changing_product_options = '{$errorChangingProductOptions}';

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = 'amazonVariationProductManageGrid';

    // fix for tool tip position in iframe
    MagentoFieldTip.prototype.changeToolTipPosition = function(element)
    {
        var toolTip = element.up().select('.tool-tip-message')[0];

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 25,
            offsetLeft: -20
        };

        if (element.up().getStyle('float') == 'right') {
            settings.offsetLeft += 18;
        }
        if (element.up().match('span')) {
            settings.offsetLeft += 15;
        }

        toolTip.clonePosition(element, settings);

        if (toolTip.hasClassName('tip-left')) {
            toolTip.style.left = (parseInt(toolTip.style.left) - toolTip.getWidth() - 10) + 'px';
        }
    };

    Event.observe(window, 'load', function() {

        Common.prototype.scroll_page_to_top = function() { return; }

        ListingGridObj = new AmazonListingVariationProductManageVariationsGrid(
            'amazonVariationProductManageGrid',
            {$listingId}
        );

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        AmazonListingRepricingPriceObj = new AmazonListingRepricingPrice();

        setTimeout(function() {
            ListingGridObj.afterInitPage();
        }, 350);
    });

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    {$hideMassAction}

</script>
HTML;

        $additionalCss = <<<HTML
<style>
    body {
        background: none;
    }

    .wrapper {
        min-width: inherit;
    }

    .footer {
        display: none;
    }

    .middle {
        padding: 0px;
        background: none;
    }

    td.help_line .hl_close {
        margin-top: -6px;
    }

    td.help_line .hl_header {
        padding: 0 0 10px !important;
    }

</style>
HTML;

        return  '<div id="messages"></div>' .
                '<div id="listing_view_progress_bar"></div>' .
                '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
                '<div id="listing_view_content_container">'.
                    parent::_toHtml() .
                '</div>' . $javascriptMain . $additionalCss;
    }

    //########################################

    protected function canChangeProductVariation(Ess_M2ePro_Model_Listing_Product $childListingProduct)
    {
        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        $lockData = $this->getLockedData($childListingProduct);
        if ($lockData['in_action']) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonChildListingProduct */
        $amazonChildListingProduct = $childListingProduct->getChildObject();

        if (!$amazonChildListingProduct->getGeneralId()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $amazonChildListingProduct->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched() && $this->hasChildWithEmptyProductOptions()) {
            return false;
        }

        if (!$childTypeModel->getParentTypeModel()->hasMatchedAttributes()) {
            return false;
        }

        return true;
    }

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->_lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load(
                $row->getData('id')
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

    protected function getTemplateDescriptionLinkHtml($listingProduct)
    {
        $templateDescriptionEditUrl = $this->getUrl(
            '*/adminhtml_amazon_template_description/edit', array(
                'id' => $listingProduct->getChildObject()->getTemplateDescriptionId()
            )
        );

        $helper = Mage::helper('M2ePro');
        $templateTitle = $listingProduct->getChildObject()->getDescriptionTemplate()->getTitle();

        return <<<HTML
<span style="font-size: 9px;">{$helper->__('Description Title')}:&nbsp;
    <a target="_blank" href="{$templateDescriptionEditUrl}">
        {$helper->escapeHtml($templateTitle)}</a>
</span>
<br/>
HTML;
    }

    //########################################

    public function getProductVariationsTree($childProduct, $attributes)
    {
        $unusedVariations = $this->getUnusedProductVariations();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $childProduct->getChildObject()->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched()) {
            $unusedVariations[] = $childTypeModel->getProductOptions();
        }

        $variationsSets = $this->getAttributesVariationsSets($unusedVariations);
        $variationsSetsSorted =  array();

        foreach ($attributes as $attribute) {
            $variationsSetsSorted[$attribute] = $variationsSets[$attribute];
        }

        $firstAttribute = key($variationsSetsSorted);

        return $this->prepareVariations($firstAttribute, $unusedVariations, $variationsSetsSorted);
    }

    protected function prepareVariations($currentAttribute, $unusedVariations, $variationsSets, $filters = array())
    {
        $return = false;

        $temp = array_flip(array_keys($variationsSets));

        $lastAttributePosition = count($variationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {
            $temp = array_keys($variationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($variationsSets[$currentAttribute] as $option) {
                $filters[$currentAttribute] = $option;

                $result = $this->prepareVariations(
                    $nextAttribute, $unusedVariations, $variationsSets, $filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$option] = $result;
            }

            if ($return !== false) {
                ksort($return[$currentAttribute]);
            }

            return $return;
        }

        $return = false;
        foreach ($unusedVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $attribute => $option) {
                if ($attribute == $currentAttribute) {
                    if (count($variationsSets) != 1) {
                        continue;
                    }

                    $values = array_flip($variationsSets[$currentAttribute]);
                    $return = array($currentAttribute => $values);

                    foreach ($return[$currentAttribute] as &$option) {
                        $option = true;
                    }

                    return $return;
                }

                if ($option != $filters[$attribute]) {
                    unset($unusedVariations[$key]);
                    continue;
                }

                foreach ($magentoVariation as $tempAttribute => $tempOption) {
                    if ($tempAttribute == $currentAttribute) {
                        $option = $tempOption;
                        $return[$currentAttribute][$option] = true;
                    }
                }
            }
        }

        if (count($unusedVariations) < 1) {
            return false;
        }

        if ($return !== false) {
            ksort($return[$currentAttribute]);
        }

        return $return;
    }

    //########################################

    public function getCurrentProductVariations()
    {

        if ($this->_currentProductVariations !== null) {
            return $this->_currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->_currentProductVariations = $productVariations;
    }

    public function getUsedProductVariations()
    {
        if ($this->_usedProductVariations === null) {
            $this->_usedProductVariations = $this->getListingProduct()
                                                 ->getChildObject()
                                                 ->getVariationManager()
                                                 ->getTypeModel()
                                                 ->getUsedProductOptions();
        }

        return $this->_usedProductVariations;
    }

    //########################################

    public function getUnusedProductVariations()
    {
        return $this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedProductOptions();
    }

    //########################################

    public function getChildListingProducts()
    {
        if ($this->_childListingProducts !== null) {
            return $this->_childListingProducts;
        }

        return $this->_childListingProducts = $this->getListingProduct()->getChildObject()
                                                   ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getAttributesVariationsSets($variations)
    {
        $attributesOptions = array();

        foreach ($variations as $variation) {
            foreach ($variation as $attr => $option) {
                if (!isset($attributesOptions[$attr])) {
                    $attributesOptions[$attr] = array();
                }

                if (!in_array($option, $attributesOptions[$attr], true)) {
                    $attributesOptions[$attr][] = $option;
                }
            }
        }

        return $attributesOptions;
    }

    //########################################

    protected function getGeneralIdLink($generalId)
    {
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $this->getListingProduct()->getListing()->getMarketplaceId()
        );

        return <<<HTML
<a href="{$url}" target="_blank" title="{$generalId}" >{$generalId}</a>
HTML;
    }

    //########################################

    protected function parseGroupedData($data)
    {
        $result = array();

        if (empty($data)) {
            return $result;
        }

        $variationData = explode('||', $data);
        foreach ($variationData as $variationAttribute) {
            $value = explode('==', $variationAttribute);
            $result[$value[0]] = $value[1];
        }

        return $result;
    }

    protected function getTooltipHtml($content)
    {
        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        return <<<HTML
<span>
    <img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconSrc}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$helpIconSrc}" />
        <span style="color:gray;">
           {$content}
        </span>
    </span>
</span>
HTML;
    }
}
