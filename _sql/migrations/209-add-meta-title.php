<?php
class Migrations_Migration208 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_articles` ADD `metaTitle` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `topseller`;
EOD;
        $this->addSql($sql);
    }
}
