<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser_Manager as AmazonManager;
use Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser_Manager as EbayManager;
use Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser_Manager as WalmartManager;

class Ess_M2ePro_Block_Adminhtml_Order_UploadByUser_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var string */
    protected $_component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('orderUploadByUserPopupGrid');

        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Collection_Custom();

        foreach ($this->getAccountsCollection()->getItems() as $id => $account) {
            /** @var Ess_M2ePro_Model_Account $account */

            $manager = $this->getManager($account);
            $item = new Varien_Object(
                array(
                    'title'      => $account->getTitle(),
                    'identifier' => $manager->getIdentifier(),
                    'from_date'  => $manager->getFromDate() ? $manager->getFromDate()->format('Y-m-d H:i:s') : null,
                    'to_date'    => $manager->getToDate() ? $manager->getToDate()->format('Y-m-d H:i:s') : null,

                    '_manager_' => $manager,
                    '_account_' => $account
                )
            );
            $collection->addItem($item);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if ($this->_component !== Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $this->addColumn(
                'title',
                array(
                    'header'   => Mage::helper('M2ePro')->__('Title'),
                    'align'    => 'left',
                    'width'    => '300px',
                    'type'     => 'text',
                    'sortable' => false,
                    'index'    => 'title',
                )
            );
        }

        $this->addColumn(
            'identifier',
            array(
                'header'   => $this->getIdentifierTitle(),
                'align'    => 'left',
                'width'    => '300px',
                'type'     => 'text',
                'sortable' => false,
                'index'    => 'identifier',
            )
        );

        $this->addColumn(
            'from_date',
            array(
                'header'   => Mage::helper('M2ePro')->__('From Date'),
                'align'    => 'left',
                'width'    => '200px',
                'index'    => 'from_date',
                'sortable' => false,
                'type'     => 'datetime',
                'format'   => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'frame_callback' => array($this, 'callbackColumnDate')
            )
        );

        $this->addColumn(
            'to_date',
            array(
                'header'   => Mage::helper('M2ePro')->__('To Date'),
                'align'    => 'left',
                'width'    => '200px',
                'index'    => 'to_date',
                'type'     => 'datetime',
                'sortable' => false,
                'format'   => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'frame_callback' => array($this, 'callbackColumnDate')
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'         => Mage::helper('M2ePro')->__('Action'),
                'width'          => '80px',
                'type'           => 'text',
                'align'          => 'right',
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnAction')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getCssRenderer()->add(
            <<<CSS
.calendar {
    z-index: 150 !important;
}

div#{$this->getId()} div.grid th {
    padding: 2px 4px !important;
}

div#{$this->getId()} div.grid td {
    padding: 2px 4px !important;
}
CSS
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function callbackColumnDate($value, $row, $column, $isExport)
    {
        /** @var AmazonManager|EbayManager|WalmartManager $manager */
        $manager = $row['_manager_'];

        if ($manager->isEnabled()) {
            return $value;
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = $row['_account_'];

        return <<<HTML
<script>
    Calendar.setup({
        inputField : "{$account->getId()}_{$column->getIndex()}",
        ifFormat : "%Y-%m-%d %H:%M:00",
        button : "{$account->getId()}_{$column->getIndex()}_trig",
        showsTime: true,
        align : "BR",
        singleClick : true
    });
</script>

<input type="text" id="{$account->getId()}_{$column->getIndex()}" name="{$account->getId()}_{$column->getIndex()}"
       class="validate-date required-entry" />
HTML;
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        /** @var AmazonManager|EbayManager|WalmartManager $manager */
        $manager = $row['_manager_'];

        /** @var Ess_M2ePro_Model_Account $account */
        $account = $row['_account_'];

        $helper = Mage::helper('M2ePro');

        $data = array(
            'label'   => $manager->isEnabled()
                ? Mage::helper('M2ePro')->__('Cancel')
                : Mage::helper('M2ePro')->__('Reimport'),

            'onclick' => $manager->isEnabled()
                ? "UploadByUserObj.resetUpload({$account->getId()})"
                : "UploadByUserObj.configureUpload({$account->getId()})",

            'class' => 'button_link'
        );

        $state = '';
        if ($manager->isEnabled()) {
            $state = <<<HTML
<br/>
<span style="color: orange; font-style: italic;">{$helper->__('(in progress)')}</span>
HTML;
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        return $button->toHtml() . $state;

    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Collection_Abstract
     */
    protected function getAccountsCollection()
    {
        switch ($this->_component) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
                $collection->getSelect()->group(array('second_table.merchant_id'));
                return $collection;

            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
                return $collection;

            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
                return $collection;
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Component is not set');
    }

    protected function getIdentifierTitle()
    {
        switch ($this->_component) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                return Mage::helper('M2ePro')->__('Merchant ID');

            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                return Mage::helper('M2ePro')->__('User ID');

            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                return Mage::helper('M2ePro')->__('Consumer ID');
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Component is not set');
    }

    protected function getManager(Ess_M2ePro_Model_Account $account)
    {
        switch ($account->getComponentMode()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Amazon_Order_UploadByUser_Manager');
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Ebay_Order_UploadByUser_Manager');
                break;

            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Walmart_Order_UploadByUser_Manager');
                break;
        }

        /** @var AmazonManager|EbayManager|WalmartManager $manager */
        $manager->setIdentifierByAccount($account);
        return $manager;
    }

    //########################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_order_uploadByUser/getPopupGrid',
            array('component' => $this->_component)
        );
    }

    //########################################

    public function setComponent($component)
    {
        $this->_component = $component;
    }

    //########################################
}
