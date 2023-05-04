	-- pay_date 2개 변경 ( 이전월과 해당월로 진행)
	SELECT a.division AS '부문', 
	            a.em_seq AS '직원', 
	                 a.cs_count AS '사용 가능 광고주 수', 
	                 ifnull(b.cs_count, '0') AS '사용 광고주 수', 
	                 concat(round(ifnull(b.cs_count, '0')/a.cs_count*100, 2), '%') AS '사용률', pay_date
	FROM 
	(SELECT CONCAT(division, em_seq) AS 'key',
	                  getComKrName(division) AS 'division', 
	                  getEmkrName(em_seq) AS 'em_seq', 
	                  COUNT(DISTINCT cs_seq) AS 'cs_count', pay_date
	FROM t_report_month
	WHERE pay_date = '2023-01' 
		AND cs_seq !='None' AND cost != '0' AND division != '6'
	GROUP BY division, em_seq) a 
	LEFT OUTER JOIN
	(SELECT CONCAT(b.division1, a.reg_emp) AS 'key',
	                  getComKrName(b.division1) AS 'division1',
	                    getEmkrName(a.reg_emp) AS 'reg_emp', 
	                    count(distinct a.cs_seq) AS 'cs_count'
	FROM t_report_sent_mailing_list a LEFT OUTER JOIN t_employee b ON(a.reg_emp = b.em_seq)
	WHERE (a.create_type = 1 AND a.`status` = 1 ) OR (a.create_type = 2 AND a.reg_date >= '2023-01-01')
	GROUP BY b.division1, a.reg_emp) b 
	ON(a.key = b.key)