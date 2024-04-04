<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search_M2ePro_Grid
    extends Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search_Grid
{
    protected $_lockedDataCache = array();

    protected $_parentAndChildReviseScheduledCache = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingSearchM2eProGrid');

        $this->setDefaultSort(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->getSelect()->distinct();
        $collection->setListingProductModeOn();

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection->joinStockItem(
            array(
            'is_in_stock' => 'is_in_stock'
            )
        );

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'status'          => 'status',
                'component_mode'  => 'component_mode',
                'listing_id'      => 'listing_id',
                'additional_data' => 'additional_data',
            )
        );
        $collection->joinTable(
            array('wlp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'           => 'listing_product_id',
                'listing_other_id'             => new Zend_Db_Expr('NULL'),
                'variation_parent_id'          => 'variation_parent_id',
                'is_variation_parent'          => 'is_variation_parent',
                'variation_child_statuses'     => 'variation_child_statuses',
                'online_sku'                   => 'sku',
                'gtin'                         => 'gtin',
                'upc'                          => 'upc',
                'ean'                          => 'ean',
                'isbn'                         => 'isbn',
                'wpid'                         => 'wpid',
                'item_id'                      => 'item_id',
                'online_qty'                   => 'online_qty',
                'online_price'                 => 'online_price',
                'is_online_price_invalid'      => 'is_online_price_invalid',
            ),
            'variation_parent_id IS NULL'
        );
        $collection->joinTable(
            array('l' => 'M2ePro/Listing'),
            'id=listing_id',
            array(
                'store_id'       => 'store_id',
                'account_id'     => 'account_id',
                'marketplace_id' => 'marketplace_id',
                'listing_title'  => 'title',
            )
        );

        $accountId = (int)$this->getRequest()->getParam('walmartAccount', false);
        $marketplaceId = (int)$this->getRequest()->getParam('walmartMarketplace', false);

        if ($accountId) {
            $collection->getSelect()->where('l.account_id = ?', $accountId);
        }

        if ($marketplaceId) {
            $collection->getSelect()->where('l.marketplace_id = ?', $marketplaceId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
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

    //########################################

    /**
     * @param string $value
     * @param Mage_Catalog_Model_Product $row
     * @param string $column
     * @param bool $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('M2ePro');

        /** @var Ess_M2ePro_Helper_Component_Walmart $walmartHelper */
        $walmartHelper = Mage::helper('M2ePro/Component_Walmart');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = $walmartHelper->getObject('Account', $row->getData('account_id'));

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = $walmartHelper->getObject('Marketplace', $row->getData('marketplace_id'));

        $listingTitle = $dataHelper->escapeHtml($row->getData('listing_title'));
        strlen($listingTitle) > 50 && $listingTitle = substr($listingTitle, 0, 50) . '...';
        $listingUrl = $this->getUrl('*/adminhtml_walmart_listing/view', array('id' => $row->getData('listing_id')));

        $value = <<<HTML
<span>{$dataHelper->escapeHtml($row->getName())}</span>
<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>
<strong>{$dataHelper->__('Listing')}:</strong>&nbsp;<a href="{$listingUrl}" target="_blank">{$listingTitle}</a>
<br/><strong>{$dataHelper->__('Account')}:</strong>&nbsp;{$dataHelper->escapeHtml($account->getTitle())}
<br/><strong>{$dataHelper->__('Marketplace')}:</strong>&nbsp;{$dataHelper->escapeHtml($marketplace->getTitle())}
<br/><strong>{$dataHelper->__('SKU')}:</strong>&nbsp;{$dataHelper->escapeHtml($row->getSku())}
HTML;

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = $walmartHelper->getObject('Listing_Product', (int)$row->getData('listing_product_id'));

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        if ($variationManager->isVariationParent()) {
            $productAttributes = $variationManager->getTypeModel()->getProductAttributes();

            $virtualProductAttributes = $variationManager->getTypeModel()->getVirtualProductAttributes();
            $virtualChannelAttributes = $variationManager->getTypeModel()->getVirtualChannelAttributes();

            $attributesStr = '';

            if (empty($virtualProductAttributes) && empty($virtualChannelAttributes)) {
                $attributesStr = implode(', ', $productAttributes);
            } else {
                foreach ($productAttributes as $attribute) {
                    if (in_array($attribute, array_keys($virtualProductAttributes))) {
                        $attributesStr .= '<span style="border-bottom: 2px dotted grey">' . $attribute .
                            ' (' . $virtualProductAttributes[$attribute] . ')</span>, ';
                    } else if (in_array($attribute, array_keys($virtualChannelAttributes))) {
                        $attributesStr .= '<span>' . $attribute .
                            ' (' . $virtualChannelAttributes[$attribute] . ')</span>, ';
                    } else {
                        $attributesStr .= $attribute . ', ';
                    }
                }

                $attributesStr = rtrim($attributesStr, ', ');
            }

            $value .= <<<HTML
<div style="font-size: 11px; font-weight: bold; color: grey;">
    {$attributesStr}
</div>
HTML;
        }

        if ($variationManager->isIndividualType() &&
            $variationManager->getTypeModel()->isVariationProductMatched()
        ) {
            $optionsStr = '';
            $productOptions = $variationManager->getTypeModel()->getProductOptions();

            foreach ($productOptions as $attribute => $option) {
                $attribute = $dataHelper->escapeHtml($attribute);
                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $option = $dataHelper->escapeHtml($option);

                $optionsStr .= <<<HTML
<strong>{$attribute}</strong>:&nbsp;{$option}<br/>
HTML;
            }

            $value .= <<<HTML
<br/>
<div style="font-size: 11px; color: grey;">
    {$optionsStr}
</div>
<br/>
HTML;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $value = $this->getProductStatus($row, $row->getData('status'));

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProductId = (int)$row->getData('listing_product_id');
        $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $listingProductId);

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationParent()) {
            $statusChangeReasons = $listingProduct->getChildObject()->getStatusChangeReasons();
            return $value . $this->getStatusChangeReasons($statusChangeReasons) .
                            $this->getScheduledTag($row) .
                            $this->getLockedTag($row);
        }

        $html = '';

        $sNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
        $sListed    = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        $sInactive  = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
        $sBlocked   = Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;

        $variationsStatuses = $row->getData('variation_child_statuses');

        if (empty($variationsStatuses)) {
            return $this->getProductStatus($row, $sNotListed) .
                   $this->getScheduledTag($row) .
                   $this->getLockedTag($row);
        }

        $sortedStatuses     = array();
        $variationsStatuses = Mage::helper('M2ePro')->jsonDecode($variationsStatuses);

        isset($variationsStatuses[$sNotListed]) && $sortedStatuses[$sNotListed] = $variationsStatuses[$sNotListed];
        isset($variationsStatuses[$sListed])    && $sortedStatuses[$sListed]    = $variationsStatuses[$sListed];
        isset($variationsStatuses[$sInactive])  && $sortedStatuses[$sInactive]  = $variationsStatuses[$sInactive];
        isset($variationsStatuses[$sBlocked])   && $sortedStatuses[$sBlocked]   = $variationsStatuses[$sBlocked];

        foreach ($sortedStatuses as $status => $productsCount) {
            if (empty($productsCount)) {
                continue;
            }

            $productsCount = '['.$productsCount.']';
            $html .= $this->getProductStatus($row, $status) . '&nbsp;'. $productsCount . '<br/>';
        }

        return $html . $this->getScheduledTag($row) .
                       $this->getLockedTag($row);
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc  = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        $urlData = array(
            'id'     => $row->getData('listing_id'),
            'filter' => base64_encode(
                'product_id[from]=' . (int)$row->getData('entity_id')
                . '&product_id[to]=' . (int)$row->getData('entity_id')
            )
        );

        $searchedChildHtml = '';
        if ($this->wasFoundByChild($row)) {
            $urlData['child_listing_product_ids'] = $this->getChildListingProductIds($row);

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');
            $titleChildOfItem  = Mage::helper('M2ePro')->__(
                'A Product you are searching for is found as part of a Multi-Variational Product.' .
                ' Click on the arrow icon to manage it individually.'
            );

            $searchedChildHtml = <<<HTML
     <img class="tool-tip-image"
     style="vertical-align: middle; margin-top: 4px; margin-left: 10px;"
     src="{$toolTipIconPath}">
     <span class="tool-tip-message tip-left" style="display:none; text-align: left; min-width: 140px;">
        <img src="{$iconHelpPath}">
        <span style="color:gray;">{$titleChildOfItem}</span>
     </span>
HTML;
        }

        $manageUrl = $this->getUrl('*/adminhtml_walmart_listing/view/', $urlData);

        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$manageUrl}"><img src="{$iconSrc}" alt="{$altTitle}" /></a>
