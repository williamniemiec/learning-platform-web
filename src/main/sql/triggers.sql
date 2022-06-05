-- ----------------------------------------------------------------------------
-- Code developed on MySQL 5.7.26.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Triggers
-- ----------------------------------------------------------------------------
DELIMITER $$
--
-- When inserting a record in the purchases table, it obtains the value of the 
-- package and adds it to the insertion query, in order to allow insertions in
-- the purchases table without having to inform the package price.
--
CREATE TRIGGER tg_purchases_new
BEFORE INSERT ON purchases
FOR EACH ROW
BEGIN
	CALL sp_bundle_price(NEW.id_bundle, @bundle_price);

	SET NEW.price = @bundle_price;
END
$$

DELIMITER $$
--
-- Checks if the admin email is already being used on the system by another
-- admin or a student. If yes, it prevents the insertion of the new
-- administrator.
--
CREATE TRIGGER tg_admins_new
BEFORE INSERT ON admins
FOR EACH ROW
BEGIN
	CALL sp_check_email_already_used(NEW.email, @alreadyInUse);
	
	IF @alreadyInUse = 1 THEN 
		SIGNAL SQLSTATE '45000' SET message_text = 'Email aready in use';
	END IF;
END
$$

DELIMITER $$
--
-- Checks if the student's email is already being used in the system by another
-- student or an administrator.
--
CREATE TRIGGER tg_students_new
BEFORE INSERT ON students
FOR EACH ROW
BEGIN
	CALL sp_check_email_already_used(NEW.email, @alreadyInUse);
	
	IF @alreadyInUse = 1 THEN 
		SIGNAL SQLSTATE '45000' SET message_text = 'Email aready in use';
	END IF;
END
$$

DELIMITER $$
--
-- Generates a notification for the student who created the comment.
--
CREATE TRIGGER tg_comments_replies_notification
AFTER INSERT ON comment_replies
FOR EACH ROW
BEGIN
	CALL sp_comments_get_creator(NEW.id_comment, @id_creator);

	IF @id_creator != NEW.id_student THEN
		INSERT INTO notifications
		(id_student, date, id_reference, type, message)
		VALUES (@id_creator, NOW(), NEW.id_comment, 0, "Your comment was replied");
	END IF;
END
$$

DELIMITER $$
--
-- Generates a notification to the student who created the topic.
--
CREATE TRIGGER tg_support_topic_replies_notification
AFTER INSERT ON support_topic_replies
FOR EACH ROW
BEGIN
	CALL sp_support_topic_get_creator(NEW.id_topic, @id_creator);

	IF NEW.user_type = 1 THEN
		INSERT INTO notifications
		(id_student, date, id_reference, type, message)
		VALUES (@id_creator, NOW(), NEW.id_topic, 1, "Your topic was replied");
	END IF;
END
$$


-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
--			Compensatory policy
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- admins
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When updating an admin, it also updates, if any, the answers they gave in
-- support topics.
--
CREATE TRIGGER tg_admins_update_support_topic
AFTER UPDATE ON admins
FOR EACH ROW
BEGIN
	UPDATE 	support_topic_replies
	SET 	support_topic_replies.id_user = NEW.id_admin
	WHERE	support_topic_replies.id_user = OLD.id_admin AND user_type = 1;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- students
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
--
-- When updating a student, it also updates, if any, the answers they gave in 
-- support topics.
--
DELIMITER $$
CREATE TRIGGER tg_students_update_support_topic
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
	UPDATE support_topic_replies
	SET 	support_topic_replies.id_user = NEW.id_student
	WHERE	support_topic_replies.id_user = OLD.id_student AND user_type = 0;
END
$$

--
-- Removing a student also removes, if any, replies from all topics that the 
-- student has created.
--
DELIMITER $$
CREATE TRIGGER tg_students_delete_support_topic
AFTER DELETE ON students
FOR EACH ROW
BEGIN
	DELETE FROM support_topic_replies
	WHERE user_type = 0 AND id_user = OLD.id_student;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- support_topic
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When updating a support topic, it also updates notifications that reference
-- it.
--
CREATE TRIGGER tg_support_topic_update_notifications
AFTER UPDATE ON support_topic
FOR EACH ROW
BEGIN
	UPDATE notifications
	SET id_reference = NEW.id_topic
	WHERE id_reference = OLD.id_topic AND type = 1;
