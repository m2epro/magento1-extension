<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Installation_Registration_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
M2E Pro requires activation for further work. To activate your installation,
you should obtain a <strong>License Key</strong>. For more details, please read our
<a href="%1%" target="_blank">Privacy Policy</a>.<br/><br/>
Fill out the form below with the necessary data. The information will be used to create your
<strong>Account</strong> on <a href="%2%" target="_blank">M2E Pro Clients Portal</a> and a new
License Key will be generated automatically.<br/><br/>
Having access to your Account on clients.m2epro.com will let you manage your Subscription,
monitor Trial and Paid Period terms, control License Key(s) data, etc.
HTML
                ,
                Mage::helper('M2ePro/Module_Support')->getWebsiteUrl() . 'privacy',
                Mage::helper('M2ePro/Module_Support')->getClientsPortalUrl()
            )
        );

        if (!$this->getData('isLicenseStepFinished')) {
            Mage::helper('M2ePro/View')->getCssRenderer()->add(
                <<<CSS
#licence_agreement .admin__field {
    padding-top: 8px;
}
CSS
            );
        }

        parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $countries = Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray();
        unset($countries[0]);
        $this->setData('available_countries', $countries);

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $userInfo['city'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $userInfo['postal_code'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $userInfo['country'] = Mage::getStoreConfig('general/country/default', $defaultStoreId);

        $earlierFormData = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key')
            ->getData('value');

        if ($earlierFormData) {
            $earlierFormData = $earlierFormData->getValue();
            $earlierFormData = (array)Mage::helper('M2ePro')->jsonDecode($earlierFormData);
            $userInfo = array_merge($userInfo, $earlierFormData);
        }

        $this->setData('user_info', $userInfo);
        $this->setData('isLicenseStepFinished', $earlierFormData && Mage::helper('M2ePro/Module_License')->getKey());

        return parent::_beforeToHtml();
    }

    protected function _prepareForm()
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'action'  => '',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'block_notice_wizard_installation_step_license',
            array(
                'legend'      => '',
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'form_email',
            'text',
            array(
                'name'     => 'email',
                'label'    => Mage::helper('M2ePro')->__('Email'),
                'value'    => $this->getUserInfoValue('email'),
                'class'    => 'M2ePro-validate-email validate-length maximum-length-80',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'first_name',
            'text',
            array(
                'name'     => 'firstname',
                'label'    => Mage::helper('M2ePro')->__('First Name'),
                'value'    => $this->getUserInfoValue('firstname'),
                'class'    => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'last_name',
            'text',
            array(
                'name'     => 'lastname',
                'label'    => Mage::helper('M2ePro')->__('Last Name'),
                'value'    => $this->getUserInfoValue('lastname'),
                'class'    => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'phone',
            'text',
            array(
                'name'     => 'phone',
                'label'    => Mage::helper('M2ePro')->__('Phone'),
                'value'    => $this->getUserInfoValue('phone'),
                'class'    => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'country',
            'select',
            array(
                'name'     => 'country',
                'label'    => Mage::helper('M2ePro')->__('Country'),
                'value'    => $this->getUserInfoValue('country'),
                'class'    => 'validate-length maximum-length-40',
                'values'   => $this->getData('available_countries'),
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'city',
            'text',
            array(
                'name'     => 'city',
                'label'    => Mage::helper('M2ePro')->__('City'),
                'value'    => $this->getUserInfoValue('city'),
                'class'    => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        $fieldset->addField(
            'postal_code',
            'text',
            array(
                'name'     => 'postal_code',
                'label'    => Mage::helper('M2ePro')->__('Postal Code'),
                'value'    => $this->getUserInfoValue('postal_code'),
                'class'    => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished')
            )
        );

        if (!$this->getData('isLicenseStepFinished')) {
            $fieldset->addField(
                'licence_agreement',
                'checkbox',
                array(
                    'field_extra_attributes' => 'id="licence_agreement"',
                    'name'                   => 'licence_agreement',
                    'class'                  => 'admin__control-checkbox',
                    'style'                  => 'padding-top: 8px;',
                    'label'                  => Mage::helper('M2ePro')->__('Terms and Privacy'),
                    'checked'                => false,
                    'value'                  => 1,
                    'required'               => true,
                    'after_element_html'     => Mage::helper('M2ePro')->__(
                        <<<HTML
&nbsp; I agree to terms and <a href="https://m2epro.com/privacy-policy" target="_blank">privacy policy</a>
HTML
                    )
                )
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getCountryLabelByCode($code, $type = 'input')
    {
        foreach ($this->getData('available_countries') as $country) {
            if ($country['value'] == $code) {
                return $country['label'];
            }
        }

        if (!empty($code)) {
            return $code;
        }

        if ($type == 'input') {
            return '';
        }

        $notSelectedWord = Mage::helper('M2ePro')->__('not selected');
        return <<<HTML
<span style="font-style: italic; color: grey;">
    [{$notSelectedWord}]
</span>
HTML;
    }

    protected function getUserInfoValue($name, $type = 'input')
    {
        $info = $this->getData('user_info');

        if (!empty($info[$name])) {
            return $info[$name];
        }

        if ($type == 'input') {
            return '';
        }

        $notSelectedWord = Mage::helper('M2ePro')->__('not selected');
        return <<<HTML
<span style="font-style: italic; color: grey;">
    [{$notSelectedWord}]
</span>
HTML;
    }

    //########################################
}