</div>
HTML;

        return $searchedChildHtml . $html;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ((!$row->getData('is_variation_parent') &&
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $currentOnlinePrice = (float)$row->getData('online_price');

        if (empty($currentOnlinePrice) ||
            ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
             !$row->getData('is_online_price_invalid')))
        {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = $row->getData('marketplace_id');
        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $marketplaceId)
            ->getChildObject()
            ->getDefaultCurrency();

        if ($row->getData('is_variation_parent')) {
            $iconHelpPath    = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

            $noticeText = Mage::helper('M2ePro')->__('The value is calculated as minimum price of all Child Products.');

            $priceHtml = <<<HTML
<img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconPath}"
    >&nbsp;<span class="tool-tip-message" style="display:none; text-align: left; width: 110px; background: #E3E3E3;">
    <img src="{$iconHelpPath}">
    <span style="color:gray;">
        {$noticeText}
    </span>
</span>
HTML;

            if (!empty($currentOnlinePrice)) {
                $currentOnlinePrice = Mage::app()->getLocale()->currency($currency)->toCurrency($currentOnlinePrice);
                $priceHtml .= "<span>{$currentOnlinePrice}</span><br />";
            }

            return $priceHtml;
        }

        $onlinePrice = $row->getData('online_price');
        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlinePrice);
        }

        return $priceValue;
    }

    // ----------------------------------------

    protected function getLockedTag($row)
    {
        $html = '';
        $childCount = 0;

        $tempLocks = $this->getLockedData($row);
        foreach ($tempLocks['object_locks'] as $lock) {
            switch ($lock->getTag()) {
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

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'child_products_in_action':
                    $childCount++;
                    break;

                default:
                    break;
            }
        }

        if ($childCount > 0) {
            $html .= '<br/><span style="color: #605fff">[Child(s) in Action...]</span>';
        }

        return $html;
    }

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

    // ---------------------------------------

    protected function getScheduledTag($row)
    {
        $html = '';

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row['id']);

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();
        if (!$scheduledAction->getId()) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator']) &&
                    !isset($this->_parentAndChildReviseScheduledCache[$row->getData('id')])) {
                    $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isPromotionsAllowed()) {
                            $reviseParts[] = 'Promotions';
                        }

                        if ($configurator->isDetailsAllowed()) {
                            $params = $additionalData['params'];

                            if (isset($params['changed_sku'])) {
                                $reviseParts[] = 'SKU';
                            }

                            if (isset($params['changed_identifier'])) {
                                $reviseParts[] = strtoupper($params['changed_identifier']['type']);
                            }

                            $reviseParts[] = 'Details';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $reviseParts = implode(', ', $reviseParts);
                    $html .= '<br/><span style="color: #605fff">[Revise of '.$reviseParts.' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Retire is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        /** @var Ess_M2ePro_Model_Resource_Magento_Product_Collection $collection */

        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $childCollection = $this->getMagentoChildProductsCollection();
        $childCollection->addFieldToFilter('product_id', $cond);

        $collection->joinTable(
            array('product_id_subQuery' => $childCollection->getSelect()),
            'variation_parent_id=id',
            array(
                'product_id_child_listing_product_ids' => 'child_listing_product_ids',
                'product_id_searched_by_child'         => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'entity_id', $cond),
                array('attribute' => 'product_id_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterTitle($collection, $column)
    {
        /** @var Ess_M2ePro_Model_Resource_Magento_Product_Collection $collection */

        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $childCollection = $this->getMagentoChildProductsCollection();
        $childCollection->getSelect()->joinLeft(
            array('cpe' => Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('catalog_product_entity')),
            'cpe.entity_id=main_table.product_id',
            array()
        );
        $childCollection->addFieldToFilter(
            'cpe.sku',
            array('like' => '%' . $this->getValueForSubQuery($value) . '%')
        );

        $collection->joinTable(
            array('product_sku_subQuery' => $childCollection->getSelect()),
            'variation_parent_id=id',
            array(
                'product_sku_child_listing_product_ids' => 'child_listing_product_ids',
                'product_sku_searched_by_child'         => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku', 'like' => '%'.$value.'%'),
                array('attribute' => 'name', 'like' => '%'.$value.'%'),
                array('attribute' => 'listing_title', 'like' => '%'.$value.'%'),
                array('attribute' => 'product_sku_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterOnlineSku($collection, $column)
    {
        /** @var Ess_M2ePro_Model_Resource_Magento_Product_Collection $collection */

        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $childCollection = $this->getChildProductsCollection();
        $childCollection->addFieldToFilter(
            'sku',
            array('like' => '%' . $this->getValueForSubQuery($value) . '%')
        );

        $collection->joinTable(
            array('online_sku_subQuery' => $childCollection->getSelect()),
            'variation_parent_id=id',
            array(
                'online_sku_child_listing_product_ids' => 'child_listing_product_ids',
                'online_sku_searched_by_child'         => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'online_sku', 'like' => '%'.$value.'%'),
                array('attribute' => 'online_sku_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterGtin($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $valueForSubQuery = $this->getValueForSubQuery($value);
        $where = <<<SQL
main_table.gtin LIKE '%{$valueForSubQuery}%' OR
main_table.upc LIKE '%{$valueForSubQuery}%' OR
main_table.ean LIKE '%{$valueForSubQuery}%' OR
main_table.isbn LIKE '%{$valueForSubQuery}%' OR
main_table.wpid LIKE '%{$valueForSubQuery}%' OR
main_table.item_id LIKE '%{$valueForSubQuery}%'
SQL;

        $childCollection = $this->getChildProductsCollection();
        $childCollection->getSelect()->where($where);

        $collection->joinTable(
            array('gtin_subQuery' => $childCollection->getSelect()),
            'variation_parent_id=id',
            array(
                'gtin_child_listing_product_ids' => 'child_listing_product_ids',
                'gtin_searched_by_child'         => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'gtin', 'like' => '%'.$value.'%'),
                array('attribute' => 'upc', 'like' => '%'.$value.'%'),
                array('attribute' => 'ean', 'like' => '%'.$value.'%'),
                array('attribute' => 'isbn', 'like' => '%'.$value.'%'),
                array('attribute' => 'wpid', 'like' => '%'.$value.'%'),
                array('attribute' => 'item_id', 'like' => '%'.$value.'%'),
                array('attribute' => 'gtin_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $quoted = $collection->getConnection()->quote($value['from']);
            $where .= 'online_qty >= ' . $quoted;
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $quoted = $collection->getConnection()->quote($value['to']);
            $where .= 'online_qty <= ' . $quoted;
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
                $condition = 'wlp.online_price >= \'' . (float)$value['from'] . '\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'wlp.online_price <= \'' . (float)$value['to'] . '\'';
            }
        }

        $collection->getSelect()->where($condition);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            "status = {$value} OR (variation_child_statuses REGEXP '\"{$value}\":[^0]') AND is_variation_parent = 1"
        );
    }

    //########################################

    protected function getMagentoChildProductsCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Option_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->getCollection()
            ->addFieldToSelect('listing_product_variation_id')
            ->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);

        $collection->getSelect()->joinLeft(
            array('lpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
            'lpv.id=main_table.listing_product_variation_id',
            array('listing_product_id')
        );
        $collection->getSelect()->joinLeft(
            array('alp' => Mage::getResourceModel('M2ePro/Walmart_Listing_Product')->getMainTable()),
            'alp.listing_product_id=lpv.listing_product_id',
            array('variation_parent_id')
        );
        $collection->addFieldToFilter('variation_parent_id', array('notnull' => true));

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'child_listing_product_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT alp.listing_product_id)'),
                'variation_parent_id'       => 'alp.variation_parent_id',
                'searched_by_child'         => new Zend_Db_Expr('1')
            )
        );

        $collection->getSelect()->group('alp.variation_parent_id');

        return $collection;
    }

    protected function getChildProductsCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Walmart_Listing_Product')->getCollection()
            ->addFieldToFilter('variation_parent_id', array('notnull' => true))
            ->addFieldToFilter('is_variation_product', 1);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'child_listing_product_ids' => new Zend_Db_Expr('GROUP_CONCAT(listing_product_id)'),
                'variation_parent_id'       => 'variation_parent_id',
                'searched_by_child'         => new Zend_Db_Expr('1')
            )
        );
        $collection->getSelect()->group('variation_parent_id');

        return $collection;
    }

    //########################################

    protected function wasFoundByChild($row)
    {
        foreach (array('product_id', 'product_sku', 'online_sku', 'gtin') as $item) {
            $searchedByChild = $row->getData("{$item}_searched_by_child");
            if (!empty($searchedByChild)) {
                return true;
            }
        }

        return false;
    }

    protected function getChildListingProductIds($row)
    {
        $ids = array();

        foreach (array('product_id', 'product_sku', 'online_sku', 'gtin') as $item) {
            $itemIds = $row->getData("{$item}_child_listing_product_ids");
            if (empty($itemIds)) {
                continue;
            }

            foreach (explode(',', $itemIds) as $itemId) {
                !isset($ids[$itemId]) && $ids[$itemId] = 0;
                $ids[$itemId]++;
            }
        }

        $maxCount = max($ids);
        foreach ($ids as $id => $count) {
            if ($count < $maxCount) {
                unset($ids[$id]);
            }
        }

        return implode(',', array_keys($ids));
    }

    //########################################
}
