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

        $this->setPageHelpLink("x/CwAJAQ");
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_license/confirmKey'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/LicenseHandler.js');
        $this->initPopUp();
    }

    protected function _beforeToHtml()
    {
        try {
            Mage::helper('M2ePro/Client')->updateBackupConnectionData(true);
        } catch (Exception $exception) {}

        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();

        // Set data for form
        // ---------------------------------------
        $this->key = Mage::helper('M2ePro')->escapeHtml($licenseHelper->getKey());
        $this->status = $licenseHelper->getStatus();

        $this->licenseData = array(
            'domain' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getDomain()),
            'ip' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getIp()),
            'info' => array(
                'email' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getEmail()),
            ),
            'valid' => array(
                'domain' => $licenseHelper->isValidDomain(),
                'ip' => $licenseHelper->isValidIp()
            ),
            'connection' => array(
                'domain' => $cacheConfig->getGroupValue('/license/connection/', 'domain'),
                'ip' => $cacheConfig->getGroupValue('/license/connection/', 'ip'),
                'directory' => $cacheConfig->getGroupValue('/license/connection/', 'directory')
            )
        );

        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        // ---------------------------------------
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $userInfo['city'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $userInfo['postal_code'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $userInfo['country'] = Mage::getStoreConfig('general/country/default', $defaultStoreId);
        // ---------------------------------------

        $this->licenseFormData = $userInfo;

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'setLocation(\''.$this->getUrl('M2ePro/adminhtml_configuration_license/refreshStatus').'\');',
            'class'   => 'refresh_status'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('refresh_status',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Use Another Key'),
            'onclick' => 'LicenseHandlerObj.changeLicenseKeyPopup();',
            'class'   => 'change_license'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_license',$buttonBlock);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'LicenseHandlerObj.confirmLicenseKey();',
            'class'   => 'confirm_key'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_key',$buttonBlock);
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