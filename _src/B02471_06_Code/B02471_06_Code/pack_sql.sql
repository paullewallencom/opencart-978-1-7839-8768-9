CREATE TABLE IF NOT EXISTS `oc_feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`)
);

CREATE TABLE IF NOT EXISTS `oc_feedback_description` (
  `feedback_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `author` varchar(64) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`feedback_id`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `oc_feedback_to_layout` (
  `feedback_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`feedback_id`,`store_id`)
);

CREATE TABLE IF NOT EXISTS `oc_feedback_to_store` (
  `feedback_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`feedback_id`,`store_id`)
);

