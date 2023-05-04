SELECT getEmkrName(t1.em_seq) AS '담당자', t2.cs_nm AS '광고주명',
		SUM(t1.pay_price) AS '매출액', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS '매체', t2.cs_num AS '사업자번호'
FROM t_contract t1
left JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t1.pay_date BETWEEN '2022-02-02' AND '2022-03-02'
AND t1.m_nm IN ('264', '808', '820', '844', '847', '496')
GROUP BY t1.em_seq, t2.cs_num, t1.m_nm






--------------------------------------------------------- 마케터별 매체 확장 유PM님 쿼리-------------------------------------------------------------- 
CREATE TEMPORARY TABLE IF NOT EXISTS A
SELECT c.em_seq, a.cs_num, a.cs_nm, a.m_nm, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ' | ') AS 'cs_m_id', sum(b.pay_price) AS 'pay_price'
FROM  (
			SELECT b.cs_num, GROUP_CONCAT(DISTINCT getCsKrName(a.cs_seq) SEPARATOR ' or ') AS 'cs_nm', 
						if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id
			FROM t_customer_md AS a
			LEFT OUTER JOIN t_customer AS b
			ON(a.cs_seq = b.cs_seq)
			WHERE a.del_date IS NULL AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY b.cs_num, IF(a.m_nm='302', a.good_type2, m_nm), a.cs_m_id
		) AS a /*RMS에 등록한 사업자번호, 광고주명, 매체, 광고계정 도출*/
LEFT OUTER JOIN (
						SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price'
						FROM t_contract
						WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') AND pay_date >= '2022-02-02' AND
						      IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
						GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
					) AS b /*매체별, 계정별 매출 도출*/ ON (a.m_nm = b.m_nm AND a.cs_m_id = b.cs_m_id)
LEFT OUTER JOIN 		(
									SELECT b.cs_num, getEmKrName(a.em_seq)
									FROM t_customer_mg AS a
									INNER JOIN(
													SELECT b.cs_num, MAX(a.mg_seq) AS 'mg_seq'
													FROM t_customer_mg AS a
													LEFT OUTER JOIN t_customer AS b
													ON(a.cs_seq = b.cs_seq)
													WHERE a.del_date IS NULL
													GROUP BY b.cs_num
												) AS b
									ON(a.mg_seq = b.mg_seq)
							) AS c /*사업자번호의 최신 담당자*/ ON(a.cs_num = c.cs_num)
GROUP BY c.em_seq, a.cs_num, a.m_nm



SELECT getEmkrName(t1.em_seq) AS '담당자', getEmkrName(t1.em_seq) AS '광고주명', t1.cs_m_id AS '아이디',
		SUM(t1.pay_price) AS '매출액', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS '매체', t1.cs_num AS '사업자번호'
FROM t_contract t1
inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t1.m_nm IN ('264', '808', '820', '844', '847', '496')
GROUP BY t1.em_seq, t2.cs_num, t1.m_nm



SELECT DISTINCT cs_seq, cs_m_id AS '아이디', reg_date AS '인입월'
FROM t_customer_md 
WHERE reg_date BETWEEN '2021-02-02' AND '2022-03-02'
-- WHERE reg_date BETWEEN DATE_ADD(NOW(), INTERVAL -1 MONTH) AND NOW()


SELECT a.cs_num, tt.em_seq, tt.cs_nm, a.cs_m_id, tt.pay_price, getcomkrname(if(a.m_nm='302',a.good_type2,a.m_nm))
FROM t_customer_md a
LEFT JOIN (
				SELECT t1.cs_seq, getEmkrName(t1.em_seq) AS 'em_seq', t2.cs_nm AS 'cs_nm', t1.cs_m_id AS 'cs_m_id',
				SUM(t1.pay_price) AS 'pay_price', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS 'm_nm', t2.cs_num AS 'cs_num'
				FROM t_contract t1
				inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
				WHERE t1.pay_date BETWEEN '2022-02-02' AND '2022-03-02'
				AND t1.m_nm IN ('264', '808', '820', '844', '847', '496')
				GROUP BY t1.em_seq, t2.cs_num, t1.m_nm
			)tt ON a.cs_seq = tt.cs_seq
