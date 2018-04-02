/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/*
 * This script will:
 * - create a `user_migrate` table,
 * - modify all the needed fields,
 * - add missing ones.
 * Then, when you are ready, you can:
 * - rename your FOSUSerBundle table to `user_fosuserbundle` (or whatever you want),
 * - rename the `user_migrate` one to `user`.
 *
 * The `username` and `groups` fields are kept but not used, so you can delete them if you don't use them'.
 *
 * If you want to use the `user_archives` table, you need to create it by using script in user.sql file.
 */

-- Use database
USE YOUR_DATABASE_NAME;

-- Creation of migrate table
CREATE TABLE user_migrate AS SELECT * FROM `user`;

-- Change Charset + Collation
ALTER TABLE user_migrate DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Remove unused fields
ALTER TABLE user_migrate DROP COLUMN username_canonical;
ALTER TABLE user_migrate DROP COLUMN email_canonical;
ALTER TABLE user_migrate DROP COLUMN confirmation_token;

-- Add indexes
ALTER TABLE user_migrate ADD CONSTRAINT PRIMARY KEY (id) USING BTREE;
ALTER TABLE user_migrate ADD CONSTRAINT un_email UNIQUE KEY (email);

-- Modify fields + add token one
ALTER TABLE user_migrate MODIFY COLUMN id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE user_migrate MODIFY COLUMN email varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE user_migrate CHANGE last_login latest_signin datetime NULL;
ALTER TABLE user_migrate ADD token varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER latest_signin;
ALTER TABLE user_migrate CHANGE password_requested_at password_request datetime NULL;
ALTER TABLE user_migrate MODIFY COLUMN salt varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;
ALTER TABLE user_migrate MODIFY COLUMN password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE user_migrate MODIFY COLUMN roles longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;

-- Move fields to right place
ALTER TABLE user_migrate CHANGE username username varchar(255) AFTER roles;
ALTER TABLE user_migrate CHANGE email email varchar(128) AFTER username;
ALTER TABLE user_migrate CHANGE creation creation datetime AFTER email;
ALTER TABLE user_migrate CHANGE enabled enabled tinyint(1) AFTER creation;
ALTER TABLE user_migrate CHANGE salt salt varchar(255) AFTER enabled;
ALTER TABLE user_migrate CHANGE password password varchar(255) AFTER salt;
ALTER TABLE user_migrate CHANGE latest_signin latest_signin datetime AFTER password;
ALTER TABLE user_migrate CHANGE password_request password_request datetime AFTER token;
ALTER TABLE user_migrate CHANGE roles roles longtext AFTER password_request;

-- Add missing fields
-- In the following, un-comment to create the field if not already existing in your table. They are all required by c975L/UserBundle
-- ALTER TABLE user_migrate ADD identifier varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER id;
-- ALTER TABLE user_migrate ADD gender set('woman','man') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER email;
-- ALTER TABLE user_migrate ADD firstname varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER gender;
-- ALTER TABLE user_migrate ADD lastname varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER firstname;
-- ALTER TABLE user_migrate ADD avatar varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER creation;
-- ALTER TABLE user_migrate ADD latest_signout datetime CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER latest_signin;
-- ALTER TABLE user_migrate ADD locale varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER roles;

-- Depending on the entity you choose, un-comment the corresponding fields below
-- Take care of the AFTER value, if you don't use all the fields
-- ADDRESS
/*
ALTER TABLE user_migrate ADD address varchar(128) DEFAULT NULL AFTER roles;
ALTER TABLE user_migrate ADD address2 varchar(128) DEFAULT NULL AFTER address;
ALTER TABLE user_migrate ADD postal varchar(10) DEFAULT NULL AFTER address2;
ALTER TABLE user_migrate ADD town varchar(64) DEFAULT NULL AFTER postal;
ALTER TABLE user_migrate ADD country varchar(64) DEFAULT NULL AFTER country;
*/
-- BUSINESS
/*
ALTER TABLE user_migrate ADD business_type varchar(24) DEFAULT NULL AFTER country;
ALTER TABLE user_migrate ADD business_name varchar(32) DEFAULT NULL AFTER business_type;
ALTER TABLE user_migrate ADD business_address varchar(128) DEFAULT NULL AFTER business_name;
ALTER TABLE user_migrate ADD business_address2 varchar(128) DEFAULT NULL AFTER business_address;
ALTER TABLE user_migrate ADD business_postal varchar(10) DEFAULT NULL AFTER business_address2;
ALTER TABLE user_migrate ADD business_town varchar(64) DEFAULT NULL AFTER business_postal;
ALTER TABLE user_migrate ADD business_country varchar(64) DEFAULT NULL AFTER business_town;
ALTER TABLE user_migrate ADD business_siret char(14) DEFAULT NULL AFTER business_country;
ALTER TABLE user_migrate ADD business_tva char(13) DEFAULT NULL AFTER business_siret;
*/
-- SOCIAL
/*
ALTER TABLE user_migrate ADD social_network varchar(64) DEFAULT NULL AFTER business_tva;
ALTER TABLE user_migrate ADD social_id varchar(255) DEFAULT NULL AFTER social_network;
ALTER TABLE user_migrate ADD social_token varchar(255) DEFAULT NULL AFTER social_id;
ALTER TABLE user_migrate ADD social_picture varchar(255) DEFAULT NULL social_token;
*/