<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_General_Form
 *
 * @method Ess_M2ePro_Helper_Component_Ebay_Configuration getConfigurationHelper()
 * @method bool getEnabledFeedback()
 * @method bool getEnabledKtype()
 * @method Ess_M2ePro_Model_Marketplace getUkMarketplace()
 * @method Ess_M2ePro_Model_Marketplace getDeMarketplace()
 * @method Ess_M2ePro_Model_Marketplace getAuMarketplace()
 * @method Ess_M2ePro_Model_Marketplace getMotorsMarketplace()
 * @method array getAvailabilityAttributes()
 * @method int getUkEpidCount()
 * @method int getUkEpidCustomCount()
 * @method int getDeEpidCount()
 * @method int getDeEpidCustomCount()
 * @method int getAuEpidCount()
 * @method int getAuEpidCustomCount()
 * @method int getMotorsEpidCount()
 * @method int getMotorsEpidCustomCount()
 * @method int getKtypeCount()
 * @method int getKtypeCustomCount()
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

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws Zend_Db_Statement_Exception
     */
    protected function _beforeToHtml()
    {
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $eBayMotorsHelper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $eBayViewHelper = Mage::helper('M2ePro/View_Ebay');
        $eBayHelper = Mage::helper('M2ePro/Component_Ebay');

        //----------------------------------------

        $this->setData('configuration_helper', Mage::helper('M2ePro/Component_Ebay_Configuration'));

        $this->setData('enabled_feedback', $eBayViewHelper->isFeedbacksShouldBeShown());
        $this->setData('enabled_ktype', $eBayMotorsHelper->isKTypeMarketplacesEnabled());

        $this->setData(
            'uk_marketplace',
            $eBayHelper->getCachedObject('Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK)
        );
        $this->setData(
            'de_marketplace',
            $eBayHelper->getCachedObject('Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE)
        );
        $this->setData(
            'au_marketplace',
            $eBayHelper->getCachedObject('Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_AU)
        );
        $this->setData(
            'motors_marketplace',
            $eBayHelper->getCachedObject('Marketplace', Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS)
        );
        $this->setData(
            'availability_attributes',
            $magentoAttributeHelper->filterByInputTypes(
                $magentoAttributeHelper->getAll(), array('textarea'), array('text')
            )
        );

        list($count, $customCount) = $eBayMotorsHelper->getDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_UK
        );
        $this->setData('uk_epid_custom_count', $customCount);
        $this->setData('uk_epid_count', $count);

        list($count, $customCount) = $eBayMotorsHelper->getDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_DE
        );
        $this->setData('de_epid_custom_count', $customCount);
        $this->setData('de_epid_count', $count);

        list($count, $customCount) = $eBayMotorsHelper->getDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_AU
        );
        $this->setData('au_epid_custom_count', $customCount);
        $this->setData('au_epid_count', $count);

        list($count, $customCount) = $eBayMotorsHelper->getDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_MOTOR
        );
        $this->setData('motors_epid_custom_count', $customCount);
        $this->setData('motors_epid_count', $count);

        list($count, $customCount) = $eBayMotorsHelper->getDictionaryRecordCount(
            Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE
        );
        $this->setData('ktype_custom_count', $customCount);
        $this->setData('ktype_count', $count);

        return parent::_beforeToHtml();
    }

    //########################################
}
