<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('freelunchlabs_mailgun/event')}
  CHANGE `timestamp` `timestamp_old` varchar(255);

ALTER TABLE {$this->getTable('freelunchlabs_mailgun/event')}
  ADD COLUMN `timestamp` timestamp;

UPDATE {$this->getTable('freelunchlabs_mailgun/event')}
  SET `timestamp`=from_unixtime(`timestamp_old`+(((timediff(now(),convert_tz(now(),@@session.time_zone,'+00:00'))*-1)/10000)*60*60));

ALTER TABLE {$this->getTable('freelunchlabs_mailgun/event')}
  DROP COLUMN `timestamp_old`;

");

$installer->endSetup();
