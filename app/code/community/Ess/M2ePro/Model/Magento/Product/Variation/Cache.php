<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Variation_Cache extends Ess_M2ePro_Model_Magento_Product_Variation
{
    //########################################

    public function getVariationsTypeStandard()
    {
        $cacheKeyParams = array(
            'virtual_attributes' => $this->getMagentoProduct()->getVariationVirtualAttributes(),
            'filter_attributes'  => $this->getMagentoProduct()->getVariationFilterAttributes(),
            'is_ignore_virtual_attributes' => $this->getMagentoProduct()->isIgnoreVariationVirtualAttributes(),
            'is_ignore_filter_attributes'  => $this->getMagentoProduct()->isIgnoreVariationFilterAttributes(),
        );

        return $this->getMethodData(__FUNCTION__, $cacheKeyParams);
    }

    public function getVariationsTypeRaw()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTitlesVariationSet()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    //########################################

    protected function getMethodData($methodName, $cacheKeyParams = array())
    {
        if ($this->getMagentoProduct() === null) {
            throw new Ess_M2ePro_Model_Exception('Magento Product was not set.');
        }

        $cacheKey = array(
            __CLASS__,
            $methodName,
        );

        if ($cacheKeyParams !== array()) {
            $cacheKey[] = $cacheKeyParams;
        }

        $cacheResult = $this->getMagentoProduct()->getCacheValue($cacheKey);

        if ($this->getMagentoProduct()->isCacheEnabled() && $cacheResult !== null) {
            return $cacheResult;
        }

        $data = call_user_func(array('parent', $methodName));

        if (!$this->getMagentoProduct()->isCacheEnabled()) {
            return $data;
        }

        return $this->getMagentoProduct()->setCacheValue($cacheKey, $data);
    }

    //########################################
}
