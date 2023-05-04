-- 기준이 되는 네이버  데이터 
DROP TEMPORARY TABLE IF EXISTS naver_list;
CREATE TEMPORARY TABLE IF NOT EXISTS naver_list
( 
INDEX `cs_seq` (cs_seq)
)
SELECT a.division1 AS 'n_division1', a.em_seq AS 'n_em_seq', a.cs_seq AS 'cs_seq',
GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ', ')AS 'n_cs_m_id', SUM(a.pay_price)AS 'n_pay_price'
FROM t_contract a					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
	AND sales_type = 1 
	AND agree_state = 3
	AND m_nm = 264
	AND del_date IS NULL 
GROUP BY a.cs_seq, em_seq
ORDER BY cs_seq;

SELECT * FROM naver_list;

---------------------------------------------------------------------------------------------------------------------------------
-- 매핑이 되어있는 지마켓 글로벌 계정 ( 매출발생여부에 괸계 없음)

DROP TEMPORARY TABLE IF EXISTS gmarket_list;
CREATE TEMPORARY TABLE IF NOT EXISTS gmarket_list
( 
INDEX `cs_seq` (cs_seq)
)
SELECT cs_seq, m_nm, GROUP_CONCAT(DISTINCT cs_m_id SEPARATOR ', ')AS 'cs_m_id'
FROM t_customer_md 
WHERE m_nm = 496
GROUP BY cs_seq;


SELECT * FROM gmarket_list;

----------------------------------------------------------------------------------------------------------------------------------
-- 매출이 발생한 지마켓 글로벌 계정 데이터 

DROP TEMPORARY TABLE IF EXISTS gmarket_cost_list;
CREATE TEMPORARY TABLE IF NOT EXISTS gmarket_cost_list
( 
INDEX `cs_seq` (cs_seq)
)

SELECT a.cs_seq, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ', ') AS 'cs_m_id', SUM(a.pay_price) AS 'pay_price'
FROM t_contract a					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
	AND sales_type = 1 
	AND agree_state = 3
	AND m_nm = 496
	AND del_date IS NULL
GROUP BY a.cs_seq;

SELECT * FROM gmarket_cost_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 지마켓글로벌 데이터 완료 

DROP TEMPORARY TABLE IF EXISTS join_list;
CREATE TEMPORARY TABLE IF NOT EXISTS join_list
SELECT a.cs_seq, m_nm, IFNULL(a.cs_m_id,'-') AS 'cs_m_id', IFNULL(pay_price,'0') AS 'pay_price' FROM gmarket_list a
LEFT JOIN gmarket_cost_list b ON a.cs_seq = b.cs_seq
GROUP BY a.cs_seq, a.cs_m_id;

SELECT * FROM join_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 합친 데이터 
DROP TEMPORARY TABLE IF EXISTS Gmarket_final_data;
CREATE TEMPORARY TABLE IF NOT EXISTS Gmarket_final_data
SELECT n_division1,
		n_em_seq,
		a.cs_seq AS 'cs_seq',
		n_cs_m_id AS 'naver_id',
		n_pay_price 'naver_cost', 
		m_nm, 
		cs_m_id AS 'Gmarket_id', 
		pay_price AS 'Gmaket_cost'
FROM naver_list a left join join_list b ON a.cs_seq = b.cs_seq;

SELECT * FROM Gmarket_final_data;
----------------------------------------------------------------------------------------------------------------------------------





-- 최종에 쓰일 함수 미리 써놓기
SELECT getcomkrname(n_division1) AS '부문',
		getemkrname(n_em_seq) AS '담당자',
		getCsKrName(a.cs_seq) AS '광고주명',
		n_cs_m_id AS 'naver_id',
		n_pay_price 'naver_cost', 
		getcomkrname(m_nm) AS '매체', 
		cs_m_id AS 'Gmarket_id', 
		pay_price AS 'Gmaket_cost', a.cs_seq
FROM naver_list a left join join_list b ON a.cs_seq = b.cs_seq;