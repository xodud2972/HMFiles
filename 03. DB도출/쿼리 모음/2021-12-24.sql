INSERT INTO t_people
        (first_name, last_name, mid_name, address, contact, comment)
    VALUES 
        (1234, 1234, 1234, 1234, 1234, 1234);
        
SELECT people_id, first_name, last_name, mid_name, address, contact, COMMENT,  GROUP_CONCAT(filename)  AS filename
									FROM t_people
								LEFT JOIN t_file
								ON t_people.people_id = t_file.file_people_id
								GROUP BY t_people.people_id
								ORDER BY people_id 
							DESC;
							
SELECT people_id, first_name, last_name, mid_name, address, contact, COMMENT, GROUP_CONCAT(filename)  AS filename
									FROM t_people
								LEFT JOIN t_file
								ON t_people.people_id = t_file.file_people_id
								WHERE t_people.people_id = 4
								GROUP BY t_people.people_id
								ORDER BY people_id 
							DESC;
							
SELECT LAST_INSERT_ID();

SELECT people_id, first_name, last_name, mid_name, address, contact, COMMENT, COUNT(filename)  AS filename
									FROM t_people
								INNER JOIN t_file
								ON t_people.people_id = t_file.file_people_id
								WHERE t_people.people_id = 4
								GROUP BY t_people.people_id
								ORDER BY people_id 
							DESC;
							
							
 SELECT filename
     FROM t_file
 LEFT JOIN t_people
     ON t_people.people_id = t_file.file_people_id
 WHERE t_people.people_id = 4t_people
 

INSERT INTO t_file
					(file_people_id, filename)
				values
					(LAST_INSERT_ID(), '12.png');


SELECT people_id, first_name, last_name, mid_name, address, contact, COMMENT, GROUP_CONCAT(filename)  AS filename
									FROM t_people
								LEFT JOIN t_file
								ON t_people.people_id = t_file.file_people_id
								GROUP BY t_people.people_id
								ORDER BY people_id 
							DESC;
							
							
							t_people
							
SELECT filename FROM t_file WHERE file_people_id=40;

SELECT LAST_INSERT_ID();

SELECT * FROM t_file;

