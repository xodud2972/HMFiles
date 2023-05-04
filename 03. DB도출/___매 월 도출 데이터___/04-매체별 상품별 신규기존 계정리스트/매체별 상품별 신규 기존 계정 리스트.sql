/*기존신규쿼리(신규요청)*/
-- 1. 임시테이블 생성 ( pay_date 를 이전 월로 변경) - 소요시간 2초 
DROP TEMPORARY TABLE IF EXISTS tb_real_csmid2_2_1;
CREATE TEMPORARY TABLE IF NOT EXISTS tb_real_csmid2_2_1
( 
INDEX `m_nm` (m_nm),
INDEX `good_type2` (good_type2),
INDEX `cs_m_id` (cs_m_id)
)
SELECT 
		IF(m_nm='302', good_type2, m_nm) AS 'm_nm', 
		good_type2, sales_type, cs_m_id, cs_seq, division1, division2, em_seq, sum(pay_price) AS 'pay_price',
		ifnull(group_concat(distinct keyword SEPARATOR ' | '),'-') AS 'keyword'
FROM t_contract 
WHERE del_date IS NULL AND sales_type IN ('1', '3') AND agree_state IN ('3') AND pay_date BETWEEN '2023-02-01' AND '2023-02-28'
GROUP BY IF(m_nm='302', good_type2, m_nm), good_type2, sales_type, cs_m_id, cs_seq, division1, division2, em_seq;
SELECT * FROM tb_real_csmid2_2_1;

-- 2. 임시테이블 생성( pay_date 를 이전 월로 변경) - 소요시간 약 8분 30초 
DROP TEMPORARY TABLE IF EXISTS tb_real_csmid1_2_1;
CREATE TEMPORARY TABLE IF NOT EXISTS tb_real_csmid1_2_1
( 
INDEX `m_nm` (m_nm),
INDEX `good_type2` (good_type2),
INDEX `cs_m_id` (cs_m_id)
)
SELECT 
		IF(m_nm='302', good_type2, m_nm) AS 'm_nm', good_type2, cs_m_id, cs_seq, division1, division2, em_seq, sum(pay_price) AS 'pay_price'
FROM t_contract 
WHERE del_date IS NULL AND sales_type IN ('1', '3') AND agree_state IN ('3') AND pay_date < '2023-02-01' 
GROUP BY IF(m_nm='302', good_type2, m_nm), good_type2, cs_m_id, cs_seq, division1, division2, em_seq;
SELECT * FROM tb_real_csmid1_2_1;

/*cpc 비교*/
-- 3. cpc 실행 - 소요시간 1초
SELECT getcomkrname(a.m_nm) AS '매체', getcomkrname(a.good_type2) AS '상품', IF(a.sales_type='1', 'CPC', 'CPT') AS 'CPC/CPT', a.cs_m_id AS '광고계정', 
       getCsKrName(a.cs_seq) AS '광고주명', getcomkrname(a.division1) AS '부문', getcomkrname(a.division2) AS '팀', getEmkrName(a.em_seq) AS '담당자', 
                 a.pay_price AS '매출', IF(b.m_nm IS NULL, '신규', '기존') AS '구분(신규/기존)', keyword
FROM tb_real_csmid2_2_1 AS a LEFT OUTER JOIN tb_real_csmid1_2_1 AS b
ON (a.m_nm = b.m_nm AND a.good_type2 = b.good_type2 and a.cs_m_id = b.cs_m_id)
WHERE a.sales_type = '1'
GROUP BY a.m_nm, a.good_type2, a.cs_m_id, a.cs_seq, a.division1, a.division2, a.em_seq, a.pay_price, IF(b.m_nm IS NULL, '신규', '기존')


/*cpt 비교*/
-- 4. cpt 실행 ( cpt는 모두 기존으로 변경) - 소요시간 3초
SELECT getcomkrname(a.m_nm) AS '매체', getcomkrname(a.good_type2) AS '상품', IF(a.sales_type='1', 'CPC', 'CPT') AS 'CPC/CPT', a.cs_m_id AS '광고계정', 
       getCsKrName(a.cs_seq) AS '광고주명', getcomkrname(a.division1) AS '부문', getcomkrname(a.division2) AS '팀', getEmkrName(a.em_seq) AS '담당자', 
                 a.pay_price AS '매출', IF(b.m_nm IS NULL, '기존', '기존') AS '구분(신규/기존)', keyword
FROM tb_real_csmid2_2_1 AS a LEFT OUTER JOIN tb_real_csmid1_2_1 AS b
ON (a.m_nm = b.m_nm AND a.good_type2 = b.good_type2 and a.cs_seq = b.cs_seq)
WHERE a.sales_type = '3'
GROUP BY a.m_nm, a.good_type2, a.cs_m_id, a.cs_seq, a.division1, a.division2, a.em_seq, a.pay_price, IF(b.m_nm IS NULL, '신규', '기존')