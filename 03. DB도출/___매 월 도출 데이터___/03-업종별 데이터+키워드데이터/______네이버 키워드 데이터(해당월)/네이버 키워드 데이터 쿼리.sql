/*키워드데이터-날짜 2개 바꿔야함*/
SELECT kg.keyword_group AS '검색용 필드', ifnull(t1.kr_name, '-') AS '신규1차업종', ifnull(t2.kr_name, '-') AS '신규2차업종', 
       ifnull(c.cs_nm, '-') AS '광고주명', IFNULL(c.url, '-') AS 'URL', k.matching_id AS '광고계정',
       (case k.product_type 
                  when 0 then '그 외'
                  when 1 then '파워링크'                 
                  when 2 then '쇼핑검색'                 
                  when 3 then '파워컨텐츠'                 
                  when 4 then '브랜드검색'                
                  when 6 then '플레이스'
                  ELSE ''
                  end) AS '상품명', 
						k.keyword AS ' 키워드', 
						ifnull(e.em_nm, '-') AS '담당자명',
                  k.impression_cnt AS '노출수', 
                  k.click_cnt AS '클릭수', 
                  ifnull(concat(round(k.click_cnt/k.impression_cnt*100, 2), '%'), '-') AS '클릭률',
                  ifnull(round(k.cost/k.click_cnt), '-') AS '평균클릭비용', 
                  k.cost AS '총비용',
                  k.conversion_cnt AS '전환수', 
                  ifnull(concat(round(k.conversion_cnt/k.click_cnt*100, 2), '%'), '-') AS '전환율',
                  k.conversion_amnt AS '전환매출액',
                  ifnull(round(k.cost/k.conversion_cnt), '-') AS '전환당비용',
                  ifnull(concat(round(k.conversion_amnt/k.cost*100, 2), '%'), '-') AS '광고수익률'
FROM (SELECT * FROM t_keyword_filter_month WHERE pay_date = '2023-02') AS k
LEFT OUTER JOIN (SELECT cm2_seq, kr_name FROM hm.t_common2 WHERE cm1_seq = 25) AS t1 ON k.cs_type_new1 = t1.cm2_seq
LEFT OUTER JOIN (SELECT cm2_seq, kr_name FROM hm.t_common2 WHERE cm1_seq = 26) AS t2 ON k.cs_type_new2 = t2.cm2_seq
LEFT OUTER JOIN (SELECT cs_seq, cs_nm, url FROM hm.t_customer WHERE del_date IS NULL) AS c ON k.cs_seq = c.cs_seq
LEFT OUTER JOIN (SELECT em_seq, em_nm FROM hm.t_employee WHERE del_date IS NULL) AS e ON k.em_seq = e.em_seq
LEFT OUTER JOIN (
						SELECT cs_seq, GROUP_CONCAT(DISTINCT keyword separator '|') AS 'keyword_group' 
						FROM t_keyword_filter_month 
						WHERE pay_date = '2023-02' GROUP BY cs_seq
					) as kg ON k.cs_seq = kg.cs_seq