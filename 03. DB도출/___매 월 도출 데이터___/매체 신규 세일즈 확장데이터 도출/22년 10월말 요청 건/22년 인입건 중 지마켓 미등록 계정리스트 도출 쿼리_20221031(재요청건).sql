DROP TEMPORARY TABLE IF EXISTS data_list; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list
( 
INDEX `cs_m_id` (cs_m_id)
)
SELECT 
			md.cs_seq AS cs_seq,	
			getEmkrName(md.reg_emp) AS reg_emp,			
			getCsKrName(md.cs_seq) AS kr_cs_seq,					
			group_concat(distinct md.cs_m_id SEPARATOR  ' | ') AS cs_m_id,			
			cus.cs_num,
			getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS m_nm
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
AND md.enter_division1 = 927
GROUP BY m_nm, kr_cs_seq
ORDER BY m_nm;

SELECT * FROM data_list WHERE kr_cs_seq = '(주)더블유지에스';
---------------------------------------------------------------------------------------------------------------------------------------
2. 최근3개월 매출이 발생한 모든 계정에 대한 데이터 
DROP TEMPORARY TABLE IF EXISTS data_list_2;
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_2
SELECT cs_seq, getCsKrName(cs_seq) AS kr_cs_seq, SUM(pay_price) AS pay_price
	FROM t_contract 
WHERE pay_date BETWEEN '2022-08-01' AND '2022-10-31' 
	and cs_seq IN (SELECT cs_seq FROM data_list)
GROUP BY cs_seq;

SELECT * FROM data_list_2;
---------------------------------------------------------------------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS data_list_naver; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_naver
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm IN ('N-주제판(NOSP-보장형디스플레이광고)','NAVER','네이버-성과형DA');
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_naver a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;

DROP TEMPORARY TABLE IF EXISTS data_list_11st; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_11st
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm = '11번가';
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_11st a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;

DROP TEMPORARY TABLE IF EXISTS data_list_tmon; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_tmon
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm = '티몬';
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_tmon a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;

DROP TEMPORARY TABLE IF EXISTS data_list_wemake; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_wemake
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm = '위메프';
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_wemake a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;

DROP TEMPORARY TABLE IF EXISTS data_list_interpark; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_interpark
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm = '인터파크';
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_interpark a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;

DROP TEMPORARY TABLE IF EXISTS data_list_etc; 
CREATE TEMPORARY TABLE IF NOT EXISTS data_list_etc
SELECT a.kr_cs_seq AS kr_cs_seq, a.cs_m_id AS cs_m_id FROM data_list a
WHERE m_nm NOT IN('N-주제판(NOSP-보장형디스플레이광고)','NAVER','네이버-성과형DA','11번가','티몬','위메프','인터파크');
SELECT a.kr_cs_seq, a.cs_m_id, ifnull(b.pay_price,'-')AS pay_price FROM data_list_etc a LEFT JOIN data_list_2 b ON a.kr_cs_seq = b.kr_cs_seq;
