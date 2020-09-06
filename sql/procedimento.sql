-- ----------------------------------------------------------------------------
-- 		Stored procedures
-- ----------------------------------------------------------------------------
DELIMITER $$
-- Obtem o preço de um pacote.
-- 
-- @param		id_bundle Id do pacote
--
-- @return		Preço do pacote com o id fornecido
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
-- Verifica se um email está sendo usado no sistema por um administrador
-- ou por um estudante.
-- 
-- @param		user_email Email a ser verificado
--
-- @return		1 se o email já esta em uso ou 0 caso contrário
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
-- Obtem o estudante que criou um comentário.
--
-- @param		comment Id do comentário
--
-- @return		Id do estudante que criou o comentário
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
-- Obtem o estudante que criou um tópico de suporte.
--
-- @param		topic Id do tópico de suporte
--
-- @return		Id do estudante que criou o tópico de suporte
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
-- Verifica se existe um comentário com um determinado id.
--
-- @param		comment Id do comentário
--
-- @return		1 se existe; 0 caso contrário
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
-- Verifica se existe um tópico de suporte com um determinado id.
--
-- @param		topic Id do tópico de suporte
--
-- @return		1 se existe; 0 caso contrário
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
-- Verifica se existe um estudante com um determinado id.
--
-- @param		student Id do estudante
--
-- @return		1 se existe; 0 caso contrário
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
-- Verifica se existe um admin com um determinado id.
--
-- @param		admin Id do admin
--
-- @return		1 se existe; 0 caso contrário
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
-- Verifica se uma aula existe.
--
-- @param		classType Tipo da aula (0 se for video e 1 se for questionário)
-- @param		module Modulo que a aula pertence
-- @param		classOrder Ordem da aula dentro do módulo
--
-- @return		1 se existe; 0 caso contrário
--
CREATE PROCEDURE sp_class_exists(IN classType BIT(1), IN module INT, IN classOrder INT, OUT result BIT(1))
BEGIN
	DECLARE r INT;
	DECLARE c CURSOR FOR 
		SELECT * FROM __tmp;

	-- Cria uma tabela temporária, visto que cursores não podem ser declarados em blocos if-else
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
-- Verifica se um tópico do suporte está aberto.
--
-- @param		topic Id do tópico
--
-- @return		1 se está aberto; 0 caso contrário
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

-- ----------------------------------------------------------------------------
-- 		Triggers
-- ----------------------------------------------------------------------------
DELIMITER $$
--
-- Ao inserir um registro na tabela purchases, obtem o valor do pacote e
-- adiciona ele na query de inserção, a fim de permitir inserções na tabela
-- purchases sem precisar informar o preço do pacote.
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
-- Verifica se o email do administrador já está sendo usado no sistema por
-- outro administrador ou por um estudante. Se sim, impede a inserção do
-- novo administrador
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
-- Verifica se o email do estudante já está sendo usado no sistema por
-- outro estudante ou por um administrador
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
-- Gera uma notificação para o estudante que criou o comentário.
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
-- Gera uma notificação para o estudante que criou o tópico.
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
--			Política compensatória
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- admins
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- Ao atualizar um administrador, atualiza também, se houver, as respostas
-- que ele deu em topicos de suporte
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
-- Ao atualizar um estudante, atualiza também, se houver, as respostas
-- que ele deu em topicos de suporte
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
-- Ao remover um estudante, remove também, se houver, as respostas
-- de todos os tópicos que ele criou
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
-- Ao atualizar um tópico do suporte, atualiza também as notificações que fazem
-- referência a ele
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
-- Ao remover um tópico do suporte, remove também as notificações que fazem
-- referência a ele
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
-- Ao atualizar um comentário, atualiza também as notificações que fazem
-- referência a ele
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
-- Ao remover um comentário, remove também as notificações que fazem
-- referência a ele
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
-- Ao atualizar uma aula do tipo questionário, atualiza também o histórico dos
-- estudantes que assistiram a ela
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
-- Ao remover uma aula do tipo questionário, remove ela também do histórico dos
-- estudantes que assistiram a ela
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
-- Ao atualizar uma aula do tipo video, atualiza também o histórico dos
-- estudantes que assistiram a ela
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
-- Ao remover uma aula do tipo video, remove ela também do histórico dos
-- estudantes que assistiram a ela
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
--			Restrição de integridade referencial
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
-- notifications
-- -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
DELIMITER $$
--
-- Ao criar uma notificação, verifica se a referência dela existe. Ou seja,
-- é verificado se existe um comentário ou topico de suporte com o id_reference.
-- Para saber se o id_reference referencia um comentário ou tópico de suporte
-- é verificado o atributo `type`. Se type for 0, referencia um comentário; caso
-- contrário, referencia um topico do suporte.
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
-- Ao modificar uma notificação, verifica se a referência dela existe. Ou seja,
-- é verificado se existe um comentário ou topico de suporte com o id_reference.
-- Para saber se o id_reference referencia um comentário ou tópico de suporte
-- é verificado o atributo `type`. Se type for 0, referencia um comentário; caso
-- contrário, referencia um topico do suporte.
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
-- Ao criar uma resposta para um tópico do suporte, verifica se ele está aberto
-- e se a referência do usuário que criou a resposta existe (ou seja, é 
-- verificado se existe um estudante ou administrador com o mesmo id_user). Para
-- saber se o id_user referencia um estudante ou administrador, é verificado o 
-- atributo `user_type`. Se type for 0, referencia um estudante; caso contrário,
-- referencia um administrador.
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
-- Ao adicionar um aula no histórico dos estudantes, verifica se ela existe.
-- Para isso, verifica o atributo `class_type`. Se for 0, verifica se existe
-- na tabela 'videos' um registro com chave primária correspondente aos atributos
-- `id_module` e `class_order` que foram adicionados; se for 1, faz o mesmo mas
-- a verificação será na tabela 'questionnaires'
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
-- Ao adicionar uma aula do tipo vídeo, verifica se já existe uma aula que
-- pertença ao mesmo módulo e com a mesma ordem dentro dele. Se sim, gerará um
-- erro.
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
-- Ao editar uma aula do tipo vídeo, verifica se já existe uma aula que
-- pertença ao mesmo módulo com a mesma ordem dentro dele. Se sim, gerará um 
-- erro. 
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
-- Ao adicionar uma aula do tipo questionário, verifica se já existe uma aula 
-- que pertença ao mesmo módulo e com a mesma ordem dentro dele. Se sim, gerará
-- um erro.
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
-- Ao editar uma aula do tipo questionário, verifica se já existe uma aula que
-- pertença ao mesmo módulo com a mesma ordem dentro dele. Se sim, gerará um 
-- erro. 
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
