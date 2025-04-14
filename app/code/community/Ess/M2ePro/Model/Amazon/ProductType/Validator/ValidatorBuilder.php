<?php

class Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorBuilder
{
    const STRING_VALIDATOR_TYPE = 'string';
    const BOOLEAN_VALIDATOR_TYPE = 'boolean';
    const NUMBER_VALIDATOR_TYPE = 'number';
    const INTEGER_VALIDATOR_TYPE = 'integer';

    /** @var array */
    private $validatorData;

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
    public function __construct(array $validatorData)
    {
        $this->validateData($validatorData);
        $this->validatorData = $validatorData;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorInterface
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function build()
    {
        $validatorType = $this->validatorData['type'];
        $allowedOptions = array();
        if (isset($this->validatorData['options'])) {
            $allowedOptions = $this->validatorData['options'];
        }


        if ($validatorType === self::STRING_VALIDATOR_TYPE && empty($allowedOptions)) {
                return $this->buildStringValidator();
        }

        if ($validatorType === self::STRING_VALIDATOR_TYPE && !empty($allowedOptions)) {
            return $this->buildSelectValidator();
        }

        if ($validatorType === self::BOOLEAN_VALIDATOR_TYPE && !empty($allowedOptions)) {
            return $this->buildBooleanValidator();
        }

        if ($validatorType === self::NUMBER_VALIDATOR_TYPE) {
            return $this->buildNumberValidator();
        }

        if ($validatorType === self::INTEGER_VALIDATOR_TYPE) {
            return $this->buildIntegerValidator();
        }

        $message = sprintf('Undefined validator type "%s"', $validatorType);
        throw new Ess_M2ePro_Model_Exception_Logic($message);
    }

    private function buildStringValidator()
    {
        $validator = new Ess_M2ePro_Model_Amazon_ProductType_Validator_StringValidator();
        $validator->setFieldTitle($this->validatorData['title']);
        $validator->setFieldGroup($this->validatorData['group_title']);
        $validationRules = array();

        if (isset($this->validatorData['validation_rules'])) {
            $validationRules = $this->validatorData['validation_rules'];
        }

        if (array_key_exists('is_required', $validationRules)) {
            $validator->setIsRequired((bool)$validationRules['is_required']);
        }

        return $validator;
    }

    private function buildSelectValidator()
    {
        $validator = new Ess_M2ePro_Model_Amazon_ProductType_Validator_SelectValidator();
        $validator->setFieldTitle($this->validatorData['title']);
        $validator->setFieldGroup($this->validatorData['group_title']);
        $validator->setAllowedOptions($this->validatorData['options']);
        $validationRules = array();

        if (isset($this->validatorData['validation_rules'])) {
            $validationRules = $this->validatorData['validation_rules'];
        }

        if (array_key_exists('is_required', $validationRules)) {
            $validator->setIsRequired((bool)$validationRules['is_required']);
        }

        return $validator;
    }

    private function buildBooleanValidator()
    {
        $validator = new Ess_M2ePro_Model_Amazon_ProductType_Validator_BooleanValidator();
        $validator->setFieldTitle($this->validatorData['title']);
        $validator->setFieldGroup($this->validatorData['group_title']);
        $validationRules = array();

        if (isset($this->validatorData['validation_rules'])) {
            $validationRules = $this->validatorData['validation_rules'];
        }

        if (array_key_exists('is_required', $validationRules)) {
            $validator->setIsRequired((bool)$validationRules['is_required']);
        }

        return $validator;
    }

    private function buildNumberValidator()
    {
        $validator = new Ess_M2ePro_Model_Amazon_ProductType_Validator_NumberValidator();
        $validator->setFieldTitle($this->validatorData['title']);
        $validator->setFieldGroup($this->validatorData['group_title']);
        $validationRules = array();

        if (isset($this->validatorData['validation_rules'])) {
            $validationRules = $this->validatorData['validation_rules'];
        }

        if (array_key_exists('is_required', $validationRules)) {
            $validator->setIsRequired((bool)$validationRules['is_required']);
        }

        return $validator;
    }

    private function buildIntegerValidator()
    {
        $validator = new Ess_M2ePro_Model_Amazon_ProductType_Validator_IntegerValidator();
        $validator->setFieldTitle($this->validatorData['title']);
        $validator->setFieldGroup($this->validatorData['group_title']);
        $validationRules = array();

        if (isset($this->validatorData['validation_rules'])) {
            $validationRules = $this->validatorData['validation_rules'];
        }
        if (array_key_exists('is_required', $validationRules)) {
            $validator->setIsRequired((bool)$validationRules['is_required']);
        }

        return $validator;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function validateData(array $validatorData)
    {
        if (empty($validatorData)) {
            $this->throwException('Validator data is empty');
        }

        if (!array_key_exists('type', $validatorData)) {
            $this->throwException('Validator type is not set');
        }

        if (!array_key_exists('title', $validatorData)) {
            $this->throwException('Empty validator title');
        }

        if (!array_key_exists('group_title', $validatorData)) {
            $this->throwException('Empty validator group title');
        }
    }

    /**
     * @param string $message
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function throwException($message)
    {
        throw new Ess_M2ePro_Model_Exception_Logic($message);
    }
}
