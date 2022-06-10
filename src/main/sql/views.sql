-- ----------------------------------------------------------------------------
-- Code developed on MySQL 5.7.26.
-- ----------------------------------------------------------------------------


-- ----------------------------------------------------------------------------
-- 		Views
-- ----------------------------------------------------------------------------
-- Total minutes that courses have.
-- ----------------------------------------------------------------------------
CREATE VIEW vw_courses_total_length (id_course, total_length) 
AS (
	SELECT		id_course, SUM(length) as total_length
	FROM 		(SELECT	id_module, length
		 		 FROM		videos
		 	 	 UNION ALL
		  		 SELECT		id_module, 5 AS length
		  		 FROM		questionnaires) AS classes
	JOIN 	 	 course_modules USING (id_module)
	GROUP BY 	 id_course
);

-- ----------------------------------------------------------------------------
-- Total minutes of classes watched by students.
-- ----------------------------------------------------------------------------
CREATE VIEW vw_student_historic_watched_length (id_student, id_module, `length`) 
AS (
	SELECT	id_student, id_module, `length`
	FROM	(SELECT	id_module, class_order, `length`
	 		 FROM		videos
	 	 	 UNION ALL
	  		 SELECT		id_module, class_order, 5 AS `length`
	  		 FROM		questionnaires) AS classes
	JOIN student_historic USING (id_module, class_order)
);