<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $marketplacesOptions = NULL;

    private $accountsOptions = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationCategoryGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Prepare ebay main category
        // ---------------------------------------
        $ebayPrimarySelect = $connRead->select();
        $ebayPrimarySelect->from(
                array('etc' => Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable())
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_main_mode as mode',
                new Zend_Db_Expr(
                    'IF (`category_main_mode` = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY.',
                         `category_main_id`,
                         `category_main_attribute`) as `value`'),
                'category_main_path as path',
                new Zend_Db_Expr(Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN.' as `type`'),
                'marketplace_id as marketplace',
                new Zend_Db_Expr('\'\' as `account`'),
            ))
            ->where('category_main_mode != ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE)
            ->group(array('mode', 'value', 'marketplace'));
        // ---------------------------------------

        // Prepare ebay secondary category
        // ---------------------------------------
        $ebaySecondarySelect = $connRead->select();
        $ebaySecondarySelect->from(
                array('etc' => Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable())
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_secondary_mode as mode',
                new Zend_Db_Expr(
                    'IF (`category_secondary_mode` = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY.',
                         `category_secondary_id`,
                         `category_secondary_attribute`) as `value`'),
                'category_secondary_path as path',
                new Zend_Db_Expr(Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY.' as `type`'),
                'marketplace_id as marketplace',
                new Zend_Db_Expr('\'\' as `account`'),
            ))
            ->where('category_secondary_mode != ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE)
            ->group(array('mode', 'value', 'marketplace'));
        // ---------------------------------------

        // Prepare store main category
        // ---------------------------------------
        $storePrimarySelect = $connRead->select();
        $storePrimarySelect->from(
                array('etc' => Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable())
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'store_category_main_mode as mode',
                new Zend_Db_Expr(
                    'IF (`store_category_main_mode` = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY.',
                         `store_category_main_id`,
                         `store_category_main_attribute`) as `value`'),
                'store_category_main_path as path',
                new Zend_Db_Expr(Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN.' as `type`'),
                new Zend_Db_Expr('\'\' as `marketplace`'),
                'account_id as account',
            ))
            ->where('store_category_main_mode != ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE)
            ->group(array('mode', 'value', 'account'));
        // ---------------------------------------

        // Prepare store secondary category
        // ---------------------------------------
        $categoryModeEbay = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
        $categoryModeNone = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;

        $storeSecondarySelect = $connRead->select();
        $storeSecondarySelect->from(
                array('etc' => Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable())
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'store_category_secondary_mode as mode',
                new Zend_Db_Expr(
                    'IF (`store_category_secondary_mode` = '.$categoryModeEbay.',
                         `store_category_secondary_id`,
                         `store_category_secondary_attribute`) as `value`'),
                'store_category_secondary_path as path',
                new Zend_Db_Expr(Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY.' as `type`'),
                new Zend_Db_Expr('\'\' as `marketplace`'),
                'account_id as account',
            ))
            ->where('store_category_secondary_mode != ?', $categoryModeNone)
            ->group(array('mode', 'value', 'account'));
        // ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $ebayPrimarySelect,
            $ebaySecondarySelect,
            $storePrimarySelect,
            $storeSecondarySelect,
        ));
        // ---------------------------------------

        // Prepare result collection
        // ---------------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect)
        );
        // ---------------------------------------

        // Join dictionary tables
        // ---------------------------------------
        $edcTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        $eascTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_account_store_category');

        $resultCollection->getSelect()->joinLeft(
            array('edc' => $edcTable),
            'edc.category_id = main_table.value AND edc.marketplace_id = main_table.marketplace'
        );

        $resultCollection->getSelect()->joinLeft(
            array('easc' => $eascTable),
            'easc.category_id = main_table.value AND easc.account_id = main_table.account'
        );
        // ---------------------------------------

        // ---------------------------------------
        $resultCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array(
            'mode', 'value', 'path', 'type', 'marketplace', 'account',
            'edc.category_id as state_ebay', 'easc.category_id as state_store',
//            new Zend_Db_Expr(
//                'IF (`mode` = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY.',
//                    IF (`type` IN ('.implode(',', $ebayCategoryTypes).'),
//                        IF (`edc`.`category_id` IS NULL, 0, 1),
//                        IF (`easc`.`category_id` IS NULL, 0, 1)
//                ), 1) as state'
//            ),
        ));
        // ---------------------------------------

