<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_ProductVocabulary extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            "
UPDATE `{$this->_installer->getTable('m2epro_registry')}`
SET `key` = '/product/variation/vocabulary/server/'
WHERE `key` = 'amazon_vocabulary_server';

UPDATE `{$this->_installer->getTable('m2epro_registry')}`
SET `key` = '/product/variation/vocabulary/local/'
WHERE `key` = 'amazon_vocabulary_local';

DELETE FROM `{$this->_installer->getTable('m2epro_registry')}`
WHERE `key` IN ('walmart_vocabulary_server', 'walmart_vocabulary_local');
"
        );

        // ---------------------------------------

        $this->_installer->getMainConfigModifier()->updateGroup(
            '/product/variation/vocabulary/attribute/auto_action/',
            array('`group` = ?' => '/amazon/vocabulary/attribute/auto_action/')
        );

        $this->_installer->getMainConfigModifier()->updateGroup(
            '/product/variation/vocabulary/option/auto_action/',
            array('`group` = ?' => '/amazon/vocabulary/option/auto_action/')
        );
    }

    //########################################
}
