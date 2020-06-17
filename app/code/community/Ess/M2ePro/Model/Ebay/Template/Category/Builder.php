<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    private $initDefaultSpecifics = false;

    //########################################

    public function build($model, array $rawData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $model */
        $model = parent::build($model, $rawData);

        if (!empty($this->_rawData['specific'])) {
            foreach ($model->getSpecifics(true) as $specific) {
                // @codingStandardsIgnoreLine
                $specific->delete();
            }

            $specifics = array();
            foreach ($this->_rawData['specific'] as $specific) {
                $specifics[] = $this->serializeSpecific($specific);
            }

            $this->saveSpecifics($model, $specifics);
        } else {
            $this->initDefaultSpecifics && $this->initDefaultSpecifics($model);
        }

        return $model;
    }

    //########################################

    protected function prepareData()
    {
        $data = array();

        $keys = array(
            'marketplace_id',
            'category_mode',
            'category_id',
            'category_attribute',
            'category_path'
        );

        foreach ($keys as $key) {
            isset($this->_rawData[$key]) && $data[$key] = $this->_rawData[$key];
        }

        $template = $this->tryToLoadById($this->_rawData, $data);
        $template->getId() === null && $template = $this->tryToLoadByData($this->_rawData, $data);
        $this->initDefaultSpecifics = $template->getId() === null;
        $this->_model = $template;

        return $data;
    }

    //########################################

    protected function tryToLoadById(array $data, array $newTemplateData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');

        if (!isset($data['template_id'])) {
            return $template;
        }

        $template->load($data['template_id']);
        $this->checkIfTemplateDataMatch($template, $newTemplateData);

        return $template;
    }

    protected function tryToLoadByData(array $data, array $newTemplateData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');

        if (!isset($data['category_mode'], $data['marketplace_id'])) {
            return $template;
        }

        $template->loadByCategoryValue(
            $data['category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY
                ? $data['category_id']
                : $data['category_attribute'],
            $data['category_mode'],
            $data['marketplace_id'],
            0
        );

        /* editing of category data is not allowed */
        if ($template->getId() !== null && $template->isLocked()) {
            $this->checkIfTemplateDataMatch($template, $newTemplateData);
            if ($template->getId() === null) {
                return $template;
            }

            if (empty($data['specific']) && !$template->getIsCustomTemplate()) {
                return $template;
            }

            $this->initSpecificsFromTemplate($template);
            $template->setData(array('is_custom_template' => 1));
        }

        return $template;
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
        if (!$template->isCategoryModeEbay()) {
           return;
        }

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

        $this->saveSpecifics($template, $specifics);
    }

    protected function initSpecificsFromTemplate(Ess_M2ePro_Model_Ebay_Template_Category $template)
    {
        if (!empty($this->_rawData['specific'])) {
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

    /**
     * editing of category data is not allowed
     */
    protected function checkIfTemplateDataMatch(
        Ess_M2ePro_Model_Ebay_Template_Category $template,
        array $newTemplateData
    ) {
        $significantKeys = array(
            'marketplace_id',
            'category_mode',
            'category_id',
            'category_attribute',
        );

        foreach ($newTemplateData as $key => $value) {
            if (in_array($key, $significantKeys, true) && $template->getData($key) != $value) {
                $this->initSpecificsFromTemplate($template);
                $template->setData(array('is_custom_template' => 1));
            }
        }
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
