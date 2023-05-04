/*업종별데이터 날짜 2개 바꿔야 함*/ -- 소요시간 - 2분30초 
SELECT trm.pay_date AS '기간', ifnull(t1.kr_name, '-') AS '신규1차업종', ifnull(t2.kr_name, '-') AS '신규2차업종', ifnull(trm.cs_nm, '-') AS '광고주명', 
       ifnull(tcu.url, '-') AS '광고주url', /*ci.cc AS '광고주수',*/ci.sum_cost_range AS '광고주의 월 총비용 구간',
       m.product_kr_name AS '매체명', trm.matching_id AS '광고계정', IF(p.product_kr_name IS NULL, ifnull(trm.product_type, '-'), ifnull(p.product_kr_name, '-')) AS '상품명',
                 trm.impression_cnt AS '노출수', 
                 trm.click_cnt AS '클릭수', 
                 ifnull(concat(round(trm.click_cnt/trm.impression_cnt*100, 2), '%'), '-') AS '클릭률',
                 ifnull(round(trm.cost/trm.click_cnt), '-') AS '평균클릭비용', 
                 trm.cost AS '총비용',
                 trm.conversion_cnt AS '전환수', 
                 ifnull(concat(round(trm.conversion_cnt/trm.click_cnt*100, 2), '%'), '-') AS '전환율',
                 trm.conversion_amnt AS '전환매출액',
                 ifnull(round(trm.cost/trm.conversion_cnt), '-') AS '전환당비용',
                 ifnull(concat(round(trm.conversion_amnt/trm.cost*100, 2), '%'), '-') AS '광고수익률',
      			  ifnull(e.em_nm, '-') AS '담당자명', 
					  ifnull(tc2_1.kr_name, '-') AS '부문', 
					  ifnull(tc2_2.kr_name, '-') AS '팀'
FROM (SELECT pay_date, cs_type_new1, cs_type_new2, cs_seq, cs_nm, media_code, matching_id, product_type, impression_cnt, click_cnt, cost, conversion_cnt, conversion_amnt, em_seq, division, team
         FROM t_report_month WHERE pay_date BETWEEN '2022-12' AND '2023-02') AS trm
LEFT OUTER JOIN (SELECT cm2_seq, kr_name FROM t_common2 WHERE cm1_seq = 25) AS t1 ON trm.cs_type_new1 = t1.cm2_seq
LEFT OUTER JOIN (SELECT cm2_seq, kr_name FROM t_common2 WHERE cm1_seq = 26) AS t2 ON trm.cs_type_new2 = t2.cm2_seq
LEFT OUTER JOIN (SELECT increment_id, product_kr_name FROM t_media_common) AS m ON trm.media_code = m.increment_id
LEFT OUTER JOIN (SELECT tmpl.real_code AS 'real_code', tmc.product_kr_name AS 'product_kr_name' FROM t_media_product_link AS tmpl INNER JOIN t_media_common AS tmc 
ON tmpl.common_id = tmc.increment_id) AS p ON trm.product_type = p.real_code
LEFT OUTER JOIN (SELECT em_seq, em_nm, division1, division2 FROM t_employee WHERE del_date IS NULL) AS e ON trm.em_seq = e.em_seq
LEFT OUTER JOIN t_common2 AS tc2_1 ON(e.division1 = tc2_1.cm2_seq)
LEFT OUTER JOIN t_common2 AS tc2_2 ON(e.division2 = tc2_2.cm2_seq)
LEFT OUTER JOIN (
						SELECT pay_date, cs_seq, 1/COUNT(*) AS 'cc', (case when SUM(cost)>=10000000 then '1000만원이상' 
																					when SUM(cost)>=9000000 then '900~1000만원'
																					when SUM(cost)>=8000000 then '800~900만원'                 
																					when SUM(cost)>=7000000 then '700~800만원'                 
																					when SUM(cost)>=6000000 then '600~700만원'                 
																					when SUM(cost)>=5000000 then '500~600만원'                
																					when SUM(cost)>=4000000 then '400~500만원'
																					when SUM(cost)>=3000000 then '300~400만원'                
																					when SUM(cost)>=2000000 then '200~300만원'                                                                                                                                                                                           
																					when SUM(cost)>=1000000 then '100~200만원'                
																					when SUM(cost)>=500000 then '50~100만원'                                                                                                                                                                                           
																					when SUM(cost)>=300000 then '30~50만원'                
																					when SUM(cost)>=100000 then '10~30만원'                                                                                                                                                                                           
																					ELSE '10만원 미만'
																					END) AS sum_cost_range
					FROM t_report_month
					WHERE pay_date BETWEEN '2022-12' AND '2023-02'
					GROUP BY pay_date, cs_seq
					) AS ci ON trm.pay_date = ci.pay_date AND trm.cs_seq = ci.cs_seq
LEFT OUTER JOIN t_customer AS tcu ON trm.cs_seq = tcu.cs_seq