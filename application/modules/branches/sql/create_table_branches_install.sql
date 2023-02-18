CREATE TABLE `c19_branches` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`branch_name` VARCHAR(200) NOT NULL COLLATE 'latin1_swedish_ci',
	`branch_phone` VARCHAR(200) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
	`branch_address` VARCHAR(200) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
	`descriptions` VARCHAR(5000) NULL DEFAULT NULL COLLATE 'latin1_swedish_ci',
	`created_date` DATETIME NULL DEFAULT NULL,
	`created_by` INT(11) NULL DEFAULT NULL,
	`modified_date` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`modified_by` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='latin1_swedish_ci' ENGINE=InnoDB
;