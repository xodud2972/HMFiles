SELECT 
		t2.enter_date AS '인입월',
		getemkrname(t2.reg_emp) AS '직원명',
		getcomkrname(t2.m_nm) AS 't1 m_nm',
		getcomkrname(IF(t1.m_nm='302', t1.good_type2, t1.m_nm)) AS 't1 m_nm', 	
		t1.cs_m_id AS '계정',
		sum(if(pay_date between '2022-01-01' AND '2022-01-31',pay_price, 0)) AS '22년 1월 매출',
		SUM(if(pay_date between '2022-02-01' AND '2022-02-31',pay_price, 0)) AS '22년 2월 매출',
		SUM(if(pay_date between '2022-03-01' AND '2022-03-31',pay_price, 0)) AS '22년 3월 매출',
		SUM(if(pay_date between '2022-04-01' AND '2022-04-31',pay_price, 0)) AS '22년 4월 매출',
		SUM(if(pay_date between '2022-05-01' AND '2022-05-31',pay_price, 0)) AS '22년 5월 매출',
		SUM(if(pay_date between '2022-06-01' AND '2022-06-31',pay_price, 0)) AS '22년 6월 매출',
		SUM(if(pay_date between '2022-07-01' AND '2022-07-31',pay_price, 0)) AS '22년 7월 매출',
		SUM(if(pay_date between '2022-08-01' AND '2022-08-31',pay_price, 0)) AS '22년 8월 매출',
		SUM(if(pay_date between '2022-09-01' AND '2022-09-31',pay_price, 0)) AS '22년 9월 매출',
		SUM(if(pay_date between '2022-10-01' AND '2022-10-31',pay_price, 0)) AS '22년 10월 매출',
		SUM(if(pay_date between '2022-11-01' AND '2022-11-31',pay_price, 0)) AS '22년 11월 매출',
		SUM(if(pay_date between '2022-12-01' AND '2022-12-31',pay_price, 0)) AS '22년 12월 매출',
		SUM(if(pay_date between '2023-01-01' AND '2023-01-31',pay_price, 0)) AS '23년 1월 매출'
FROM t_contract t1
		INNER JOIN (
			SELECT 
					md.enter_date AS 'enter_date', 
					md.reg_emp AS 'reg_emp', 
					IF(md.m_nm='302', md.good_type2, md.m_nm) AS 'm_nm', 		
					md.cs_m_id AS 'cs_m_id', 
					enter_division1 AS 'division1', 
					md.cs_seq
			FROM t_customer_md md 
			WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
				AND md.enter_date BETWEEN '2022-01-01' AND '2023-12-31' 
				AND md.del_date IS NULL
				AND md.enter_division1 = 927
				AND md.enter_state = 1
			GROUP BY cs_seq, m_nm, cs_m_id
			ORDER BY enter_date
		) t2 ON (t1.cs_seq = t2.cs_seq) AND (t1.cs_m_id = t2.cs_m_id) AND (IF(t1.m_nm='302', t1.good_type2, t1.m_nm)=t2.m_nm)
WHERE pay_date between '2022-01-01' AND '2023-01-31'
		AND agree_state = '3' 
		AND sales_type = '1'
		AND t1.del_date IS NULL
		AND t1.division1 = 927
GROUP BY t1.cs_seq, t1.cs_m_id
ORDER BY t2.enter_date, t2.reg_emp;