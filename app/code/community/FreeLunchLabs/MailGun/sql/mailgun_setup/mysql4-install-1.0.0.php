<?php

$installer = $this;

$installer->startSetup();

$installer->run("

 SET FOREIGN_KEY_CHECKS = 0;
 DROP TABLE IF EXISTS {$this->getTable('freelunchlabs_mailgun/email')};
 DROP TABLE IF EXISTS {$this->getTable('freelunchlabs_mailgun/event')};
 SET FOREIGN_KEY_CHECKS = 1;

-- DROP TABLE IF EXISTS {$this->getTable('freelunchlabs_mailgun/email')};
CREATE TABLE {$this->getTable('freelunchlabs_mailgun/email')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) NULL,
  `mailgun_id` varchar(255) NOT NULL default '',
  `email_address` varchar(255) NOT NULL default '',
  `subject` text,
  `body` text,
  `date_sent` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('freelunchlabs_mailgun/event')};
CREATE TABLE {$this->getTable('freelunchlabs_mailgun/event')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email_id` int(11) unsigned NULL,
  `event_type` varchar(255) NOT NULL default '',
  `timestamp` varchar(255),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mailgun_event` FOREIGN KEY (`email_id`) REFERENCES `{$this->getTable('freelunchlabs_mailgun/email')}` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();