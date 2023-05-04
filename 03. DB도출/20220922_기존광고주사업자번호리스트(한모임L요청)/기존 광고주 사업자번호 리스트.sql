

DROP TEMPORARY TABLE IF EXISTS live_list;
CREATE TEMPORARY TABLE IF NOT EXISTS live_list
SELECT 
			t1.cs_seq, 
			getemkrname(em_seq) AS '담당자명',  	
			ifnull(group_concat(distinct cs_m_id SEPARATOR ' | '),'-') AS '계정',
			getcomkrname(IF(t1.m_nm='302', t1.good_type2, t1.m_nm)) AS '매체',
			t2.cs_num AS '사업자번호', 
		  	getCsKrName(t1.cs_seq) '광고주명'
FROM t_contract t1 
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE 1=1 
	AND pay_date between '2022-01-01' AND '2022-08-31'
	AND IF(t1.m_nm='302', t1.good_type2, t1.m_nm) IN (264, 496, 808, 844, 847, 820)
	AND agree_state = '3' 
	AND sales_type IN (1,3) 
	AND t1.del_date IS NULL
GROUP BY t1.cs_seq, t1.cs_m_id;

SELECT * FROM live_list;


DROP TEMPORARY TABLE IF EXISTS naver_live_list;
CREATE TEMPORARY TABLE IF NOT EXISTS naver_live_list
SELECT 
			t1.cs_seq, 
			getemkrname(em_seq) AS '담당자명',  	
			ifnull(group_concat(distinct cs_m_id SEPARATOR ' | '),'-') AS '계정',
			getcomkrname(IF(t1.m_nm='302', t1.good_type2, t1.m_nm)) AS '매체',
			t2.cs_num AS '사업자번호', 
		  	getCsKrName(t1.cs_seq) '광고주명'
FROM t_contract t1 
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE 1=1 
	AND pay_date between '2022-01-01' AND '2022-08-31'
	AND m_nm = 264
	AND agree_state = '3' 
	AND sales_type IN (1,3) 
	AND t1.del_date IS NULL
GROUP BY t1.cs_seq, t1.cs_m_id;

SELECT * FROM naver_live_list;



DROP TEMPORARY TABLE IF EXISTS gmarket_live_list;
CREATE TEMPORARY TABLE IF NOT EXISTS gmarket_live_list
SELECT 
			t1.cs_seq, 
			getemkrname(em_seq) AS '담당자명',  	
			ifnull(group_concat(distinct cs_m_id SEPARATOR ' | '),'-') AS '계정',
			getcomkrname(IF(t1.m_nm='302', t1.good_type2, t1.m_nm)) AS '매체',
			t2.cs_num AS '사업자번호', 
		  	getCsKrName(t1.cs_seq) '광고주명'
FROM t_contract t1 
INNER JOIN t_customer t2 ON t1.cs_seq = t2.cs_seq
WHERE 1=1 
	AND pay_date between '2022-01-01' AND '2022-08-31'
	AND m_nm = 496
	AND agree_state = '3' 
	AND sales_type IN (1,3) 
	AND t1.del_date IS NULL
GROUP BY t1.cs_seq, t1.cs_m_id;


SELECT * FROM naver_live_list; 									-- 1번 쿼리
SELECT * FROM gmarket_live_list;									-- 2번 쿼리
SELECT distinct t1.광고주명 FROM live_list t1 -- 3번 쿼리
 
1. 전체 네이버, 지마켓글로벌 1월~8월 라이브계정 중복제거한 cs_seq와 광고주명 도출 (3번 퀴리)
2. 위 리스트에서 네이버, 지마켓글로벌 각각 vlookup진행(1번,2번 쿼리)
3. 가로로 나오는 매체별 정보에서 지마켓데이터 있는 내용만 필터해서 (엑셀필터)
4. 그대로 네이버데이터에 덮어쓰기 ( 필터상태에서는 복붙이 안되서 다른시트에 옮겨가면서 진행함)
5. 마지막으로 담당자는 최신담당자로 vlookup함. ( 최신담당자 쿼리는 아래쪽 쿼리를 사용)

매체 추가로 추가작업내용
1. 1월~8월 라이브계정 중복제거한 광고주명 도출 (3번 퀴리)
2. 위 로우데이터에서 네이버, 지마켓글로벌 작업한 내용 옆에 로우데이터로 붙임
3. 가로로 나오는 매체별 정보에서 필터를 이용해서 네이버 지마켓은 그대로 덮어쓰고 나머지는 그대로 도출
4. 담당자까지 브이룩업



DROP TEMPORARY TABLE IF EXISTS em_list;
CREATE TEMPORARY TABLE IF NOT EXISTS em_list
( 
INDEX `em_seq` (em_seq)
)
SELECT em_seq, em_nm, getcomkrname(division1) AS 'division1', getcomkrname(division2) AS 'division2'
FROM t_employee 

DROP TEMPORARY TABLE IF EXISTS latest_em;
CREATE TEMPORARY TABLE IF NOT EXISTS latest_em
( 
INDEX `m_nm` (m_nm),
INDEX `cs_m_id` (cs_m_id)
)
SELECT a.cs_seq, IF(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id, a.em_seq
FROM t_contract a
INNER JOIN (
		SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, max(pay_date) AS 'max_pay_date'
		FROM t_contract
		WHERE pay_date BETWEEN '2022-01-01' AND '2022-08-31' 
			AND del_date IS NULL 
			AND sales_type IN ('1') 
			AND agree_state IN ('3')
		GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id
) b ON a.cs_m_id = b.cs_m_id and a.pay_date = b.max_pay_date AND IF(a.m_nm='302', a.good_type2, a.m_nm) = b.m_nm
WHERE a.del_date IS NULL 
		AND a.sales_type IN ('1') 
		AND a.agree_state IN ('3') 
      AND em_seq IN (SELECT em_seq FROM em_list)			
GROUP BY a.cs_seq, 'm_nm', a.cs_m_id, a.em_seq;

SELECT cs_m_id, getEmkrName(em_seq) FROM latest_em


