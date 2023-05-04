-- 아래 쿼리는 순서대로 top20 개를 뽑는 쿼리
-- division1 IN (6,809)	인지 NOT IN 인지에 따라 hm 과 디플/대대행으로 구분됨
-- 날짜 변경해주면 됨
SELECT getcomkrname(m_nm), getcomkrname(good_type2), sum(pay_price)AS pay_price, left(pay_date,4)AS pay_date, 
		if(division1 IN(6,809),'대대행+디플', 'hm') AS 'division1', cs_seq, getCsKrName(cs_seq)
FROM t_contract 
WHERE pay_date BETWEEN '2022-01-01' AND '2022-06-31'
		AND agree_state = '3' 
		AND sales_type IN ('1','3')
		AND del_date IS NULL
		AND division1 IN (6,809)	
GROUP BY cs_seq
ORDER BY pay_price DESC LIMIT 20;

-- 아래쿼리는 위 쿼리에서 도출된 20개의 cs_seq를 넣어서 20개의 광고주의 해당연도 모든 광고 도출
-- division1 IN (6,809)	인지 NOT IN 인지에 따라 hm 과 디플/대대행으로 구분됨
-- 날짜 변경해주면 됨
SELECT getcomkrname(m_nm), getcomkrname(good_type2), sum(pay_price)AS pay_price, left(pay_date,4)AS pay_date, 
		if(division1 IN(6,809),'대대행+디플', 'hm') AS 'division1', cs_seq, getCsKrName(cs_seq)
FROM t_contract 
WHERE pay_date BETWEEN '2022-01-01' AND '2022-06-31'
AND cs_seq IN (43644,66246,67655,67692,69290,45889,68774,63581,64651,67117,67746,23481,64437,48042,69227,68660,53444,59227,66047,67112)
		AND agree_state = '3' 
		AND sales_type IN ('1','3')
		AND del_date IS NULL
		AND division1 IN (6,809)	
GROUP BY left(pay_date,4), m_nm, good_type2, cs_seq;

-- 도출 날짜는 2017, 2018, 2019, 2020, 2021, 2022.01~2022.06 까지 데이터
-- 부문 구분은 hm과 대대행/디지털플래닝
-- CPT, CPC 둘 다 포함



-- 아래쿼리는 로우데이터 도출 쿼리
-- 로우데이터는 hm과 디플/대대행 을 따로 뽑아야 데이터가 맞아서 쿼리를 따로 돌렸음
-- AND division1 IN (6,809)	과 NOT IN 에 차이임
SELECT getcomkrname(m_nm), getcomkrname(good_type2), sum(pay_price)AS pay_price, left(pay_date,4)AS pay_date, 
		if(division1 IN(6,809),'대대행+디플', 'hm') AS 'division1', m_nm, good_type2
FROM t_contract 
WHERE pay_date BETWEEN '2017-01-01' AND '2022-08-31'
		AND agree_state = '3' 
		AND sales_type IN ('1','3')
		AND del_date IS NULL
		AND division1 NOT IN (6,809)	
GROUP BY left(pay_date,4), m_nm, good_type2;

SELECT getcomkrname(m_nm), getcomkrname(good_type2), sum(pay_price)AS pay_price, left(pay_date,4)AS pay_date, 
		if(division1 IN(6,809),'대대행+디플', 'hm') AS 'division1', m_nm, good_type2
FROM t_contract 
WHERE pay_date BETWEEN '2017-01-01' AND '2022-06-31'
		AND agree_state = '3' 
		AND sales_type IN ('1','3')
		AND del_date IS NULL
		AND division1 IN (6,809)	
GROUP BY left(pay_date,4), m_nm, good_type2;