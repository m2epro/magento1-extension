<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingSearchGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection products in listing
        // ---------------------------------------
        $activeComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductCollection->addFieldToFilter('main_table.component_mode', array('in' => $activeComponents));
        $listingProductCollection->getSelect()->distinct();
        $listingProductCollection->getSelect()->join(
            array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '(`l`.`id` = `main_table`.`listing_id`)',
            array('listing_title'=>'title','store_id')
        );
        // ---------------------------------------

        $listingProductCollection->getSelect()->joinLeft(
            array('alp' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable()),
            'main_table.id=alp.listing_product_id',
            array()
        );

        $listingProductCollection->getSelect()->where('(
            (`main_table`.`component_mode` = "'.Ess_M2ePro_Helper_Component_Amazon::NICK.'"
                AND `alp`.variation_parent_id IS NULL)
            OR `main_table`.`component_mode` IN ("'.Ess_M2ePro_Helper_Component_Buy::NICK.'")
        )');

        // Communicate with magento product table
        // ---------------------------------------
        $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                                     ->select()
                                     ->from($table,new Zend_Db_Expr('MAX(`store_id`)'))
                                     ->where("`entity_id` = `main_table`.`product_id`")
                                     ->where("`attribute_id` = `ea`.`attribute_id`")
                                     ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $listingProductCollection->getSelect()->join(
            array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
            '(cpe.entity_id = `main_table`.product_id)',
            array('sku')
        );
        $listingProductCollection->getSelect()->join(
            array('cisi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
            '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',
            array('is_in_stock')
        );
        $listingProductCollection->getSelect()->join(
            array('cpev'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')),
            "(`cpev`.`entity_id` = `main_table`.product_id)",
            array('value')
        );
        $listingProductCollection->getSelect()->join(
            array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),
            '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',
            array()
        );
        $listingProductCollection->getSelect()->where('`cpev`.`store_id` = ('.$dbSelect->__toString().')');
        // ---------------------------------------

        $listingProductCollection->getSelect()->joinLeft(
            new Zend_Db_Expr('(
                SELECT
                    lp.listing_product_id,
                    lp.general_id_owner,
                    lp.general_id,
                    lp.sku
                FROM (
                    SELECT
                        listing_product_id,
                        is_general_id_owner as general_id_owner,
                        general_id,
                        sku
                    FROM ' . Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable() . '
                    WHERE variation_parent_id IS NULL
                    UNION
                    SELECT
                        listing_product_id,
                        template_new_product_id as general_id_owner,
                        general_id,
                        sku
                    FROM ' . Mage::getResourceModel('M2ePro/Buy_Listing_Product')->getMainTable() . '
                ) as lp
            )'),
            'main_table.id=t.listing_product_id',
            array(
                'general_id'    => 'general_id',
                'sku'           => 'sku'
            )
        );

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array(
                'is_m2epro_listing'             => new Zend_Db_Expr('1'),
                'magento_sku'                   => 'cpe.sku',
                'is_in_stock'                   => 'cisi.is_in_stock',
                'product_name'                  => 'cpev.value',
                'listing_title'                 => 'l.title',
                'store_id'                      => 'l.store_id',
                'account_id'                    => 'l.account_id',
                'marketplace_id'                => 'l.marketplace_id',
                'listing_product_id'            => 'main_table.id',
                'component_mode'                => 'main_table.component_mode',
                'product_id'                    => 'main_table.product_id',
                'listing_id'                    => 'main_table.listing_id',
                'status'                        => 'main_table.status',
                'general_id_owner'              => 't.general_id_owner',
                'general_id'                    => 't.general_id',
                'online_sku'                    => 't.sku'
            )
        );

        // ---------------------------------------
        $listingOtherCollection = Mage::getModel('M2ePro/Listing_Other')->getCollection();
        $listingOtherCollection->addFieldToFilter('main_table.component_mode', array('in' => $activeComponents));
        $listingOtherCollection->getSelect()->distinct();

        // add stock availability, type id, status & visibility to select
        // ---------------------------------------
        $listingOtherCollection->getSelect()
            ->joinLeft(
                array('cisi' => Mage::getResourceModel('cataloginventory/stock_item')->getMainTable()),
                '(`cisi`.`product_id` = `main_table`.`product_id` AND cisi.stock_id = 1)',
                array('is_in_stock'))
            ->joinLeft(array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                '(cpe.entity_id = `main_table`.product_id)',
                array('magento_sku'=>'sku'));
        // ---------------------------------------

        $listingOtherCollection->getSelect()->joinLeft(
            new Zend_Db_Expr('(
                SELECT
                    lo.listing_other_id,
                    lo.title,
                    lo.general_id,
                    lo.sku
                FROM (
                    SELECT
                        listing_other_id,
                        title,
                        general_id,
                        sku
                    FROM ' . Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getMainTable() . '
                    UNION
                    SELECT
                        listing_other_id,
                        title,
                        general_id,
                        sku
                    FROM ' . Mage::getResourceModel('M2ePro/Buy_Listing_Other')->getMainTable() . '
                ) as lo
            )'),
            'main_table.id=t.listing_other_id',
            array(
                'title'         => 'title',
                'general_id'    => 'general_id',
                'sku'           => 'sku'
            )
        );

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
                'is_m2epro_listing'             => new Zend_Db_Expr(0),
                'magento_sku'                   => 'cpe.sku',
                'is_in_stock'                   => 'cisi.is_in_stock',
                'product_name'                  => 't.title',
                'listing_title'                 => new Zend_Db_Expr('NULL'),
                'store_id'                      => new Zend_Db_Expr(0),
                'account_id'                    => 'main_table.account_id',
                'marketplace_id'                => 'main_table.marketplace_id',
                'listing_product_id'            => new Zend_Db_Expr('NULL'),
                'component_mode'                => 'main_table.component_mode',
                'product_id'                    => 'main_table.product_id',
                'listing_id'                    => new Zend_Db_Expr('NULL'),
                'status'                        => 'main_table.status',
                'general_id_owner'              => new Zend_Db_Expr('NULL'),
                'general_id'                    => 't.general_id',
                'online_sku'                    => 't.sku'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $selects = array(
            $listingProductCollection->getSelect(),
            $listingOtherCollection->getSelect()
        );

        $unionSelect = Mage::getResourceModel('core/config')->getReadConnection()->select();
        $unionSelect->union($selects);

        $resultCollection = new Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array(
                'is_m2epro_listing',
                'magento_sku',
                'is_in_stock',
                'product_name',
                'listing_title',
                'store_id',
                'account_id',
                'marketplace_id',
                'listing_product_id',
                'component_mode',
                'product_id',
                'listing_id',
                'status',
                'general_id_owner',
                'general_id',
                'online_sku',
            )
        );

        // Set collection to grid
        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Listing / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'product_name',
            'filter_index' => 'product_name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $options = Mage::helper('M2ePro/View_Common_Component')->getEnabledComponentsTitles();

            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'component_mode',
                'sortable'       => false,
                'options'        => $options
            ));
        }

        $this->addColumn('stock_availability',
            array(
                'header'=> Mage::helper('M2ePro')->__('Stock Availability'),
                'width' => '100px',
                'index' => 'is_in_stock',
                'filter_index' => 'is_in_stock',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    1 => Mage::helper('M2ePro')->__('In Stock'),
                    0 => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnStockAvailability')
        ));

        if (Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK) ||
            Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK)) {
            $this->addColumn('is_m2epro_listing', array(
                'header'    => Mage::helper('M2ePro')->__('Listing Type'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'is_m2epro_listing',
                'options'   => array(
                    1 => Mage::helper('M2ePro')->__('M2E Pro'),
                    0 => Mage::helper('M2ePro')->__('3rd Party')
                )
            ));
        }

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'online_sku',
            'filter_index' => 'online_sku',
            'frame_callback' => array($this, 'callbackColumnSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('Identifier'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN    => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    =>
                        Mage::helper('M2ePro')->__('Inactive (Blocked)')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('goto_listing_item', array(
            'header'    => Mage::helper('M2ePro')->__('Manage'),
            'align'     => 'center',
            'width'     => '50px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('product_id'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productId = (int)$row->getData('product_id');
        $storeId = (int)$row->getData('store_id');

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/','show_products_thumbnails'
        );
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $productId.'<hr/><img src="'.$imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$productId.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = '<i style="color:gray;">receiving...</i>';
        } else {
            $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';
        }

        if (!is_null($row->getData('listing_id'))) {
            $urlParams = array();
            $urlParams['id'] = $row->getData('listing_id');
            $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_listing/search');

            $listingUrl = Mage::helper('M2ePro/View')->getUrl($row, 'listing', 'view', $urlParams);
            $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

            if (strlen($listingTitle) > 50) {
                $listingTitle = substr($listingTitle, 0, 50) . '...';
            }

            $value .= '<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>';
            $value .= '<strong>' . Mage::helper('M2ePro')->__('Listing') . ': </strong>';
            $value .= '<a href="' . $listingUrl . '">' . $listingTitle . '</a>';
        }

        if (!is_null($row->getData('magento_sku'))) {
            $tempSku = $row->getData('magento_sku');

            $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU').':</strong> ';
            $value .= Mage::helper('M2ePro')->escapeHtml($tempSku);
        }

        if (is_null($row->getData('listing_product_id'))) {
            return $value;
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component')->getUnknownObject('Listing_Product',$listingProductId);

        $productOptions = array();

        if ($listingProduct->isComponentModeAmazon()) {

            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if (!$variationManager->isIndividualType()) {

                if ($variationManager->isVariationParent()) {

                    $productAttributes = $listingProduct->getChildObject()->getVariationManager()
                        ->getTypeModel()->getProductAttributes();

                    $virtualProductAttributes = $variationManager->getTypeModel()->getVirtualProductAttributes();
                    $virtualChannelAttributes = $variationManager->getTypeModel()->getVirtualChannelAttributes();

                    $value .= '<div style="font-size: 11px; font-weight: bold; color: grey;"><br/>';
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
                    $value .= $attributesStr;
                    $value .= '</div>';
                }
                return $value;
            }

            if ($variationManager->getTypeModel()->isVariationProductMatched()) {
                $productOptions = $listingProduct->getChildObject()->
                    getVariationManager()->getTypeModel()->getProductOptions();
            }
        } else {
            if ($listingProduct->getChildObject()->getVariationManager()->isVariationProductMatched()) {
                $productOptions = $listingProduct->getChildObject()->getVariationManager()->getProductOptions();
            }
        }

        if ($productOptions) {
            $value .= '<br/>';
            $value .= '<div style="font-size: 11px; color: grey;"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
            $value .= '<br/>';
        }

        return $value;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('is_in_stock'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnSku($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if (empty($value)) {

            if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<i style="color:gray;">receiving...</i>';
            }

            if ($row->getData('general_id_owner')) {

                switch ($row->getData('component_mode')) {

                    case Ess_M2ePro_Helper_Component_Amazon::NICK:
                        return Mage::helper('M2ePro')->__('New ASIN/ISBN');
                    case Ess_M2ePro_Helper_Component_Buy::NICK:
                        return Mage::helper('M2ePro')->__('New SKU');
                }
            }

            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = '';
        if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value, $row->getData('marketplace_id'));
        } else if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Buy::NICK) {
            $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($value);
        }

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $value = '<span style="color: brown;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $value = '<span style="color: blue;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->__('Go to Listing');
        $iconSrc = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        if ($row->getData('is_m2epro_listing')) {
            $url = $this->getUrl('*/adminhtml_common_'.$row->getData('component_mode').'_listing/view/',array(
                'id' => $row->getData('listing_id'),
                'filter' => base64_encode(
                    'product_id[from]='.(int)$row->getData('product_id')
                    .'&product_id[to]='.(int)$row->getData('product_id')
                )
            ));
        } else {
            $url = $this->getUrl('*/adminhtml_common_'.$row->getData('component_mode').'_listing_other/view/', array(
                'account' => $row->getData('account_id'),
                'marketplace' => $row->getData('marketplace_id'),
                'filter' => base64_encode(
                    'title='.$row->getData('online_sku')
                )
            ));
        }

        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$url}"><img src="{$iconSrc}" /></a>
</div>
HTML;

        return $html;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()
            ->where('product_name LIKE ? OR magento_sku LIKE ? OR listing_title LIKE ?', '%'.$value.'%');
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}