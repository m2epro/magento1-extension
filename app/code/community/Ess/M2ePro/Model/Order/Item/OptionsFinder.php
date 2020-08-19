<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Order_Item_OptionsFinder
{
    protected $_channelOptions = array();

    protected $_magentoOptions = array();

    protected $_magentoValue = array();

    protected $_channelLabels = array();
    /** @var Ess_M2ePro_Model_Magento_Product */
    protected $_magentoProduct;

    protected $_failedOptions = array();

    protected $_optionsData = array('associated_options' => array(), 'associated_products' => array());
    /** @var bool */
    protected $_isNeedToReturnFirstOptionValues;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setChannelOptions(array $options = array())
    {
        $this->_channelOptions = Mage::helper('M2ePro')->toLowerCaseRecursive($options);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function addChannelOptions(array $options = array())
    {
        // $options keys may contain numeric values of option labels, so we need use "+" instead of array_merge
        $this->_channelOptions = $this->_channelOptions + Mage::helper('M2ePro')->toLowerCaseRecursive($options);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setMagentoOptions(array $options = array())
    {
        $this->_magentoOptions = $options;
        return $this;
    }

    //########################################

    /**
     * @throws \InvalidArgumentException
     */
    public function find()
    {
        $this->_failedOptions = array();
        $this->_optionsData   = array('associated_options' => array(), 'associated_products' => array());

        if ($this->getProductType() === null || empty($this->_magentoOptions)) {
            return;
        }

        if ($this->_magentoProduct->isGroupedType()) {
            $associatedProduct = $this->getGroupedAssociatedProduct();

            if ($associatedProduct === null) {
                return;
            }

            $this->_optionsData['associated_products'] = array($associatedProduct->getId());
            return;
        }

        if (empty($this->_channelOptions)) {
            $this->isNeedToReturnFirstOptionValues() && $this->matchFirstOptions();
            return;
        }

        $this->matchOptions();
    }

    //########################################

    /**
     * @return array
     */
    public function getOptionsData()
    {
        if (isset($this->_optionsData['associated_products'])) {
            $this->_optionsData['associated_products'] = Mage::helper(
                'M2ePro/Magento_Product'
            )->prepareAssociatedProducts(
                $this->_optionsData['associated_products'],
                $this->_magentoProduct
            );
        }

        return $this->_optionsData;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasFailedOptions()
    {
        return !empty($this->_failedOptions);
    }

    /**
     * @return array
     */
    public function getFailedOptions()
    {
        return $this->_failedOptions;
    }

    /**
     * @return string
     */
    public function getOptionsNotFoundMessage()
    {
        if ($this->_magentoProduct->isConfigurableType()) {
            $message = 'There is no associated Product found for Configurable Product.';
        } elseif ($this->_magentoProduct->isGroupedType()) {
            $message = 'There is no associated Product found for Grouped Product.';
        } else {
            $message = sprintf(
                'Product Option(s) "%s" not found.',
                implode(', ', $this->_failedOptions)
            );
        }

        return $message;
    }

    //########################################

    /**
     * @return array|null|string
     * @throws \InvalidArgumentException
     */
    protected function getProductType()
    {
        if ($this->_magentoProduct === null) {
            throw new \InvalidArgumentException('Magento Product was not set.');
        }

        $type = $this->_magentoProduct->getTypeId();
        if (!in_array($type, $this->getAllowedProductTypes())) {
            throw new \InvalidArgumentException(sprintf('Product type "%s" is not supported.', $type));
        }

        return $type;
    }

    protected function matchFirstOptions()
    {
        $options  = array();
        $products = array();

        foreach ($this->_magentoOptions as $magentoOption) {
            $optionId = $magentoOption['option_id'];
            $valueId  = $magentoOption['values'][0]['value_id'];

            $options[$optionId] = $valueId;
            $products["{$optionId}::{$valueId}"] = $magentoOption['values'][0]['product_ids'];
        }

        $this->_optionsData = array(
            'associated_options'  => $options,
            'associated_products' => $products
        );
    }

    protected function matchOptions()
    {
        $options  = array();
        $products = array();

        foreach ($this->_magentoOptions as $magentoOption) {
            $this->_channelLabels = array();
            $this->_magentoValue  = array();

            $magentoOption['labels'] = array_filter($magentoOption['labels']);
            if ($this->isOptionFailed($magentoOption)) {
                continue;
            }

            $this->appendOption($magentoOption, $options);
            $this->appendProduct($magentoOption, $products);
        }

        $this->_optionsData = array(
            'associated_options'  => $options,
            'associated_products' => $products
        );
    }

    //########################################

    /**
     * @param array $magentoOption
     * @return bool
     */
    protected function isOptionFailed(array $magentoOption)
    {
        $this->findChannelLabels($magentoOption['labels']);

        if (empty($this->_channelLabels)) {
            $this->_failedOptions[] = array_shift($magentoOption['labels']);
            return true;
        }

        $this->findMagentoValue($magentoOption['values']);

        if (empty($this->_magentoValue) ||
            !isset($this->_magentoValue['value_id']) ||
            !isset($this->_magentoValue['product_ids'])) {
            $this->_failedOptions[] = array_shift($magentoOption['labels']);
            return true;
        }

        return false;
    }

    /**
     * @param array $optionLabels
     */
    protected function findChannelLabels(array $optionLabels)
    {
        $optionLabels = Mage::helper('M2ePro')->toLowerCaseRecursive($optionLabels);

        foreach ($optionLabels as $label) {
            if (isset($this->_channelOptions[$label])) {
                $this->_channelLabels = array('labels' => $this->_channelOptions[$label]);
                return;
            }
        }
    }

    /**
     * @param array $magentoOptionValues
     */
    protected function findMagentoValue(array $magentoOptionValues)
    {
        foreach ($magentoOptionValues as $optionValue) {
            $valueLabels = Mage::helper('M2ePro')->toLowerCaseRecursive($optionValue['labels']);

            foreach ((array)$this->_channelLabels['labels'] as $channelOptionLabel) {
                if (in_array($channelOptionLabel, $valueLabels, true)) {
                    $this->_magentoValue = $optionValue;
                    return;
                }
            }
        }
    }

    //########################################

    /**
     * @param array $magentoOption
     * @param array $options
     */
    protected function appendOption(array $magentoOption, array &$options)
    {
        $optionId = $magentoOption['option_id'];
        $valueId  = $this->_magentoValue['value_id'];

        $options[$optionId] = $valueId;
    }

    /**
     * @param array $magentoOption
     * @param array $products
     */
    protected function appendProduct(array $magentoOption, array &$products)
    {
        $optionId = $magentoOption['option_id'];
        $valueId  = $this->_magentoValue['value_id'];

        $products["{$optionId}::{$valueId}"] = $this->_magentoValue['product_ids'];
    }

    //########################################

    protected function getGroupedAssociatedProduct()
    {
        $variationName = array_shift($this->_channelOptions);

        if (($variationName === null || strlen(trim($variationName)) == 0) &&
            !$this->isNeedToReturnFirstOptionValues()) {
            return null;
        }

        foreach ($this->_magentoOptions as $option) {
            // return product if it's name is equal to variation name
            if ($variationName === null || trim(strtolower($option->getName())) == trim(strtolower($variationName))) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function isNeedToReturnFirstOptionValues()
    {
        if ($this->_isNeedToReturnFirstOptionValues !== null) {
            return $this->_isNeedToReturnFirstOptionValues;
        }

        $configValue = (bool)Mage::helper('M2ePro/Module_Configuration')
            ->getCreateWithFirstProductOptionsWhenVariationUnavailable();

        return $this->_isNeedToReturnFirstOptionValues = $configValue;
    }

    protected function getAllowedProductTypes()
    {
        return Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes();
    }

    //########################################
}
