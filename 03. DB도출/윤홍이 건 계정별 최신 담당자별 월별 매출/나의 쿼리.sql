-- 2부문 전원, 3부문 마케팅팀 
	DROP TEMPORARY TABLE IF EXISTS em_list;
	CREATE TEMPORARY TABLE IF NOT EXISTS em_list
	( 
	INDEX `em_seq` (em_seq)
	)
	SELECT em_seq, em_nm, getcomkrname(division1) AS 'division1', getcomkrname(division2) AS 'division2'
	FROM t_employee 
	WHERE division1 = 927 OR division2 = 1202 
	ORDER BY division1, division2;
	
SELECT * FROM em_list;
------------------------------------------------------------------------------------------------------------------------------------------------------
-- 계정별 최신 담당자 도출 
	DROP TEMPORARY TABLE IF EXISTS latest_em;
	CREATE TEMPORARY TABLE IF NOT EXISTS latest_em
	( 
	INDEX `m_nm` (m_nm),
	INDEX `cs_m_id` (cs_m_id)
	)
	SELECT a.cs_seq, IF(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id, a.em_seq
	FROM t_contract a
	INNER JOIN (
			SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, max(pay_date) AS 'max_pay_date'
			FROM t_contract
			WHERE pay_date BETWEEN '2021-01-01' AND '2022-06-30' 
				AND del_date IS NULL 
				AND sales_type IN ('1') 
				AND agree_state IN ('3')
			GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
	) b ON a.cs_m_id = b.cs_m_id and a.pay_date = b.max_pay_date AND IF(a.m_nm='302', a.good_type2, a.m_nm) = b.m_nm
	WHERE a.del_date IS NULL 
			AND a.sales_type IN ('1') 
			AND a.agree_state IN ('3') 
	      AND em_seq IN (SELECT em_seq FROM em_list)			
	GROUP BY a.cs_seq, 'm_nm', a.cs_m_id, a.em_seq;
	
SELECT * FROM latest_em;
------------------------------------------------------------------------------------------------------------------------------------------------------\
-- 전기간 월별 
	DROP TEMPORARY TABLE IF EXISTS month_price;
	CREATE TEMPORARY TABLE IF NOT EXISTS month_price
	( 
	INDEX `m_nm` (m_nm),
	INDEX `cs_m_id` (cs_m_id)
	)
	SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, em_seq, left(pay_date, 7) AS 'pay_month', SUM(pay_price) AS 'pay_price'
	from t_contract
	WHERE del_date IS NULL 
		AND sales_type IN ('1') 
		AND agree_state IN ('3')
	GROUP BY 'm_nm', cs_m_id, em_seq, 'pay_month';
	
SELECT * FROM month_price;
	------------------------------------------------------------------------------------------------------------------------------------------------------
-- 최신 담당자와의 계약 기간(개월수)
	DROP TEMPORARY TABLE IF EXISTS count_em;
	CREATE TEMPORARY TABLE IF NOT EXISTS count_em
	( 
	INDEX `m_nm` (m_nm),
	INDEX `cs_m_id` (cs_m_id)
	)
	SELECT m_nm, cs_m_id, em_seq, count(pay_month) AS 'count_em'
	FROM month_price
	WHERE pay_month <= '2022-06'
			AND (m_nm, cs_m_id, em_seq) IN (SELECT m_nm, cs_m_id, em_seq FROM latest_em)
	GROUP BY m_nm, cs_m_id, em_seq;
	
SELECT * FROM count_em;
------------------------------------------------------------------------------------------------------------------------------------------------------
-- HM 계약기간(개월수)
	DROP TEMPORARY TABLE IF EXISTS count_id;
	CREATE TEMPORARY TABLE IF NOT EXISTS count_id
	( 
	INDEX `m_nm` (m_nm),
	INDEX `cs_m_id` (cs_m_id)
	)
	SELECT m_nm, cs_m_id, count(pay_month) AS 'count_id'
	FROM month_price
	WHERE pay_month <= '2022-06'
		AND (m_nm, cs_m_id) IN (SELECT m_nm, cs_m_id FROM latest_em)
	GROUP BY m_nm, cs_m_id;
	
SELECT * FROM count_id;
------------------------------------------------------------------------------------------------------------------------------------------------------
-- 전기간으로 뽑은 데이터에서 선택기간으로 도출하기
	DROP TEMPORARY TABLE IF EXISTS month_price_result;
	CREATE TEMPORARY TABLE IF NOT EXISTS month_price_result
	( 
	INDEX `m_nm` (m_nm),
	INDEX `cs_m_id` (cs_m_id)
	)	
	SELECT m_nm, cs_m_id, em_seq, pay_month, pay_price
	FROM month_price
	WHERE pay_month BETWEEN '2021-01' AND '2022-06' 
	AND (m_nm, cs_m_id, em_seq) IN (SELECT m_nm, cs_m_id, em_seq FROM latest_em);
	
SELECT * FROM month_price_result;
------------------------------------------------------------------------------------------------------------------------------------------------------
-- 최종적으로 모든 임시테이블 붙이기
	DROP TEMPORARY TABLE IF EXISTS final_result;
	CREATE TEMPORARY TABLE IF NOT EXISTS final_result	
	SELECT getcomkrname(te.division1), getcomkrname(te.division2), getEmkrName(le.em_seq), tc.cs_nm, getcomkrname(le.m_nm), le.cs_m_id, ci.count_id, ce.count_em, tc.cs_num, 
			 tc.license_cs_status, getcomkrname(tc.cs_type_new2), tc.mg_nm, ifnull(concat(tc.mg_tel1, tc.mg_tel2, tc.mg_tel3), '') AS '유선', 
			 ifnull(concat(tc.mg_cell1, tc.mg_cell2, tc.mg_cell3), '') AS '무선', tc.mg_email, tc.url, mpr.pay_month, mpr.pay_price
	FROM latest_em as le 
	LEFT OUTER JOIN t_customer AS tc ON (le.cs_seq = tc.cs_seq)
	LEFT OUTER JOIN t_employee AS te ON (le.em_seq = te.em_seq)
	LEFT OUTER JOIN count_id AS ci ON (le.m_nm = ci.m_nm and le.cs_m_id = ci.cs_m_id)
	LEFT OUTER JOIN count_em AS ce ON (le.m_nm = ce.m_nm and le.cs_m_id = ce.cs_m_id AND le.em_seq = ce.em_seq)
	LEFT OUTER JOIN month_price_result mpr ON (le.m_nm = mpr.m_nm and le.cs_m_id = mpr.cs_m_id AND le.em_seq = mpr.em_seq);
		
SELECT * FROM final_result;