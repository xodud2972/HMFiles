SELECT 
DISTINCT 
		*
		 FROM t_employee
	LEFT JOIN t_common2
		ON t_employee.em_seq = t_common2.cm1_seq
		WHERE division1 = 1216 AND division2 = 1217
	
	
-- -----------------------------------------------참고 쿼리 (매체별 날짜별 pay_price)------------------------------------------------------------------------
SELECT pay_date, 
	SUM(CASE WHEN m_nm='41' OR good_type2='957' THEN pay_price ELSE 0 END) AS '카카오',  /*rms 카카오+카카오모먼트k-쇼핑박스*/
	SUM(CASE WHEN m_nm='496' THEN pay_price ELSE 0 END) AS '이베이',
	SUM(CASE WHEN m_nm='265' THEN pay_price ELSE 0 END) AS '구글',
	SUM(CASE WHEN m_nm='264' THEN pay_price ELSE 0 END) AS '네이버', /*rms 네이버+파워컨텐츠*/
	SUM(CASE WHEN m_nm='845' THEN pay_price ELSE 0 END) AS '페이스북',
	SUM(CASE WHEN good_type2='438' THEN pay_price ELSE 0 END) AS 'TG',
	SUM(CASE WHEN m_nm='808' THEN pay_price ELSE 0 END) AS '11st'
FROM t_contract 
WHERE del_date IS NULL 
AND (m_nm IN (41,496,265,264,845,808) OR good_type2 IN (438,957) )
AND sales_type='1'
AND agree_state='3'
AND pay_date BETWEEN '2021-12-01' AND '2021-12-31'
GROUP BY pay_date


-- --------------------------------------거의 공통 쿼리-------------------------------------------------------------------------------------------
WHERE del_date IS NULL 
AND sales_type='1'
AND agree_state='3'
AND pay_date BETWEEN '2020-08-01' AND '2020-08-12'


  
-- ------------------------------------------------------퇴사자 DB쿼리---------------------------------------------------------------------------  
SELECT getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS '매체명', 
		md.cs_m_id AS '계정', 
		md.reg_date AS '인입일', 
      getEmkrName(md.reg_emp) AS '담당자', 
		cus.cs_num AS '사업자번호', 
		IF(md.m_nm='302', md.good_type2, md.m_nm) AS 'm_nm', 
		md.del_date
FROM t_customer_md md INNER JOIN (
													SELECT cs_seq, cs_num 
												  	FROM t_customer
												  	WHERE reg_date >= '2021-01-01' 
													AND del_date is null
											) cus ON (md.cs_seq = cus.cs_seq)
WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
AND md.del_date IS NULL
AND cus.cs_num NOT IN (
								SELECT t_customer.cs_num
							   FROM t_customer_md LEFT JOIN t_customer ON (t_customer_md.cs_seq = t_customer.cs_seq) 
							   WHERE t_customer_md.m_nm = '808' 
								AND t_customer_md.del_date IS NULL
							)
							
							



-- ------------------------------------------------------퇴사자 DB쿼리---------------------------------------------------------------------------
SELECT cs_num, getcomkrname(c1.cs_type_new1) AS cs_type_new1 , getcomkrname(c1.cs_type_new2) AS cs_type_new2, 
				c1.cs_nm,c1.url AS url, c1.mg_nm, c1.mg_cell1, c1.mg_cell2, c1.mg_cell3,
				c1.mg_tel1, c1.mg_tel2, c1.mg_tel3, c1.mg_email, 
				round(SUM(c2.pay_price)/3,0) AS pay_price, 
				getcomkrname(c2.m_nm1) AS m_nm, pay_date
			FROM t_customer c1
			INNER JOIN(
							SELECT * FROM (
												SELECT cs_seq, cs_m_id, if(m_nm="302",good_type2,m_nm) as m_nm1, pay_date, pay_price, good_type2
																			from t_contract
																			WHERE 1=1
 																			AND cs_m_id='hyeon4464'
																			-- AND pay_date BETWEEN "2021-10-01" AND "2021-12-31"
																			AND del_date is null
																			AND sales_type="1" 
																			AND agree_state="3" 
												) AS a
						--	WHERE good_type2 = '1198'
							)c2 ON c1.cs_seq = c2.cs_seq
WHERE c1.del_date is null
GROUP BY c2.cs_m_id, m_nm
ORDER BY m_nm
							
							
						

------------------------------------------  1월 확장매체 11번가미등록 신규인입건 조회 최종쿼리 ---------------------------------------- 
SELECT getCsKrName(md.cs_seq) AS '광고주명', getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS '매체명', md.cs_m_id AS '광고계정', 
md.reg_date AS '인입일', getEmkrName(md.reg_emp) AS '마케터명', cus.cs_num AS '사업자번호'
FROM t_customer_md md 
inner JOIN (SELECT cs_seq, cs_num FROM t_customer WHERE reg_date BETWEEN '2022-01-01' AND '2022-01-31' AND del_date is NULL) cus 
ON (md.cs_seq = cus.cs_seq)
WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
AND cus.cs_seq NOT IN (
							SELECT DISTINCT cs_seq
							FROM t_customer_md
							WHERE m_nm = '808'
							AND del_date IS NULL
							)
AND md.del_date IS NULL



------------------------------------------- 2월 확장매체 11번가미등록 신규인입건 조회 최종쿼리--------------------------------------------------------------
SELECT getCsKrName(md.cs_seq) AS '광고주명', getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS '매체명', md.cs_m_id AS '광고계정', 
md.reg_date AS '인입일', getEmkrName(md.reg_emp) AS '마케터명', cus.cs_num AS '사업자번호'
FROM t_customer_md md 
inner JOIN (SELECT cs_seq, cs_num FROM t_customer WHERE reg_date BETWEEN '2022-02-01' AND '2022-02-28' AND del_date is NULL) cus 
ON (md.cs_seq = cus.cs_seq)
WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
AND cus.cs_seq NOT IN (
							SELECT DISTINCT cs_seq
							FROM t_customer_md
							WHERE m_nm = '808'
							AND del_date IS NULL
							)
AND md.del_date IS NULL


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



------------------------------------------------------------- 마케터별 매체 확장 -------------------------------------------------------------- 
SELECT t1.em_seq AS '담당자', t1.cs_num AS '사업자번호', t1.cs_nm AS '광고주명', getcomkrname(t1.m_nm) AS '매채', GROUP_CONCAT(DISTINCT t1.cs_m_id SEPARATOR ' | ') AS '아이디', t2.pay_price AS '매출액'
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