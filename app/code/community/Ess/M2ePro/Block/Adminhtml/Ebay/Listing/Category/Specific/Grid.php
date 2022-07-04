<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;
use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;

/**
 * @method setCategoriesData()
 * @method getCategoriesData()
 */
class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const SPECIFICS_MODE_NOT_SET_REQUIRED     = 'not-set-required';
    const SPECIFICS_MODE_NOT_SET_NOT_REQUIRED = 'not-set-not-required';
    const SPECIFICS_MODE_DEFAULT              = 'default';
    const SPECIFICS_MODE_CUSTOM               = 'custom';

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategorySpecificGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Collection_Custom();

        foreach ($this->getCategoriesData() as $hash => $data) {
            $row = $data[eBayCategory::TYPE_EBAY_MAIN];

            if (!isset($row['is_custom_template'])) {
                $specificsRequired = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                    $row['value'],
                    $this->_listing->getMarketplaceId()
                );

                $spMode = $specificsRequired
                    ? self::SPECIFICS_MODE_NOT_SET_REQUIRED
                    : self::SPECIFICS_MODE_NOT_SET_NOT_REQUIRED;
            } elseif ($row['is_custom_template'] == 1) {
                $spMode = self::SPECIFICS_MODE_CUSTOM;
            } else {
                $spMode = self::SPECIFICS_MODE_DEFAULT;
            }

            $row['id'] = $hash;
            $row['specifics_mode'] = $spMode;
            $row['full_path'] = $row['path'] .' '. $row['value'];

            $collection->addItem(new Varien_Object($row));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'category', array(
                'header'    => Mage::helper('M2ePro')->__('eBay Primary Category'),
                'align'     => 'left',
                'width'     => '*',
                'index'     => 'full_path',
                'filter_condition_callback' => array($this, 'callbackFilterCategory'),
                'frame_callback' => array($this, 'callbackColumnCategoryCallback')
            )
        );

        $this->addColumn(
            'specifics', array(
                  'header'    => Mage::helper('M2ePro')->__('Item Specifics'),
                  'align'     => 'left',
                  'width'     => '400',
                  'type'      => 'options',
                  'index'     => 'specifics_mode',
                  'options'   => array(
                      self::SPECIFICS_MODE_NOT_SET_REQUIRED     => Mage::helper('M2ePro')->__('Not Set (required)'),
                      self::SPECIFICS_MODE_NOT_SET_NOT_REQUIRED => Mage::helper('M2ePro')->__('Not Set (not required)'),
                      self::SPECIFICS_MODE_DEFAULT              => Mage::helper('M2ePro')->__('Default'),
                      self::SPECIFICS_MODE_CUSTOM               => Mage::helper('M2ePro')->__('Custom'),
                  ),
                  'filter_condition_callback' => array($this, 'callbackFilterSpecifics'),
                  'frame_callback' => array($this, 'callbackColumnSpecificsCallback')
              )
        );

        $this->addColumn(
            'actions', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'center',
                'width'     => '150px',
                'type'      => 'action',
                'index'     => 'actions',
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'sortable'  => false,
                'filter'    => false,
                'no_link'   => true,
                'actions'   => array(
                    'editSpecifics' => array(
                        'caption'        => Mage::helper('catalog')->__('Edit'),
                        'field'          => 'id',
                        'onclick_action' => "EbayListingCategorySpecificGridObj.actions['editSpecificsAction']"
                    ),
                )
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnCategoryCallback($value, $row, $column, $isExport)
    {
        if ($row['mode'] == TemplateCategory::CATEGORY_MODE_EBAY) {
            return "{$row['path']}&nbsp;({$row['value']})";
        } elseif ($row['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE) {
            return $row['path'];
        }

        return '';
    }

    public function callbackColumnSpecificsCallback($value, $row, $column, $isExport)
    {
        /** @var Varien_Object $row */
        $helper = Mage::helper('M2ePro');

        if ($row['specifics_mode'] === self::SPECIFICS_MODE_NOT_SET_REQUIRED) {
            return <<<HTML
<span style="font-style: italic; color: red;">{$helper->__('Not Set')}</span>
HTML;
        } elseif ($row['specifics_mode'] === self::SPECIFICS_MODE_NOT_SET_NOT_REQUIRED) {
            return <<<HTML
<span style="font-style: italic; color: grey;">{$helper->__('Not Set')}</span>
HTML;
        } elseif ($row['specifics_mode'] === self::SPECIFICS_MODE_CUSTOM) {
            return "<span>{$helper->__('Custom')}</span>";
        } elseif ($row['specifics_mode'] === self::SPECIFICS_MODE_DEFAULT) {
            return "<span>{$helper->__('Default')}</span>";
        }

        return '';
    }

    //########################################

    protected function callbackFilterCategory($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'full_path', $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
        );
    }

    protected function callbackFilterSpecifics($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'specifics_mode', $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_MATCH
        );
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');
        $urls = array_merge(
            $helper->getControllerActions('adminhtml_ebay_listing_categorySettings', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_category', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_accountStoreCategory')
        );

        $urls['adminhtml_ebay_listing_categorySettings'] = $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/save', array(
                '_current' => true
            )
        );

        $translations = $helper->jsonEncode(
            array(
                'Specifics' => $helper->__('Specifics'),

                'select_relevant_category' => $helper->__(
                    'To proceed, Category data must be specified.
                    Ensure you set Item Specifics for all assigned Categories.'
                )
            )
        );
        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $constants = $helper->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');

        $isAllSelected = (int)!$this->isAllSpecificsSelected();
        $categoriesData = Mage::helper('M2ePro')->jsonEncode($this->getCategoriesData());

        $commonJs = <<<HTML
<script type="text/javascript">

    EbayListingCategoryProductGridObj.afterInitPage();
    EbayListingCategorySpecificGridObj.setCategoriesData({$categoriesData});
    EbayListingCategoryProductGridObj.validateCategories(
        '{$isAllSelected}', '{$isAllSelected}'
    )
    
</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {

            $additionalJs = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants}, 'Ess_M2ePro_Helper_Component_Ebay_Category');
    
    EbayListingCategoryProductGridObj = new EbayListingCategoryProductGrid('{$this->getId()}');
    EbayListingCategorySpecificGridObj = new EbayListingCategorySpecificGrid('{$this->getId()}');
    EbayListingCategorySpecificGridObj.setMarketplaceId({$this->_listing->getMarketplaceId()});

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    protected function isAllSpecificsSelected()
    {
        foreach ($this->getCategoriesData() as $id => $categoryData) {
            if ($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'] === null) {
                $specificsRequired = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                    $categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'],
                    $this->_listing->getMarketplaceId()
                );

                if ($specificsRequired) {
                    return false;
                }
            }
        }

        return true;
    }

    //########################################
}
