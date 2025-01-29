<?php

class Ess_M2ePro_Block_Adminhtml_Wizard_AmazonMigrationToProductTypes_Content
    extends Mage_Adminhtml_Block_Abstract
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amazontMigrationToProductTypes_content');
        $this->setTemplate('M2ePro/wizard/amazonMigrationToProductTypes/content.phtml');
    }

    public function getSupportArticleUrl()
    {
        return 'https://docs-m1.m2epro.com/latest-amazon-updates-in-m2e-pro-v6-71-0-and-higher-for-magento-1-users';
    }

    protected function _toHtml()
    {
        $proceedLink = $this->getUrl('*/*/accept');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'An error during of marketplace synchronization.' =>
                    Mage::helper('M2ePro')->__(
                        'An error during of marketplace synchronization.'
                    )
            )
        );

        $javascript = <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
       WizardAmazonMigrationToProductTypesObj = new WizardAmazonMigrationToProductTypes('{$proceedLink}');
    });

</script>
HTML;

        return $javascript  . parent::_toHtml();
    }
}
