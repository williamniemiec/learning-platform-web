-- ----------------------------------------------------------------------------
-- Code developed on MySQL 5.7.26.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Creation
-- ----------------------------------------------------------------------------
-- students
-- ----------------------------------------------------------------------------
INSERT INTO students
(name, genre, birthdate, email, password) 
VALUES ("student", 0, '1990-01-01', 'student@lp.com', MD5('teste12345@A'));		-- id_student = 1

INSERT INTO students 
(name, genre, birthdate, email, password) 
VALUES ("Fulano", 0, '1990-01-01', 'fulano@gmail.com', MD5('teste12345@A'));		-- id_student = 2

INSERT INTO students 
(name, genre, birthdate, email, password) 
VALUES ("Beltrano", 0, '1991-01-01', 'beltrano@gmail.com', MD5('teste12345@A'));	-- id_student = 3

INSERT INTO students 
(name, genre, birthdate, email, password, photo) 
VALUES ("Sicrano", 0, '1992-01-01', 'sicrano@gmail.com', MD5('teste12345@A'), 'photo.jpg');	-- id_student = 4

-- ----------------------------------------------------------------------------
-- modules
-- ----------------------------------------------------------------------------
INSERT INTO modules (name) VALUES ("Basic javascript");						-- id_module = 1
INSERT INTO modules (name) VALUES ("Intermediate Javascript");				-- id_module = 2
INSERT INTO modules (name) VALUES ("Advanced javascript");					-- id_module = 3
INSERT INTO modules (name) VALUES ("Introduction to programming");			-- id_module = 4
INSERT INTO modules (name) VALUES ("Basic C");								-- id_module = 5
INSERT INTO modules (name) VALUES ("Intermediate C");						-- id_module = 6
INSERT INTO modules (name) VALUES ("Advanced C");							-- id_module = 7
INSERT INTO modules (name) VALUES ("Basic C++");							-- id_module = 8
INSERT INTO modules (name) VALUES ("Intermediate C++");						-- id_module = 9
INSERT INTO modules (name) VALUES ("Advanced C++");							-- id_module = 10
INSERT INTO modules (name) VALUES ("Basic Assembly");						-- id_module = 11
INSERT INTO modules (name) VALUES ("Intermediate Assembly");				-- id_module = 12
INSERT INTO modules (name) VALUES ("Advanced Assembly");					-- id_module = 13
INSERT INTO modules (name) VALUES ("Basic Java");							-- id_module = 14
INSERT INTO modules (name) VALUES ("Intermediate Java");					-- id_module = 15
INSERT INTO modules (name) VALUES ("Advanced Java");						-- id_module = 16
INSERT INTO modules (name) VALUES ("Basic Python");							-- id_module = 17
INSERT INTO modules (name) VALUES ("Intermediate Python");					-- id_module = 18
INSERT INTO modules (name) VALUES ("Advanced Python");						-- id_module = 19
INSERT INTO modules (name) VALUES ("Basic PHP");							-- id_module = 20
INSERT INTO modules (name) VALUES ("Intermediate PHP");						-- id_module = 21
INSERT INTO modules (name) VALUES ("Advanced PHP");							-- id_module = 22

-- ----------------------------------------------------------------------------
-- courses
-- ----------------------------------------------------------------------------
INSERT INTO courses (name,logo) VALUES ('Javascript', 'logo_js.jpg');											-- id_course = 1
INSERT INTO courses (name,logo,description) VALUES ('PHP', 'logo_php.png', 'Learn the main concepts of PHP');	-- id_course = 2
INSERT INTO courses (name,logo) VALUES ('C++', 'logo_cpp.jpg');													-- id_course = 3
INSERT INTO courses (name) VALUES ('C');																		-- id_course = 4
INSERT INTO courses (name,logo) VALUES ('Assembly', 'logo_assembly.jpg');										-- id_course = 5
INSERT INTO courses (name,logo) VALUES ('Java', 'logo_java.jpg');												-- id_course = 6
INSERT INTO courses (name,logo) VALUES ('Python', 'logo_python.png');											-- id_course = 7

