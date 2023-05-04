SELECT DISTINCT
	t2.cs_num, getcomkrname(cs_type_new1)AS cs_type_new1, getcomkrname(cs_type_new2)AS cs_type_new2, cs_nm, url AS url, mg_nm, mg_cell1, mg_cell2, mg_cell3,
	mg_tel1, mg_tel2, mg_tel3, mg_email, cs_m_id, getcomkrname(if(m_nm="302",good_type2,m_nm)) AS m_nm, if(m_nm="302",good_type2,m_nm) AS m_nm1
FROM t_customer_md t1
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t2.cs_num = "410-81-33949"
GROUP BY cs_m_id

SELECT DISTINCT 
	t2.cs_num, getcomkrname(cs_type_new1)AS cs_type_new1, getcomkrname(cs_type_new2)AS cs_type_new2, cs_nm, url AS url, 
	mg_nm, mg_cell1, mg_cell2, mg_cell3, mg_tel1, mg_tel2, mg_tel3, mg_email, IFNULL(round(sum(pay_price)/3,0),0) AS pay_price, 
	getcomkrname(t3.m_nm) as m_nm
FROM t_customer_md t1 
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq 
LEFT JOIN ( 
				SELECT cs_seq, SUM(pay_price)AS "pay_price", pay_date, if(m_nm="302",good_type2,m_nm) AS m_nm 
				FROM t_contract 
				WHERE pay_date BETWEEN "2021-12-01" AND "2022-02-28" 
				AND cs_m_id = "alznner4639" 
				AND if(m_nm="302",good_type2,m_nm) = 264 
				AND del_date IS NULL 
				AND sales_type IN ('1') 
				AND agree_state IN ('3') )t3 ON t2.cs_seq = t3.cs_seq 
WHERE cs_m_id = "alznner4639" 
AND t1.md_seq = (SELECT MAX(md_seq) FROM t_customer_md WHERE cs_m_id="alznner4639" AND if(m_nm="302",good_type2,m_nm) = 264) 



SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, ROUND(sum(pay_price)/3) AS 'pay_price'
FROM t_contract
WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') AND pay_date BETWEEN '2021-12-01' AND '2022-02-31' AND
      IF(m_nm='302', good_type2, m_nm) = '264' AND cs_m_id = 'alznner4639' 
GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id




-- -----------------------2022.03.25 del_date 추가 개선 --------------------
SELECT DISTINCT
			t2.cs_num, getcomkrname(cs_type_new1) AS cs_type_new1, getcomkrname(cs_type_new2) AS cs_type_new2, 
			cs_nm, url AS url, mg_nm, mg_cell1, mg_cell2, mg_cell3,mg_tel1, mg_tel2, mg_tel3, mg_email, IFNULL(ROUND(SUM(pay_price)/3,0),0) AS pay_price, getcomkrname(t3.m_nm) AS m_nm
FROM t_customer_md t1
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
LEFT JOIN (
SELECT cs_seq, SUM(pay_price) AS "pay_price", pay_date, if(m_nm="302",good_type2,m_nm) AS m_nm
FROM t_contract
WHERE pay_date BETWEEN "2022-01-01" AND "2022-03-31" AND cs_m_id = "alznner4639" AND if(m_nm="302",good_type2,m_nm) = 41 AND del_date IS NULL AND sales_type IN ("1") AND agree_state IN ("3")
					)t3 ON t2.cs_seq = t3.cs_seq
WHERE cs_m_id = "301776" AND t1.md_seq = (
SELECT MAX(md_seq)
FROM t_customer_md
WHERE cs_m_id="301776" 
AND if(m_nm="302",good_type2,m_nm) = "41")
AND t1.del_date IS null
GROUP BY cs_m_id




-----------------------매출부분select부분에 따로 붙이는 것으로 수정--------------------------------
SELECT DISTINCT
	t2.cs_num, getcomkrname(cs_type_new1)AS cs_type_new1, getcomkrname(cs_type_new2)AS cs_type_new2, 
	cs_nm, url AS url, mg_nm, mg_cell1, mg_cell2, mg_cell3,mg_tel1, mg_tel2, mg_tel3, mg_email, (select IFNULL(round(SUM(t3.pay_price)/3,0),0) AS pay_price
FROM  (
				SELECT cs_seq, SUM(pay_price)AS "pay_price", pay_date, if(m_nm="302",good_type2,m_nm) AS m_nm
				FROM t_contract 
				WHERE pay_date BETWEEN "2022-01-01" AND "2022-03-31" 
				AND cs_m_id = "301776"
				AND if(m_nm="302",good_type2,m_nm) = 41
				AND del_date IS NULL
				AND sales_type IN ("1") 
				AND agree_state IN ("3")
			)t3 ) AS price
FROM t_customer_md t1
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE cs_m_id = "301776"
AND t1.md_seq = (SELECT MAX(md_seq) FROM t_customer_md WHERE cs_m_id="301776" AND if(m_nm="302",good_type2,m_nm) = "41" )
AND t1.del_date IS null
GROUP BY cs_m_id	