WHERE a.del_date IS NULL 
AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN('264', '496', '808', '820', '847', '844')



1. 광고주명과 사업자번호 도출
	SELECT cust.cs_num AS '사업자번호', getEmkrName(mg.em_seq) AS '담당자명', getCsKrName(cust.cs_seq) AS '광고주명'
	FROM t_customer_mg AS mg
	LEFT OUTER JOIN t_customer AS cust
	ON(mg.cs_seq = cust.cs_seq)
	WHERE mg.del_date IS NULL
2. 각 매체별아이디 와매출 도출
	SELECT getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq
	FROM t_contract
	WHERE del_date IS NULL AND sales_type IN ('1') 
	AND agree_state IN ('3') 
	AND pay_date BETWEEN '2022-02-02' AND '2022-03-02' 
	AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
	GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
3. 합치기
	SELECT DISTINCT a.cs_num1, getEmkrName(a.em_seq), getCsKrName(a.cs_seq), getcomkrname(b.m_nm), b.pay_price, b.id
	FROM (
			SELECT cust.cs_num AS 'cs_num1', mg.em_seq AS 'em_seq', cust.cs_seq AS 'cs_seq', cust.cs_num
			FROM t_customer_mg AS mg
			LEFT OUTER JOIN t_customer AS cust
			ON mg.cs_seq = cust.cs_seq
			WHERE mg.del_date IS NULL
			)a 
	left JOIN (
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq, cs_m_id AS 'id', em_seq
			FROM t_contract
			WHERE del_date IS NULL 
			AND sales_type IN ('1') 
			AND agree_state IN ('3') 
			AND pay_date BETWEEN '2022-02-01' AND '2022-03-02' 
			AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
			)b ON a.em_seq = b.em_seq
-- WHERE b.m_nm IN ('264', '496', '808', '820', '847', '844')
--	m_nm IS NOT NULL
 	AND getEmkrName(a.em_seq) = '권도희'
	GROUP BY a.em_seq, a.cs_num, m_nm
				
					
				
				
				
				
				
				
				
				
				
				
SELECT getEmkrName(a.em_seq) AS 'em_seq', b.cs_num AS 'cs_num', getCsKrName(c.cs_seq) AS 'cs_nm',
		getcomkrname(if(c.m_nm='302',c.good_type2, c.m_nm)) AS 'm_nm', c.cs_m_id AS 'cs_m_id', sum(d.pay_price)
FROM t_customer_mg AS a
LEFT JOIN(
			SELECT cust.cs_num AS 'cs_num', mg.mg_seq AS 'mg_seq', cust.cs_seq AS 'cs_seq'
			FROM t_customer_mg AS mg
			inner JOIN t_customer AS cust
			ON(mg.cs_seq = cust.cs_seq)
			WHERE mg.del_date IS NULL
			AND mg.reg_date >= '2020-01-01'
			GROUP BY cust.cs_num
		) AS b
ON(a.mg_seq = b.mg_seq)
left JOIN t_customer_md c ON b.cs_seq = c.cs_seq
left JOIN (
				SELECT getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq
				FROM t_contract
				WHERE del_date IS NULL AND sales_type IN ('1') 
				AND agree_state IN ('3') 
				AND pay_date BETWEEN '2022-02-02' AND '2022-03-02' 
				AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
				GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
			)d ON a.cs_seq = d.cs_seq
WHERE getcomkrname(if(c.m_nm='302',c.good_type2, c.m_nm)) IS NOT NULL
AND if(c.m_nm='302',c.good_type2, c.m_nm) IN ('264', '808', '820', '844', '847', '496')
AND getEmkrName(a.em_seq) = '권도희'
GROUP BY a.em_seq, cs_num, m_nm
ORDER BY a.em_seq, cs_nm				

------------------------------------------------------------- 마케터별 최근 매출발생목록-------------------------------------------------------------- 
SELECT getEmkrName(t1.em_seq) AS '담당자', t2.cs_nm AS '광고주명', t1.cs_m_id AS '아이디',
		SUM(t1.pay_price) AS '매출액', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS '매체', t2.cs_num AS '사업자번호'
