<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_MotorController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    public function addViewAction()
    {
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add');
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    // ---------------------------------------

    public function viewItemAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Item $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_item');
        $block->setListingProductId($entityId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function viewFilterAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Filter $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_filter');
        $block->setListingProductId($entityId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function viewGroupAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_group');
        $block->setListingProductId($listingProductId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function addItemGridAction()
    {
        $motorsType = $this->getRequest()->getParam('motors_type');
        $motorsType = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey($motorsType);

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Grid Epid_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_item_'.$motorsType.'_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function addFilterGridAction()
    {
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Filter_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_filter_grid');
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function addGroupGridAction()
    {
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Group_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_group_grid');
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    // ---------------------------------------

    public function viewItemGridAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Item_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_item_grid');
        $block->setListingProductId($entityId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function viewFilterGridAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Filter_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_filter_grid');
        $block->setListingProductId($entityId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    public function viewGroupGridAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_group_grid');
        $block->setListingProductId($listingProductId);
        $block->setMotorsType($motorsType);
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function viewGroupContentAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $model */
        $model = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($groupId);

        if ($model->isModeItem()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_group_items');
        } else {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_group_filters');
        }

        $block->setGroupId($groupId);

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function saveFilterAction()
    {
        $post = $this->getRequest()->getPost();
        parse_str($post['conditions'], $post['conditions']);

        foreach ($post['conditions'] as $key => $value) {
            if (empty($value) || $key == 'massaction') {
                unset($post['conditions'][$key]);
            }
        }

        $data = array(
            'title' => $post['title'],
            'type' => $post['type'],
            'note' => $post['note'],
            'conditions' => json_encode($post['conditions']),
        );

        $model = Mage::getModel('M2ePro/Ebay_Motor_Filter');
        $model->addData($data)->save();

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function saveAsGroupAction()
    {
        $post = $this->getRequest()->getPost();

        $data = array(
            'title' => $post['title'],
            'type' => $post['type'],
            'mode' => $post['mode'],
        );

        if ($data['mode'] == Ess_M2ePro_Model_Ebay_Motor_Group::MODE_ITEM) {

            parse_str($post['items'], $post['items']);

            $itemsData = array();
            foreach ($post['items'] as $id => $note) {
                $itemsData[] = array(
                    'id' => $id,
                    'note' => $note
                );
            }

            $data['items_data'] = Mage::helper('M2ePro/Component_Ebay_Motors')->buildItemsAttributeValue(
                $itemsData
            );
        }

        $model = Mage::getModel('M2ePro/Ebay_Motor_Group');
        $model->addData($data)->save();

        if ($data['mode'] == Ess_M2ePro_Model_Ebay_Motor_Group::MODE_FILTER) {

            $filtersIds = $post['items'];
            if (!is_array($filtersIds)) {
                $filtersIds = explode(',', $filtersIds);
            }

            $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_motor_filter_to_group');
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');

            foreach ($filtersIds as $filterId) {
                $connWrite->insert($tableName, array(
                        'filter_id' => $filterId,
                        'group_id' => $model->getId(),
                    )
                );
            }
        }

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function setNoteToFiltersAction()
    {
        $filtersIds = $this->getRequest()->getParam('filters_ids');
        $note = $this->getRequest()->getParam('note');

        if (!is_array($filtersIds)) {
            $filtersIds = explode(',', $filtersIds);
        }

        $tableName = Mage::getResourceModel('M2ePro/Ebay_Motor_Filter')->getMainTable();

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $connWrite->update($tableName, array(
                'note' => $note
            ), '`id` IN ('.implode(',', $filtersIds).')'
        );

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function removeItemFromGroupAction()
    {
        $itemsIds = $this->getRequest()->getParam('items_ids');
        $groupId = $this->getRequest()->getParam('group_id');

        if (!is_array($itemsIds)) {
            $itemsIds = explode(',', $itemsIds);
        }

        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $model */
        $model = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($groupId);
        $items = $model->getItems();

        foreach ($itemsIds as $itemId) {
            unset($items[$itemId]);
        }

        if (count($items) > 0) {
            $model->setItemsData(Mage::helper('M2ePro/Component_Ebay_Motors')->buildItemsAttributeValue($items));
            $model->save();
        } else {
            $model->deleteInstance();
        }

        $this->getResponse()->setBody(0);
    }

    //---------------------------------------

    public function removeItemFromProductAction()
    {
        $itemsIds = $this->getRequest()->getParam('items_ids');
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        if (!is_array($itemsIds)) {
            $itemsIds = explode(',', $itemsIds);
        }

        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $entityId);

        $motorsAttribute = Mage::helper('M2ePro/Component_Ebay_Motors')->getAttribute($motorsType);
        $attributeValue = $listingProduct->getMagentoProduct()->getAttributeValue($motorsAttribute);

        $motorsData = Mage::helper('M2ePro/Component_Ebay_Motors')->parseAttributeValue($attributeValue);

        foreach ($itemsIds as $itemId) {
            unset($motorsData['items'][$itemId]);
        }

        $attributeValue = Mage::helper('M2ePro/Component_Ebay_Motors')->buildAttributeValue($motorsData);

        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateMotorsAttributesData(
            $listingProduct->getListingId(), array($entityId),
            $motorsAttribute, $attributeValue, true
        );

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function removeFilterAction()
    {
        $filtersIds = $this->getRequest()->getParam('filters_ids');

        if (!is_array($filtersIds)) {
            $filtersIds = explode(',', $filtersIds);
        }

        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Motor_Filter_Collection $filters */
        $filters = Mage::getModel('M2ePro/Ebay_Motor_Filter')->getCollection()
            ->addFieldToFilter('id', array('in' => $filtersIds));

        foreach ($filters->getItems() as $filter) {
            $filter->deleteInstance();
        }

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function removeFilterFromGroupAction()
    {
        $filtersIds = $this->getRequest()->getParam('filters_ids');
        $groupId = $this->getRequest()->getParam('group_id');

        if (!is_array($filtersIds)) {
            $filtersIds = explode(',', $filtersIds);
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $coreResourceModel = Mage::getSingleton('core/resource');

        $filterGroupRelation = $coreResourceModel->getTableName('m2epro_ebay_motor_filter_to_group');

        $connWrite->delete($filterGroupRelation, array(
            'filter_id in (?)' => $filtersIds,
            'group_id = ?' => $groupId,
        ));

        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $model */
        $model = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($groupId);

        if (count($model->getFiltersIds()) == 0) {
            $model->deleteInstance();
        }

        $this->getResponse()->setBody(0);
    }

    //---------------------------------------

    public function removeFilterFromProductAction()
    {
        $filtersIds = $this->getRequest()->getParam('filters_ids');
        $entityId = $this->getRequest()->getParam('entity_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        if (!is_array($filtersIds)) {
            $filtersIds = explode(',', $filtersIds);
        }

        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $entityId);

        $motorsAttribute = Mage::helper('M2ePro/Component_Ebay_Motors')->getAttribute($motorsType);
        $attributeValue = $listingProduct->getMagentoProduct()->getAttributeValue($motorsAttribute);

        $motorsData = Mage::helper('M2ePro/Component_Ebay_Motors')->parseAttributeValue($attributeValue);

        foreach ($filtersIds as $filterId) {
            if (($key = array_search($filterId, $motorsData['filters'])) !== false) {
                unset($motorsData['filters'][$key]);
            }
        }

        $attributeValue = Mage::helper('M2ePro/Component_Ebay_Motors')->buildAttributeValue($motorsData);

        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateMotorsAttributesData(
            $listingProduct->getListingId(), array($entityId),
            $motorsAttribute, $attributeValue, true
        );

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function removeGroupFromListingProductAction()
    {
        $groupsIds = $this->getRequest()->getParam('groups_ids');
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $motorsType = $this->getRequest()->getParam('motors_type');

        if (!is_array($groupsIds)) {
            $groupsIds = explode(',', $groupsIds);
        }

        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

        $motorsAttribute = Mage::helper('M2ePro/Component_Ebay_Motors')->getAttribute($motorsType);
        $attributeValue = $listingProduct->getMagentoProduct()->getAttributeValue($motorsAttribute);

        $motorsData = Mage::helper('M2ePro/Component_Ebay_Motors')->parseAttributeValue($attributeValue);

        foreach ($groupsIds as $filterId) {
            if (($key = array_search($filterId, $motorsData['groups'])) !== false) {
                unset($motorsData['groups'][$key]);
            }
        }

        $attributeValue = Mage::helper('M2ePro/Component_Ebay_Motors')->buildAttributeValue($motorsData);

        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateMotorsAttributesData(
            $listingProduct->getListingId(), array($listingProductId),
            $motorsAttribute, $attributeValue, true
        );

        $this->getResponse()->setBody(0);
    }

    // ---------------------------------------

    public function removeGroupAction()
    {
        $groupsIds = $this->getRequest()->getParam('groups_ids');

        if (!is_array($groupsIds)) {
            $groupsIds = explode(',', $groupsIds);
        }

        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Motor_Group_Collection $groups */
        $groups = Mage::getModel('M2ePro/Ebay_Motor_Group')->getCollection()
            ->addFieldToFilter('id', array('in' => $groupsIds));

        foreach ($groups->getItems() as $group) {
            $group->deleteInstance();
        }

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function updateMotorsDataAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequest()->getParam('listing_products_ids');
        $motorsType = $this->getRequest()->getParam('motors_type');
        $overwrite = $this->getRequest()->getParam('overwrite', 0) == 1;

        $items = $this->getRequest()->getParam('items');
        $filtersIds = $this->getRequest()->getParam('filters_ids');
        $groupsIds = $this->getRequest()->getParam('groups_ids');

        if (!is_array($listingProductIds)) {
            $listingProductIds = explode(',', $listingProductIds);
        }

        parse_str($items, $items);
        $itemsData = array();
        foreach ($items as $id => $note) {
            $itemsData[] = array(
                'id' => $id,
                'note' => $note
            );
        }

        if (!empty($filtersIds) && !is_array($filtersIds)) {
            $filtersIds = explode(',', $filtersIds);
        }

        if (!empty($groupsIds) && !is_array($groupsIds)) {
            $groupsIds = explode(',', $groupsIds);
        }

        $attrValue = Mage::helper('M2ePro/Component_Ebay_Motors')->buildAttributeValue(array(
            'items' => $itemsData,
            'filters' => $filtersIds,
            'groups' => $groupsIds
        ));

        $motorsAttribute = Mage::helper('M2ePro/Component_Ebay_Motors')
            ->getAttribute($motorsType);

        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateMotorsAttributesData(
            $listingId, $listingProductIds, $motorsAttribute, $attrValue, $overwrite
        );

        $this->getResponse()->setBody(0);
    }

    //########################################

    public function addCustomMotorsRecordAction()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getParam('motors_type');

        $insertData = $this->getRequest()->getParam('row', array());
        foreach ($insertData as &$item) {
            $item == '' && $item = null;
        }
        $insertData['is_custom'] = 1;

        $tableName = $helper->getDictionaryTable($motorsType);
        $idKey = $helper->getIdentifierKey($motorsType);

        $existedItem = Mage::getSingleton('core/resource')->getConnection('core/read')
            ->select()
            ->from($tableName)
            ->where("{$idKey} = ?", $insertData[$idKey])
            ->query()
            ->fetch();

        if ($existedItem) {

            return $this->getResponse()->setBody(json_encode(array(
                'result'  => false,
                'message' => Mage::helper('M2ePro')->__('Record with such identifier is already exists.')
            )));
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $connWrite->insert($tableName, $insertData);

        return $this->getResponse()->setBody(json_encode(array('result' => true)));
    }

    public function removeCustomMotorsRecordAction()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getParam('motors_type');
        $keyId = $this->getRequest()->getParam('key_id');

        if (!$motorsType || !$keyId) {

            return $this->getResponse()->setBody(json_encode(array(
                'result'  => false,
                'message' => Mage::helper('M2ePro')->__('The some of required fields are not filled up.')
            )));
        }

        $tableName = $helper->getDictionaryTable($motorsType);
        $idKey = $helper->getIdentifierKey($motorsType);

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $connWrite->delete($tableName, array("{$idKey} = ?" => $keyId));

        return $this->getResponse()->setBody(json_encode(array('result' => true)));
    }

    //########################################

    public function closeInstructionAction()
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/ebay/motors/','was_instruction_shown', 1);
    }

    //########################################
}