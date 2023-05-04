SELECT cus.cs_nm AS 광고주명, cus.cs_num AS 사업자번호, getEmkrName(max(mg.em_seq)) AS 'em_seq', cus.smartstore_yn
FROM t_customer cus
INNER JOIN t_customer_mg mg ON cus.cs_seq = mg.cs_seq
WHERE smartstore_yn = 'y'
GROUP BY cus.cs_nm, cus.cs_num


SELECT * FROM t_customer cus
inner join t_customer_mg mg
	ON cus.cs_seq = mg.cs_seq
WHERE smartstore_yn = 'y'



