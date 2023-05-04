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
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
-- 매핑이 되어있는 11번가 ( 매출발생여부에 괸계 없음)

DROP TEMPORARY TABLE IF EXISTS 11_list;
CREATE TEMPORARY TABLE IF NOT EXISTS 11_list
( 
INDEX `cs_seq` (cs_seq)
)
SELECT cs_seq, m_nm, GROUP_CONCAT(DISTINCT cs_m_id SEPARATOR ', ')AS 'cs_m_id'
FROM t_customer_md 
WHERE m_nm = 808
GROUP BY cs_seq;

SELECT * FROM 11_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 매출이 발생한 11번가 계정 데이터 

DROP TEMPORARY TABLE IF EXISTS 11st_cost_list;
CREATE TEMPORARY TABLE IF NOT EXISTS 11st_cost_list
( 
INDEX `cs_seq` (cs_seq)
)

SELECT a.cs_seq, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ', ') AS 'cs_m_id', SUM(a.pay_price) AS 'pay_price'
FROM t_contract a					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
	AND sales_type = 1 
	AND agree_state = 3
	AND m_nm = 808
	AND del_date IS NULL
GROUP BY a.cs_seq;

SELECT * FROM 11st_cost_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 11st 데이터 완료 

DROP TEMPORARY TABLE IF EXISTS 11st_join_list;
CREATE TEMPORARY TABLE IF NOT EXISTS 11st_join_list
SELECT a.cs_seq, m_nm, IFNULL(a.cs_m_id,'-') AS '11st_cs_m_id', IFNULL(pay_price,'0') AS '11st_pay_price' FROM 11_list a
LEFT JOIN 11st_cost_list b ON a.cs_seq = b.cs_seq
GROUP BY a.cs_seq, a.cs_m_id;

SELECT * FROM 11st_join_list;
----------------------------------------------------------------------------------------------------------------------------------
--11번가 데이터 최종 추가
DROP TEMPORARY TABLE IF EXISTS Gmarket_11st_final_data;
CREATE TEMPORARY TABLE IF NOT EXISTS Gmarket_11st_final_data

SELECT n_division1, n_em_seq, a.cs_seq, naver_id, naver_cost, Gmarket_id, Gmaket_cost, 11st_cs_m_id, 11st_pay_price FROM Gmarket_final_data a 
LEFT JOIN 11st_join_list b ON a.cs_seq = b.cs_seq;

SELECT * FROM Gmarket_11st_final_data;
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
-- 매핑이 되어있는 위메프 ( 매출발생여부에 괸계 없음)

DROP TEMPORARY TABLE IF EXISTS wemake_list;
CREATE TEMPORARY TABLE IF NOT EXISTS wemake_list
( 
INDEX `cs_seq` (cs_seq)
)
SELECT cs_seq, m_nm, GROUP_CONCAT(DISTINCT cs_m_id SEPARATOR ', ')AS 'cs_m_id'
FROM t_customer_md 
WHERE good_type2 = 847
GROUP BY cs_seq;

SELECT * FROM wemake_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 매출이 발생한 위메프 계정 데이터 

DROP TEMPORARY TABLE IF EXISTS wemake_cost_list;
CREATE TEMPORARY TABLE IF NOT EXISTS wemake_cost_list
( 
INDEX `cs_seq` (cs_seq)
)

SELECT a.cs_seq, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ', ') AS 'cs_m_id', SUM(a.pay_price) AS 'pay_price'
FROM t_contract a					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
	AND sales_type = 1 
	AND agree_state = 3
	AND good_type2 = 847
	AND del_date IS NULL
GROUP BY a.cs_seq;

SELECT * FROM wemake_cost_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 위메프 데이터 완료 

DROP TEMPORARY TABLE IF EXISTS wemake_join_list;
CREATE TEMPORARY TABLE IF NOT EXISTS wemake_join_list
SELECT a.cs_seq, m_nm, IFNULL(a.cs_m_id,'-') AS 'wemake_cs_m_id', IFNULL(pay_price,'0') AS 'wemake_pay_price' FROM wemake_list a
LEFT JOIN wemake_cost_list b ON a.cs_seq = b.cs_seq
GROUP BY a.cs_seq, a.cs_m_id;

