<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Magento_Product_Rule_Condition_Abstract
    extends Varien_Object
    implements Mage_Rule_Model_Condition_Interface
{
    /**
     * Defines which operators will be available for this condition
     *
     * @var string
     */
    protected $_inputType = null;

    /**
     * Default values for possible operator options
     * @var array
     */
    protected $_defaultOperatorOptions = null;

    /**
     * Default combinations of operator options, depending on input type
     * @var array
     */
    protected $_defaultOperatorInputByType = null;

    /**
     * List of input types for values which should be array
     * @var array
     */
    protected $_arrayInputTypes = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        if ($options = $this->getAttributeOptions()) {
            foreach ($options as $attr=>$dummy) { $this->setAttribute($attr); break; 
            }
        }

        if ($options = $this->getOperatorOptions()) {
            foreach ($options as $operator=>$dummy) { $this->setOperator($operator); break; 
            }
        }
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'string'      => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()', '??', '!??'),
                'numeric'     => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
                'date'        => array('==', '>=', '<='),
                'select'      => array('==', '!='),
                'boolean'     => array('==', '!='),
                'multiselect' => array('{}', '!{}', '()', '!()'),
                'grid'        => array('()', '!()'),
            );
            $this->_arrayInputTypes = array('multiselect', 'grid');
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * Default operator options getter
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = array(
                '=='  => Mage::helper('rule')->__('is'),
                '!='  => Mage::helper('rule')->__('is not'),
                '>='  => Mage::helper('rule')->__('equals or greater than'),
                '<='  => Mage::helper('rule')->__('equals or less than'),
                '>'   => Mage::helper('rule')->__('greater than'),
                '<'   => Mage::helper('rule')->__('less than'),
                '{}'  => Mage::helper('rule')->__('contains'),
                '!{}' => Mage::helper('rule')->__('does not contain'),
                '()'  => Mage::helper('rule')->__('is one of'),
                '!()' => Mage::helper('rule')->__('is not one of'),
                '??'  => Mage::helper('rule')->__('is empty'),
                '!??' => Mage::helper('rule')->__('is not empty')
            );
        }

        return $this->_defaultOperatorOptions;
    }

    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * @param array $arrAttributes
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type'=>$this->getType(),
            'attribute'=>$this->getAttribute(),
            'operator'=>$this->getOperator(),
            'value'=>$this->getValue(),
            'is_value_processed'=>$this->getIsValueParsed(),
        );
        return $out;
    }

    /**
     * @return string
     */
    public function asXml()
    {
        $xml = "<type>".$this->getType()."</type>"
            ."<attribute>".$this->getAttribute()."</attribute>"
            ."<operator>".$this->getOperator()."</operator>"
            ."<value>".$this->getValue()."</value>";
        return $xml;
    }

    public function loadArray($arr)
    {
        $this->setType($arr['type']);
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $this->setOperator(isset($arr['operator']) ? $arr['operator'] : false);
        $this->setValue(isset($arr['value']) ? $arr['value'] : false);
        $this->setIsValueParsed(isset($arr['is_value_parsed']) ? $arr['is_value_parsed'] : false);

        return $this;
    }

    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }

        $arr = (array)$xml;
        $this->loadArray($arr);
        return $this;
    }

    public function loadAttributeOptions()
    {
        return $this;
    }

    public function getAttributeOptions()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = array();
        foreach ($this->getAttributeOption() as $k=>$v) {
            $opt[] = array('value'=>$k, 'label'=>$v);
        }

        return $opt;
    }

    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption($this->getDefaultOperatorOptions());
        $this->setOperatorByInputType($this->getDefaultOperatorInputByType());
        return $this;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        if (null === $this->_inputType) {
            return 'string';
        }

        return $this->_inputType;
    }

    /**
     * @return array
     */
    public function getOperatorSelectOptions()
    {
        $type = $this->getInputType();
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($this->getOperatorOption() as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value' => $k, 'label' => $v);
            }
        }

        return $opt;
    }

    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    public function getValueSelectOptions()
    {
        $valueOption = $opt = array();
        if ($this->hasValueOption()) {
            $valueOption = (array) $this->getValueOption();
        }

        foreach ($valueOption as $k => $v) {
            $opt[] = array('value' => $k, 'label' => $v);
        }

        return $opt;
    }

    /**
     * Retrieve parsed value
     *
     * @return array|string|int|float
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if ($this->isArrayOperatorType() && is_string($value)) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }

            $this->setValueParsed($value);
        }

        return $this->getData('value_parsed');
    }

    /**
     * Check if value should be array
     *
     * Depends on operator input type
     *
     * @return bool
     */
    public function isArrayOperatorType()
    {
        $op = $this->getOperator();
        return $op === '()' || $op === '!()' || in_array($this->getInputType(), $this->_arrayInputTypes);
    }

    public function getValue()
    {
        if ($this->getInputType()=='date' && !$this->getIsValueParsed()) {
            // date format intentionally hard-coded
            $this->setValue(
                Mage::app()->getLocale()->date(
                    $this->getData('value'),
                    Varien_Date::DATE_INTERNAL_FORMAT, null, false
                )->toString(Varien_Date::DATE_INTERNAL_FORMAT)
            );
            $this->setIsValueParsed(true);
        }

        return $this->getData('value');
    }

    public function getValueName()
    {
        $value = $this->getValue();
        if ($value === null || '' === $value) {
            return '...';
        }

        $options = $this->getValueSelectOptions();
        $valueArr = array();
        if (!empty($options)) {
            foreach ($options as $o) {
                if (is_array($value)) {
                    if (in_array($o['value'], $value)) {
                        $valueArr[] = $o['label'];
                    }
                } else {
                    if (is_array($o['value'])) {
                        foreach ($o['value'] as $v) {
                            if ($v['value']==$value) {
                                return $v['label'];
                            }
                        }
                    }

                    if ($o['value'] == $value) {
                        return $o['label'];
                    }
                }
            }
        }

        if (!empty($valueArr)) {
            $value = implode(', ', $valueArr);
        }

        return $value;
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('rule')->__('Please choose a Condition to add...')),
        );
    }

    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    public function asHtml()
    {
        $html = $this->getTypeElementHtml()
            .$this->getAttributeElementHtml()
            .$this->getOperatorElementHtml()
            .$this->getValueElementHtml()
            .$this->getRemoveLinkHtml()
            .$this->getChooserContainerHtml();
        return $html;
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml();
        return $html;
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__type', 'hidden', array(
            'name'    => 'rule[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
            'value'   => $this->getType(),
            'no_span' => true,
            'class'   => 'hidden',
            )
        );
    }

    public function getTypeElementHtml()
    {
        return $this->getTypeElement()->getHtml();
    }

    public function getAttributeElement()
    {
        if ($this->getAttribute() === null) {
            foreach ($this->getAttributeOption() as $k => $v) {
                $this->setAttribute($k);
                break;
            }
        }

        return $this->getForm()->addField(
            $this->getPrefix().'__'.$this->getId().'__attribute', 'select', array(
            'name'=>'rule['.$this->getPrefix().']['.$this->getId().'][attribute]',
            'values'=>$this->getAttributeSelectOptions(),
            'value'=>$this->getAttribute(),
            'value_name'=>$this->getAttributeName(),
            )
        )->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    public function getAttributeElementHtml()
    {
        return $this->getAttributeElement()->getHtml();
    }

    /**
     * Retrieve Condition Operator element Instance
     * If the operator value is empty - define first available operator value as default
     *
     * @return Varien_Data_Form_Element_Select
     */
    public function getOperatorElement()
    {
        $options = $this->getOperatorSelectOptions();
        if ($this->getOperator() === null) {
            foreach ($options as $option) {
                $this->setOperator($option['value']);
                break;
            }
        }

        $elementId   = sprintf('%s__%s__operator', $this->getPrefix(), $this->getId());
        $elementName = sprintf('rule[%s][%s][operator]', $this->getPrefix(), $this->getId());
        $element     = $this->getForm()->addField(
            $elementId, 'select', array(
            'name'          => $elementName,
            'values'        => $options,
            'value'         => $this->getOperator(),
            'value_name'    => $this->getOperatorName(),
            )
        );
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }

    public function getOperatorElementHtml()
    {
        return $this->getOperatorElement()->getHtml();
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see Varien_Data_Form_Element
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    public function getValueElementRenderer()
    {
        if (strpos($this->getValueElementType(), '/')!==false) {
            return Mage::getBlockSingleton($this->getValueElementType());
        }

        return Mage::getBlockSingleton('rule/editable');
    }

    public function getValueElement()
    {
        $elementParams = array(
            'name'               => 'rule['.$this->getPrefix().']['.$this->getId().'][value]',
            'value'              => $this->getValue(),
            'values'             => $this->getValueSelectOptions(),
            'value_name'         => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply'     => $this->getExplicitApply(),
        );
        if ($this->getInputType()=='date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
            $elementParams['format']       = Varien_Date::DATE_INTERNAL_FORMAT;
        }

        return $this->getForm()->addField(
            $this->getPrefix().'__'.$this->getId().'__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer($this->getValueElementRenderer());
    }

    public function getValueElementHtml()
    {
        return $this->getValueElement()->getHtml();
    }

    public function getAddLinkHtml()
    {
        $src = Mage::getDesign()->getSkinUrl('M2ePro/images/rule_component_add.gif');
        $html = '<img src="' . $src . '" class="rule-param-add v-middle" alt=""
                                         title="' . Mage::helper('rule')->__('Add') . '"/>';
        return $html;
    }

    public function getRemoveLinkHtml()
    {
        $src = Mage::getDesign()->getSkinUrl('M2ePro/images/rule_component_remove.gif');
        $html = ' <span class="rule-param">
                     <a href="javascript:void(0)" class="rule-param-remove"
                                                  title="' . Mage::helper('rule')->__('Remove') . '">
                        <img src="' . $src . '" alt="" class="v-middle" />
                     </a>
                  </span>';

        return $html;
    }

    public function getChooserContainerHtml()
    {
        $url = $this->getValueElementChooserUrl();
        $html = '';
        if ($url) {
            $html = '<div class="rule-chooser" url="' . $url . '"></div>';
        }

        return $html;
    }

    public function asString($format = '')
    {
        $str = $this->getAttributeName() . ' ' . $this->getOperatorName() . ' ' . $this->getValueName();
        return $str;
    }

    public function asStringRecursive($level=0)
    {
        $str = str_pad('', $level * 3, ' ', STR_PAD_LEFT) . $this->asString();
        return $str;
    }

    /**
     * Validate product attrbute value for condition
     *
     * @param   mixed $validatedValue product attribute value
     * @return  bool
     */
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');

        if ($this->getInputType() == 'date' && !empty($validatedValue) && !is_numeric($validatedValue)) {
            $validatedValue = (int)$helper->createGmtDateTime($validatedValue)
                ->format('U');
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();

        if ($this->getInputType() == 'date' && !empty($value) && !is_numeric($value)) {
            $value = (int)$helper->createGmtDateTime($value)
                ->format('U');
        }

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
            if (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue)) {
                    $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                } else {
                    $result = $this->_compareValues($validatedValue, $value);
                }
            }
                break;

            case '<=': case '>':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue <= $value;
            }
                break;

            case '>=': case '<':
            if (!is_scalar($validatedValue)) {
                return false;
            } else {
                $result = $validatedValue >= $value;
            }
                break;

            case '{}': case '!{}':
            if (is_scalar($validatedValue) && is_array($value)) {
                foreach ($value as $item) {
                    if (stripos($validatedValue, $item)!==false) {
                        $result = true;
                        break;
                    }
                }
            } elseif (is_array($value)) {
                if (is_array($validatedValue)) {
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    return false;
                }
            } else {
                if (is_array($validatedValue)) {
                    $result = in_array($value, $validatedValue);
                } else {
                    $result = $this->_compareValues($value, $validatedValue, false);
                }
            }
                break;

            case '()': case '!()':
            if (is_array($validatedValue)) {
                $result = count(array_intersect($validatedValue, (array)$value))>0;
            } else {
                $value = (array)$value;
                foreach ($value as $item) {
                    if ($this->_compareValues($validatedValue, $item)) {
                        $result = true;
                        break;
                    }
                }
            }
                break;

            case '??':case '!??':
                $result = empty($validatedValue);
                break;
        }

        if ('!=' == $op || '>' == $op || '<' == $op || '!{}' == $op || '!()' == $op || '!??' == $op) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * Case and type insensitive comparison of values
     *
     * @param  string|int|float $validatedValue
     * @param  string|int|float $value
     * @param  bool $strict
     * @return bool
     */
    protected function _compareValues($validatedValue, $value, $strict = true)
    {
        if ($strict && is_numeric($validatedValue) && is_numeric($value)) {
            return $validatedValue == $value;
        } else {
            $validatePattern = preg_quote($validatedValue, '~');
            if ($strict) {
                $validatePattern = '^' . $validatePattern . '$';
            }

            try {
                $result = (bool)preg_match('~' . $validatePattern . '~iu', $value);
            } catch (Exception $e) {
                return false;
            }

            return $result;
        }
    }

    public function validate(Varien_Object $object)
    {
        return $this->validateAttribute($object->getData($this->getAttribute()));
    }

    /**
     * Retrieve operator for php validation
     *
     * @return string
     */
    public function getOperatorForValidate()
    {
        return $this->getOperator();
    }

    //########################################
}
