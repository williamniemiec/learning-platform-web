-- ----------------------------------------------------------------------------
-- O código foi escrito com base no MySQL 5.7.26 e foi utilizado o programa 
-- HeidiSQL versão 10.2.0 para testar a execução do código.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Criação do banco de dados
-- ----------------------------------------------------------------------------
CREATE DATABASE learning_platform
CHARACTER SET utf8 COLLATE utf8_general_ci;

USE learning_platform;


-- ----------------------------------------------------------------------------
-- 		Criação de tabelas
-- ----------------------------------------------------------------------------
CREATE TABLE students (
	id_student	INT 			NOT NULL	AUTO_INCREMENT,
	name		VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(name) > 0),
	genre		BIT(1)			NOT NULL,
	birthdate	DATE			NOT NULL,
	email		VARCHAR(100)	NOT NULL	UNIQUE,
	password	VARCHAR(32)		NOT NULL 	CHECK (CHAR_LENGTH(password) = 32),
	photo		VARCHAR(40) 	CHECK (CHAR_LENGTH(photo)),

	PRIMARY KEY (id_student)
) ENGINE=InnoDB;

CREATE TABLE notifications (
	id_notification	INT 			NOT NULL 	AUTO_INCREMENT,
	id_student		INT				NOT NULL,
	date 			DATETIME		NOT NULL,
	id_reference	INT 			NOT NULL 	CHECK (id_reference > 0),
	type 			BIT(1)			NOT NULL,
	message			TEXT			NOT NULL 	CHECK (CHAR_LENGTH(message) > 0),
	`read`			BIT(1)			DEFAULT		0,

	PRIMARY KEY (id_notification),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE modules (
	id_module	INT 			NOT NULL 	AUTO_INCREMENT,
	name 		VARCHAR(50)		NOT NULL,

	PRIMARY KEY (id_module)
) ENGINE=InnoDB;

CREATE TABLE courses (
	id_course 	INT 			NOT NULL 	AUTO_INCREMENT,
	name		VARCHAR(50)		NOT NULL,
	logo		VARCHAR(40) 	CHECK (CHAR_LENGTH(logo) > 0),
	description	VARCHAR(200) 	CHECK (CHAR_LENGTH(description) > 0),

	PRIMARY KEY (id_course)
) ENGINE=InnoDB;

CREATE TABLE course_modules (
	id_course		INT 			NOT NULL,
	id_module		INT 			NOT NULL,
	module_order	INT 			NOT NULL 	CHECK (module_order > 0),

	PRIMARY KEY (id_module, id_course),
	UNIQUE (id_course, module_order),
	FOREIGN KEY (id_module) REFERENCES modules(id_module)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (id_course) REFERENCES courses(id_course)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bundles (
	id_bundle	INT 			NOT NULL 	AUTO_INCREMENT,
	name		VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(name) > 0),
	price		DECIMAL(6,2)	NOT NULL 	CHECK (price >= 0),
	logo		VARCHAR(40) 	CHECK (CHAR_LENGTH(logo) > 0),	
	description	VARCHAR(100) 	CHECK (CHAR_LENGTH(description) > 0),

	PRIMARY KEY (id_bundle)
) ENGINE=InnoDB;

CREATE TABLE bundle_courses (
	id_bundle	INT 			NOT NULL,
	id_course	INT 			NOT NULL,

	PRIMARY KEY (id_bundle, id_course),
	FOREIGN KEY (id_bundle) REFERENCES bundles(id_bundle)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (id_course) REFERENCES courses(id_course)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE purchases (
	id_student	INT 			NOT NULL,
	id_bundle	INT 			NOT NULL,
	date 		DATETIME		NOT NULL,
	price		DECIMAL(6,2)	NOT NULL 	CHECK (price >= 0),

	PRIMARY KEY (id_student, id_bundle),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (id_bundle) REFERENCES bundles(id_bundle)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE questionnaires (
	id_module		INT 			NOT NULL,
	class_order		INT 			NOT NULL,
	question 		VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(question) > 0),
	q1				VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(q1) > 0),
	q2				VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(q2) > 0),
	q3				VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(q3) > 0),
	q4				VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(q4) > 0),
	answer			BIT(3)			NOT NULL 	CHECK (answer >= 1 && answer <= 4),

	PRIMARY KEY (id_module, class_order),
	FOREIGN KEY (id_module) REFERENCES modules(id_module)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE videos (
	id_module		INT 			NOT NULL,
	class_order		INT 			NOT NULL,
	title			VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(title) > 0),
	description		VARCHAR(200) 	CHECK (CHAR_LENGTH(description) > 0),
	videoID			VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(videoID) > 0),
	length			INT 			NOT NULL 	CHECK (length > 0),

	PRIMARY KEY (id_module, class_order),
	FOREIGN KEY (id_module) REFERENCES modules(id_module)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_historic (
	id_student		INT 		NOT NULL,
	id_module		INT 		NOT NULL,
	class_order		INT 		NOT NULL,
	class_type		BIT(1)		NOT NULL,
	date 			DATE		NOT NULL,

	PRIMARY KEY (id_student, id_module, class_order),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comments (
	id_comment		INT 			NOT NULL	AUTO_INCREMENT,
	id_student		INT,
	id_course		INT 			NOT NULL,
	id_module		INT 			NOT NULL,
	class_order		INT 			NOT NULL,
	date			DATETIME		NOT NULL,
	text			TEXT 			NOT NULL,

	PRIMARY KEY (id_comment),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (id_module, class_order) REFERENCES videos(id_module, class_order)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (id_course) REFERENCES courses(id_course)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comment_replies (
	id_reply		INT 			NOT NULL 	AUTO_INCREMENT,
	id_student		INT,
	id_comment		INT 			NOT NULL,
	date			DATETIME 		NOT NULL,
	text			TEXT 			NOT NULL 	CHECK (CHAR_LENGTH(text) > 0),

	PRIMARY KEY (id_reply),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE SET NULL,
	FOREIGN KEY (id_comment) REFERENCES comments(id_comment)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notebook (
	id_note			INT 			NOT NULL 	AUTO_INCREMENT,
	id_student		INT 			NOT NULL,
	id_module		INT 			NOT NULL,
	class_order		INT 			NOT NULL,
	title			VARCHAR(100)	NOT NULL 	CHECK(CHAR_LENGTH(title) > 0),
	content			TEXT 			NOT NULL	CHECK(CHAR_LENGTH(content) > 0),
	date			DATETIME		NOT NULL,

	PRIMARY KEY (id_note),
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE CASCADE,
	FOREIGN KEY (id_module, class_order) REFERENCES videos(id_module, class_order)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE support_topic_category (
	id_category		INT 			NOT NULL 	AUTO_INCREMENT,
	name			VARCHAR(50)		NOT NULL 	CHECK (CHAR_LENGTH(name) > 0),

	PRIMARY KEY (id_category)
) ENGINE=InnoDB;

CREATE TABLE support_topic (
	id_topic		INT 			NOT NULL 	AUTO_INCREMENT,
	id_category 	INT 			NOT NULL,
	id_student 		INT 			NOT NULL,
	title			VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(title) > 0),
	date 			DATETIME 		NOT NULL,
	message 	 	TEXT 			NOT NULL 	CHECK (CHAR_LENGTH(message) > 0),
	closed 			BIT(1) 			DEFAULT 	0,

	PRIMARY KEY (id_topic),
	FOREIGN KEY (id_category) REFERENCES support_topic_category(id_category)
		ON UPDATE CASCADE,
	FOREIGN KEY (id_student) REFERENCES students(id_student)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE support_topic_replies (
	id_reply 		INT 			NOT NULL 	AUTO_INCREMENT,
	id_topic 		INT 			NOT NULL,
	id_user 		INT 			NOT NULL,
	date 			DATETIME 		NOT NULL,
	user_type 		BIT(1)			NOT NULL,
	text 			TEXT 			NOT NULL 	CHECK (CHAR_LENGTH(text) > 0),

	PRIMARY KEY (id_reply),
	FOREIGN KEY (id_topic) REFERENCES support_topic(id_topic)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

-- OBS: Apesar de o nome ser o mesmo que a da tabela comment_replies, id_reply
-- não é uma chave estrangeira para essa tabela


CREATE TABLE authorization (
	id_authorization	INT 			NOT NULL	AUTO_INCREMENT,
	name				VARCHAR(50)		NOT NULL 	CHECK (CHAR_LENGTH(name) > 0),
	level 				INT 			NOT NULL 	CHECK (level >= 0),

	PRIMARY KEY (id_authorization)
) ENGINE=InnoDB;

CREATE TABLE admins (
	id_admin 			INT 			NOT NULL	AUTO_INCREMENT,
	id_authorization	INT 			NOT NULL,
	name				VARCHAR(100)	NOT NULL 	CHECK (CHAR_LENGTH(name) > 0),
	genre				BIT(1)			NOT NULL,
	birthdate			DATE			NOT NULL,
	email				VARCHAR(100)	NOT NULL	UNIQUE,
	password			VARCHAR(32)		NOT NULL 	CHECK (CHAR_LENGTH(text) = 32),

	PRIMARY KEY (id_admin),
	FOREIGN KEY (id_authorization) REFERENCES authorization(id_authorization)
		ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE actions (
	id_action 			INT 			NOT NULL 	AUTO_INCREMENT,
	date				DATETIME		NOT NULL,
	id_admin			INT				NOT NULL,
	description 		VARCHAR(200)	NOT NULL 	CHECK (CHAR_LENGTH(description) > 0),

	PRIMARY KEY (id_action),
	FOREIGN KEY (id_admin) REFERENCES admins(id_admin)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;