FROM t_contract t1
inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t1.m_nm IN ('264', '808', '820', '844', '847', '496')
GROUP BY t1.em_seq, t2.cs_num, t1.m_nm
------------------------------------------------------------- 최근 1개월 인입여부-------------------------------------------------------------- 
SELECT DISTINCT cs_seq, cs_m_id AS '아이디', reg_date AS '인입월'
FROM t_customer_md 
WHERE reg_date BETWEEN '2021-02-02' AND '2022-03-02'
-- WHERE reg_date BETWEEN DATE_ADD(NOW(), INTERVAL -1 MONTH) AND NOW()



SELECT c.em_seq, a.cs_num, a.cs_nm, a.m_nm, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ' | ') AS 'cs_m_id', sum(b.pay_price) AS 'pay_price'
FROM  (
			SELECT b.cs_num, GROUP_CONCAT(DISTINCT getCsKrName(a.cs_seq) SEPARATOR ' or ') AS 'cs_nm', 
						if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id
			FROM t_customer_md AS a
			LEFT OUTER JOIN t_customer AS b
			ON(a.cs_seq = b.cs_seq)
			WHERE a.del_date IS NULL AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY b.cs_num, IF(a.m_nm='302', a.good_type2, m_nm), a.cs_m_id
		) AS a /*RMS에 등록한 사업자번호, 광고주명, 매체, 광고계정 도출*/
LEFT OUTER JOIN (
						SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price'
						FROM t_contract
						WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') AND pay_date >= '2022-02-02' AND
						      IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
						GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
					) AS b /*매체별, 계정별 매출 도출*/ ON (a.m_nm = b.m_nm AND a.cs_m_id = b.cs_m_id)
LEFT OUTER JOIN 		(
									SELECT b.cs_num, getEmKrName(a.em_seq)
									FROM t_customer_mg AS a
									INNER JOIN(
													SELECT b.cs_num, MAX(a.mg_seq) AS 'mg_seq'
													FROM t_customer_mg AS a
													LEFT OUTER JOIN t_customer AS b
													ON(a.cs_seq = b.cs_seq)
													WHERE a.del_date IS NULL
													GROUP BY b.cs_num
												) AS b
									ON(a.mg_seq = b.mg_seq)
							) AS c /*사업자번호의 최신 담당자*/ ON(a.cs_num = c.cs_num)
GROUP BY c.em_seq, a.cs_num, a.m_nm



------------------------------------------------------------모든 광고주 계정------------------------------------------------------------- 
SELECT a.cs_num, tt.em_seq, tt.cs_nm, a.cs_m_id, tt.pay_price, getcomkrname(if(a.m_nm='302',a.good_type2,a.m_nm))
FROM t_customer_md a
LEFT JOIN (
				SELECT t1.cs_seq, getEmkrName(t1.em_seq) AS 'em_seq', t2.cs_nm AS 'cs_nm', t1.cs_m_id AS 'cs_m_id',
				SUM(t1.pay_price) AS 'pay_price', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS 'm_nm', t2.cs_num AS 'cs_num'
				FROM t_contract t1
				inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
				WHERE t1.pay_date BETWEEN '2022-02-02' AND '2022-03-02'
				AND t1.m_nm IN ('264', '808', '820', '844', '847', '496')
				GROUP BY t1.em_seq, t2.cs_num, t1.m_nm
			)tt ON a.cs_seq = tt.cs_seq
WHERE a.del_date IS NULL 
AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN('264', '496', '808', '820', '847', '844')




SELECT getEmkrName(a.em_seq) AS 'em_seq', b.cs_num AS 'cs_num', getCsKrName(c.cs_seq) AS 'cs_nm',
		getcomkrname(if(c.m_nm='302',c.good_type2, c.m_nm)) AS 'm_nm', c.cs_m_id AS 'cs_m_id', sum(d.pay_price)
FROM t_customer_mg AS a
LEFT JOIN(
			SELECT cust.cs_num AS 'cs_num', MAX(mg.mg_seq) AS 'mg_seq', cust.cs_seq AS 'cs_seq'
			FROM t_customer_mg AS mg
			LEFT OUTER JOIN t_customer AS cust
			ON(mg.cs_seq = cust.cs_seq)
			WHERE mg.del_date IS NULL
			GROUP BY cust.cs_num
		) AS b
