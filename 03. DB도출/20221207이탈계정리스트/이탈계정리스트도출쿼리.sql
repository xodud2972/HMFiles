SET @c_date_1 = '2022-01-01', @c_date_2 = '2022-01-31', @l_date_1 = '2021-12-01', @l_date_2 = '2021-12-31';

DROP TEMPORARY TABLE IF EXISTS cs_m_id_list;
CREATE TEMPORARY TABLE IF NOT EXISTS cs_m_id_list
SELECT cs_m_id FROM t_real_real_r_transfer_id 
						WHERE r_transfer_date2 BETWEEN @c_date_1 AND @c_date_2
							AND del_date IS NULL 
							AND em_seq IN (33,57,212,337,551,566,556,560,817,930,1012,1271,1280,1281,1282,1296,1319,1351,1378,1485,1486,1488,1494,1495,1496,1502,1503,1530,1578,1580,1581,1593,1594,1613,1665,1669,1670,200955,200956,200967,200968,201030,201038,201059,201061,201080,201105,201106,201107,201108,201114,201116,201117,201118);
-- SELECT * FROM cs_m_id_list;				
DROP TEMPORARY TABLE IF EXISTS data_list;
CREATE TEMPORARY TABLE IF NOT EXISTS data_list
SELECT getEmkrName(em_seq) AS 'em_seq', getcomkrname(m_nm) AS 'm_nm', 
			getCsKrName(cs_seq) AS 'cs_seq', cs_m_id, left(r_transfer_date2,7) AS 'out_month',
			m1 AS 'out_before_pay_price', m2 AS 'out_month_pay_price'
		FROM ( 				
						SELECT em_seq, m_nm, cs_m_id, r_transfer_date1, r_transfer_date2 
						FROM t_real_real_r_transfer_id 
						WHERE r_transfer_date2 BETWEEN @c_date_1 AND @c_date_2
							AND del_date IS NULL 
							AND em_seq IN (33,57,212,337,551,566,556,560,817,930,1012,1271,1280,1281,1282,1296,1319,1351,1378,1485,1486,1488,1494,1495,1496,1502,1503,1530,1578,1580,1581,1593,1594,1613,1665,1669,1670,200955,200956,200967,200968,201030,201038,201059,201061,201080,201105,201106,201107,201108,201114,201116,201117,201118)	
				) AS a LEFT OUTER JOIN ( 				
						SELECT t1.m_nm AS bm_nm, t1.cs_m_id AS bcs_m_id, t1.cs_seq,
								SUM(CASE WHEN pay_date BETWEEN @l_date_1 AND @l_date_2 THEN pay_price ELSE 0 END) AS m1,
								SUM(CASE WHEN pay_date BETWEEN @c_date_1 AND @c_date_2 THEN pay_price ELSE 0 END) AS m2
						FROM t_contract as t1 
							inner join t_customer as t2 ON t1.cs_seq=t2.cs_seq
						WHERE pay_date BETWEEN @l_date_1 AND @c_date_2
							AND sales_type='1' 
							AND t1.del_date IS NULL 
							AND em_seq IN (33,57,212,337,551,566,556,560,817,930,1012,1271,1280,1281,1282,1296,1319,1351,1378,1485,1486,1488,1494,1495,1496,1502,1503,1530,1578,1580,1581,1593,1594,1613,1665,1669,1670,200955,200956,200967,200968,201030,201038,201059,201061,201080,201105,201106,201107,201108,201114,201116,201117,201118)
						GROUP BY cs_seq, m_nm, cs_m_id
				) AS b ON a.m_nm = b.bm_nm AND a.cs_m_id = b.bcs_m_id 
ORDER BY m1 DESC;
-- SELECT * FROM data_list;
SELECT distinct a.em_seq AS '마케터', a.m_nm AS '매체', b.cs_seq AS '광고주명', a.cs_m_id AS '광고주ID', a.out_month AS '이탈월', 
			ifnull(a.out_before_pay_price,'0') AS '이탈전월매출', IFNULL(out_month_pay_price, '0') AS '이탈당월매출'
			FROM data_list AS a
LEFT JOIN ( 
				SELECT getCsKrName(md.cs_seq)AS cs_seq, cs_m_id, getcomkrname(m_nm) AS m_nm FROM t_customer_md AS md 
				WHERE cs_m_id IN (SELECT * FROM cs_m_id_list)
			) AS b ON a.cs_m_id = b.cs_m_id and a.m_nm = b.m_nm