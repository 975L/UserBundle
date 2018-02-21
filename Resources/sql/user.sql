/*
 * (c) 2017: 975l <contact@975l.com>
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
  `email` varchar(128) NOT NULL,
  `gender` set('woman','man') DEFAULT NULL,
  `firstname` varchar(48) DEFAULT NULL,
  `lastname` varchar(48) DEFAULT NULL,
  `creation` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `latest_signin` datetime DEFAULT NULL,
  `latest_signout` datetime DEFAULT NULL,
  `token` varchar(40) DEFAULT NULL,
  `password_request` datetime DEFAULT NULL,
  `roles` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- ---------------------------------
-- Table structure for user_archives
-- ---------------------------------
-- DROP TABLE IF EXISTS `user_archives`;
/*
CREATE TABLE `user_archives` (
  `id` bigint(20) unsigned NOT NULL,
  `email` varchar(128) NOT NULL,
  `gender` set('woman','man') DEFAULT NULL,
  `firstname` varchar(48) DEFAULT NULL,
  `lastname` varchar(48) DEFAULT NULL,
  `creation` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `latest_signin` datetime DEFAULT NULL,
  `latest_signout` datetime DEFAULT NULL,
  `token` varchar(40) DEFAULT NULL,
  `password_request` datetime DEFAULT NULL,
  `roles` longtext DEFAULT NULL
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