SELECT * FROM wemake_join_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 위메프 데이터 최종 추가
DROP TEMPORARY TABLE IF EXISTS Gmarket_11st_wemake_final_data;
CREATE TEMPORARY TABLE IF NOT EXISTS Gmarket_11st_wemake_final_data
SELECT n_division1, n_em_seq, a.cs_seq, 
		naver_id, naver_cost, 
		Gmarket_id, Gmaket_cost, 
		11st_cs_m_id, 11st_pay_price, 
		wemake_cs_m_id, wemake_pay_price 
FROM Gmarket_11st_final_data a 
LEFT JOIN wemake_join_list b ON a.cs_seq = b.cs_seq;

SELECT * FROM Gmarket_11st_wemake_final_data;
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
----------------------------------------------------------------------------------------------------------------------------------
-- 매핑이 되어있는 티몬 ( 매출발생여부에 괸계 없음)

DROP TEMPORARY TABLE IF EXISTS tmon_list;
CREATE TEMPORARY TABLE IF NOT EXISTS tmon_list
( 
INDEX `cs_seq` (cs_seq)
)
SELECT cs_seq, m_nm, GROUP_CONCAT(DISTINCT cs_m_id SEPARATOR ', ')AS 'cs_m_id'
FROM t_customer_md 
WHERE good_type2 = 820
GROUP BY cs_seq;

SELECT * FROM tmon_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 매출이 발생한 티몬 계정 데이터 

DROP TEMPORARY TABLE IF EXISTS tmon_cost_list;
CREATE TEMPORARY TABLE IF NOT EXISTS tmon_cost_list
( 
INDEX `cs_seq` (cs_seq)
)

SELECT a.cs_seq, GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ', ') AS 'cs_m_id', SUM(a.pay_price) AS 'pay_price'
FROM t_contract a					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
	AND sales_type = 1 
	AND agree_state = 3
	AND good_type2 = 820
	AND del_date IS NULL
GROUP BY a.cs_seq;

SELECT * FROM tmon_cost_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 티몬 데이터 완료 

DROP TEMPORARY TABLE IF EXISTS tmon_join_list;
CREATE TEMPORARY TABLE IF NOT EXISTS tmon_join_list
SELECT a.cs_seq, m_nm, IFNULL(a.cs_m_id,'-') AS 'tmon_cs_m_id', IFNULL(pay_price,'0') AS 'tmon_pay_price' FROM tmon_list a
LEFT JOIN tmon_cost_list b ON a.cs_seq = b.cs_seq
GROUP BY a.cs_seq, a.cs_m_id;

SELECT * FROM tmon_join_list;
----------------------------------------------------------------------------------------------------------------------------------
-- 위메프 데이터 최종 추가
DROP TEMPORARY TABLE IF EXISTS Gmarket_11st_wemake_tmon_final_data;
CREATE TEMPORARY TABLE IF NOT EXISTS Gmarket_11st_wemake_tmon_final_data
SELECT getcomkrname(n_division1) AS '부문', getemkrname(n_em_seq) AS '담당자', getcskrname(a.cs_seq) AS '광고주명', 
		naver_id,ifnull(naver_id,'-') AS naver_id1, ifnull(naver_cost,'-') AS naver_cost, 
		ifnull(Gmarket_id,'-') AS Gmarket_id, ifnull(Gmaket_cost,'-') AS Gmarket_Cost, 
		ifnull(11st_cs_m_id,'-') AS 11st_id, ifnull(11st_pay_price,'-') AS 11st_Cost, 
		ifnull(wemake_cs_m_id,'-') AS wemake_id, ifnull(wemake_pay_price,'-') AS wemake_Cost,
		ifnull(tmon_cs_m_id,'-') AS tmon_id, ifnull(tmon_pay_price,'-') AS tmon_Cost
FROM Gmarket_11st_wemake_final_data a 
LEFT JOIN tmon_join_list b ON a.cs_seq = b.cs_seq;

SELECT * FROM Gmarket_11st_wemake_tmon_final_data;

