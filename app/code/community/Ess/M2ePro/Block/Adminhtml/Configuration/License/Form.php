<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_License_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationLicenseForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/license.phtml');

        // ---------------------------------------

        $this->setPageHelpLink("global-settings");
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_license/confirmKey'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/License.js');
        $this->initPopUp();
    }

    protected function _beforeToHtml()
    {
        try {
            Mage::helper('M2ePro/Client')->updateLocationData(true);
        // @codingStandardsIgnoreLine
        } catch (Exception $exception) {}

        $this->key = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getKey());

        $this->licenseData = array(
            'domain'     => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getDomain()),
            'ip'         => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getIp()),
            'info'       => array(
                'email' => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getEmail()),
            ),
            'valid'      => array(
                'domain' => Mage::helper('M2ePro/Module_License')->isValidDomain(),
                'ip'     => Mage::helper('M2ePro/Module_License')->isValidIp()
            ),
            'connection' => array(
                'domain'    => Mage::helper('M2ePro/Client')->getDomain(),
                'ip'        => Mage::helper('M2ePro/Client')->getIp(),
                'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
            )
        );

        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        // ---------------------------------------
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $userInfo['city'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY, $defaultStoreId);
        $userInfo['postal_code'] = Mage::getStoreConfig(
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE,
            $defaultStoreId
        );


        $userInfo['country'] = Mage::helper('core')->getDefaultCountry($defaultStoreId);
        // ---------------------------------------

        $this->licenseFormData = $userInfo;

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'setLocation(\''.$this->getUrl('M2ePro/adminhtml_configuration_license/refreshStatus').'\');',
            'class'   => 'refresh_status'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('refresh_status', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Use Another Key'),
            'onclick' => 'LicenseObj.changeLicenseKeyPopup();',
            'class'   => 'change_license'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_license', $buttonBlock);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'LicenseObj.confirmLicenseKey();',
            'class'   => 'confirm_key'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_key', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function initPopUp()
    {
        $themeFileName = 'prototype/windows/themes/magento.css';
        $themeLibFileName = 'lib/'.$themeFileName;
        $themeFileFound = false;
        $skinBaseDir = Mage::getDesign()->getSkinBaseDir(
            array(
                '_package' => Mage_Core_Model_Design_Package::DEFAULT_PACKAGE,
                '_theme' => Mage_Core_Model_Design_Package::DEFAULT_THEME,
            )
        );

        if (!$themeFileFound && is_file($skinBaseDir .'/'.$themeLibFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
        }

        if (!$themeFileFound && is_file(Mage::getBaseDir().'/js/'.$themeFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        if (!$themeFileFound) {
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        $this->getLayout()->getBlock('head')
            ->addJs('prototype/window.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css');

        return $this;
    }

    //########################################
}
