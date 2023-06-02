<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_viewComponentHelper;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId(Mage::helper('M2ePro/View')->getCurrentView() . 'AccountGrid');

        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date', array(
                'header'       => Mage::helper('M2ePro')->__('Creation Date'),
                'align'        => 'left',
                'width'        => '150px',
                'type'         => 'datetime',
                'format'       => Mage::app()->getLocale()
                                      ->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'        => 'create_date',
                'filter_index' => 'main_table.create_date'
            )
        );

        $this->addColumn(
            'update_date', array(
                'header'       => Mage::helper('M2ePro')->__('Update Date'),
                'align'        => 'left',
                'width'        => '150px',
                'type'         => 'datetime',
                'format'       => Mage::app()->getLocale()
                                      ->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'        => 'update_date',
                'filter_index' => 'main_table.update_date'
            )
        );

        $this->addColumn(
            'actions', array(
                'header'   => Mage::helper('M2ePro')->__('Actions'),
                'align'    => 'left',
                'width'    => '150px',
                'type'     => 'action',
                'index'    => 'actions',
                'filter'   => false,
                'sortable' => false,
                'getter'   => 'getId',
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_action',
                'actions'  => array(
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption'        => Mage::helper('M2ePro')->__('Delete'),
                        'onclick_action' => 'AccountGridObj.accountHandler.on_delete_popup',
                        'field'          => 'id',
                    )
                )
            )
        );

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
        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label' => Mage::helper('M2ePro')->__('Delete'),
                'url'   => ''
            )
        );
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


        $text = Mage::helper('M2ePro')->__(
            <<<HTML
<p>You are about to delete your eBay/Amazon/Walmart seller account from M2E Pro. This will remove the
account-related Listings and Products from the extension and disconnect the synchronization.
Your listings on the channel will <b>not</b> be affected.</p>
<p>Please confirm if you would like to delete the account.</p>
<p>Note: once the account is no longer connected to your M2E Pro, please remember to delete it from
<a href="%url%">M2E Accounts</a></p>
HTML
            ,
            Mage::helper('M2ePro/Module_Support')->getAccountsUrl()
        );

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'on_delete_account_message' => $text
            )
        );

        $url = Mage::helper('M2ePro')->jsonEncode(
            array(
                '*/*/delete' => $this->getUrl('*/*/delete')
            )
        );

        $js = <<<JS

        M2ePro.translator.add({$translations});
        M2ePro.url.add({$url});

        if (typeof AccountGridObj != 'undefined') {
            AccountGridObj.afterInitPage();
        }

        Event.observe(window, 'load', function() {
            setTimeout(function() {
                AccountGridObj = new AccountGrid('{$this->getId()}');
                AccountGridObj.afterInitPage();
            }, 350);
        });
JS;

        return '<div style="display: none" id="on_delete_account_template">'.$confirm.'</div>'
                . '<script>'.$js.'</script>'
                . parent::_toHtml();
    }

    //########################################
}