ON(a.mg_seq = b.mg_seq)
left JOIN t_customer_md c ON b.cs_seq = c.cs_seq
left JOIN (
				SELECT getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq
				FROM t_contract
				WHERE del_date IS NULL AND sales_type IN ('1') 
				AND agree_state IN ('3') 
				AND pay_date BETWEEN '2022-02-02' AND '2022-03-02' 
				AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
				GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
			)d ON a.cs_seq = d.cs_seq
WHERE getcomkrname(if(c.m_nm='302',c.good_type2, c.m_nm)) IS NOT NULL
AND if(c.m_nm='302',c.good_type2, c.m_nm) IN ('264', '808', '820', '844', '847', '496')
AND getEmkrName(a.em_seq) = '권도희'
GROUP BY a.em_seq, cs_num, m_nm
ORDER BY a.em_seq, cs_nm		



1. 광고주명과 사업자번호 도출
	SELECT cust.cs_num AS '사업자번호', getEmkrName(mg.em_seq) AS '담당자명', getCsKrName(cust.cs_seq) AS '광고주명'
	FROM t_customer_mg AS mg
	LEFT OUTER JOIN t_customer AS cust
	ON(mg.cs_seq = cust.cs_seq)
	WHERE mg.del_date IS NULL
	
2. 각 매체별아이디 와매출 도출
	SELECT getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq
	FROM t_contract
	WHERE del_date IS NULL AND sales_type IN ('1') 
	AND agree_state IN ('3') 
	AND pay_date BETWEEN '2022-02-02' AND '2022-03-02' 
	AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
	GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
	
3. 합치기
	SELECT a.cs_num, getEmkrName(a.em_seq), getCsKrName(a.cs_seq), getcomkrname(b.m_nm), b.pay_price, GROUP_CONCAT(DISTINCT b.id SEPARATOR ' | ')
	FROM (
			SELECT cust.cs_num AS 'cs_num', mg.em_seq AS 'em_seq', cust.cs_seq AS 'cs_seq', cust.cs_nm
			FROM t_customer_mg AS mg
			LEFT OUTER JOIN t_customer AS cust
			ON mg.cs_seq = cust.cs_seq
			WHERE mg.del_date IS NULL
			)a 
	RIGHT OUTER JOIN (
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq, cs_m_id AS 'id'
			FROM t_contract
			WHERE del_date IS NULL 
			AND sales_type IN ('1') 
			AND agree_state IN ('3') 
			AND pay_date BETWEEN '2021-11-01' AND '2022-03-02' 
			AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
			)b ON a.cs_seq = b.cs_seq -- cs_seq로 묶어서 그런거 같은데............
	WHERE b.m_nm IN ('264', '496', '808', '820', '847', '844')
--	m_nm IS NOT NULL
-- 	AND getEmkrName(a.em_seq) = '권도희'
	GROUP BY a.em_seq, a.cs_num, m_nm
	
	
	
	
SELECT getEmkrName(t1.em_seq) AS '담당자', t2.cs_nm AS '광고주명',
		SUM(t1.pay_price) AS '매출액', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS '매체', t2.cs_num AS '사업자번호'
FROM t_contract t1
left JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t1.pay_date BETWEEN '2022-02-02' AND '2022-03-02'
AND t1.m_nm IN ('264', '808', '820', '844', '847', '496')
GROUP BY t1.em_seq, t2.cs_num, t1.m_nm






--------------------------------------------------------- 마케터별 매체 확장 유PM님 쿼리-------------------------------------------------------------- 
CREATE TEMPORARY TABLE IF NOT EXISTS A
SELECT c.em_seq, a.cs_num, a.cs_nm, a.m_nm, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ' | ') AS 'cs_m_id', sum(b.pay_price) AS 'pay_price'
FROM  (
			SELECT b.cs_num, GROUP_CONCAT(DISTINCT getCsKrName(a.cs_seq) SEPARATOR ' or ') AS 'cs_nm', 
						if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id
			FROM t_customer_md AS a
			LEFT OUTER JOIN t_customer AS b
			ON(a.cs_seq = b.cs_seq)
			WHERE a.del_date IS NULL AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY b.cs_num, IF(a.m_nm='302', a.good_type2, m_nm), a.cs_m_id
		) AS a /*RMS에 등록한 사업자번호, 광고주명, 매체, 광고계정 도출*/
