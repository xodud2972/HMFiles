-- 22년 04월 11번가 미등록 계정 리스트 도출 쿼리 pay_date만 변경해주면 된다.
SELECT getCsKrName(md.cs_seq) AS '광고주명', 
	getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) AS '매체명', 
	md.cs_m_id AS '광고계정', md.reg_date AS '인입일', getEmkrName(md.reg_emp) AS '마케터명', 
	cus.cs_num AS '사업자번호'
FROM t_customer_md md 
inner JOIN (
							SELECT cs_seq, cs_num 
							FROM t_customer 
							WHERE reg_date BETWEEN '2022-04-01' AND '2022-04-31' 
							AND del_date is NULL
						) cus ON (md.cs_seq = cus.cs_seq)
WHERE getcomkrname(IF(md.m_nm='302', md.good_type2, md.m_nm)) IS NOT NULL 
AND cus.cs_seq NOT IN (
											SELECT DISTINCT cs_seq
											FROM t_customer_md
											WHERE m_nm = '808'
											AND del_date IS NULL
											)
AND md.del_date IS NULL