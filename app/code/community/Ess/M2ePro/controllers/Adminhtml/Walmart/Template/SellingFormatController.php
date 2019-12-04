<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Selling Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/EditHandler.js')
            ->addJs('M2ePro/Walmart/Template/EditHandler.js')
            ->addJs('M2ePro/Walmart/Template/SellingFormatHandler.js')
            ->addJs('M2ePro/AttributeHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/L4taAQ");

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

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_walmart_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_sellingFormat_edit')
            )
             ->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        // ---------------------------------------
        $data = array();

        $keys = array(
            'title',
            'marketplace_id',

            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value',

            'price_mode',
            'price_coefficient',
            'price_custom_attribute',

            'map_price_mode',
            'map_price_custom_attribute',

            'price_variation_mode',

            'promotions_mode',

            'sale_time_start_date_mode',
            'sale_time_end_date_mode',

            'sale_time_start_date_custom_attribute',
            'sale_time_end_date_custom_attribute',

            'sale_time_start_date_value',
            'sale_time_end_date_value',

            'item_weight_mode',
            'item_weight_custom_value',
            'item_weight_custom_attribute',

            'price_vat_percent',

            'lag_time_mode',
            'lag_time_value',
            'lag_time_custom_attribute',

            'product_tax_code_mode',
            'product_tax_code_custom_value',
            'product_tax_code_custom_attribute',

            'must_ship_alone_mode',
            'must_ship_alone_value',
            'must_ship_alone_custom_attribute',

            'ships_in_original_packaging_mode',
            'ships_in_original_packaging_value',
            'ships_in_original_packaging_custom_attribute',

            'shipping_override_rule_mode',

            'attributes_mode'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if ($data['sale_time_start_date_value'] === '') {
            $data['sale_time_start_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_time_start_date_value'] = Mage::helper('M2ePro')->getDate(
                $data['sale_time_start_date_value'], false, 'Y-m-d 00:00:00'
            );
        }

        if ($data['sale_time_end_date_value'] === '') {
            $data['sale_time_end_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_time_end_date_value'] = Mage::helper('M2ePro')->getDate(
                $data['sale_time_end_date_value'], false, 'Y-m-d 00:00:00'
            );
        }

        $data['title'] = strip_tags($data['title']);

        $data['attributes'] = Mage::helper('M2ePro')->jsonEncode(
            $this->getComparedData($post, 'attributes_name', 'attributes_value')
        );
        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Template_SellingFormat')->load($id);

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldData = $snapshotBuilder->getSnapshot();

        $model->addData($data)->save();

        $this->updateServices($post, $model->getId());
        $this->updatePromotions($post, $model->getId());

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ChangeProcessor');
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getData(
                array('id', 'status'), array('only_physical_units' => true)
            )
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));

        $params = array('id' => $model->getId());
        if ($this->getRequest()->getParam('marketplace_locked')) {
            $params['marketplace_id'] = $model->getData('marketplace_id');
        }

        $url = Mage::helper('M2ePro')->getBackUrl(
            '*/adminhtml_walmart_template/index', array(), array(
            'edit' => $params
            )
        );

        return $this->_redirectUrl($url);
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

    protected function updateServices($data, $templateId)
    {
        $collection = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverride')
            ->getCollection()
            ->addFieldToFilter('template_selling_format_id', (int)$templateId);

        foreach ($collection as $item) {
            $item->delete();
        }

        if (empty($data['shipping_override_rule'])) {
            return;
        }

        $newServices = array();
        foreach ($data['shipping_override_rule'] as $serviceData) {
            $newServices[] = array(
                'template_selling_format_id' => $templateId,
                'method'              => $serviceData['method'],
                'is_shipping_allowed' => $serviceData['is_shipping_allowed'],
                'region'              => $serviceData['region'],
                'cost_mode'           => !empty($serviceData['cost_mode']) ? $serviceData['cost_mode'] : 0,
                'cost_value'          => !empty($serviceData['cost_value']) ? $serviceData['cost_value'] : 0,
                'cost_attribute'      => !empty($serviceData['cost_attribute']) ? $serviceData['cost_attribute'] : ''
            );
        }

        if (empty($newServices)) {
            return;
        }

        $coreRes = Mage::getSingleton('core/resource');
        $coreRes->getConnection('core_write')->insertMultiple(
            $coreRes->getTableName('M2ePro/Walmart_Template_SellingFormat_ShippingOverride'), $newServices
        );
    }

    protected function updatePromotions($data, $templateId)
    {
        $collection = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Promotion')->getCollection()
                            ->addFieldToFilter('template_selling_format_id', (int)$templateId);

        foreach ($collection as $item) {
            $item->delete();
        }

        if (empty($data['promotions'])) {
            return;
        }

        $newPromotions = array();
        foreach ($data['promotions'] as $promotionData) {
            if (!empty($promotionData['from_date']['value'])) {
                $startDate = Mage::helper('M2ePro')->getDate(
                    $promotionData['from_date']['value'], false, 'Y-m-d H:i'
                );
            } else {
                $startDate = Mage::helper('M2ePro')->getCurrentGmtDate(
                    false, 'Y-m-d H:i'
                );
            }

            if (!empty($promotionData['to_date']['value'])) {
                $endDate = Mage::helper('M2ePro')->getDate(
                    $promotionData['to_date']['value'], false, 'Y-m-d H:i'
                );
            } else {
                $endDate = Mage::helper('M2ePro')->getCurrentGmtDate(
                    false, 'Y-m-d H:i'
                );
            }

            $newPromotions[] = array(
                'template_selling_format_id'   => $templateId,
                'price_mode'                   => $promotionData['price']['mode'],
                'price_attribute'              => $promotionData['price']['attribute'],
                'price_coefficient'            => $promotionData['price']['coefficient'],
                'start_date_mode'              => $promotionData['from_date']['mode'],
                'start_date_attribute'         => $promotionData['from_date']['attribute'],
                'start_date_value'             => $startDate,
                'end_date_mode'                => $promotionData['to_date']['mode'],
                'end_date_attribute'           => $promotionData['to_date']['attribute'],
                'end_date_value'               => $endDate,
                'comparison_price_mode'        => $promotionData['comparison_price']['mode'],
                'comparison_price_attribute'   => $promotionData['comparison_price']['attribute'],
                'comparison_price_coefficient' => $promotionData['comparison_price']['coefficient'],
                'type'                         => $promotionData['type'],
            );
        }

        if (empty($newPromotions)) {
            return;
        }

        $coreRes = Mage::getSingleton('core/resource');
        $coreRes->getConnection('core_write')->insertMultiple(
            $coreRes->getTableName('M2ePro/Walmart_Template_SellingFormat_Promotion'), $newPromotions
        );
    }

    //########################################

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
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_SellingFormat', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_walmart_template/index');
    }

    //########################################

    public function getTaxCodesPopupHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_TaxCodes $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_template_sellingFormat_taxCodes',
            '',
            array(
                'marketplaceId' => $this->getRequest()->getParam('marketplaceId'),
                'noSelection'   => $this->getRequest()->getParam('noSelection')
            )
        );

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getTaxCodesGridAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_TaxCodes $block */
        $block = $this->getLayout()
            ->createBlock(
                'M2ePro/adminhtml_walmart_template_sellingFormat_taxCodes_grid',
                '',
                array(
                    'marketplaceId' => $this->getRequest()->getParam('marketplaceId'),
                    'noSelection'   => $this->getRequest()->getParam('noSelection')
                )
            );

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################
}