LEFT OUTER JOIN (
						SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price'
						FROM t_contract
						WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') AND pay_date >= '2022-02-02' AND
						      IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
						GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
					) AS b /*매체별, 계정별 매출 도출*/ ON (a.m_nm = b.m_nm AND a.cs_m_id = b.cs_m_id)
LEFT OUTER JOIN 		(
									SELECT b.cs_num, getEmKrName(a.em_seq)
									FROM t_customer_mg AS a
									INNER JOIN(
													SELECT b.cs_num, MAX(a.mg_seq) AS 'mg_seq'
													FROM t_customer_mg AS a
													LEFT OUTER JOIN t_customer AS b
													ON(a.cs_seq = b.cs_seq)
													WHERE a.del_date IS NULL
													GROUP BY b.cs_num
												) AS b
									ON(a.mg_seq = b.mg_seq)
							) AS c /*사업자번호의 최신 담당자*/ ON(a.cs_num = c.cs_num)
GROUP BY c.em_seq, a.cs_num, a.m_nm



------------------------------------------------------------- 마케터별 최근 매출발생목록-------------------------------------------------------------- 
SELECT getEmkrName(t1.em_seq) AS '담당자', getEmkrName(t1.em_seq) AS '광고주명', t1.cs_m_id AS '아이디',
		SUM(t1.pay_price) AS '매출액', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS '매체', t1.cs_num AS '사업자번호'
FROM t_contract t1
inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE t1.m_nm IN ('264', '808', '820', '844', '847', '496')
GROUP BY t1.em_seq, t2.cs_num, t1.m_nm
------------------------------------------------------------- 최근 1개월 인입여부-------------------------------------------------------------- 
SELECT DISTINCT cs_seq, cs_m_id AS '아이디', reg_date AS '인입월'
FROM t_customer_md 
WHERE reg_date BETWEEN '2021-02-02' AND '2022-03-02'
-- WHERE reg_date BETWEEN DATE_ADD(NOW(), INTERVAL -1 MONTH) AND NOW()



------------------------------------------------------------모든 광고주 계정------------------------------------------------------------- 
SELECT a.cs_num, tt.em_seq, tt.cs_nm, a.cs_m_id, tt.pay_price, getcomkrname(if(a.m_nm='302',a.good_type2,a.m_nm))
FROM t_customer_md a
LEFT JOIN (
				SELECT t1.cs_seq, getEmkrName(t1.em_seq) AS 'em_seq', t2.cs_nm AS 'cs_nm', t1.cs_m_id AS 'cs_m_id',
				SUM(t1.pay_price) AS 'pay_price', getcomkrname(if(t1.m_nm='302',t1.good_type2, t1.m_nm)) AS 'm_nm', t2.cs_num AS 'cs_num'
				FROM t_contract t1
				inner JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
				WHERE t1.pay_date BETWEEN '2022-02-02' AND '2022-03-02'
				AND t1.m_nm IN ('264', '808', '820', '844', '847', '496')
				GROUP BY t1.em_seq, t2.cs_num, t1.m_nm
			)tt ON a.cs_seq = tt.cs_seq
WHERE a.del_date IS NULL 
AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN('264', '496', '808', '820', '847', '844')



1. 광고주명과 사업자번호 도출
	SELECT cust.cs_num AS '사업자번호', getEmkrName(mg.em_seq) AS '담당자명', getCsKrName(cust.cs_seq) AS '광고주명'
	FROM t_customer_mg AS mg
	LEFT OUTER JOIN t_customer AS cust
	ON(mg.cs_seq = cust.cs_seq)
	WHERE mg.del_date IS NULL
2. 각 매체별아이디 와매출 도출
	SELECT getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq
	FROM t_contract
	WHERE del_date IS NULL AND sales_type IN ('1') 
	AND agree_state IN ('3') 
	AND pay_date BETWEEN '2022-02-02' AND '2022-03-02' 
	AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
	GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
