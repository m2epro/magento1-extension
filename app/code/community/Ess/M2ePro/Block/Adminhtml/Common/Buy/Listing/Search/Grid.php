<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingSearchGrid');
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
        $listingProductCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->distinct();
        $listingProductCollection->getSelect()
            ->join(array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                   '(`l`.`id` = `main_table`.`listing_id`)',
                   array('listing_title'=>'title','store_id'))
            ->join(array('bl'=>Mage::getResourceModel('M2ePro/Buy_Listing')->getMainTable()),
                   '(`bl`.`listing_id` = `l`.`id`)',
                   array('template_selling_format_id'));
        // ---------------------------------------

        // Communicate with magento product table
        // ---------------------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(Mage::getSingleton('core/resource')
                ->getTableName('catalog_product_entity_varchar'),
            new Zend_Db_Expr('MAX(`store_id`)'))
            ->where("`entity_id` = `main_table`.`product_id`")
            ->where("`attribute_id` = `ea`.`attribute_id`")
            ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $listingProductCollection->getSelect()
        //->join(array('csi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
//                             '(csi.product_id = `main_table`.product_id)',array('qty'))
            ->join(array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                '(cpe.entity_id = `main_table`.product_id)',
                array('magento_sku'=>'sku'))
            ->join(array('cisi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
                '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',
                array('is_in_stock'))
            ->join(array('cpev'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')),
                "(`cpev`.`entity_id` = `main_table`.product_id)",
                array('value'))
            ->join(array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),
                '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',
                array())
            ->where('`cpev`.`store_id` = ('.$dbSelect->__toString().')');
        // ---------------------------------------

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
                'product_id'                    => 'main_table.product_id',
                'listing_id'                    => 'main_table.listing_id',
                'status'                        => 'main_table.status',
                'template_new_product_id'       => 'second_table.template_new_product_id',
                'general_id'                    => 'second_table.general_id',
                'online_sku'                    => 'second_table.sku',
                'online_qty'                    => 'second_table.online_qty',
                'online_price'                  => 'second_table.online_price'
            )
        );

        // ---------------------------------------
        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
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

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
                'is_m2epro_listing'             => new Zend_Db_Expr(0),
                'magento_sku'                   => 'cpe.sku',
                'is_in_stock'                   => 'cisi.is_in_stock',
                'product_name'                  => 'second_table.title',
                'listing_title'                 => new Zend_Db_Expr('NULL'),
                'store_id'                      => new Zend_Db_Expr(0),
                'account_id'                    => 'main_table.account_id',
                'marketplace_id'                => 'main_table.marketplace_id',
                'listing_product_id'            => new Zend_Db_Expr('NULL'),
                'product_id'                    => 'main_table.product_id',
                'listing_id'                    => new Zend_Db_Expr('NULL'),
                'status'                        => 'main_table.status',
                'template_new_product_id'       => new Zend_Db_Expr('NULL'),
                'general_id'                    => 'second_table.general_id',
                'online_sku'                    => 'second_table.sku',
                'online_qty'                    => 'second_table.online_qty',
                'online_price'                  => 'second_table.online_price',
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
                'product_id',
                'listing_id',
                'status',
                'template_new_product_id',
                'general_id',
                'online_sku',
                'online_qty',
                'online_price'
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

        if (Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK)) {
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
            'header' => Mage::helper('M2ePro')->__('Reference ID'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'online_sku',
            'filter_index' => 'online_sku',
            'frame_callback' => array($this, 'callbackColumnSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('Rakuten.com SKU'),
            'align' => 'left',
            'width' => '100px',
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
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive')
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

        $withoutImageHtml = '<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $productId))
            .'" target="_blank">'
            .$productId
            .'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/','show_products_thumbnails');
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
            $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_buy_listing/search');

            $listingUrl = $this->getUrl('*/adminhtml_common_buy_listing/view',$urlParams);
            $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

            if (strlen($listingTitle) > 50) {
                $listingTitle = substr($listingTitle, 0, 50) . '...';
            }

            $value .= '<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>';
            $value .= '<strong>'
                .Mage::helper('M2ePro')->__('Listing')
                .': </strong> <a href="'
                .$listingUrl
                .'">'
                .$listingTitle
                .'</a>';
        }

        if (!is_null($row->getData('magento_sku'))) {
            $tempSku = $row->getData('magento_sku');

            $value .= '<br/><strong>'
                . Mage::helper('M2ePro')->__('SKU')
                . ':</strong> '
                . Mage::helper('M2ePro')->escapeHtml($tempSku);
        }

        if (is_null($row->getData('listing_product_id'))) {
            return $value;
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$listingProductId);

        if ($listingProduct->getChildObject()->getVariationManager()->isVariationProduct() &&
            $listingProduct->getChildObject()->getVariationManager()->isVariationProductMatched()
        ) {
            $productOptions = $listingProduct->getChildObject()->getVariationManager()->getProductOptions();

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

            if ($row->getData('template_new_product_id')) {
                return Mage::helper('M2ePro')->__('New SKU');
            }

            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($value);
        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        return Mage::app()->getLocale()->currency('USD')->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            default:
                break;
        }

        if (is_null($row->getData('listing_product_id'))) {
            return $value;
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$listingProductId);

        $tempLocks = $listingProduct->getObjectLocks();

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'new_sku_action':
                    $title = Mage::helper('M2ePro')->__('Add New SKU in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'list_action':
                    $title = Mage::helper('M2ePro')->__('List in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'relist_action':
                    $title = Mage::helper('M2ePro')->__('Relist in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'revise_action':
                    $title = Mage::helper('M2ePro')->__('Revise in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'stop_action':
                    $title = Mage::helper('M2ePro')->__('Stop in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                case 'stop_and_remove_action':
                    $title = Mage::helper('M2ePro')->__('Stop And Remove in Progress...');
                    $value .= '<br/><span style="color: #605fff">['.$title.']</span>';
                    break;

                default:
                    break;

            }
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        if ($row->getData('is_m2epro_listing')) {
            $url = $this->getUrl('*/adminhtml_common_buy_listing/view/', array(
                'id'=>$row->getData('listing_id'),
                'filter'=>base64_encode(
                    'product_id[from]='.(int)$row->getData('product_id')
                    .'&product_id[to]='.(int)$row->getData('product_id')
                )
            ));
        } else {
            $url = $this->getUrl('*/adminhtml_common_buy_listing_other/view/', array(
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
        return $this->getUrl('*/adminhtml_common_buy_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}