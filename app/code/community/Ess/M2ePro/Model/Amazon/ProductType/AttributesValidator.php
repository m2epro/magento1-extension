<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributesValidator
{
    /**
     * @param $amazonListingProduct
     * @param $productTypeId
     * @return void
     * @throws Ess_M2ePro_Model_Exception
     */
    public function validate(
        $amazonListingProduct,
         $productTypeId
    ) {
        $resource = Mage::getResourceModel('M2ePro/Amazon_ProductType_Validation');
        $validationResult = Mage::getModel('M2ePro/Amazon_ProductType_Validation');
        $resource->load(
            $validationResult,
            $amazonListingProduct->getListingProductId(),
            'listing_product_id'
        );

        if ($validationResult->isObjectNew()) {
            $validationResult->setListingProductId($amazonListingProduct->getListingProductId());
        }

        $validationResult->setValidStatus();
        $validationResult->setErrorMessages(array());

        try {
            /**
             * @var $templateProductTypeRepository Ess_M2ePro_Model_Amazon_Template_ProductType_Repository
             */
            $templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
            $productType = $templateProductTypeRepository->get($productTypeId);
        } catch (Ess_M2ePro_Model_Exception_EntityNotFound $exception) {
            $validationResult->setInvalidStatus();
            $validationResult->addErrorMessage(Mage::helper('M2ePro')->__('Product Type not found'));

            $resource->save($validationResult);

            return;
        }

        $magentoProduct = $amazonListingProduct->getActualMagentoProduct();

        foreach ($productType->getCustomAttributesList() as $customAttribute) {
            $path = $customAttribute['name'];
            try {
                $validator = $productType->getDictionary()->getValidatorByPath($path);
            } catch (Ess_M2ePro_Model_Exception_Logic $e) {
                $validationResult->addErrorMessage('WARNING! ' . $e->getMessage());
                continue;
            }

            if (!$validator->isRequiredSpecific()) {
                continue;
            }

            $attributeCode = $customAttribute['attribute_code'];
            $attributeValue = $magentoProduct->getAttributeValue($attributeCode);
            $isAttributeValid = $validator->validate($attributeValue);

            if (!$isAttributeValid) {
                $validationResult->setInvalidStatus();
                foreach ($validator->getErrors() as $errorMessage) {
                    $validationResult->addErrorMessage($errorMessage);
                }
            }
        }

        $validationResult->touchCreateDate();
        $validationResult->touchUpdateDate();
        $resource->save($validationResult);
    }
}
