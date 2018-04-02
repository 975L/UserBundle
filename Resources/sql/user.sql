/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for user
-- ----------------------------
-- DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `gender` set('woman','man') DEFAULT NULL,
  `firstname` varchar(48) DEFAULT NULL,
  `lastname` varchar(48) DEFAULT NULL,
  `creation` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `latest_signin` datetime DEFAULT NULL,
  `latest_signout` datetime DEFAULT NULL,
  `token` varchar(40) DEFAULT NULL,
  `password_request` datetime DEFAULT NULL,
  `roles` longtext DEFAULT NULL,
  `locale` varchar(2) DEFAULT NULL
-- Depending on the entity you choose, un-comment the corresponding fields below
-- Take care of trailing comma taht should be added/removed
--  ADDRESS
/*
  `address` varchar(128) DEFAULT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `postal` varchar(10) DEFAULT NULL,
  `town` varchar(64) DEFAULT NULL,
  `country` varchar(64) DEFAULT NULL,
*/
-- BUSINESS
/*
  `business_type` varchar(24) DEFAULT NULL,
  `business_name` varchar(32) DEFAULT NULL,
  `business_address` varchar(128) DEFAULT NULL,
  `business_address2` varchar(128) DEFAULT NULL,
  `business_postal` varchar(10) DEFAULT NULL,
  `business_town` varchar(64) DEFAULT NULL,
  `business_country` varchar(64) DEFAULT NULL,
  `business_siret` char(14) DEFAULT NULL,
  `business_tva` char(13) DEFAULT NULL,
*/
-- SOCIAL
/*
  `social_network` varchar(64) DEFAULT NULL,
  `social_id` varchar(255) DEFAULT NULL,
  `social_token` varchar(255) DEFAULT NULL,
  `social_picture` varchar(255) DEFAULT NULL
*/
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `un_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ---------------------------------
-- Table structure for user_archives
-- ---------------------------------
-- DROP TABLE IF EXISTS `user_archives`;
/*
CREATE TABLE `user_archives` (
  `id` bigint(20) unsigned DEFAULT NULL,
  `identifier` varchar(32) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `gender` set('woman','man') DEFAULT NULL,
  `firstname` varchar(48) DEFAULT NULL,
  `lastname` varchar(48) DEFAULT NULL,
  `creation` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `latest_signin` datetime DEFAULT NULL,
  `latest_signout` datetime DEFAULT NULL,
  `token` varchar(40) DEFAULT NULL,
  `password_request` datetime DEFAULT NULL,
  `roles` longtext DEFAULT NULL,
  `locale` varchar(24) DEFAULT NULL
-- Depending on the entity you choose, un-comment the corresping fields below
--  ADDRESS
--  `address` varchar(128) DEFAULT NULL,
--  `address2` varchar(128) DEFAULT NULL,
--  `postal` varchar(10) DEFAULT NULL,
--  `town` varchar(64) DEFAULT NULL,
--  `country` varchar(64) DEFAULT NULL,
-- BUSINESS
--  `business_type` varchar(24) DEFAULT NULL,
--  `business_name` varchar(32) DEFAULT NULL,
--  `business_address` varchar(128) DEFAULT NULL,
--  `business_address2` varchar(128) DEFAULT NULL,
--  `business_postal` varchar(10) DEFAULT NULL,
--  `business_town` varchar(64) DEFAULT NULL,
--  `business_country` varchar(64) DEFAULT NULL,
--  `business_siret` char(14) DEFAULT NULL,
--  `business_tva` char(13) DEFAULT NULL,
-- SOCIAL
--  `social_network` varchar(64) DEFAULT NULL,
--  `social_id` varchar(255) DEFAULT NULL,
--  `social_token` varchar(255) DEFAULT NULL,
--  `social_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/


-- --------------------------------------
-- sp_UserArchive
-- --------------------------------------
-- Archives the User
/*
DROP PROCEDURE IF EXISTS sp_UserArchive;
DELIMITER $
CREATE PROCEDURE sp_UserArchive(qId BIGINT(20))
LANGUAGE SQL NOT DETERMINISTIC CONTAINS SQL SQL SECURITY INVOKER
BEGIN
    -- Inserts User in archive
    INSERT INTO user_archives
        SELECT *
        FROM user
        WHERE (id = qId);
END$
DELIMITER ;
*/