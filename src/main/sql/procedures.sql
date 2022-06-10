/**
 * Copyright (c) William Niemiec.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

-- ----------------------------------------------------------------------------
-- Code developed on MySQL 5.7.26.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Stored procedures
-- ----------------------------------------------------------------------------
DELIMITER $$
-- Gets a bundle price.
-- 
-- :param		id_bundle: Bundle id
--
-- :return		Bundle price
CREATE PROCEDURE sp_bundle_price(IN id_bundle INT, OUT bundle_price DECIMAL(6,2))
BEGIN
	DECLARE current_bundle_price DECIMAL(6,2);
	DECLARE `status` BIT(1);
	DECLARE c CURSOR FOR
		SELECT	price
		FROM	bundles
		WHERE	bundles.id_bundle = id_bundle;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET
		`status` = 1;
	
	OPEN c;
	REPEAT FETCH c INTO current_bundle_price;
	UNTIL `status` = 1 END REPEAT;

	CLOSE c;
	SET bundle_price = current_bundle_price;
END
$$

DELIMITER $$
-- 
-- Checks if an email is being used on the system by an administrator or a
-- student.
-- 
-- :param		user_email: Email to be verified
--
-- :return		1 if the email is already in use or 0 otherwise
--
CREATE PROCEDURE sp_check_email_already_used(IN user_email VARCHAR(100), OUT alreadyInUse BIT(1))
BEGIN
	DECLARE result INT;
	DECLARE `status` BIT(1);
	DECLARE c CURSOR FOR
		SELECT COUNT(*) AS already_in_use FROM (
			SELECT 	email
			FROM 	students
			UNION
			SELECT	email
			FROM 	admins) AS users
		WHERE users.email = user_email;

	OPEN c;
	FETCH c INTO result;

	CLOSE c;
	IF result > 0 THEN
		SET alreadyInUse = 1;
	ELSE
		SET alreadyInUse = 0;
	END IF;
END
$$

DELIMITER $$
--
-- Gets the student who created a comment.
--
-- :param		comment: Comment id
--
-- :return		Student id who created the comment
--
CREATE PROCEDURE sp_comments_get_creator(IN comment INT, OUT creator INT)
BEGIN
	DECLARE result INT;
	DECLARE c CURSOR FOR
		SELECT	id_student
		FROM 	comments
		WHERE	id_comment = comment;

	OPEN c;
	FETCH c INTO result;

	CLOSE c;
	SET creator = result;
END
$$

DELIMITER $$
--
-- Gets the student who created a support topic.
--
-- :param		topic: Support topic id
--
-- :return		Student id who created the support topic
--
CREATE PROCEDURE sp_support_topic_get_creator(IN topic INT, OUT creator INT)
BEGIN
	DECLARE result INT;
	DECLARE c CURSOR FOR
		SELECT	id_student
		FROM 	support_topic
		WHERE	id_topic = topic;

	OPEN c;
	FETCH c INTO result;

	CLOSE c;
	SET creator = result;
END
$$

DELIMITER $$
--
-- Checks if there is a comment with an id.
--
-- :param		comment: Comment id
--
-- :return		1 if exists or 0 otherwise
--
CREATE PROCEDURE sp_comments_exists(IN `comment` INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR
		SELECT 	COUNT(*)
		FROM 	comments
		WHERE 	id_comment = `comment`;

	OPEN c;
	FETCH c INTO r;

	CLOSE c;
	IF r = 0 THEN
		SET result = 0;
	ELSE
		SET result = 1;
	END IF;
END
$$

DELIMITER $$
--
-- Checks if there is a support with an id.
--
-- :param		topic: Support topic id
--
-- :return		1 if exists or 0 otherwise
--
CREATE PROCEDURE sp_support_topic_exists(IN topic INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR
		SELECT 	COUNT(*)
		FROM 	support_topic
		WHERE 	id_topic = topic;

	OPEN c;
	FETCH c INTO r;

	CLOSE c;
	IF r = 0 THEN
		SET result = 0;
	ELSE
		SET result = 1;
	END IF;
END
$$

DELIMITER $$
--
-- Checks if there is a student with an id.
--
-- :param		student: Student id
--
-- :return		1 if exists or 0 otherwise
--
CREATE PROCEDURE sp_students_exists(IN student INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR
		SELECT 	COUNT(*)
		FROM 	students
		WHERE 	id_student = student;

	OPEN c;
	FETCH c INTO r;

	CLOSE c;
	IF r = 0 THEN
		SET result = 0;
	ELSE
		SET result = 1;
	END IF;
END
$$

DELIMITER $$
--
-- Checks if there is an admin with an id.
--
-- :param		admin: Admin id
--
-- :return		1 if exists or 0 otherwise
--
CREATE PROCEDURE sp_admins_exists(IN admin INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR
		SELECT 	COUNT(*)
		FROM 	admins
		WHERE 	id_admin = admin;

	OPEN c;
	FETCH c INTO r;

	CLOSE c;
	IF r = 0 THEN
		SET result = 0;
	ELSE
		SET result = 1;
	END IF;
END
$$

DELIMITER $$
--
-- Checks whether a class exists.
--
-- :param		classType: Class type (0 for video and 1 for questionnaires)
-- :param		module: Module that the class belongs to
-- :param		classOrder: Class order within the module
--
-- :return		1 if the class exists or 0 otherwise
--
CREATE PROCEDURE sp_class_exists(IN classType BIT(1), IN module INT, IN classOrder INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR 
		SELECT * FROM __tmp;

	-- Creates a temporary table, as cursors cannot be declared in if-else blocks
	DROP TEMPORARY TABLE IF EXISTS __tmp;
	
	IF classType = 0 THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS __tmp AS (
			SELECT 	COUNT(*)
			FROM 	videos
			WHERE 	id_module = module AND class_order = classOrder
		);
	ELSE 
		CREATE TEMPORARY TABLE IF NOT EXISTS __tmp AS (
			SELECT 	COUNT(*)
			FROM 	questionnaires
			WHERE 	id_module = module AND class_order = classOrder
		);
	END IF;
	
	OPEN c;
	FETCH c INTO r;
	CLOSE c;
	
	IF r = 0 THEN
		SET result = 0;
	ELSE
		SET result = 1;
	END IF;

	DROP TEMPORARY TABLE __tmp;
END
$$

DELIMITER $$
--
-- Checks whether a topic is open.
--
-- :param		topic: Topic id
--
-- :return		1 if the topic is open and 0 otherwise
--
CREATE PROCEDURE sp_support_topic_is_open(IN topic INT, OUT result BiT(1))
BEGIN
	DECLARE c CURSOR FOR
		SELECT	closed
		FROM 	support_topic
		WHERE 	id_topic = topic;

	OPEN c;
	FETCH c INTO result;

	CLOSE c;
	IF result = 0 THEN 
		SET result = 1;
	ELSE 
		SET result = 0;
	END IF;
END
$$
