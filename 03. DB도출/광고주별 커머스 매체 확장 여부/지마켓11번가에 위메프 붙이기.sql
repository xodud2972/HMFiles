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