END
$$

DELIMITER $$
--
-- Removing a topic from support also removes notifications that reference it.
--
CREATE TRIGGER tg_support_topic_delete_notifications
AFTER DELETE ON support_topic
FOR EACH ROW
BEGIN
	DELETE FROM notifications
	WHERE id_reference = OLD.id_topic AND type = 1;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- comments
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When updating a comment, it also updates notifications that reference it.
--
CREATE TRIGGER tg_comments_update_notifications
AFTER UPDATE ON comments
FOR EACH ROW
BEGIN
	UPDATE notifications
	SET id_reference = NEW.id_comment
	WHERE id_reference = OLD.id_comment AND type = 0;
END
$$

DELIMITER $$
--
-- Removing a comment also removes notifications that reference it.
--
CREATE TRIGGER tg_comments_delete_notifications
AFTER DELETE ON comments
FOR EACH ROW
BEGIN
	DELETE FROM notifications
	WHERE id_reference = OLD.id_comment AND type = 1;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- questionnaires
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When updating a quiz class, it also updates the history of the students who 
-- attended it.
--
CREATE TRIGGER tg_questionnaires_update_student_historic
AFTER UPDATE ON questionnaires
FOR EACH ROW
BEGIN
	UPDATE student_historic
	SET id_module = NEW.id_module, class_order = NEW.class_order
	WHERE id_module = OLD.id_module AND class_order = OLD.class_order;
END
$$

--
-- Removing a quiz class also removes it from the history of students who 
-- attended it.
--
DELIMITER $$
CREATE TRIGGER tg_questionnaires_delete_student_historic
AFTER DELETE ON questionnaires
FOR EACH ROW
BEGIN
	DELETE FROM student_historic
	WHERE id_module = OLD.id_module AND class_order = OLD.class_order;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- videos
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When updating a video class, it also updates the history of the students who
-- watched it.
--
CREATE TRIGGER tg_videos_update_student_historic
AFTER UPDATE ON videos
FOR EACH ROW
BEGIN
	UPDATE student_historic
	SET id_module = NEW.id_module, class_order = NEW.class_order
	WHERE id_module = OLD.id_module AND class_order = OLD.class_order;
END
$$

DELIMITER $$
--
-- When removing a video class, it also removes it from the history of the 
-- students who watched it.
--
CREATE TRIGGER tg_videos_delete_student_historic
AFTER DELETE ON videos
FOR EACH ROW
BEGIN
	DELETE FROM student_historic
	WHERE id_module = OLD.id_module AND class_order = OLD.class_order;
END
$$


