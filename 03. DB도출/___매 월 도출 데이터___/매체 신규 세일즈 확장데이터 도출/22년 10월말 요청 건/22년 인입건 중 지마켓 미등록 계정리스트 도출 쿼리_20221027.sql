1. 2부문 2022년 인입기준 지마켓 미등록 계정리스트 전체 쿼리
DROP TEMPORARY TABLE IF EXISTS data_list; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list
( 
INDEX `cs_m_id` (cs_m_id)
)
SELECT 
			md.reg_date,
			getEmkrName(md.reg_emp) AS reg_emp,
			md.cs_m_id AS cs_m_id,
			getCsKrName(md.cs_seq) AS kr_cs_seq,
			md.cs_seq AS cs_seq,			
			getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS m_nm,
			cus.cs_num,
			getcomkrname(enter_division1) AS division
FROM t_customer_md md 
inner JOIN (
							SELECT cs_seq, cs_num 
							FROM t_customer 
							WHERE left(reg_date,4) = '2022'
							AND del_date is NULL
						) cus ON (md.cs_seq = cus.cs_seq)
WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
AND cus.cs_seq NOT IN (
											SELECT DISTINCT cs_seq
											FROM t_customer_md
											WHERE m_nm = '496'
											AND del_date IS NULL
											)
AND md.del_date IS NULL
AND md.enter_division1 = 927;

SELECT * FROM data_list;
---------------------------------------------------------------------------------------------------------------------------------------
2. 최근3개월 매출이 발생한 모든 계정에 대한 데이터 
DROP TEMPORARY TABLE IF EXISTS data_list_2;
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_2
( 
INDEX `cs_m_id` (cs_m_id)
)
SELECT cs_m_id, SUM(pay_price) FROM t_contract 
WHERE pay_date BETWEEN '2022-08-01' AND '2022-10-31' 
and cs_m_id IN (SELECT cs_m_id FROM data_list)
GROUP BY cs_m_id;

SELECT * FROM data_list_2;
---------------------------------------------------------------------------------------------------------------------------------------
3. 1번쿼리 도출데이터에 2번쿼리 데이터를 계정id로 엑셀에서 vlookup하여 데이터 도출