<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_ShippingOverride_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    private $enabledMarketplaces = NULL;
    private $attributes = NULL;
    private $overrideDictionaryData = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateShippingOverrideEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/template/shippingOverride/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'onclick' => 'AmazonTemplateShippingOverrideHandlerObj.addRow();',
                'class' => 'add add_shipping_override_rule_button'
            ));
        $this->setChild('add_shipping_override_rule_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Remove'),
                'onclick' => 'AmazonTemplateShippingOverrideHandlerObj.removeRow(this);',
                'class' => 'delete icon-btn remove_shipping_override_rule_button'
            ));
        $this->setChild('remove_shipping_override_rule_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    public function getAttributes()
    {
        if (is_null($this->attributes)) {
            $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();
        }

        return $this->attributes;
    }

    //########################################

    public function getEnabledMarketplaces()
    {
        if (is_null($this->enabledMarketplaces)) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->enabledMarketplaces = $collection;
        }

        return $this->enabledMarketplaces->getItems();
    }

    //########################################

    public function getOverrideDictionaryData()
    {
        if (is_null($this->overrideDictionaryData)) {
            /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_shipping_override');

            $this->overrideDictionaryData = $connRead->select()->from($table)->query()->fetchAll();
        }

        return $this->overrideDictionaryData;
    }

    //########################################
}