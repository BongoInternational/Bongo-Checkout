<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('postorder')} (
  `postorder_id` int(11) unsigned NOT NULL auto_increment,
  `partner_key` varchar(32) NOT NULL default '',
  `status` char(1) NOT NULL default '',
  `order_id` int(11) default NULL,
  `order` text,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`postorder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 