-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
--			Referential integrity constraint
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- notifications
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When creating a notification, check if its reference exists. That is, it 
-- checks if there is a comment or support topic with the id_reference. To see
-- if the id_reference references a comment or support topic, check the `type`
-- attribute. If type is 0, references a comment; otherwise, references a
-- support topic.
--
CREATE TRIGGER tg_notifications_new
BEFORE INSERT ON notifications
FOR EACH ROW
BEGIN
	IF NEW.type = 0 THEN
		CALL sp_comments_exists(NEW.id_reference, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text =	"id_reference - There is no comment with 
								the specified id";
		END IF;
	ELSE
		CALL sp_support_topic_exists(NEW.id_reference, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text =	"id_reference - There is no support topic with
								the specified id";
		END IF;
	END IF;
END
$$

DELIMITER $$
--
-- When modifying a notification, check if its reference exists. That is, it 
-- checks if there is a comment or support topic with the id_reference. To see
-- if the id_reference references a comment or support topic, check the `type`
-- attribute. If type is 0, references a comment; otherwise, references a
-- support topic.
--
CREATE TRIGGER tg_notifications_update
BEFORE UPDATE ON notifications
FOR EACH ROW
BEGIN
	IF OLD.type = 0 THEN
		CALL sp_comments_exists(OLD.id_reference, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text = 	"id_reference - There is no comment with the 
								specified id";
		END IF;
	ELSE
		CALL sp_support_topic_exists(OLD.id_reference, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text =	"id_reference - There is no support topic with
								the specified id";
		END IF;
	END IF;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- support_topic_replies
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When creating an answer for a support topic, it checks that it is open and
-- that the reference of the user who created the answer exists (that is, it
-- checks if there is a student or administrator with the same id_user). To
-- see if id_user references a student or administrator, check the `user_type`
-- attribute. If type is 0, references a student; otherwise, references an
-- administrator.
--
CREATE TRIGGER tg_support_topic_replies_new
BEFORE INSERT ON support_topic_replies
FOR EACH ROW
BEGIN
	CALL sp_support_topic_is_open(NEW.id_topic, @isOpen);

	IF @isOpen = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET message_text = "Support topic is closed";
	END IF;

	IF NEW.user_type = 0 THEN
		CALL sp_students_exists(NEW.id_user, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text = "id_user - There is no student with the specified id";
		END IF;
	ELSE
		CALL sp_admins_exists(NEW.id_user, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text = "id_user - There is no admin with the specified id";
		END IF;
	END IF;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- student_historic
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When adding a class to the student history, check if it exists. For that, it
-- checks the `class_type` attribute. If it is 0, checks if there is a record
-- in the 'videos' table with a primary key corresponding to the `id_module`
-- and `class_order` attributes that were added; if it is 1, do the same but
-- the check will be in the 'questionnaires' table.
--
CREATE TRIGGER tg_student_historic_new
BEFORE INSERT ON student_historic
FOR EACH ROW
BEGIN
	IF NEW.class_type = 0 THEN
		CALL sp_class_exists(0, NEW.id_module, NEW.class_order, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text =	"There is no video class with the specified
								id_module and class_order";
		END IF;
	ELSE
		CALL sp_class_exists(1, NEW.id_module, NEW.class_order, @exists);

		IF @exists = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET message_text =	"There is no questionnaire class with the specified
								id_module and class_order";
		END IF;
	END IF;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
--			Restrição de integridade de chave
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- videos
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When adding a video class, check if there is already a class that belongs
-- to the same module and with the same order within it. If yes, it will
-- generate an error.
--
CREATE TRIGGER tg_videos_new
BEFORE INSERT ON videos
FOR EACH ROW
BEGIN
	CALL sp_class_exists(1, NEW.id_module, NEW.class_order, @exists);

	IF @exists = 1 THEN
		SIGNAL SQLSTATE '45000' 
		SET message_text =	"There is a questionnaire class with the same module id
							and class order";
	END IF;
END
$$

DELIMITER $$
--
-- When editing a video class, check if there is already a class that belongs
-- to the same module with the same order within it. If yes, it will generate
-- an error.
--
CREATE TRIGGER tg_videos_update
BEFORE UPDATE ON videos
FOR EACH ROW
BEGIN
	CALL sp_class_exists(1, OLD.id_module, OLD.class_order, @exists);

	IF @exists = 1 THEN
		SIGNAL SQLSTATE '45000' 
		SET message_text =	"There is a questionnaire class with the same module id
							and class order";
	END IF;
END
$$

-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- questionnaires
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- When adding a quiz type class, check if there is already a class that
-- belongs to the same module and with the same order within it. If yes, it
-- will generate an error.
--
CREATE TRIGGER tg_questionnaires_new
BEFORE INSERT ON questionnaires
FOR EACH ROW
BEGIN
	CALL sp_class_exists(0, NEW.id_module, NEW.class_order, @exists);

	IF @exists = 1 THEN
		SIGNAL SQLSTATE '45000' 
		SET message_text =	"There is a video class with the same module id 
							and class order";
	END IF;
END
$$

DELIMITER $$
--
-- When editing a quiz type class, check if there is already a class that
-- belongs to the same module with the same order within it. If yes, it will
-- generate an error.
--
CREATE TRIGGER tg_questionnaires_update
BEFORE UPDATE ON questionnaires
FOR EACH ROW
BEGIN
	CALL sp_class_exists(1, OLD.id_module, OLD.class_order, @exists);

	IF @exists = 1 THEN
		SIGNAL SQLSTATE '45000' 
		SET message_text =	"There is a video class with the same module id
							and class order";
	END IF;
END
$$