-- ----------------------------------------------------------------------------
-- course_modules
-- ----------------------------------------------------------------------------
INSERT INTO course_modules VALUES (1, 4, 1);
INSERT INTO course_modules VALUES (1, 1, 2);
INSERT INTO course_modules VALUES (1, 2, 3);
INSERT INTO course_modules VALUES (1, 3, 4);
INSERT INTO course_modules VALUES (2, 4, 1);
INSERT INTO course_modules VALUES (2, 20, 2);
INSERT INTO course_modules VALUES (2, 21, 3);
INSERT INTO course_modules VALUES (2, 22, 4);
INSERT INTO course_modules VALUES (3, 4, 1);
INSERT INTO course_modules VALUES (3, 8, 2);
INSERT INTO course_modules VALUES (3, 9, 3);
INSERT INTO course_modules VALUES (3, 10, 4);
INSERT INTO course_modules VALUES (4, 4, 1);
INSERT INTO course_modules VALUES (4, 5, 2);
INSERT INTO course_modules VALUES (4, 6, 3);
INSERT INTO course_modules VALUES (4, 7, 4);
INSERT INTO course_modules VALUES (5, 4, 1);
INSERT INTO course_modules VALUES (5, 11, 2);
INSERT INTO course_modules VALUES (5, 12, 3);
INSERT INTO course_modules VALUES (5, 13, 4);
INSERT INTO course_modules VALUES (6, 4, 1);
INSERT INTO course_modules VALUES (6, 14, 2);
INSERT INTO course_modules VALUES (6, 15, 3);
INSERT INTO course_modules VALUES (6, 16, 4);
INSERT INTO course_modules VALUES (7, 4, 1);
INSERT INTO course_modules VALUES (7, 17, 2);
INSERT INTO course_modules VALUES (7, 18, 3);
INSERT INTO course_modules VALUES (7, 19, 4);

-- ----------------------------------------------------------------------------
-- bundles
-- ----------------------------------------------------------------------------
INSERT INTO bundles (name,price,description) 
VALUES ('Web', 19.99, 'Learn the most used languages for web programming, such as PHP, JS,among others');	-- id_bundle = 1

INSERT INTO bundles (name,price,description) 
VALUES ('Low level', 39.99, 'Learn low-level languages like C, C ++ and Assembly');							-- id_bundle = 2

INSERT INTO bundles (name,price,logo, description) 
VALUES ('Desktop programming', 29.99, 'dp.jpg', 
	'Learn the most used languages for desktop programming, such as Java, C ++, Python,among others');		-- id_bundle = 3

INSERT INTO bundles (name,price,logo, description) 
VALUES ('Web fullstack', 99.99, 'fullstack.jpg', 'Learn all about front-end and back-end');						-- id_bundle = 4

-- ----------------------------------------------------------------------------
-- bundle_courses
-- ----------------------------------------------------------------------------
INSERT INTO bundle_courses VALUES (1, 1);
INSERT INTO bundle_courses VALUES (2, 4);
INSERT INTO bundle_courses VALUES (2, 5);
INSERT INTO bundle_courses VALUES (3, 3);
INSERT INTO bundle_courses VALUES (3, 6);
INSERT INTO bundle_courses VALUES (3, 7);
INSERT INTO bundle_courses VALUES (4, 1);
INSERT INTO bundle_courses VALUES (4, 2);

-- ----------------------------------------------------------------------------
-- purchases
-- ----------------------------------------------------------------------------
-- Note: 'price' attribute will be provided by a trigger (in application).
-- ----------------------------------------------------------------------------
INSERT INTO purchases VALUES (1, 1, '2020-07-04 20:35:00', 19.00);
INSERT INTO purchases VALUES (1, 2, '2020-07-04 20:35:00', 39.99);
INSERT INTO purchases VALUES (2, 3, '2020-07-04 20:35:00', 29.99);
INSERT INTO purchases VALUES (1, 3, '2020-07-11 20:19:00', 99.99);