3. 합치기
	SELECT DISTINCT a.cs_num1, getEmkrName(a.em_seq), getCsKrName(a.cs_seq), getcomkrname(b.m_nm), b.pay_price, b.id
	FROM (
			SELECT cust.cs_num AS 'cs_num1', mg.em_seq AS 'em_seq', cust.cs_seq AS 'cs_seq', cust.cs_num
			FROM t_customer_mg AS mg
			LEFT OUTER JOIN t_customer AS cust
			ON mg.cs_seq = cust.cs_seq
			WHERE mg.del_date IS NULL
			)a 
	left JOIN (
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price', cs_seq, cs_m_id AS 'id', em_seq
			FROM t_contract
			WHERE del_date IS NULL 
			AND sales_type IN ('1') 
			AND agree_state IN ('3') 
			AND pay_date BETWEEN '2022-02-01' AND '2022-03-02' 
			AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
			GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
			)b ON a.em_seq = b.em_seq
-- WHERE b.m_nm IN ('264', '496', '808', '820', '847', '844')
--	m_nm IS NOT NULL
 	AND getEmkrName(a.em_seq) = '권도희'
	GROUP BY a.em_seq, a.cs_num, m_nm
				
					
				
				
				
				
				
				
				
				

SELECT t1.cs_num AS '사업자번호', t1.cs_nm AS '광고주명', getcomkrname(t1.m_nm) AS '매채', t1.cs_m_id AS '아이디', t2.pay_price AS '매출액'
FROM (				
		SELECT b.cs_num, getCsKrName(a.cs_seq) AS 'cs_nm', if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id
		FROM t_customer_md AS a
		inner JOIN t_customer AS b
			ON(a.cs_seq = b.cs_seq)
		WHERE a.del_date IS NULL 
			AND m_nm IN ('264', '496', '808', '820', '847', '844')
		GROUP BY a.cs_m_id, a.m_nm
		)t1
LEFT JOIN(
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price'
			FROM t_contract
			WHERE del_date IS NULL 
			AND sales_type IN ('1') 
			AND agree_state IN ('3') 
			AND pay_date > '2022-02-02'
			AND m_nm IN ('264', '496', '808', '820', '847', '844')
			group BY cs_m_id, m_nm
			)t2  ON t1.cs_m_id = t2.cs_m_id AND t1.m_nm=t2.m_nm
-- WHERE t1.cs_nm = ''			
GROUP BY t1.cs_m_id, t1.m_nm
ORDER BY t1.cs_nm
-- 매체별로 금액이 달라야 한다....			




		SELECT b.cs_num, getCsKrName(a.cs_seq) AS 'cs_nm', if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id, getCsKrName(c.em_seq)
		FROM t_customer_md AS a
		inner JOIN t_customer AS b ON(a.cs_seq = b.cs_seq)
		INNER JOIN t_customer_mg AS c ON c.cs_seq = b.cs_seq
		WHERE a.del_date IS NULL 
			AND m_nm IN ('264', '496', '808', '820', '847', '844')
		GROUP BY a.cs_m_id, a.m_nm
		
		
------------------------------------------------------------- 마케터별 매체 확장-------------------------------------------------------------- 
SELECT t1.em_seq AS '담당자', t1.cs_num AS '사업자번호', t1.cs_nm AS '광고주명', getcomkrname(t1.m_nm) AS '매채', t1.cs_m_id AS '아이디', t2.pay_price AS '매출액'
FROM (				
		SELECT b.cs_num, getCsKrName(a.cs_seq) AS 'cs_nm', if(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id, getEmkrName(MAX(c.em_seq)) AS 'em_seq'
		FROM t_customer_md AS a
		inner JOIN t_customer AS b ON(a.cs_seq = b.cs_seq)
		INNER JOIN t_customer_mg AS c ON c.cs_seq = b.cs_seq
		WHERE a.del_date IS NULL AND if(a.m_nm='302', a.good_type2, a.m_nm) IN ('264', '496', '808', '820', '847', '844')
		GROUP BY a.cs_m_id, a.m_nm
		)t1
left JOIN(
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, sum(pay_price) AS 'pay_price'
			FROM t_contract
			WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') 
			AND pay_date > '2022-02-02'
			AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
			group BY cs_m_id, m_nm
			)t2  ON t1.cs_m_id = t2.cs_m_id AND t1.m_nm=t2.m_nm
GROUP BY t1.em_seq, t1.cs_m_id, t1.m_nm
ORDER BY t1.em_seq, t1.cs_num
