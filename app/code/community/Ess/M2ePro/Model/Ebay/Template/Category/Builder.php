<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    private $_initDefaultSpecifics = false;
    private $_filteredData = array();

    //########################################

    public function build($model, array $rawData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $model */
        $model = parent::build($model, $rawData);
        $specifics = $this->getSpecifics($model);
        $this->saveSpecifics($model, $specifics);
        return $model;
    }

    //########################################

    protected function prepareData()
    {
        $template = $this->getTemplate();
        $this->initSpecificsFromTemplate($template);
        $this->_model = $template;
        return $this->getFilteredData();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getTemplate()
    {
        if (isset($this->_rawData['template_id'])) {
            return $this->loadTemplateById($this->_rawData['template_id']);
        }

        $isCustomTemplate = isset($this->_rawData['is_custom_template'])
            ? $this->_rawData['is_custom_template']
            : false;

        return $isCustomTemplate
            ? $this->createCustomTemplate()
            : $this->getDefaultTemplate();
    }

    protected function createCustomTemplate()
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');
        $template->setData('is_custom_template', 1);
        return $template;
    }

    protected function loadTemplateById($id)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');
        $template->load($id);
        $this->checkIfTemplateDataMatch($template);
        return $template;
    }

    protected function getDefaultTemplate()
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');

        if (!isset($this->_rawData['category_mode'], $this->_rawData['marketplace_id'])) {
            return $template->setData('is_custom_template', 0);
        }

        $value =  $this->_rawData['category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY
            ? $this->_rawData['category_id']
            : $this->_rawData['category_attribute'];

        $template->loadByCategoryValue(
            $value,
            $this->_rawData['category_mode'],
            $this->_rawData['marketplace_id'],
            0
        );

        if ($template->isObjectNew()) {
            $this->_initDefaultSpecifics = true;
        }

        return $template;
    }

    protected function getFilteredData()
    {
        if (!empty($this->_filteredData)) {
            return $this->_filteredData;
        }

        $keys = array(
            'marketplace_id',
            'category_mode',
            'category_id',
            'category_attribute',
            'category_path'
        );

        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $this->_filteredData[$key] = $this->_rawData[$key];
            }
        }

        return $this->_filteredData;
    }

    /**
     * editing of category data is not allowed
     */
    protected function checkIfTemplateDataMatch(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        $significantKeys = array(
            'marketplace_id',
            'category_mode',
            'category_id',
            'category_attribute',
        );

        foreach ($this->getFilteredData() as $key => $value) {
            if (in_array($key, $significantKeys, true) && $template->getData($key) != $value) {
                $this->initSpecificsFromTemplate($template);
                $template->setData(array('is_custom_template' => 1));
            }
        }
    }

    //########################################

    protected function getSpecifics(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        if (!empty($this->_rawData['specific'])) {
            return $this->getNewSpecifics($template);
        }

        if ($this->_initDefaultSpecifics) {
            return $this->initDefaultSpecifics($template);
        }

        return array();
    }

    //########################################

    protected function saveSpecifics(Ess_M2ePro_Model_Ebay_Template_Category $template, array $specifics)
    {
        $transaction = Mage::getModel('core/resource_transaction');

        foreach ($specifics as $specific) {
            $specific['template_category_id'] = $template->getId();

            $specificModel = Mage::getModel('M2ePro/Ebay_Template_Category_Specific');
            $specificModel->setData($specific);

            $transaction->addObject($specificModel);
        }

        $transaction->save();
    }

    protected function initDefaultSpecifics(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        $dictionarySpecifics = (array)Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSpecifics(
            $template->getCategoryId(),
            $template->getMarketplaceId()
        );

        $specifics = array();
        foreach ($dictionarySpecifics as $dictionarySpecific) {
            $specifics[] = array(
                'mode'            => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS,
                'attribute_title' => $dictionarySpecific['title'],
                'value_mode'      => Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE
            );
        }

        return $specifics;
    }

    protected function getNewSpecifics(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        $specifics = array();
        foreach ($template->getSpecifics(true) as $specific) {
            // @codingStandardsIgnoreLine
            $specific->delete();
        }
        foreach ($this->_rawData['specific'] as $specific) {
            $specifics[] = $this->serializeSpecific($specific);
        }

        return $specifics;
    }

    protected function initSpecificsFromTemplate(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        if (!empty($this->_rawData['specific']) || $template->isObjectNew()) {
            return;
        }

        $helper = Mage::helper('M2ePro');
        foreach ($template->getSpecifics() as $specific) {
            $specific['value_ebay_recommended'] = (array)$helper->jsonDecode($specific['value_ebay_recommended']);
            $specific['value_custom_value']     = (array)$helper->jsonDecode($specific['value_custom_value']);

            $this->_rawData['specific'][] = $specific;
        }
    }

    //----------------------------------------

    public function serializeSpecific(array $specific)
    {
        $specificData = array(
            'mode'            => (int)$specific['mode'],
            'attribute_title' => $specific['attribute_title'],
            'value_mode'      => (int)$specific['value_mode']
        );

        if (isset($specific['value_ebay_recommended'])) {
            $recommendedValue = $specific['value_ebay_recommended'];
            !is_array($recommendedValue) && $recommendedValue = array($recommendedValue);

            $specificData['value_ebay_recommended'] = Mage::helper('M2ePro')->jsonEncode($recommendedValue);
        }

        if (isset($specific['value_custom_value'])) {
            $customValue = $specific['value_custom_value'];
            !is_array($customValue) && $customValue = array($customValue);

            $specificData['value_custom_value'] = Mage::helper('M2ePro')->jsonEncode($customValue);
        }

        if (isset($specific['value_custom_attribute'])) {
            $specificData['value_custom_attribute'] = $specific['value_custom_attribute'];
        }

        return $specificData;
    }

    //########################################

    public function getDefaultData()
    {
        return array(
            'category_id'        => 0,
            'category_path'      => '',
            'category_mode'      => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY,
            'category_attribute' => ''
        );
    }

    //########################################
}