-- ----------------------------------------------------------------------------
-- videos
-- ----------------------------------------------------------------------------
INSERT INTO videos VALUES (4, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (4, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (4, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (1, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (1, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (1, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (2, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (2, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (2, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (3, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (3, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (3, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (5, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (5, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (5, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (6, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (6, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (6, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (7, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (7, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (7, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (8, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (8, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (8, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (9, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (9, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (9, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (10, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (10, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (10, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (11, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (11, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (11, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (12, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (12, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (12, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (13, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (13, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (13, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (14, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (14, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (14, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (15, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (15, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (15, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (16, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (16, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (16, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (20, 1, 'Introduction - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (20, 2, 'Introduction - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (20, 3, 'Introduction - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (21, 1, 'Intermediate - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (21, 2, 'Intermediate - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (21, 3, 'Intermediate - Part III', null, '8tPnX7OPo0Q', 10);

INSERT INTO videos VALUES (22, 1, 'Advanced - Part I', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (22, 2, 'Advanced - Part II', null, '8tPnX7OPo0Q', 10);
INSERT INTO videos VALUES (22, 3, 'Advanced - Part III', null, '8tPnX7OPo0Q', 10);

-- ----------------------------------------------------------------------------
-- questionnaires
-- ----------------------------------------------------------------------------
INSERT INTO questionnaires 
VALUES (
	1, 4, 'How to display the contents of a variable on the console?',
	'console.output(variable)',
	'log(variable)',
	'console.log(variable)',
	'alert(variable)',
	3
);

INSERT INTO questionnaires 
VALUES (
	20, 4, 'What is the tag used in HTML to add a PHP command?',
	'<php COMMAND >',
	'<? COMMAND >',
	'<?php COMMAND >',
	'<?php COMMAND ?>',
	4
);

INSERT INTO questionnaires 
VALUES (
	8, 4, 'The inline keyword in C ++ serves to:',
	"For the content of a function to be extracted and placed in the function's invocation locations",
	'To convert the function to a lambda function',
	'Prohibit a function from being used as an argument to another function',
	'Prohibit a function from being invoked more than twice',
	1
);

-- ----------------------------------------------------------------------------
-- student_historic
-- ----------------------------------------------------------------------------
INSERT INTO student_historic VALUES (1, 1, 1, 0, '2020-07-04');
INSERT INTO student_historic VALUES (1, 1, 4, 1, '2020-07-04');
INSERT INTO student_historic VALUES (2, 1, 1, 0, '2020-07-05');
INSERT INTO student_historic VALUES (1, 14, 1, 0, '2020-08-01');

-- ----------------------------------------------------------------------------
-- comments
-- ----------------------------------------------------------------------------
INSERT INTO comments 
(id_student, id_course, id_module, class_order, date, text)
VALUES (1, 1, 1, 1, '2020-07-04 21:56:00', 'very good');

INSERT INTO comments 
(id_student, id_course, id_module, class_order, date, text)
VALUES (2, 1, 1, 1, '2020-07-04 21:57:00', 'the quality is not very good');

INSERT INTO comments
(id_student, id_course, id_module, class_order, date, text)
VALUES (1, 1, 3, 1, '2020-07-04 21:57:00', 'perfect explanation');

-- ----------------------------------------------------------------------------
-- comment_replies
-- ----------------------------------------------------------------------------
INSERT INTO comment_replies 
(id_student, id_comment, date, text)
VALUES (2, 1, '2020-07-06 21:34:00', 'I agree');

INSERT INTO comment_replies 
(id_student, id_comment, date, text)
VALUES (1, 2, '2020-07-06 21:34:00',"it's good for me. Try running in another browser");

INSERT INTO comment_replies 
(id_student, id_comment, date, text)
VALUES (2, 2, '2020-07-06 21:35:00',"It worked. Thank you!");

-- ----------------------------------------------------------------------------
-- notebook
-- ----------------------------------------------------------------------------
INSERT INTO notebook
(id_student, id_module, class_order, title, content, date)
VALUES (1, 1, 1, 'js - print on console', 'console.log(CONTENT)', '2020-08-01 19:52:00');

INSERT INTO notebook
(id_student, id_module, class_order, title, content, date)
VALUES (2, 1, 1, 'js - variable declaration', 'const, let, var', '2020-08-01 19:53:00');

INSERT INTO notebook
(id_student, id_module, class_order, title, content, date)
VALUES (1, 14, 1, 'java – reserved keywords', '‘abstract,assert,boolean,break,byte,case,catch,char,class,const,default,
	do,double,else,enum,extends,false,final,finally,float,for,goto,if,implements,import,instanceof,int,interface,long,
	native,new,null,package,private,protected,public,return,short,static,strictfp,super,switch,synchronized,this,throw,
	throws,transient,true,try,void,volatile,while,continue’', '2020-08-01 19:54:00');

-- ----------------------------------------------------------------------------
-- authorization
-- ----------------------------------------------------------------------------
INSERT INTO authorization (name,level) VALUES ('ROOT', 0);
INSERT INTO authorization (name,level) VALUES ('MANAGER', 1);
INSERT INTO authorization (name,level) VALUES ('SUPPORTER', 2);

-- ----------------------------------------------------------------------------
-- admins
-- ----------------------------------------------------------------------------
INSERT INTO admins 
(id_authorization, name, genre, birthdate, email, password)
VALUES (1, 'admin', 0, '2000-01-01', 'admin@lp.com', MD5("teste12345@A"));

INSERT INTO admins 
(id_authorization, name, genre, birthdate, email, password)
VALUES (1, 'Tetrano', 0, '1992-01-01', 'tetrano@hotmail.com', MD5("teste12345@A"));

INSERT INTO admins 
(id_authorization, name, genre, birthdate, email, password)
VALUES (2, 'Heptano', 1, '1993-01-01', 'heptano@hotmail.com', MD5("teste12345@A"));

INSERT INTO admins 
(id_authorization, name, genre, birthdate, email, password)
VALUES (3, 'Citano', 0, '1994-01-01', 'citano@hotmail.com', MD5("teste12345@A"));

-- ----------------------------------------------------------------------------
-- actions
-- ----------------------------------------------------------------------------
INSERT INTO actions (date, id_admin, description) VALUES ('2020-07-05 07:10:00', 1, '[UPD] Topic answered - id_topic=1');
INSERT INTO actions (date, id_admin, description) VALUES ('2020-07-05 07:11:00', 2, '[UPD] Topic opened - id_topic=2');
INSERT INTO actions (date, id_admin, description) VALUES ('2020-07-05 07:15:00', 1, '[DEL] Class - id_module=1, class_order=1');

-- ----------------------------------------------------------------------------
-- support_topic_category
-- ----------------------------------------------------------------------------
INSERT INTO support_topic_category (name) VALUES ('COMPLAINT');
INSERT INTO support_topic_category (name) VALUES ('COMPLIMENT');
INSERT INTO support_topic_category (name) VALUES ('SUGGESTION');

-- ----------------------------------------------------------------------------
-- support_topic
-- ----------------------------------------------------------------------------
INSERT INTO support_topic 
(id_category, id_student, title, date, message) 
VALUES (3, 1, 'chat', '2020-07-04 22:03:00', "the platform could have a chat between students");

INSERT INTO support_topic 
(id_category, id_student, title, date, message) 
VALUES (2, 1, 'Congratulations', '2020-07-04 22:04:00', "the platform is excelent");

INSERT INTO support_topic 
(id_category, id_student, title, date, message, closed) 
VALUES (1, 1, 'php course problem', '2020-07-04 22:06:00', "the php course classes have low audio", 0);

-- ----------------------------------------------------------------------------
-- support_topic_replies
-- ----------------------------------------------------------------------------
INSERT INTO support_topic_replies 
(id_topic, id_user, date, user_type, text)
VALUES (1, 1, '2020-07-05 07:10', 1, 'Great suggestion!');

INSERT INTO support_topic_replies 
(id_topic, id_user, date, user_type, text)
VALUES (3, 3, '2020-07-05 07:11', 1, 'We fixed this. Please try again');

INSERT INTO support_topic_replies 
(id_topic, id_user, date, user_type, text)
VALUES (1, 1, '2020-07-05 07:14', 0, 'thanks');
