<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingEditForm');
        $this->setTemplate('M2ePro/walmart/listing/edit/form.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_walmart_listing/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $formData = $this->getFormData();

        $this->sellingFormatTemplates = $this->getTemplates(
            'Template_SellingFormat', isset($formData['marketplace_id']) ? $formData['marketplace_id'] : null
        );
        $this->descriptionsTemplates = $this->getTemplates('Template_Description');
        $this->synchronizationsTemplates = $this->getTemplates('Template_Synchronization');

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getTemplates($model, $marketplaceId = null)
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection($model);
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $marketplaceId && $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('id', 'title'));

        $collection->setOrder('main_table.title', Varien_Data_Collection::SORT_ORDER_ASC);

        $data = $collection->toArray();

        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        return $data['items'];
    }

    //########################################

    public function getFormData()
    {
        if (!$this->getRequest()->getParam('id')) {
            return $this->getDefaults();
        }

        return $this->getListing()->getData();
    }

    protected function getDefaults()
    {
        return array(
            'title' => '',
            'template_selling_format_id' => '',
            'template_description_id' => '',
            'template_synchronization_id' => '',
        );
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->listing === null) {
            $this->listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################
}
