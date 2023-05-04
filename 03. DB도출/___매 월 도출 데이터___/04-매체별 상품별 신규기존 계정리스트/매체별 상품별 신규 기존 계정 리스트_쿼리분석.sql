-- 첫번쨰 임시테이블이 tb_real_csmid2_2_1 -> last_month_data 이전 월 데이터 
-- 두번쨰 임시테이블이 tb_real_csmid1_2_1 -> last_every_data 이전 월 이전 모든 데이터

/*기존신규쿼리(신규요청)*/
-- 1. 임시테이블 생성 ( pay_date 를 이전 월로 변경) - 소요시간 2초 
DROP TEMPORARY TABLE IF EXISTS last_month_data;
CREATE TEMPORARY TABLE IF NOT EXISTS last_month_data
( 
INDEX `m_nm` (m_nm),
INDEX `good_type2` (good_type2),
INDEX `cs_m_id` (cs_m_id)
)
SELECT 
		IF(m_nm='302', good_type2, m_nm) AS 'm_nm', 
		good_type2, sales_type, cs_m_id, cs_seq, division1, division2, em_seq, 
		sum(pay_price) AS 'pay_price'
		,ifnull(group_concat(distinct keyword SEPARATOR ' | '),'-') AS 'keyword'
FROM t_contract 
WHERE del_date IS NULL AND sales_type IN ('1', '3') AND agree_state IN ('3') AND pay_date BETWEEN '2023-02-01' AND '2023-02-28'
GROUP BY IF(m_nm='302', good_type2, m_nm), good_type2, sales_type, cs_m_id, cs_seq, division1, division2, em_seq;

SELECT * FROM last_month_data;


-- 2. 임시테이블 생성( pay_date 를 이전 월로 변경) - 소요시간 약 6분 30초 
	DROP TEMPORARY TABLE IF EXISTS last_every_data;
	CREATE TEMPORARY TABLE IF NOT EXISTS last_every_data
	( 
	INDEX `m_nm` (m_nm),
	INDEX `good_type2` (good_type2),
	INDEX `cs_m_id` (cs_m_id)
	)
	SELECT 
			IF(m_nm='302', good_type2, m_nm) AS 'm_nm', 				
			good_type2, cs_m_id, cs_seq, division1, division2, em_seq, 
			sum(pay_price) AS 'pay_price'
	--		,ifnull(group_concat(distinct keyword SEPARATOR ' | '),'-') AS 'keyword'		
	FROM t_contract 
	WHERE del_date IS NULL AND sales_type IN ('1', '3') AND agree_state IN ('3') AND pay_date < '2023-02-01' 
	GROUP BY IF(m_nm='302', good_type2, m_nm), good_type2, cs_m_id, cs_seq, division1, division2, em_seq;
	SELECT * FROM last_every_data;


/*cpc 비교*/
-- 3. cpc 실행( cpc는 신규/기존 구분) <-> cpt  - 소요시간 5초
SELECT 
		getcomkrname(a.m_nm) AS '매체', 
		getcomkrname(a.good_type2) AS '상품', 
		IF(a.sales_type='1', 'CPC', 'CPT') AS 'CPC/CPT', 
		a.cs_m_id AS '광고계정', 
      getCsKrName(a.cs_seq) AS '광고주명', 
		getcomkrname(a.division1) AS '부문', 
		getcomkrname(a.division2) AS '팀', 
		getEmkrName(a.em_seq) AS '담당자', 
      a.pay_price AS '매출', 
		IF(b.m_nm IS NULL, '신규', '기존') AS '구분(신규/기존)'
		-- ,keyword
FROM last_month_data AS a 
LEFT OUTER JOIN last_every_data AS b ON (
														a.m_nm = b.m_nm 
														AND a.good_type2 = b.good_type2 
														AND a.cs_m_id = b.cs_m_id
														)
WHERE a.sales_type = '1' -- 아래 쿼리와 차이 
GROUP BY a.m_nm, a.good_type2, a.cs_m_id, a.cs_seq, a.division1, a.division2, a.em_seq, a.pay_price, IF(b.m_nm IS NULL, '신규', '기존')


/*cpt 비교*/
-- 4. cpt 실행 ( cpt는 모두 기존으로 변경) - 소요시간 3초
SELECT
 		getcomkrname(a.m_nm) AS '매체', 
		getcomkrname(a.good_type2) AS '상품', 
		IF(a.sales_type='1', 'CPC', 'CPT') AS 'CPC/CPT', 
		a.cs_m_id AS '광고계정', 
      getCsKrName(a.cs_seq) AS '광고주명', 
		getcomkrname(a.division1) AS '부문', 
		getcomkrname(a.division2) AS '팀', 
		getEmkrName(a.em_seq) AS '담당자', 
      a.pay_price AS '매출', 
		IF(b.m_nm IS NULL, '기존', '기존') AS '구분(신규/기존)'
		-- ,keyword
FROM last_month_data AS a 
LEFT OUTER JOIN last_every_data AS b ON (
														a.m_nm = b.m_nm 
														AND a.good_type2 = b.good_type2 
														AND a.cs_seq = b.cs_seq
													)
WHERE a.sales_type = '3' -- 위 쿼리와 차이
GROUP BY a.m_nm, a.good_type2, a.cs_m_id, a.cs_seq, a.division1, a.division2, a.em_seq, a.pay_price, IF(b.m_nm IS NULL, '신규', '기존')