//        var_dump($resultCollection->getSelectSql(true)); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('M2ePro');

        $this->addColumn('path', array(
            'header'        => Mage::helper('M2ePro')->__('Title'),
            'align'         => 'left',
            'type'          => 'text',
//            'width'         => '150px',
            'index'         => 'path',
            'escape'        => true,
            'filter_index'  => 'main_table.path',
            'frame_callback'=> array($this, 'callbackColumnPath'),
            'filter_condition_callback' => array($this, 'callbackFilterPath'),
        ));

        $options = array(
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN       => $helper->__('Primary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY  => $helper->__('Secondary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN      => $helper->__('Store Primary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY => $helper->__('Store Secondary'),
        );
        $this->addColumn('type', array(
            'header'        => $helper->__('Type'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'type',
            'sortable'      => false,
            'filter_index'  => 'main_table.type',
            'options'       => $options
        ));

        $this->addColumn('marketplace', array(
            'header'        => $helper->__('eBay Site'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'marketplace',
            'filter_index'  => 'main_table.marketplace',
            'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
            'frame_callback'=> array($this, 'callbackColumnMarketplace'),
            'options'       => $this->getMarketplacesOptions(),
        ));

        $this->addColumn('account', array(
            'header'        => $helper->__('Account'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'account',
            'filter_index'  => 'main_table.account',
            'filter_condition_callback' => array($this, 'callbackFilterAccount'),
            'frame_callback'=> array($this, 'callbackColumnAccount'),
            'options'       => $this->getAccountsOptions(),
        ));

        $this->addColumn('state', array(
            'header'        => $helper->__('State'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'state',
            'sortable'  => false,
            'filter_index'  => 'state',
            'filter_condition_callback' => array($this, 'callbackFilterState'),
            'frame_callback'=> array($this, 'callbackColumnState'),
            'options'       => array(
                1 => $helper->__('Active'),
                0 => $helper->__('Removed'),
            ),
        ));

        $this->addColumn('actions', array(
            'header'    => $helper->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getTemplateId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_category/edit',
                        'params' => array(
                            'mode' => '$mode',
                            'value' => '$value',
                            'type' => '$type',
                            'marketplace' => '$marketplace',
                            'account' => '$account'
                        )
                    ),
                    'field'     => 'id'
                ),
            )
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnPath($value, $row, $column, $isExport)
    {
        $mode = $row->getData('mode');
        $type = $row->getData('type');

        if (empty($value) && $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            if (in_array($type, Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())) {
                $value = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $row->getData('value'), $row->getData('marketplace')
                );
            } else {
                $value = Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath(
                    $row->getData('value'), $row->getData('account')
                );
            }
        }

        if (empty($value) && $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $value = Mage::helper('M2ePro')->__('Magento Attribute') .
                     ' > ' .
                     Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($row->getData('value'));
        }

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $value .= ' (' . $row->getData('value') . ')';
        }

        return $value;
    }

    protected function callbackFilterPath($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->orWhere('main_table.path LIKE ?', '%'. $value . '%');
        $collection->getSelect()->orWhere('main_table.value LIKE ?', '%'. $value . '%');
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('Any');
        }

        return $value;
    }

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.marketplace = \'\' OR main_table.marketplace = ?', (int)$value);
    }

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('Any');
        }

        return $value;
    }

    protected function callbackFilterAccount($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.account = \'\' OR main_table.account = ?', (int)$value);
    }

    public function callbackColumnState($value, $row, $column, $isExport)
    {
        if ($row->getData('mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $row->setData('state', 1);
            return $column->getRenderer()->render($row);
        }

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        if ((in_array($row->getData('type'), $ebayCategoryTypes) && is_null($row->getData('state_ebay'))) ||
            (!in_array($row->getData('type'), $ebayCategoryTypes) && is_null($row->getData('state_store')))
        ) {
            $row->setData('state', 0);
            return $column->getRenderer()->render($row);
        }

        $row->setData('state', 1);
        return $column->getRenderer()->render($row);
    }

    protected function callbackFilterState($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        if ($value == 1) {
            $collection->getSelect()->where(
                '((edc.category_id IS NOT NULL AND main_table.type IN ('.implode(',', $ebayCategoryTypes).')) OR
                (easc.category_id IS NOT NULL AND main_table.type IN ('.implode(',', $storeCategoryTypes).'))) OR
                (main_table.mode = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE.')');
        } else {
            $collection->getSelect()->where(
                '((edc.category_id IS NULL AND main_table.type IN ('.implode(',', $ebayCategoryTypes).')) OR
                (easc.category_id IS NULL AND main_table.type IN ('.implode(',', $storeCategoryTypes).'))) AND
                (main_table.mode != '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE.')');
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_ebay_category/edit',
            array(
                'mode' => $row->getData('mode'),
                'value' => $row->getData('value'),
                'type' => $row->getData('type'),
                'marketplace' => $row->getData('marketplace'),
                'account' => $row->getData('account'),
            )
        );
    }

    public function getRowClass($row)
    {
        if ($row->getData('mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return '';
        }

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        if ((in_array($row->getData('type'), $ebayCategoryTypes) && is_null($row->getData('state_ebay'))) ||
            (!in_array($row->getData('type'), $ebayCategoryTypes) && is_null($row->getData('state_store')))
        ) {
            return 'invalid-row';
        }

        return '';
    }

    //########################################

    private function getMarketplacesOptions()
    {
        if (is_null($this->marketplacesOptions)) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->marketplacesOptions = $collection->toOptionHash();
        }

        return $this->marketplacesOptions;
    }

    private function getAccountsOptions()
    {
        if (is_null($this->accountsOptions)) {
            $collection = Mage::getModel('M2ePro/Account')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);

            $this->accountsOptions = $collection->toOptionHash();
        }

        return $this->accountsOptions;
    }

    //########################################
}