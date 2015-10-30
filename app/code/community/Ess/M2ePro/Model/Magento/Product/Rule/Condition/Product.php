<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Condition_Product
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Abstract
{
    protected $_entityAttributeValues = null;

    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    protected $_arrayInputTypes = array();

    protected $_customFiltersCache = array();

    //########################################

    public function getValue()
    {
        if ($this->getInputType()=='date' && !$this->getIsValueParsed()) {
            // date format intentionally hard-coded
            $this->setValue(
                Mage::app()->getLocale()->date($this->getData('value'),
                    Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString(Varien_Date::DATE_INTERNAL_FORMAT)
            );
            $this->setIsValueParsed(true);
        }
        return $this->getData('value');
    }

    //########################################

    /**
     * Validate product attribute value for condition
     *
     * @param Varien_Object $product
     * @return bool
     */
    public function validate(Varien_Object $product)
    {
        /** @var  $product Mage_Catalog_Model_Product */
        $attrCode = $this->getAttribute();

        if ($this->isFilterCustom($attrCode)) {
            $value = $this->getCustomFilterInstance($attrCode)->getValueByProductInstance($product);
            return $this->validateAttribute($value);
        }

        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($product->getAvailableInCategories());
        }

        if (! isset($this->_entityAttributeValues[$product->getId()])) {
            if (!$product->getResource()) {
                return false;
            }
            $attr = $product->getResource()->getAttribute($attrCode);

            if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
                $oldValue = $this->getValue();
                $this->setValue(strtotime($this->getValue()));
                $value = strtotime($product->getData($attrCode));
                $result = $this->validateAttribute($value);
                $this->setValue($oldValue);
                return $result;
            }

            if ($attr && $attr->getFrontendInput() == 'multiselect') {
                $value = $product->getData($attrCode);
                $value = strlen($value) ? explode(',', $value) : array();
                return $this->validateAttribute($value);
            }

            return $this->validateAttribute($product->getData($attrCode));
        } else {
            $productStoreId = $product->getData('store_id');
            if (is_null($productStoreId) ||
                !isset($this->_entityAttributeValues[(int)$product->getId()][(int)$productStoreId])) {
                $productStoreId = 0;
            }

            $attributeValue = $this->_entityAttributeValues[(int)$product->getId()][(int)$productStoreId];

            $attr = $product->getResource()->getAttribute($attrCode);
            if ($attr && $attr->getBackendType() == 'datetime') {
                $attributeValue = strtotime($attributeValue);

                if (!is_int($this->getValueParsed())) {
                    $this->setValueParsed(strtotime($this->getValue()));
                }
            } else if ($attr && $attr->getFrontendInput() == 'multiselect') {
                $attributeValue = strlen($attributeValue) ? explode(',', $attributeValue) : array();
            }

            return (bool)$this->validateAttribute($attributeValue);
        }
    }

    //########################################

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function getValueElementRenderer()
    {
        if (strpos($this->getValueElementType(), '/')!==false) {
            return Mage::getBlockSingleton($this->getValueElementType());
        }

        return Mage::getBlockSingleton('M2ePro/adminhtml_magento_product_rule_renderer_editable');
    }

    //########################################

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $attribute = $this->getAttribute();
        if ($attribute != 'sku' && $attribute != 'category_ids') {
            return '';
        }

        $urlParameters = array(
            'attribute' => $attribute,
            'store' => $this->getStoreId(),
            'form' => $this->getJsFormObject()
        );

        return Mage::helper('adminhtml')->getUrl('*/adminhtml_general/getRuleConditionChooserHtml', $urlParameters);
    }

    //########################################

    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType['category'] = array('==', '!=', '{}', '!{}', '()', '!()');
            $this->_arrayInputTypes[] = 'category';
            /*
             * price and price range modification
             */
            $this->_defaultOperatorInputByType['price'] = array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}');
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Retrieve attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeObject()
    {
        try {
            $obj = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $this->getAttribute());
        }
        catch (Exception $e) {
            $obj = new Varien_Object();
            $obj->setEntity(Mage::getResourceSingleton('catalog/product'))
                ->setFrontendInput('text');
        }
        return $obj;
    }

    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['attribute_set_id'] = Mage::helper('catalogrule')->__('Attribute Set');
        $attributes['category_ids'] = Mage::helper('catalogrule')->__('Category');

        foreach ($this->getCustomFilters() as $filterId => $instanceName) {
            $customFilterInstance = $this->getCustomFilterInstance($filterId);

            if ($customFilterInstance instanceof Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract) {
                $attributes[$filterId] = $customFilterInstance->getLabel();
            }
        }
    }

    /**
     * Load attribute options
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::helper('M2ePro/Magento_Attribute')->getAllAsObjects();

        $attributes = array();
        foreach ($productAttributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);
        natcasesort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = Mage::getSingleton('eav/config')
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
            $selectOptions = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } else if ($this->isFilterCustom($this->getAttribute())) {
            $selectOptions = $this->getCustomFilterInstance($this->getAttribute())->getOptions();
        } else if (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = array();
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option=null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option'.(!is_null($option) ? '/'.$option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
            $image = Mage::getDesign()->getSkinUrl('M2ePro/images/rule_chooser_trigger.gif');
            break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' . $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                Mage::helper('rule')->__('Open Chooser') . '" /></a>';
        }
        return $html;
    }

    /**
     * Collect validated attributes
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $productCollection
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ($attribute == 'category_ids' || $this->isFilterCustom($attribute)) {
            return $this;
        }

        if ($this->getAttributeObject()->isScopeGlobal()) {
            $attributes = $this->getRule()->getCollectedAttributes();
            $attributes[$attribute] = true;
            $this->getRule()->setCollectedAttributes($attributes);
            $productCollection->addAttributeToSelect($attribute, 'left');
        } else {
            $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
        }

        return $this;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())->getInputType();
        }
        if ($this->getAttribute() == 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->getAttributeObject()->getAttributeCode() == 'category_ids') {
            return 'category';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())->getValueElementType();
        }
        if ($this->getAttribute() == 'attribute_set_id') {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();

        if ($this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() == 'date'
        ) {
            $element->setImage(Mage::getDesign()->getSkinUrl('M2ePro/images/grid-cal.gif'));
        }

        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage(Mage::getDesign()->getSkinUrl('M2ePro/images/grid-cal.gif'));
                    break;
            }
        }

        return $element;
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if ($this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() == 'date'
        ) {
            return true;
        }

        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
            return true;
        }

        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }
        return false;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], array('{}', '!{}'));
        if ($attribute && $attribute->getBackendType() == 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (!empty($arr['operator'])
                    && in_array($arr['operator'], array('!()', '()'))
                    && false !== strpos($arr['value'], ',')) {

                    $tmp = array();
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = Mage::app()->getLocale()->getNumber($value);
                    }
                    $arr['value'] =  implode(',', $tmp);
                } else {
                    $arr['value'] =  Mage::app()->getLocale()->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset($arr['is_value_parsed'])
                ? Mage::app()->getLocale()->getNumber($arr['is_value_parsed']) : false;
        }

        return parent::loadArray($arr);
    }

    /**
     * Correct '==' and '!=' operators
     * Categories can't be equal because product is included categories selected by administrator and in their parents
     *
     * @return string
     */
    public function getOperatorForValidate()
    {
        $op = $this->getOperator();
        if ($this->getInputType() == 'category') {
            if ($op == '==') {
                $op = '{}';
            } elseif ($op == '!=') {
                $op = '!{}';
            }
        }

        return $op;
    }

    //########################################

    protected function getCustomFilters()
    {
        return array(
            'is_in_stock' => 'Stock',
            'qty' => 'Qty',
        );
    }

    protected function isFilterCustom($filterId)
    {
        $customFilters = $this->getCustomFilters();
        return isset($customFilters[$filterId]);
    }

    /**
     * @param $filterId
     * @return Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
     */
    protected function getCustomFilterInstance($filterId)
    {
        $customFilters = $this->getCustomFilters();
        if (!isset($customFilters[$filterId])) {
            return null;
        }

        if (!isset($this->_customFiltersCache[$filterId])) {
            $this->_customFiltersCache[$filterId] = Mage::getModel(
                'M2ePro/Magento_Product_Rule_Custom_'.$customFilters[$filterId]
            );
        }

        return $this->_customFiltersCache[$filterId];
    }

    //########################################
}