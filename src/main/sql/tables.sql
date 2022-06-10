--
-- Copyright (c) William Niemiec.
--
-- This source code is licensed under the MIT license found in the
-- LICENSE file in the root directory of this source tree.
--

-- ----------------------------------------------------------------------------
-- Code developed on MySQL 5.7.26.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Database creation
-- ----------------------------------------------------------------------------
CREATE DATABASE learning_platform
CHARACTER SET utf8 COLLATE utf8_general_ci;

USE learning_platform;


-- ----------------------------------------------------------------------------
-- 		Table creation
-- ----------------------------------------------------------------------------
CREATE TABLE students (
	id_student	INT 			NOT NULL	AUTO_INCREMENT,
	name		VARCHAR(100)	NOT NULL,
	genre		INT				NOT NULL,
	birthdate	DATE			NOT NULL,
	email		VARCHAR(100)	NOT NULL	UNIQUE,
	password	VARCHAR(32)		NOT NULL,
	photo		VARCHAR(40),

	PRIMARY KEY (id_student)
) ENGINE=InnoDB;

CREATE TABLE notifications (
	id_notification	INT 			NOT NULL 	AUTO_INCREMENT,
	id_student		INT				NOT NULL,
	date 			DATETIME		NOT NULL,
	id_reference	INT 			NOT NULL,
	type 			INT				NOT NULL,
	message			TEXT			NOT NULL,
	`read`			INT				DEFAULT		0,

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
	logo		VARCHAR(40),
	description	VARCHAR(200),

	PRIMARY KEY (id_course)
) ENGINE=InnoDB;

CREATE TABLE course_modules (
	id_course		INT 			NOT NULL,
	id_module		INT 			NOT NULL,
	module_order	INT 			NOT NULL,

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
	name		VARCHAR(100)	NOT NULL,
	price		DECIMAL(6,2)	NOT NULL,
	logo		VARCHAR(40),	
	description	VARCHAR(100),

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
	price		DECIMAL(6,2)	NOT NULL,

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
	question 		VARCHAR(100)	NOT NULL,
	q1				VARCHAR(100)	NOT NULL,
	q2				VARCHAR(100)	NOT NULL,
	q3				VARCHAR(100)	NOT NULL,
	q4				VARCHAR(100)	NOT NULL,
	answer			BIT(3)			NOT NULL,

	PRIMARY KEY (id_module, class_order),
	FOREIGN KEY (id_module) REFERENCES modules(id_module)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE videos (
	id_module		INT 			NOT NULL,
	class_order		INT 			NOT NULL,
	title			VARCHAR(100)	NOT NULL,
	description		VARCHAR(200),
	videoID			VARCHAR(100)	NOT NULL,
	length			INT 			NOT NULL,

	PRIMARY KEY (id_module, class_order),
	FOREIGN KEY (id_module) REFERENCES modules(id_module)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_historic (
	id_student		INT 		NOT NULL,
	id_module		INT 		NOT NULL,
	class_order		INT 		NOT NULL,
	class_type		INT			NOT NULL,
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
	text			TEXT 			NOT NULL,

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
	title			VARCHAR(100)	NOT NULL,
	content			TEXT 			NOT NULL	,
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
	name			VARCHAR(50)		NOT NULL,

	PRIMARY KEY (id_category)
) ENGINE=InnoDB;

CREATE TABLE support_topic (
	id_topic		INT 			NOT NULL 	AUTO_INCREMENT,
	id_category 	INT 			NOT NULL,
	id_student 		INT 			NOT NULL,
	title			VARCHAR(100)	NOT NULL,
	date 			DATETIME 		NOT NULL,
	message 	 	TEXT 			NOT NULL,
	closed 			INT 			DEFAULT 	0,

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
	user_type 		INT				NOT NULL,
	text 			TEXT 			NOT NULL,

	PRIMARY KEY (id_reply),
	FOREIGN KEY (id_topic) REFERENCES support_topic(id_topic)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

-- Note: Even though the name is the same as that of the 'comment_replies' 
-- table, 'id_reply' is not a foreign key to that table.


CREATE TABLE authorization (
	id_authorization	INT 			NOT NULL	AUTO_INCREMENT,
	name				VARCHAR(50)		NOT NULL,
	level 				INT 			NOT NULL,

	PRIMARY KEY (id_authorization)
) ENGINE=InnoDB;

CREATE TABLE admins (
	id_admin 			INT 			NOT NULL	AUTO_INCREMENT,
	id_authorization	INT 			NOT NULL,
	name				VARCHAR(100)	NOT NULL,
	genre				INT				NOT NULL,
	birthdate			DATE			NOT NULL,
	email				VARCHAR(100)	NOT NULL	UNIQUE,
	password			VARCHAR(32)		NOT NULL,

	PRIMARY KEY (id_admin),
	FOREIGN KEY (id_authorization) REFERENCES authorization(id_authorization)
		ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE actions (
	id_action 			INT 			NOT NULL 	AUTO_INCREMENT,
	date				DATETIME		NOT NULL,
	id_admin			INT				NOT NULL,
	description 		VARCHAR(200)	NOT NULL,

	PRIMARY KEY (id_action),
	FOREIGN KEY (id_admin) REFERENCES admins(id_admin)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;
