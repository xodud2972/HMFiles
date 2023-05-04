DROP TEMPORARY TABLE IF EXISTS data_list;
CREATE TEMPORARY TABLE IF NOT EXISTS data_list
( 
INDEX `cs_type_new1` (cs_type_new1)
)
SELECT cs_type_new1, cost
FROM t_report_daily 
WHERE pay_date BETWEEN '2022-09-01' AND '2022-10-15';hm_sales

SELECT * FROM data_list;

SELECT getcomkrname(cs_type_new1) AS 'cs_type_new1', sum(cost) AS sum_cost
FROM data_list 
GROUP BY cs_type_new1
ORDER BY sum_cost DESC 
LIMIT 6;
----------------------------------------위에는 광고비 순위----------------------------------------------


------------------------아래는 매체별 업종 순위------------------------------------
SELECT distinct cs_type_new1 , COUNT(cs_type_new1) 'cnt'
FROM t_report_daily 
WHERE pay_date BETWEEN '2022-09-01' AND '2022-10-15'
GROUP BY cs_type_new1
ORDER BY COUNT(cs_type_new1) DESC LIMIT 6;
------------------------------------------------평균 CPC 및 CTR 구하기 ------------------------
SELECT cs_type_new1, 
		avg(click_cnt/impression_cnt*100) as ctr, 
		avg(cost/click_cnt) as ppc
FROM t_report_daily 
WHERE pay_date BETWEEN '2022-09-01' AND '2022-10-15'
and cs_type_new1 =1020
GROUP BY cs_type_new1
-----------------------------------------------------------------------------------------------------
SELECT pay_date, sum(cost)
FROM t_report_daily 
WHERE pay_date BETWEEN '2022-09-01' AND '2022-10-15'
GROUP BY pay_date