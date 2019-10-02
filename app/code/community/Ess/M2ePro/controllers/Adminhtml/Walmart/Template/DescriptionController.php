<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Description Policies'));

        $this->getLayout()->getBlock('head')
                ->addJs('M2ePro/Template/EditHandler.js')
                ->addJs('M2ePro/Walmart/Template/EditHandler.js')
                ->addJs('M2ePro/Walmart/Template/Description/Handler.js')
                ->addJs('M2ePro/AttributeHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_walmart_template/index');
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()
                                    ->createBlock('M2ePro/adminhtml_walmart_template_description_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        /** @var Ess_M2ePro_Model_Walmart_Template_Description $templateModel */
        $id = $this->getRequest()->getParam('id');
        $templateModel = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Description')->load($id);

        if (!$templateModel->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $templateModel);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_description_edit'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Saving general data
        // ---------------------------------------
        $keys = array(
            'title',
            'title_mode',
            'title_template',

            'brand_mode',
            'brand_custom_value',
            'brand_custom_attribute',

            'manufacturer_mode',
            'manufacturer_custom_value',
            'manufacturer_custom_attribute',

            'manufacturer_part_number_mode',
            'manufacturer_part_number_custom_value',
            'manufacturer_part_number_custom_attribute',

            'count_per_pack_mode',
            'count_per_pack_custom_value',
            'count_per_pack_custom_attribute',

            'multipack_quantity_mode',
            'multipack_quantity_custom_value',
            'multipack_quantity_custom_attribute',

            'msrp_rrp_mode',
            'msrp_rrp_custom_attribute',

            'model_number_mode',
            'model_number_custom_value',
            'model_number_custom_attribute',

            'total_count_mode',
            'total_count_custom_value',
            'total_count_custom_attribute',

            'keywords_mode',
            'keywords_custom_value',
            'keywords_custom_attribute',

            'key_features_mode',

            'other_features_mode',

            'image_main_mode',
            'image_main_attribute',

            'image_variation_difference_mode',
            'image_variation_difference_attribute',

            'gallery_images_mode',
            'gallery_images_attribute',
            'gallery_images_limit',

            'attributes_mode',

            'description_mode',
            'description_template',
        );

        $dataForAdd = array();
        foreach ($keys as $key) {
            isset($post[$key]) && $dataForAdd[$key] = $post[$key];
        }

        $dataForAdd['title'] = strip_tags($dataForAdd['title']);

        $helper = Mage::helper('M2ePro');

        $dataForAdd['key_features']   = $helper->jsonEncode($post['key_features']);
        $dataForAdd['other_features'] = $helper->jsonEncode($post['other_features']);
        $dataForAdd['attributes']     = $helper->jsonEncode(
            $this->getComparedData($post, 'attributes_name', 'attributes_value')
        );

        /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_Description')->load($id);

        $oldData = array();
        if ($descriptionTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
            $snapshotBuilder->setModel($descriptionTemplate);

            $oldData = $snapshotBuilder->getSnapshot();
        }

        $descriptionTemplate->addData($dataForAdd)->save();
        // ---------------------------------------

        $id = $descriptionTemplate->getId();

        // Is Need Synchronize
        // ---------------------------------------
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
        $snapshotBuilder->setModel($descriptionTemplate);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Description_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_Description_AffectedListingsProducts');
        $affectedListingsProducts->setModel($descriptionTemplate);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Description_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'), array('only_physical_units' => true))
        );
        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit' => array('id' => $id)))
        );
    }

    protected function getComparedData($data, $keyName, $valueName)
    {
        $result = array();

        if (!isset($data[$keyName]) || !isset($data[$valueName])) {
            return $result;
        }

        $keyData = array_filter($data[$keyName]);
        $valueData = array_filter($data[$valueName]);

        if (count($keyData) !== count($valueData)) {
            return $result;
        }

        foreach ($keyData as $index => $value) {
            $result[] = array('name' => $value, 'value' => $valueData[$index]);
        }

        return $result;
    }

    // ---------------------------------------

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_Description', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%s record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################
}
