/*AMS 미권한 계정 도출에 사용하는 쿼리(AMS월별 총비용 데이터)*/
SELECT CONCAT(if(media_code = 5, 'NAVER', if(media_code = 7, '모먼트', 'KAKAO')), matching_id), SUM(cost)
FROM t_report_month
WHERE pay_date = '2022-12' AND media_code IN (5, 7, 89)
GROUP BY media_code, matching_id