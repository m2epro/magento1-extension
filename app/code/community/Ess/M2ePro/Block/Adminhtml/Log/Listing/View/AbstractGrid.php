<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Listing_View_AbstractGrid extends
    Ess_M2ePro_Block_Adminhtml_Log_Listing_AbstractGrid
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId($this->getComponentMode() . 'LogListingGrid' . $this->getEntityId());

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->_entityIdFieldName = self::LISTING_PRODUCT_ID_FIELD;
        $this->_logModelName = 'Listing_Log';
    }

    //########################################

    protected function getLogHash($log)
    {
        return crc32("{$log->getActionId()}_{$log->getListingId()}_{$log->getListingProductId()}");
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Resource_Listing_Log_Collection $collection
     */
    protected function applyFilters($collection)
    {
        // Set listing filter
        // ---------------------------------------
        if ($this->getEntityId()) {
            if ($this->isListingProductLog() && $this->getListingProduct()->isComponentModeAmazon() &&
                $this->getListingProduct()->getChildObject()->getVariationManager()->isRelationParentType()) {
                $collection->addFieldToFilter(
                    array(
                        self::LISTING_PRODUCT_ID_FIELD,
                        self::LISTING_PARENT_PRODUCT_ID_FIELD
                    ),
                    array(
                        array(
                            'attribute' => self::LISTING_PRODUCT_ID_FIELD,
                            'eq'        => $this->getEntityId()
                        ),
                        array(
                            'attribute' => self::LISTING_PARENT_PRODUCT_ID_FIELD,
                            'eq'        => $this->getEntityId()
                        )
                    )
                );
            } else {
                $collection->addFieldToFilter($this->getEntityField(), $this->getEntityId());
            }
        }

        // ---------------------------------------

        $collection->addFieldToFilter('main_table.component_mode', $this->getComponentMode());

        // Add Filter By Account
        // ---------------------------------------
        if ($accountId = $this->getRequest()->getParam($this->getComponentMode() . 'Account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        } else {
            $collection->getSelect()->joinLeft(
                array('account_table' => Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                'account_table.id = main_table.account_id',
                array('real_account_id' => 'account_table.id')
            );

            $collection->addFieldToFilter('account_table.id', array('notnull' => true));
        }

        // Add Filter By Marketplace
        // ---------------------------------------
        if ($marketplaceId = $this->getRequest()->getParam($this->getComponentMode() . 'Marketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        } else {
            $collection->getSelect()->joinLeft(
                array('marketplace_table' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                'marketplace_table.id = main_table.marketplace_id',
                array('marketplace_status' => 'marketplace_table.status')
            );

            $collection->addFieldToFilter('marketplace_table.status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date', array(
                'header'    => Mage::helper('M2ePro')->__('Creation Date'),
                'align'     => 'left',
                'type'      => 'datetime',
                'filter_index' => 'main_table.create_date',
                'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'width'     => '150px',
                'index'     => 'create_date',
                'column_css_class' => 'random_border_color'
            )
        );

        $this->addColumn(
            'action', array(
                'header'       => Mage::helper('M2ePro')->__('Action'),
                'align'        => 'left',
                'width'        => '150px',
                'type'         => 'options',
                'index'        => 'action',
                'sortable'     => false,
                'filter_index' => 'main_table.action',
                'options'      => $this->getActionTitles()
            )
        );

        if (!$this->getEntityId()) {
            $this->addColumn(
                'listing_title', array(
                    'header'         => Mage::helper('M2ePro')->__('Listing'),
                    'align'          => 'left',
                    'width'          => '150px',
                    'type'           => 'text',
                    'index'          => 'listing_title',
                    'filter_index'   => 'main_table.listing_title',
                    'frame_callback' => array($this, 'callbackColumnListingTitleID'),
                    'filter_condition_callback' => array($this, 'callbackFilterListingTitleID')
                )
            );
        }

        if (!$this->isListingProductLog()) {
            $this->addColumn(
                'product_title', array(
                    'header'         => Mage::helper('M2ePro')->__('Magento Product'),
                    'align'          => 'left',
                    'width'          => '280px',
                    'type'           => 'text',
                    'index'          => 'product_title',
                    'filter_index'   => 'main_table.product_title',
                    'frame_callback' => array($this, 'callbackColumnProductTitleID'),
                    'filter_condition_callback' => array($this, 'callbackFilterProductTitleID')
                )
            );
        }

        if ($this->isListingProductLog() && $this->getListingProduct()->isComponentModeAmazon() &&
            ($this->getListingProduct()->getChildObject()->getVariationManager()->isRelationParentType() ||
                $this->getListingProduct()->getChildObject()->getVariationManager()->isRelationChildType() ||
                $this->getListingProduct()->getChildObject()->getVariationManager()->isIndividualType())) {
            $this->addColumn(
                'attributes', array(
                    'header'         => Mage::helper('M2ePro')->__('Variation'),
                    'align'          => 'left',
                    'width'          => '250px',
                    'index'          => 'additional_data',
                    'sortable'       => false,
                    'filter_index'   => 'main_table.additional_data',
                    'frame_callback' => array($this, 'callbackColumnAttributes'),
                    'filter_condition_callback' => array($this, 'callbackFilterAttributes')
                )
            );
        }

        $this->addColumn(
            'description', array(
                'header'         => Mage::helper('M2ePro')->__('Message'),
                'align'          => 'left',
                'type'           => 'text',
                'index'          => 'description',
                'filter_index'   => 'main_table.description',
                'frame_callback' => array($this, 'callbackColumnDescription')
            )
        );

        $this->addColumn(
            'initiator', array(
                'header'         => Mage::helper('M2ePro')->__('Run Mode'),
                'width'          => '80px',
                'index'          => 'initiator',
                'align'          => 'right',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => $this->_getLogInitiatorList(),
                'frame_callback' => array($this, 'callbackColumnInitiator')
            )
        );

        $this->addColumn(
            'type', array(
                'header'         => Mage::helper('M2ePro')->__('Type'),
                'width'          => '80px',
                'index'          => 'type',
                'align'          => 'right',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => $this->_getLogTypeList(),
                'frame_callback' => array($this, 'callbackColumnType')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnListingTitleID($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50);
        }

        $value = Mage::helper('M2ePro')->escapeHtml($value);
        $productId = (int)$row->getData('product_id');

        $urlData = array(
            'id'     => $row->getData('listing_id'),
            'filter' => base64_encode("product_id[from]={$productId}&product_id[to]={$productId}")
        );

        $manageUrl = $this->getUrl('*/adminhtml_' . $row->getData('component_mode') . '_listing/view', $urlData);

        if ($row->getData('listing_id')) {
            $url = $this->getUrl(
                '*/adminhtml_'.$row->getData('component_mode').'_listing/view',
                array('id' => $row->getData('listing_id'))
            );

            $value = '<a target="_blank" href="'.$url.'">' .
                $value .
                '</a><br/>ID: '.$row->getData('listing_id');

            if ($productId) {
                $value .= '<br/>Product:<br/>'
                    . '<a target="_blank" href="' . $manageUrl . '">'
                    . $row->getData('product_title') . '</a>';
            }
        }

        return $value;
    }

    public function callbackColumnProductTitleID($value, $row, $column, $isExport)
    {
        if (!$row->getData('product_id')) {
            return $value;
        }

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getData('product_id')));
        $value = '<a target="_blank" href="'.$url.'" target="_blank">'.
            Mage::helper('M2ePro')->escapeHtml($value).
            '</a><br/>ID: '.$row->getData('product_id');

        $additionalData = Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
        if (empty($additionalData['variation_options'])) {
            return $value;
        }

        $value .= '<div style="font-size: 11px; color: grey;">';
        foreach ($additionalData['variation_options'] as $attribute => $option) {
            !$option && $option = '--';
            $value .= '<strong>'.
                Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</strong>:&nbsp;'.
                Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }

        $value .= '</div>';

        return $value;
    }

    public function callbackColumnAttributes($value, $row, $column, $isExport)
    {
        $additionalData = Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
        if (empty($additionalData['variation_options'])) {
            return '';
        }

        $result = '<div style="font-size: 11px; color: grey;">';
        foreach ($additionalData['variation_options'] as $attribute => $option) {
            !$option && $option = '--';
            $result .= '<strong>'.
                Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</strong>:&nbsp;'.
                Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }

        $result .= '</div>';

        return $result;
    }

    //########################################

    protected function callbackFilterListingTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'listing_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%'. $value .'%');
        is_numeric($value) && $where .= ' OR listing_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterProductTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'product_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%'. $value .'%');
        is_numeric($value) && $where .= ' OR product_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterAttributes($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter('main_table.additional_data', array('like' => '%'. $value .'%'));
    }

    //########################################

    /**
     * Implements by using traits
     */
    abstract protected function getExcludedActionTitles();

    // ---------------------------------------

    protected function getActionTitles()
    {
        $allActions = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Listing_Log');

        return array_diff_key($allActions, $this->getExcludedActionTitles());
    }

    //########################################
}
