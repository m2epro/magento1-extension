<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $viewComponentHelper = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialize view
        // ---------------------------------------
        $view = Mage::helper('M2ePro/View')->getCurrentView();
        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper($view);
        // ---------------------------------------

        // Initialization block
        // ---------------------------------------
        $this->setId($view . 'AccountGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection of accounts
        $collection = $this->getCollection();
        if (is_null($collection)) {
            $collection = Mage::getModel('M2ePro/Account')->getCollection();
        }

        $components = $this->viewComponentHelper->getActiveComponents();
        $collection->addFieldToFilter('main_table.component_mode', array('in'=>$components));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'onclick_action' => 'AccountGridHandlerObj.accountHandler.on_delete_popup',
                    'field'     => 'id',
                )
            )
        ));

        return parent::_prepareColumns();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set delete action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => ''
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/accountGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return Mage::helper('M2ePro/View')
            ->getUrl($row, 'account', 'edit', array('id' => $row->getData('id')));
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm')->toHtml();

        $text = 'Attention! By applying this action you delete the Account data only from current M2E Pro instance. ';
        $text .= 'It does not affect the Subscription status and Billing process for this Channel Account. <br><br>';
        $text .= 'To delete Channel Account which you don\'t need to manage under M2E Pro Subscription Plan, ';
        $text .= 'go to the <a href="%url%" target="_blank">Clients Portal</a>.';
        $text = Mage::helper('M2ePro')->__($text, Mage::helper('M2ePro/Module_Support')->getClientsPortalBaseUrl());

        $translations = Mage::helper('M2ePro')->jsonEncode(array(
            'on_delete_account_message' => $text
        ));

        $url = Mage::helper('M2ePro')->jsonEncode(array(
            '*/*/delete' => $this->getUrl('*/*/delete')
        ));

        $js = <<<JS

        M2ePro.translator.add({$translations});
        M2ePro.url.add({$url});

        if (typeof AccountGridHandlerObj != 'undefined') {
            AccountGridHandlerObj.afterInitPage();
        }

        Event.observe(window, 'load', function() {
            setTimeout(function() {
                AccountGridHandlerObj = new AccountGridHandler('{$this->getId()}');
                AccountGridHandlerObj.afterInitPage();
            }, 350);
        });
JS;

        return '<div style="display: none" id="on_delete_account_template">'.$confirm.'</div>'
                . '<script>'.$js.'</script>'
                . parent::_toHtml();
    }

    //########################################
}