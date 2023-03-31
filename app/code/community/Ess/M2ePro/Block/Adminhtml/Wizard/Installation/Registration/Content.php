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
Fill out the form below with the required information. This information will be used to register
you on <a href="%2%" target="_blank">M2E Accounts</a> and auto-generate a new License Key.<br/><br/>
Access to <a href="%2%" target="_blank">M2E Accounts</a> will allow you to manage your Subscription, keep track
of your Trial and Paid terms, control your License Key details, and more.
HTML
                ,
                Mage::helper('M2ePro/Module_Support')->getPrivacyPolicyUrl(),
                Mage::helper('M2ePro/Module_Support')->getAccountsUrl()
            )
        );

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

        $userInfo['city'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY, $defaultStoreId);
        $userInfo['postal_code'] = Mage::getStoreConfig(
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE,
            $defaultStoreId
        );

        $userInfo['country'] = Mage::helper('core')->getDefaultCountry($defaultStoreId);

        $earlierFormData = array();

        if(Mage::helper('M2ePro/Module')->getRegistration()->isExistInfo()){
            $earlierFormDataObj = Mage::helper('M2ePro/Module')->getRegistration()->getInfo();

            $earlierFormData['email'] = $earlierFormDataObj->getEmail();
            $earlierFormData['first_name'] = $earlierFormDataObj->getFirstname();
            $earlierFormData['last_name'] = $earlierFormDataObj->getLastname();
            $earlierFormData['phone'] = $earlierFormDataObj->getPhone();
            $earlierFormData['country'] = $earlierFormDataObj->getCountry();
            $earlierFormData['city'] = $earlierFormDataObj->getCity();
            $earlierFormData['postal_code'] = $earlierFormDataObj->getPostalCode();
        }

        $userInfo = array_merge($userInfo, $earlierFormData);

        $this->setData('user_info', $userInfo);
        $this->setData(
            'isLicenseStepFinished',
            !empty($earlierFormData) && Mage::helper('M2ePro/Module_License')->getKey()
        );

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
            $privacyUrl = Mage::helper('M2ePro/Module_Support')->getPrivacyPolicyUrl();
            $termsAndConditionsUrl = Mage::helper('M2ePro/Module_Support')->getTermsAndConditionsUrl();
            $fieldset->addField(
                'licence_agreement',
                'checkbox',
                array(
                    'name'                   => 'licence_agreement',
                    'class'                  => 'admin__control-checkbox',
                    'label'                  => Mage::helper('M2ePro')->__('Terms and Privacy'),
                    'checked'                => false,
                    'value'                  => 1,
                    'required'               => true,
                    'after_element_html'     => Mage::helper('M2ePro')->__(
                        <<<HTML
&nbsp; I agree to <a href="$termsAndConditionsUrl" target="_blank">terms</a> and <a href="$privacyUrl" target="_blank">privacy policy</a>
HTML
                    )
                )
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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
