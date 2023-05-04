-- contract에서 가져오는데 그게 customer에서 가져와서 붙여보자.
-- contract에서 매출이 발생하지 않아도 매체가확장되어있으면 0 이라고 나와야한다.
SELECT getcomkrname(a.division1)AS '부문', getEmkrName(a.em_seq)AS '담당자', getCsKrName(a.cs_seq)AS '광고주',
GROUP_CONCAT(DISTINCT a.cs_m_id SEPARATOR ',')AS '네이버 광고계정', SUM(a.pay_price)AS '네이버 총비용',
ifnull(b.b_id, '-') AS '지마켓글로벌 광고계정', ifnull(b.b_price, '-') AS '지마켓글로벌 총비용',
ifnull(c.b_id, '-') AS '위메프 광고계정'		, ifnull(c.b_price , '-')AS '위메프 총비용',
ifnull(d.b_id, '-') AS '티몬 광고계정'			 , ifnull(d.b_price, '-') AS '티몬 총비용',
ifnull(e.b_id, '-') AS '11번가 광고계정'		 , ifnull(e.b_price, '-') AS '11번가 총비용'
FROM t_contract a
left JOIN (
					SELECT 
					GROUP_CONCAT(distinct cs_m_id SEPARATOR ',')AS 'b_id', SUM(pay_price) AS 'b_price', cs_seq
					FROM t_contract
					WHERE pay_date between '2022-06-01'  AND '2022-06-31'
					AND m_nm = 496
					AND del_date IS NULL 
					AND sales_type IN ("1") 
					AND agree_state IN ("3")
					GROUP BY cs_seq, em_seq
					ORDER BY getCsKrName(cs_seq)
			)b ON a.cs_seq = b.cs_seq
left JOIN (
					SELECT 
					GROUP_CONCAT(distinct cs_m_id SEPARATOR ',')AS 'b_id', SUM(pay_price) AS 'b_price', cs_seq
					FROM t_contract
					WHERE pay_date between '2022-06-01'  AND '2022-06-31'
					AND m_nm = 847
					AND del_date IS NULL 
					AND sales_type IN ("1") 
					AND agree_state IN ("3")
					GROUP BY cs_seq, em_seq
					ORDER BY getCsKrName(cs_seq)
			)c ON a.cs_seq = c.cs_seq			
left JOIN (
					SELECT 
					GROUP_CONCAT(distinct cs_m_id SEPARATOR ',')AS 'b_id', SUM(pay_price) AS 'b_price', cs_seq
					FROM t_contract
					WHERE pay_date between '2022-06-01'  AND '2022-06-31'
					AND m_nm = 820
					AND del_date IS NULL 
					AND sales_type IN ("1") 
					AND agree_state IN ("3")
					GROUP BY cs_seq, em_seq
					ORDER BY getCsKrName(cs_seq)
			)d ON a.cs_seq = d.cs_seq	
left JOIN (
					SELECT 
					GROUP_CONCAT(distinct cs_m_id SEPARATOR ',')AS 'b_id', SUM(pay_price) AS 'b_price', cs_seq
					FROM t_contract
					WHERE pay_date between '2022-06-01'  AND '2022-06-31'
					AND m_nm = 808
					AND del_date IS NULL 
					AND sales_type IN ("1") 
					AND agree_state IN ("3")
					GROUP BY cs_seq, em_seq
					ORDER BY getCsKrName(cs_seq)
			)e ON a.cs_seq = e.cs_seq					
WHERE pay_date between '2022-06-01'  AND '2022-06-31'
AND m_nm = 264
AND del_date IS NULL 
AND sales_type IN ("1") 
AND agree_state IN ("3")
GROUP BY a.cs_seq, em_seq
ORDER BY getCsKrName(cs_seq)
