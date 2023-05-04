SELECT distinct getcomkrname(cs_type_new1) FROM t_customer GROUP BY cs_type_new1

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 5;
-- ------------------------------------------------------------------------------------------------------------------------------------
SELECT getcomkrname(a.m_nm) AS  '매체',
--		 getcomkrname(b.cs_type) AS 'type', 
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 845 -- 페이스북
GROUP BY type_new1 
ORDER BY cnt DESC 
LIMIT 6;

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 265 -- 구글
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 6;

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 41 -- 카카오
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 6;

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 264 -- 네이버
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 6;

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 496 -- 지마켓글로벌
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 6;

SELECT getcomkrname(a.m_nm) AS  '매체',
		 getcomkrname(b.cs_type_new1) AS type_new1, 
		 getcomkrname(b.cs_type_new2) AS type_new2,
		 count(cs_type_new1) as cnt
from t_contract a
INNER JOIN t_customer b ON a.cs_seq = b.cs_seq
WHERE a.pay_date BETWEEN '2022-09-01' AND '2022-10-15'
AND a.m_nm = 808 -- 11번가
GROUP BY type_new1 
ORDER BY cnt DESC
LIMIT 6;



1. 가을 및 수석시즌 의 기간 정의