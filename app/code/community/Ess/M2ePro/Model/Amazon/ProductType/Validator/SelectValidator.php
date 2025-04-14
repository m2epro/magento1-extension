<?php

class Ess_M2ePro_Model_Amazon_ProductType_Validator_SelectValidator
    implements Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorInterface
{
    /** @var string */
    private $fieldTitle = '';
    /** @var string */
    private $fieldGroup = '';
    /** @var bool */
    private $isRequired = false;
    /** @var string[]  */
    private $allowedOptions = array();
    /** @var array */
    private $errors = array();

    /**
     * @param mixed $value
     */
    public function validate($value)
    {
        $value = $this->tryConvertToString($value);
        if (empty($value)) {
            $this->errors[] = sprintf(
                '[%s] The value of "%s" is missing.',
                $this->fieldGroup,
                $this->fieldTitle
            );

            return false;
        }

        if (!(in_array($value, $this->allowedOptions) || array_key_exists($value, $this->allowedOptions))) {
            $message = sprintf(
                '[%s] The value of "%s" is invalid.',
                $this->fieldGroup,
                $this->fieldTitle
            );

            $this->errors[] = $message;

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isRequiredSpecific()
    {
        return $this->isRequired;
    }

    /**
     * @param string $fieldTitle
     * @return void
     */
    public function setFieldTitle($fieldTitle)
    {
        $this->fieldTitle = $fieldTitle;
    }

    /**
     * @param string $fieldGroup
     * @return void
     */
    public function setFieldGroup($fieldGroup)
    {
        $this->fieldGroup = $fieldGroup;
    }

    /**
     * @param bool $isRequired
     * @return void
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @param array $allowedOptions
     * @return void
     */
    public function setAllowedOptions($allowedOptions)
    {
        $this->allowedOptions = $allowedOptions;
    }

    /**
     * @param mixed $value
     */
    private function tryConvertToString($value)
    {
        if (
            is_string($value)
            || is_numeric($value)
            || $value === null
        ) {
            $value = (string)$value;

            return trim($value);
        }

        return null;
    }
}
