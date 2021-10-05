-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
    `matnr` INT(6) NOT NULL,
	`sex` CHAR(1) NOT NULL,
	`vorname` VARCHAR(32) NOT NULL,
	`name` VARCHAR(32) NOT NULL,
	`strasse` VARCHAR(32) NOT NULL,
	`ort` VARCHAR(32) NOT NULL,
	`statusorig` VARCHAR(12) NOT NULL,
	`email` VARCHAR(32) NOT NULL,
	`telefon` VARCHAR(32) NOT NULL,
	`password` VARCHAR(64) NOT NULL,
    `pwd_login` VARCHAR(255) DEFAULT NULL,
    `role` SMALLINT DEFAULT NULL,
    `last_seen` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `password` (`password`),
    KEY `role` (`role`)
);

-- ----------------------------
-- Table structure for slots
-- ----------------------------
DROP TABLE IF EXISTS `slots`;
CREATE TABLE `slots` (
    `bs_kursId` VARCHAR(32) NOT NULL,
	`room_name` VARCHAR(32) NOT NULL,
    `room_name_short` VARCHAR(12) NOT NULL,
    `img_url` VARCHAR(255) DEFAULT NULL,
    `opens_at` TIME NOT NULL,
    `closes_at` TIME NOT NULL,
    `schedule` INT(3) DEFAULT NULL,
    PRIMARY KEY (`bs_kursId`),
    KEY `room_name_short` (`room_name_short`)
);

-- ----------------------------
-- Table structure for bookings
-- ----------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
    `booking_id` SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
    `active` SMALLINT DEFAULT NULL,
    `bs_kursId` VARCHAR(32) NOT NULL,
    `matnr` INT(6) NOT NULL,
    `last_booking` DATETIME DEFAULT NULL,
    PRIMARY KEY (`booking_id`),
    KEY `bs_kursId` (`bs_kursId`),
    KEY `matnr` (`matnr`)
);

-- ----------------------------
-- Table structure for booking history
-- ----------------------------
DROP TABLE IF EXISTS `booking_history`;
CREATE TABLE `booking_history` (
    `bs_kursId` VARCHAR(32) NOT NULL,
	`room_name` VARCHAR(32) NOT NULL,
    `matnr` INT(6) NOT NULL,
    `success` SMALLINT NOT NULL,
    `booked_at` TIME NOT NULL,
    KEY `bs_kursId` (`bs_kursId`),
    KEY `matnr` (`matnr`)
);


INSERT INTO `slots` VALUES ('BS_Kursid_178675', 'RWTH Bibliothek 2', 'bib2', '', 080000, 140000)
INSERT INTO `slots` VALUES ('BS_Kursid_178676', 'RWTH Bibliothek 2', 'bib2', '', 140000, 200000)
INSERT INTO `slots` VALUES ('BS_Kursid_175850', 'FH Jülich Testbib', 'fhb', '', 090000, 110000)
INSERT INTO `slots` VALUES ('BS_Kursid_175850', 'FH Jülich Testbib', 'fhb', '', 090000, 110000)
INSERT INTO `slots` VALUES ('BS_Kursid_178493', 'SemiTemp', 'semitemp', '', 080000, 140000)
