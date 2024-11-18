<?php

use Ess_M2ePro_Model_Resource_Amazon_Template_Shipping as Resource;

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Shipping_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    const REQUEST_PARAM_KEY_ACCOUNT_ID = 'account_id';

    /** @var Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping_Repository */
    private $dictionaryRepository;
    /** @var Ess_M2ePro_Model_Amazon_Account_Repository */
    private $accountRepository;
    /** @var array */
    private $formDataCache;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateShippingEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/template/shipping/form.phtml');

        $this->dictionaryRepository = Mage::getModel('M2ePro/Amazon_Dictionary_TemplateShipping_Repository');
        $this->accountRepository = Mage::getModel('M2ePro/Amazon_Account_Repository');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        if ($this->formDataCache !== null) {
            return $this->formDataCache;
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_Shipping $model */
        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $formData = array();
        if ($model) {
            $formData = $model->toArray();
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_Shipping_Builder $builder */
        $builder = Mage::getModel('M2ePro/Amazon_Template_Shipping_Builder');
        $default = $builder->getDefaultData();

       return $this->formDataCache = array_merge($default, $formData);
    }

    /**
     * @return string
     */
    public function getDuplicateHeaderText()
    {
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            return (string)Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('Add %component_name% Shipping Policy',
                    Mage::helper('M2ePro/Component_Amazon')->getTitle()
                )
            );
        }

        return (string)Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Add Shipping Policy')
        );
    }

    public function getAccountOptions()
    {
        $options = array(
            array(
                'value' => '',
                'label' => '',
                'attrs' => 'style="display: none;"',
            ),
        );

        $requestAccountId = $this->getRequest()
                                 ->getParam(self::REQUEST_PARAM_KEY_ACCOUNT_ID);

        $optionsData = $requestAccountId !== null
            ? $this->getOptionsDataForSingleAccount($requestAccountId)
            : $this->getOptionsDataForManyAccounts();

        foreach ($optionsData as $datum) {
            /** @var Ess_M2ePro_Model_Account $account */
            $account = $datum['account'];
            $options[] = array(
                'value' => $account->getId(),
                'label' => $this->__($account->getTitle()),
                'attrs' => $datum['selected'] ? 'selected' : '',
            );
        }

        return $options;
    }

    /**
     * @return list<array{account: Ess_M2ePro_Model_Account, selected: bool}>
     */
    private function getOptionsDataForSingleAccount($accountId)
    {
        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            return array();
        }

        return array(
            array(
                'account' => $account->getParentObject(),
                'selected' => true,
            )
        );
    }

    /**
     * @return list<array{account: Ess_M2ePro_Model_Account, selected: bool}>
     */
    private function getOptionsDataForManyAccounts()
    {
        $accounts = $this->accountRepository->getAll();
        if (empty($accounts)) {
            return array();
        }

        $formData = $this->getFormData();

        $result = array();
        foreach ($accounts as $account) {
            $result[] = array(
                'account' => $account->getParentObject(),
                'selected' => $account->getId() === $formData[Resource::COLUMN_ACCOUNT_ID],
            );
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getAttributesForAccountSelect()
    {
        $requestAccountId = $this->getRequest()
                                 ->getParam(self::REQUEST_PARAM_KEY_ACCOUNT_ID);
        if (
            $requestAccountId !== null
            && $this->accountRepository->isExists((int)$requestAccountId)
        ) {
            return 'style='
                . '"'
                . 'pointer-events: none;'
                . ' background: rgba(0, 0, 0, 0.1);'
                . ' color: light-dark(graytext, rgb(170, 170, 170));'
                . '"';
        }

        $formData = $this->getFormData();

        return !empty($formData[Resource::COLUMN_ACCOUNT_ID]) ? 'disabled' : '';
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Account[] $amazonAccounts
     * @return Ess_M2ePro_Model_Account[]
     */
    private function getParentAccountsSortedByTitle($amazonAccounts)
    {
        $byTitle = array();
        foreach ($amazonAccounts as $account) {
            $parent = $account->getParentObject();
            $byTitle[$parent->getTitle()] = $parent;
        }

        asort($byTitle);

        return array_values($byTitle);
    }

    /**
     * @return array[]
     */
    public function getDictionaryOptions()
    {
        $options = array(
            array(
                'value' => '',
                'label' => '',
                'attrs' => 'style="display: none;"',
            ),
        );

        $formData = $this->getFormData();
        if (empty($formData[Resource::COLUMN_ACCOUNT_ID])) {
            return $options;
        }

        $dictionaries = $this->dictionaryRepository->retrieveByAccountId(
            $formData[Resource::COLUMN_ACCOUNT_ID]
        );

        $formTemplateId = $formData[Resource::COLUMN_TEMPLATE_ID];
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping $dictionary */
        foreach ($dictionaries as $dictionary) {
            $options[] = array(
                'value' => $dictionary->getTemplateId(),
                'label' => $this->__($dictionary->getTitle()),
                'attrs' => $dictionary->getTemplateId() === $formTemplateId ? 'selected' : '',
            );
        }

        return $options;
    }
}
