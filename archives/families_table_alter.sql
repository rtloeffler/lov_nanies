ALTER TABLE `families` ADD `how_many_hours` INT(10) UNSIGNED NOT NULL AFTER `how_heared_about_us`, ADD `pay_rate` INT(10) UNSIGNED NOT NULL AFTER `how_many_hours`, ADD `housekeeping_or_cooking` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `pay_rate`;
ALTER TABLE `families` CHANGE `how_many_hours` `what_hours` VARCHAR(500) NOT NULL;