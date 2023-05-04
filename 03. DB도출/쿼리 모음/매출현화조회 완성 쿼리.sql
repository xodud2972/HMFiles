-- 일자별 (측정기준: 기간) (비교대상 날짜 없는 쿼리)
SELECT a.*, b.*
FROM ( 
		SELECT pay_date as pay_date , good_type2, SUM(pay_price) as pay_price, m_nm
		FROM t_contract WHERE pay_date BETWEEN '2022-04-01' AND '2022-04-02' 
		AND del_date IS NULL 
		AND sales_type IN (1,3) 
		AND agree_state = '3' 
		GROUP BY pay_date ,  good_type2 
		) AS a 
INNER JOIN ( 
		SELECT cm2_seq, cm3_seq, code4, code5 
		FROM t_common2
		WHERE cm3_seq > 0
		AND use_yn='Y' 
		) AS b ON a.good_type2=b.cm2_seq
		
		
-- 계정리스트
SELECT a.*, getcomkrname(b.code4) AS code4, getcomkrname(b.code5) AS code5, getcomkrname(b.code6) AS code6 
FROM ( 
		SELECT getcomkrname(division1) AS 'division1', getcomkrname(division2) AS 'division2', getemkrname(em_seq) AS 'em_seq', 
		getCsKrname(cs_seq) AS 'cs_seq', cs_m_id AS 'cs_m_id', getcomkrname(m_nm) as 'm_nm', sum(pay_price) AS 'pay_price', 
		good_type2, getcomkrname(good_type2) as good_type22 
		FROM t_contract 
		WHERE pay_date between '2022-04-01' AND '2022-04-31'
		AND del_date IS NULL AND sales_type IN (1,3) 
		AND agree_state = '3' 
		AND good_type2 = 384
		GROUP BY division1, division2, em_seq, cs_seq
		 ) AS a 
INNER JOIN ( 
		SELECT cm2_seq, cm1_seq, CODE1, CODE2, kr_name, cm3_seq, code4, code5, code6 
		FROM t_common2 b 
		WHERE cm3_seq > 0 
		AND use_yn='Y' 
		AND CODE4 = 1230 
		) AS b ON a.good_type2=b.cm2_seq		