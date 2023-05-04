SELECT 		getCsKrName(cs_seq) '광고주명',
			  	getcomkrname(IF(m_nm='302', good_type2, m_nm)) AS '매체',
			  	left(pay_date, 4) AS 'pay_year',
				left(pay_date, 7) AS 'pay_month',	
				sum(pay_price)
	FROM t_contract 
	WHERE left(pay_date, 7) BETWEEN '2017-01' AND '2022-06'
		AND agree_state = '3' 
		AND sales_type = '1'
		AND del_date IS NULL
	GROUP BY left(pay_date, 7), IF(m_nm='302', good_type2, m_nm), cs_seq