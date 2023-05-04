-- 12월에 매출한 데이터를 제외하기 위해 도출한 12월 라이브계정리스트
DROP TEMPORARY TABLE IF EXISTS december_list;
CREATE TEMPORARY TABLE IF NOT EXISTS december_list
select getemkrname(em_seq), cs_seq, getcskrname(cs_seq), cs_m_id, 
		pay_date, pay_price, 
		getcomkrname(m_nm), getcomkrname(division1)
from t_contract 
where left(pay_date,7) = '2022-12'
	AND division1 = 927
	AND m_nm = 496
	AND del_date IS NULL 
	AND sales_type IN ("1") 
	AND agree_state IN ("3")		
group by cs_seq;
SELECT * from december_list;

-- 12월 라이브계정을 도출하기위해 사용할 sub임시테이블
DROP TEMPORARY TABLE IF EXISTS sub_list;
CREATE TEMPORARY TABLE IF NOT EXISTS sub_list
select cs_seq, max(pay_date) as pay_date
from t_contract 
where left(pay_date,7) < '2022-12'
	AND division1 = 927
	AND m_nm = 496
	AND del_date IS NULL 
	AND sales_type IN ("1") 
	AND agree_state IN ("3")		
group by cs_seq;
SELECT * FROM sub_list

/** max(pay_date) 즉, 가장 마지막 매출발생일 당일에 매출액을 도출하는 쿼리.
	아래 쿼리 데이터는 12월 이전데이터이기 때문에 아래 쿼리결과에서 위 쿼리인 12월 데이터를 vlookup으로 제거해야한다.**/
DROP TEMPORARY TABLE IF EXISTS before_december_list;
CREATE TEMPORARY TABLE IF NOT EXISTS before_december_list	
SELECT *
from(
	select
		getemkrname(em_seq), cs_seq, getcskrname(cs_seq), cs_m_id, 
		pay_date, pay_price, 
		getcomkrname(m_nm), getcomkrname(division1)
	from t_contract
	where left(pay_date,7) < '2022-12'
	AND (cs_seq, pay_date) in (SELECT cs_seq, pay_date FROM sub_list)
	AND division1 = 927
	AND m_nm = 496
	AND del_date IS NULL 
	AND sales_type IN ("1") 
	AND agree_state IN ("3")	
	order by pay_date desc
) t
group by t.cs_seq;
SELECT * FROM before_december_list;

/** 피이관목록테이블에서 피이관된 계정리스트 도출 하여 피이관여부 확인 **/
SELECT m_nm, cs_m_id, r_transfer_date1, r_transfer_date2
FROM t_real_real_r_transfer_id
WHERE division1 = 927
AND m_nm = 496
AND del_date IS NULL

SELECT * from december_list;
SELECT * FROM before_december_list;

결국 엑셀로 작업함.
12월 이전 로우데이터에서 12월라이브계정리스트 제거해서 공유함.