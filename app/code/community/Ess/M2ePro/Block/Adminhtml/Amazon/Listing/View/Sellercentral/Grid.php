<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Sellercentral_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    const ACTUAL_QTY_EXPRESSION = 'IF(alp.is_afn_channel = 1, alp.online_afn_qty, alp.online_qty)';

    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
    protected $_sellingFormatTemplate;

    protected $_lockedDataCache = array();

    protected $_parentAsins;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    protected $_parentAndChildReviseScheduledCache = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('amazonListingViewGrid' . $this->_listing->getId());

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Template_SellingFormat', $this->_listing->getData('template_selling_format_id'), null,
            array('template')
        );
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_view_modeSwitcher'
        );
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->setListingProductModeOn();
        $collection->setStoreId($this->_listing->getStoreId());
        $collection->setListing($this->_listing->getId());

        $collection->addAttributeToSelect('name')
                   ->addAttributeToSelect('sku')
                   ->joinStockItem(array('qty' => 'qty', 'is_in_stock' => 'is_in_stock'));

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'status'          => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$this->_listing->getId(),
                'status' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN,
                )
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'search_settings_status'         => 'search_settings_status',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_afn_qty'                 => 'online_afn_qty',
                'online_regular_price'           => 'online_regular_price',
                'online_regular_sale_price'      => 'IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  NULL
                )',
                'online_regular_sale_price_start_date'   => 'online_regular_sale_price_start_date',
                'online_regular_sale_price_end_date'     => 'online_regular_sale_price_end_date',
                'online_business_price'          => 'online_business_price',
                'online_business_discounts'      => 'online_business_discounts',
                'is_repricing'                   => 'is_repricing',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
                'variation_child_statuses'      => 'variation_child_statuses',
                'variation_parent_id'           => 'variation_parent_id',
                'defected_messages'              => 'defected_messages',
                'variation_parent_afn_state'       => 'variation_parent_afn_state',
                'variation_parent_repricing_state' => 'variation_parent_repricing_state',
            ),
            '{{table}}.is_variation_parent = 0'
        );

        $collection->getSelect()->joinLeft(
            array('malpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            '(`alp`.`listing_product_id` = `malpr`.`listing_product_id`)',
            array(
                'is_repricing_disabled' => 'is_online_disabled',
                'is_repricing_inactive' => 'is_online_inactive',
            )
        );

        $collection->addExpressionAttributeToSelect(
            'online_actual_qty',
            self::ACTUAL_QTY_EXPRESSION,
            array()
        );

        if ($this->isFilterOrSortByPriceIsUsed('online_price', 'amazon_online_price')) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('lps' => Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction')->getMainTable()),
            'lps.listing_product_id=main_table.id',
            array()
        );

        $collection->addFieldToFilter('is_variation_parent', 0);
        $collection->addFieldToFilter(
            'variation_parent_id', array('in' => $this->getCollection()->getColumnValues('id'))
        );
        $collection->addFieldToFilter('lps.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'variation_parent_id' => 'second_table.variation_parent_id',
                'count'               => new Zend_Db_Expr('COUNT(lps.id)')
            )
        );
        $collection->getSelect()->group('variation_parent_id');

        foreach ($collection->getItems() as $item) {
            $this->_parentAndChildReviseScheduledCache[$item->getData('variation_parent_id')] = true;
        }

        return parent::_afterLoadCollection();
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
                'store_id'     => $this->_listing->getStoreId(),
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
            'amazon_sku', array(
                'header'       => Mage::helper('M2ePro')->__('SKU'),
                'align'        => 'left',
                'width'        => '150px',
                'type'         => 'text',
                'index'        => 'amazon_sku',
                'filter_index' => 'amazon_sku',
                'renderer'     => 'M2ePro/adminhtml_amazon_grid_column_renderer_sku'
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '150px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'filter'         => 'M2ePro/adminhtml_amazon_grid_column_filter_generalId',
                'frame_callback' => array($this, 'callbackColumnGeneralId'),
                'filter_condition_callback' => array($this, 'callbackFilterGeneralId')
            )
        );

        $this->addColumn(
            'qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '70px',
                'type'                      => 'number',
                'index'                     => 'online_actual_qty',
                'filter_index'              => 'online_actual_qty',
                'renderer'                  => 'M2ePro/adminhtml_amazon_grid_column_renderer_qty',
                'filter'                    => 'M2ePro/adminhtml_amazon_grid_column_filter_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '110px',
            'type' => 'number',
            'index' => 'min_online_price',
            'filter_index' => 'min_online_price',
            'marketplace_id' => $this->_listing->getMarketplaceId(),
            'account_id' => $this->_listing->getAccountId(),
            'renderer' => 'M2ePro/adminhtml_amazon_grid_column_renderer_price',
            'filter_condition_callback' => array($this, 'callbackFilterPrice'),
            'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_price',
        );

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn(
            'status', array(
                'header'       => Mage::helper('M2ePro')->__('Status'),
                'width'        => '140px',
                'index'        => 'status',
                'filter_index' => 'status',
                'type'         => 'options',
                'sortable'     => false,
                'options'      => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Incomplete')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $groups = array(
            'actions' => Mage::helper('M2ePro')->__('Actions'),
            'edit_fulfillment' => Mage::helper('M2ePro')->__('Fulfillment'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem(
            'revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'deleteAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Remove from Channel & Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'switchToAfn', array(
            'label'    => Mage::helper('M2ePro')->__('Switch to AFN'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'edit_fulfillment'
        );

        $this->getMassactionBlock()->addItem(
            'switchToMfn', array(
            'label'    => Mage::helper('M2ePro')->__('Switch to MFN'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'edit_fulfillment'
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load($row->getData('id'));
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return $value;
        }

        if ($variationManager->isRelationChildType()) {
            $typeModel = $variationManager->getTypeModel();

            $productOptions = $typeModel->getProductOptions();
            $channelOptions = $typeModel->getChannelOptions();

            $parentTypeModel = $variationManager->getTypeModel()->getParentTypeModel();

            $virtualProductAttributes = array_keys($parentTypeModel->getVirtualProductAttributes());
            $virtualChannelAttributes = array_keys($parentTypeModel->getVirtualChannelAttributes());

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

            $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getMatchedAttributes();

            if (!empty($matchedAttributes)) {
                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    if (isset($channelOptions[$amazonAttr])) {
                        $sortedOptions[$amazonAttr] = $channelOptions[$amazonAttr];
                    }
                }

                $channelOptions = $sortedOptions;
            }

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Magento Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($productOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualProductAttributes)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $value .= '<span style="' . $style . '"><b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</span><br/>';
            }

            $value .= '</div>';

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Amazon Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($channelOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualChannelAttributes)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $value .= '<span style="' . $style . '"><b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</span><br/>';
            }

            $value .= '</div>';

            return $value;
        }

        $productOptions = array();
        if ($listingProduct->getChildObject()->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $productOptions = $listingProduct->getChildObject()->getVariationManager()
                ->getTypeModel()->getProductOptions();
        }

        $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
        foreach ($productOptions as $attribute => $option) {
            if ($option === '' || $option === null) {
                $option = '--';
            }
            $value .= '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }

        $value .= '</div>';

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $value,
            $this->_listing->getMarketplaceId()
        );

        $parentAsinHtml = '';
        $variationParentId = $row->getData('variation_parent_id');
        if (!empty($variationParentId)) {
            $parentAsinHtml = '<br/><span style="display: block;
                                                margin-bottom: 5px;
                                                font-size: 10px;
                                                color: grey;">'.
                Mage::helper('M2ePro')->__(
                    'child ASIN/ISBN<br/>of parent %parent_asin%',
                    $this->getParentAsin($row->getData('id'))
                ) . '</span>';
        }

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == 1) {
            $generalIdOwnerHtml = '<span style="font-size: 10px; color: grey; display: block;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        return <<<HTML
<a href="{$url}" target="_blank">{$value}</a>{$parentAsinHtml}{$generalIdOwnerHtml}
HTML;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $viewLogIcon = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_grid_column_renderer_viewLogIcon_listing'
        );

        $value .= $viewLogIcon->render($row);

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row->getData('id'));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $value .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $value .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator']) &&
                    !isset($this->_parentAndChildReviseScheduledCache[$row->getData('id')])) {
                    $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isRegularPriceAllowed() || $configurator->isBusinessPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isDetailsAllowed()) {
                            $reviseParts[] = 'Details';
                        }

                        if ($configurator->isImagesAllowed()) {
                            $reviseParts[] = 'Images';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $value .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                              .' is Scheduled...]</span>';
                } else {
                    $value .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $value .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $value .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        foreach ($tempLocks as $lock) {
            switch ($lock->getTag()) {
                case 'list_action':
                    $value .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $value .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $value .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $value .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'switch_to_afn_action':
                    $value .= '<br/><span style="color: #605fff">[Switch to AFN in Progress...]</span>';
                    break;

                case 'switch_to_mfn_action':
                    $value .= '<br/><span style="color: #605fff">[Switch to MFN in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $value;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku','like' => '%'.$value.'%'),
                array('attribute' => 'amazon_sku', 'like' => '%'.$value.'%'),
                array('attribute' => 'name', 'like' => '%'.$value.'%')
            )
        );
    }

    protected function callbackFilterGeneralId($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue('input');
        if ($inputValue !== null) {
            $collection->addFieldToFilter('general_id', array('like' => '%' . $inputValue . '%'));
        }

        $selectValue = $column->getFilter()->getValue('select');
        if ($selectValue !== null) {
            $collection->addFieldToFilter('is_general_id_owner', $selectValue);
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
            $where .= self::ACTUAL_QTY_EXPRESSION . ' >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= self::ACTUAL_QTY_EXPRESSION . ' <= ' . (int)$value['to'];
        }

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where .= ' AND ';
            }

            if ((int)$value['afn'] == 1) {
                $where .= 'is_afn_channel = 1';
            } else {
                $partialFilter = Ess_M2ePro_Model_Amazon_Listing_Product::VARIATION_PARENT_IS_AFN_STATE_PARTIAL;
                $where .= "(is_afn_channel = 0 OR variation_parent_afn_state = {$partialFilter})";
            }
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
                $condition = 'min_online_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'min_online_price <= \''.(float)$value['to'].'\'';
            }

            $condition = '(' . $condition . ') OR (';

            if (isset($value['from']) && $value['from'] != '') {
                $condition .= 'max_online_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'max_online_price <= \''.(float)$value['to'].'\'';
            }

            $condition .= ')';
        }

        if (isset($value['is_repricing']) && $value['is_repricing'] !== '') {
            if (!empty($condition)) {
                $condition = '(' . $condition . ') AND ';
            }

            if ((int)$value['is_repricing'] == 1) {
                $condition .= 'is_repricing = 1';
            } else {
                $partialFilter = Ess_M2ePro_Model_Amazon_Listing_Product::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL;
                $condition .= "(is_repricing = 0 OR variation_parent_repricing_state = {$partialFilter})";
            }
        }

        $collection->getSelect()->having($condition);
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_listing/viewGrid', array('_current'=>true));
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

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    public function getEmptyText()
    {
        return Mage::helper('M2ePro')->__(
            'Only Simple and Child Products listed on Amazon will be shown in Seller Сentral View Mode.'
        );
    }

    //########################################

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

    protected function getParentAsin($childId)
    {
        if ($this->_parentAsins === null) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_amazon_listing_product');

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id','variation_parent_id'))
                ->where('listing_product_id IN (?)', $this->getCollection()->getAllIds())
                ->where('variation_parent_id IS NOT NULL');

            $parentIds = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id', 'general_id'))
                ->where('listing_product_id IN (?)', $parentIds);

            $parentAsins = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $this->_parentAsins = array();
            foreach ($parentIds as $childId => $parentId) {
                $this->_parentAsins[$childId] = $parentAsins[$parentId];
            }
        }

        return $this->_parentAsins[$childId];
    }

    //########################################
}
