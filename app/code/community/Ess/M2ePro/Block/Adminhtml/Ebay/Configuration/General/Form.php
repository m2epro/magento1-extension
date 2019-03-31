<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationGeneralForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/configuration/general/form.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $configModel = Mage::helper('M2ePro/Module')->getConfig();

        $this->view_ebay_mode = $configModel->getGroupValue('/view/ebay/', 'mode');

        $this->view_ebay_feedbacks_notification_mode = (bool)(int)$configModel->getGroupValue(
            '/view/ebay/feedbacks/notification/','mode'
        );

        $this->is_ebay_feedbacks_enabled = Mage::helper('M2ePro/View_Ebay')->isFeedbacksShouldBeShown();

        $this->use_last_specifics_mode = (bool)(int)$configModel->getGroupValue(
            '/view/ebay/template/category/','use_last_specifics'
        );
        $this->check_the_same_product_already_listed_mode = (bool)(int)$configModel->getGroupValue(
            '/ebay/connector/listing/','check_the_same_product_already_listed'
        );

        $this->upload_images_mode = (int)$configModel->getGroupValue(
            '/ebay/description/','upload_images_mode'
        );
        $this->should_be_ulrs_secure = (int)$configModel->getGroupValue(
            '/ebay/description/','should_be_ulrs_secure'
        );
        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Marketplace $motorsMarketplace */
        $this->motors_marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS
        );
        $this->uk_marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK
        );
        $this->de_marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE
        );
        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Mysql4_Marketplace_Collection $ktypeMarketplaceCollection */
        $ktypeMarketplaceCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
        $ktypeMarketplaceCollection->addFieldToFilter('is_ktype', 1);
        $ktypeMarketplaceCollection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        $this->is_motors_ktypes_marketplace_enabled = (bool)$ktypeMarketplaceCollection->getSize();
        // ---------------------------------------

        // ---------------------------------------
        list($ebayDictionaryCount, $customDictionaryCount) = $this->getMotorsDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_MOTOR
        );
        $this->motors_epids_motor_dictionary_ebay_count   = $ebayDictionaryCount;
        $this->motors_epids_motor_dictionary_custom_count = $customDictionaryCount;

        list($ebayDictionaryCount, $customDictionaryCount) = $this->getMotorsDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE
        );
        $this->motors_ktypes_dictionary_ebay_count   = $ebayDictionaryCount;
        $this->motors_ktypes_dictionary_custom_count = $customDictionaryCount;

        list($ebayDictionaryCount, $customDictionaryCount) = $this->getMotorsDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_UK
        );
        $this->motors_epids_uk_dictionary_ebay_count   = $ebayDictionaryCount;
        $this->motors_epids_uk_dictionary_custom_count = $customDictionaryCount;

        list($ebayDictionaryCount, $customDictionaryCount) = $this->getMotorsDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_DE
        );
        $this->motors_epids_de_dictionary_ebay_count   = $ebayDictionaryCount;
        $this->motors_epids_de_dictionary_custom_count = $customDictionaryCount;
        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $this->attributes_for_motors = $magentoAttributeHelper->filterByInputTypes(
            $magentoAttributeHelper->getAll(), array('textarea'), array('text')
        );

        $this->motors_epids_motor_attribute = $configModel->getGroupValue('/ebay/motors/','epids_motor_attribute');
        $this->motors_epids_uk_attribute    = $configModel->getGroupValue('/ebay/motors/','epids_uk_attribute');
        $this->motors_epids_de_attribute    = $configModel->getGroupValue('/ebay/motors/','epids_de_attribute');
        $this->motors_ktypes_attribute      = $configModel->getGroupValue('/ebay/motors/','ktypes_attribute');
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    private function getMotorsDictionaryRecordCount($type)
    {
        $resource = Mage::getSingleton('core/resource');
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $selectStmt = $resource->getConnection('core_read')
            ->select()
            ->from(
                $type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE
                    ? $dbHelper->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_ktype')
                    : $dbHelper->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_epid'),
                array(
                    'count' => new Zend_Db_Expr('COUNT(*)'),
                    'is_custom'
                )
            )
            ->group(array('is_custom'));

        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        if ($helper->isTypeBasedOnEpids($type)) {
            $selectStmt->where('scope = ?', $helper->getEpidsScopeByType($type));
        }

        $queryStmt = $selectStmt->query();
        $custom = $ebay = 0;

        while ($row = $queryStmt->fetch()) {
            $row['is_custom'] == 1 ? $custom = $row['count'] : $ebay = $row['count'];
        }

        return array((int)$ebay, (int)$custom);
    }

    //########################################
}