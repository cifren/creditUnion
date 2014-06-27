RENAME TABLE `credit_union`.`Fininstitut` TO `credit_union`.`fininstitut` ;
ALTER TABLE `client` DROP FOREIGN KEY `FK_C7440455DCD6CC49` ;
ALTER TABLE `client` CHANGE `branch_id` `fininstitut_id` INT( 11 ) NOT NULL ;
ALTER TABLE client ADD CONSTRAINT FK_C74404552AD02C4B FOREIGN KEY (fininstitut_id) REFERENCES fininstitut (id);

ALTER TABLE `import_format` DROP FOREIGN KEY `FK_A28BC8C7DCD6CC49` ;
ALTER TABLE `import_format` CHANGE `branch_id` `fininstitut_id` INT( 11 ) NOT NULL ;
ALTER TABLE import_format ADD CONSTRAINT FK_A28BC8C72AD02C4B FOREIGN KEY (fininstitut_id) REFERENCES fininstitut (id);