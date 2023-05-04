SELECT 
	DISTINCT 
		em_nm AS 이름
		, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq = POSITION1) AS 직급
		, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division1) AS 부서
		, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division2) AS 팀
		 FROM t_employee
	INNER JOIN t_common2
		ON t_employee.em_seq = t_common2.cm1_seq
	WHERE em_nm = '김민주'
;

SELECT * FROM t_employee 
	LEFT JOIN t_common2 
		ON t_employee.em_seq = t_common2.cm1_seq 
	WHERE em_nm = '엄태영';
	
SELECT * FROM t_employee 
	INNER JOIN t_common2 
		ON t_employee.em_seq = t_common2.cm1_seq 
	WHERE em_nm = '김민주';

SELECT  
		em_nm AS 이름
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq = POSITION1) AS 직급
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division1) AS 부서
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division2) AS 팀
		FROM t_employee
	LEFT JOIN t_common2
		ON t_employee.em_seq = t_common2.cm1_seq
	WHERE em_nm = '엄태영'
;

SELECT 
	DISTINCT
		em_nm AS 이름
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq = POSITION1) AS 직급
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division1) AS 부서
	, (SELECT kr_name FROM t_common2 WHERE t_common2.cm2_seq =  division2) AS 팀
		FROM t_employee
		LEFT JOIN t_common2
		ON t_employee.em_seq = t_common2.cm1_seq
		WHERE division2 = 953 AND em_nm IN('김민주', '신현준', '유정민', '엄태영');
		
SELECT * FROM t_customer_mg WHERE reg_tit_support_board_listme = '11:33:54' AND reg_date = '2022-01-04';

CALL PROC_CUS_MG('aa', 'bb', 11);
SELECT * FROM t_customer_mg;






-- 계층형 게시판 DB test
-- 게시판 조회 
SELECT board_id, b_title, b_content, b_group_no, b_group_ord, b_depth, b_reg_date, b_writer, b_hit 
                                        FROM t_board 
                                    ORDER BY b_group_no desc, b_group_ord asc;


-- 게시물 댓글 조회 
SELECT * FROM t_reply 
-- WHERE r_board_id = 1
ORDER BY r_group_no, r_group_ord, r_depth;


-- 게시물 파일 조회
SELECT t_board.board_id, b_title, t_file.file_id, t_file.f_name, t_file.f_size, t_file.f_path, 
t_file.f_tmp_name, t_file.f_upload_date
FROM t_board INNER JOIN t_file ON t_board.board_id = t_file.f_board_id;
-- WHERE test_board.board_id = 1;


-- 새 게시글 insert 쿼리 
INSERT INTO t_board (b_title, b_content, b_group_no, b_group_ord, b_depth, b_reg_date, b_writer, b_hit)
VALUES ('10번글', '10번글', 
(SELECT group_no FROM (select MAX(b_group_no)+1 as group_no FROM t_board)A),
0, 0, SYSDATE(), '쓴이', 0);


-- 새 게시글에 대한 답변글 insert쿼리 (board_id와 b_group_no를 알아야 함)
INSERT INTO t_board(b_title, b_content, b_group_no, b_group_ord, b_depth, b_reg_date, b_wrtier, b_hit)
VALUES ('10번 답답글', '10번 답답글', 
(SELECT group_no FROM (SELECT b_group_no AS group_no FROM t_board WHERE board_id = 20)A),
(SELECT group_ord FROM (SELECT max(b_group_ord)+1 as group_ord FROM t_board WHERE b_group_no = 9)B),
1, SYSDATE(), '작성자', 0);

SELECT * FROM t_board ORDER BY b_group_no desc, b_group_ord ASC;

-- 새로운 댓글 insert 쿼리 r_board_id 만 바꿔서 넣어주면 가능. 단, depth는 아직 조건을 주지 않음.
-- 대댓글의 경우 해당 댓글의 id를 불러오고 depth를 +1 해주는 걸로 변경하자.
INSERT INTO t_reply 
	(r_board_id, r_title, r_content, r_write_name, r_group_no, r_group_ord, r_depth, r_regdate)
VALUES (
	2, '2게시물 3번째 댓', '2게시물 3번째 댓', '최우식',
	(SELECT group_no FROM (SELECT max(r_group_no) AS group_no FROM t_reply WHERE r_board_id = 2)A),
	(SELECT group_ord FROM (SELECT MAX(r_group_ord)+1 AS group_ord FROM t_reply WHERE r_board_id=2)B),
	1,
	SYSDATE()
);

SELECT * FROM t_reply 
-- WHERE r_board_id = 1
ORDER BY r_group_no, r_group_ord, r_depth;


SELECT MAX(b_group_no)+1 FROM t_board as MaxGroupNo;

SELECT b_title, b_content
       FROM t_board
   WHERE board_id = 32;
   
   SELECT b_group_no FROM t_board as MaxGroupNo where board_id = 20;
   
   
   SELECT t_board.board_id, t_board.b_title, t_board.b_content, t_reply.r_content, t_reply.r_write_name, t_reply.r_regdate
            FROM t_board
        INNER JOIN t_reply
            ON t_board.board_id = t_reply.r_board_id
        WHERE t_board.board_id = 99
        
  SELECT f_board_id, f_name
      FROM t_file
  INNER JOIN t_board
      ON t_board.board_id = t_file.f_board_id
  WHERE t_board.board_id = 112
  
  
  
  
  
  SELECT 
		 	CONCAT(tc1.kr_name, tc2.kr_name) AS '부문',  			
			te.em_nm AS '담당자명',
			tc5.cs_nm AS '광고주명',
  			tc4.kr_name AS '매체구분',					 	
			tc3.kr_name AS '상품구분',  			
  			sum(pay_price) AS '12월 매출액'
  FROM t_contract AS tc
			LEFT JOIN t_common2 AS tc1 ON (tc.division1 = tc1.cm2_seq)
			LEFT JOIN t_common2 AS tc2 ON (tc.division2 = tc2.cm2_seq)
			LEFT JOIN t_common2 AS tc3 ON (tc.good_type2 = tc3.cm2_seq)
			LEFT JOIN t_common2 AS tc4 ON (tc.m_nm = tc4.cm2_seq)
			LEFT JOIN t_customer AS tc5 ON (tc.cs_seq = tc5.cs_seq)
			LEFT JOIN t_employee AS te ON (tc.em_seq = te.em_seq)
  WHERE DATE(tc.pay_date) BETWEEN '2021-12-01' AND '2021-12-31'
	GROUP BY tc5.cs_nm
  ORDER BY tc5.cs_nm;
  
  
  
sELECT file_id, f_reply_id, f_name
            FROM t_file
        INNER JOIN t_reply
            ON t_file.f_reply_id = t_reply.reply_id
		WHERE t_file.f_reply_id = 49
		
		
		
		SELECT t_reply.reply_id, t_reply.r_content, t_reply.r_write_name, t_reply.r_regdate
            FROM t_board
        INNER JOIN t_reply
            ON t_board.board_id = t_reply.r_board_id
        WHERE t_board.board_id = 122
        ORDER BY t_reply.reply_id DESC
        
        
        
        
SELECT 
	CONCAT(tc3.kr_name, tc4.kr_name) AS 부문,
	te.em_nm AS '담당자명',
	tc5.ceo_nm AS '매체구분',
	tc1.kr_name AS '매체구분',
	tc2.kr_name AS '상품구분',
	pay_price AS '12월매출액',
	pay_date AS '매출 날짜'
FROM t_contract AS tc
	LEFT JOIN t_common2 AS tc1 ON (tc.m_nm = tc1.cm2_seq)
	LEFT JOIN t_common2 AS tc2 ON (tc.good_type2 = tc2.cm2_seq)
	LEFT JOIN t_customer AS tc5 ON (tc.cs_num = tc5.cs_num)
	LEFT JOIN t_common2 AS tc3 ON ( tc.division1 = tc3.cm2_seq)
	LEFT JOIN t_common2 AS tc4 ON ( tc.division2 = tc4.cm2_seq)
	LEFT JOIN t_employee AS te ON ( tc.em_seq = te.em_seq)
WHERE tc.ct_seq > 53000000 AND DATE(tc.pay_date) BETWEEN '2021-12-01' AND '2021-12-31'
	ORDER BY tc.pay_date;
	
	
SELECT * FROM t_board 
WHERE board_id < 155 OR b_group_no < 34
	ORDER BY b_group_no DESC, b_group_ord ASC LIMIT 10
	
	
SELECT * FROM t_board WHERE b_notice=0 and
					b_group_no > 30
			ORDER BY b_group_no desc, b_group_ord asc LIMIT 1;
				
SELECT * FROM t_board WHERE b_notice=0 and
					b_group_no < 30
				ORDER BY b_group_no desc, b_group_ord asc LIMIT 1;		
				
-- 게시글 댓글의 첨부파일만 출력
-- 그중에 f_reply_id를 구분해야함... 어케하지..				
SELECT * FROM t_file 
	LEFT JOIN t_board ON t_board.board_id = t_file.f_board_id
	LEFT JOIN t_reply ON t_reply.reply_id = t_file.f_reply_id
		WHERE t_board.board_id = 173 AND t_file.f_reply_id IS NOT null
		
		


-- 게시글 첨부파일만 출력		S


SELECT group_concat(f_name), f_board_id, f_reply_id
      FROM t_file
         	WHERE f_reply_id IN(SELECT t_reply.reply_id FROM t_board
											  INNER JOIN t_reply
											      ON t_board.board_id = t_reply.r_board_id
											  WHERE t_board.board_id = 122)
		GROUP BY f_reply_id;
        
        
        
        
SELECT * FROM t_board WHERE board_id = 158; 

SELECT * FROM t_board
 WHERE board_id IN (
    (SELECT board_id FROM t_board WHERE board_id < 158  ORDER BY board_id DESC LIMIT 1),
    (SELECT board_id FROM t_board WHERE board_id > 158  ORDER BY board_id LIMIT 1),
   );
SELECT * FROM t_board WHERE board_id < 107  ORDER BY board_id DESC LIMIT 1
SELECT * FROM t_board WHERE board_id > 107  ORDER BY board_id LIMIT 1


SELECT board_id, b_title, b_content, b_group_no, b_group_ord, b_depth, b_reg_date, b_writer, b_hit 
                                FROM t_board 
                            ORDER BY b_notice DESC, b_group_no desc, b_group_ord asc

SELECT b_group_no, b_group_ord, max(b_depth)+1 FROM t_board WHERE board_id = 185

SELECT * FROM t_employee 
ORDER BY em_seq DESC
LIMIT 50;



SELECT mc.m_nm, mc.cs_m_id, mc.end_date, mc.division1, mc.division2, mc.em_nm, 
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 6 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-6', 
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 5 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-5', 	
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 4 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-4', 	
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 3 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-3', 	
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 2 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-2', 	
		 sum(if(DATE_FORMAT(mc.end_date - INTERVAL 1 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월-1', 	
		 sum(if(DATE_FORMAT(mc.end_date, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월',
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 1 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+1', 		 
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 2 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+2', 
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 3 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+3', 
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 4 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+4', 		 
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 5 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+5', 
		 sum(if(DATE_FORMAT(mc.end_date + INTERVAL 6 MONTH, '%Y-%m') = tc.pay_month, tc.pay_price, 0)) AS '퇴사월+6',
		 SUM(tc.pay_price)
FROM
(SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, DATE_FORMAT(pay_date, '%Y-%m') AS 'pay_month', SUM(pay_price) AS 'pay_price'
	FROM t_contract
	WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') AND pay_date >= '2020-07-01'
	GROUP BY IF(m_nm='302', good_type2, m_nm), cs_m_id, DATE_FORMAT(pay_date, '%Y-%m')) AS tc
	RIGHT OUTER JOIN
		(SELECT t.m_nm, t.cs_m_id, e.end_date, e.division1, e.division2, e.em_nm
			FROM
			(SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, em_seq
				FROM t_contract
				WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') and
     			 em_seq IN ('1281','200958','1077','200967','37','1018','1610','1407','200956','1296','200955',
					  			'200959','200968','1315','1563','639','921','279','1564','1502','923','1503','1378','1669','1592','1670')) as t	  	  
				INNER JOIN 
					(SELECT em_seq, em_nm, division1, division2, end_date 
						FROM t_employee 
						WHERE em_seq IN ('1281','200958','1077','200967','37','1018','1610','1407','200956','1296','200955',
											'200959','200968','1315','1563','639','921','279','1564','1502','923','1503','1378','1669','1592','1670')) AS e
				ON (t.em_seq = e.em_seq)
				GROUP BY t.m_nm, t.cs_m_id, t.em_seq) as mc
	ON(tc.m_nm = mc.m_nm AND tc.cs_m_id = mc.cs_m_id)
	GROUP BY mc.m_nm, mc.cs_m_id, mc.em_nm
	
	
	
SELECT f_name, f_board_id, f_reply_id
   FROM t_file
   WHERE f_reply_id IN(SELECT t_reply.reply_id FROM t_board
                       INNER JOIN t_reply
                       ON t_board.board_id = t_reply.r_board_id
                       WHERE t_board.board_id = 168)
   GROUP BY f_reply_id;

SELECT board_id, b_title FROM t_board WHERE board_id < 168 ORDER BY board_id DESC LIMIT 1

SELECT max(board_id), b_title FROM t_board WHERE board_id < 168 


-- 참고 쿼리 (매체별 날짜별 pay_price)
SELECT pay_date, 
	SUM(CASE WHEN m_nm='41' OR good_type2='957' THEN pay_price ELSE 0 END) AS '카카오',  /*rms 카카오+카카오모먼트k-쇼핑박스*/
	SUM(CASE WHEN m_nm='496' THEN pay_price ELSE 0 END) AS '이베이',
	SUM(CASE WHEN m_nm='265' THEN pay_price ELSE 0 END) AS '구글',
	SUM(CASE WHEN m_nm='264' THEN pay_price ELSE 0 END) AS '네이버', /*rms 네이버+파워컨텐츠*/
	SUM(CASE WHEN m_nm='845' THEN pay_price ELSE 0 END) AS '페이스북',
	SUM(CASE WHEN good_type2='438' THEN pay_price ELSE 0 END) AS 'TG',
	SUM(CASE WHEN m_nm='808' THEN pay_price ELSE 0 END) AS '11st'
FROM t_contract 
WHERE del_date IS NULL 
AND (m_nm IN (41,496,265,264,845,808) OR good_type2 IN (438,957) )
AND sales_type='1'
AND agree_state='3'
AND pay_date BETWEEN '2021-12-01' AND '2021-12-31'
GROUP BY pay_date


-- 거의 공통 쿼리
WHERE del_date IS NULL 
AND sales_type='1'
AND agree_state='3'
AND pay_date BETWEEN '2020-08-01' AND '2020-08-12'




-- 연도, 연도+월, 업체명, 매체구분1, 매체구분, 상품구분, 광고주id, division1, division1, 직원명, 매출액
SELECT YEAR, MONTH, getcskrname(cs_seq), m_nm1, getcomkrname(m_nm), getcomkrname(good_type2), cs_m_id, 
						  getcomkrname(division1),getcomkrname(getemdivision(em_seq)), getemkrname(em_seq) ,  pay_price 
FROM (
		SELECT LEFT(pay_date,4) AS YEAR, LEFT(pay_date,7) AS MONTH, cs_seq, IF(m_nm='302',good_type2,m_nm) AS m_nm1,
				 m_nm,  good_type2, cs_m_id, division1, em_seq, SUM(pay_price) AS  pay_price
		FROM t_contract 
		WHERE del_date IS NULL AND pay_date BETWEEN '2021-12-01' AND '2021-12-31' 
				AND sales_type IN ('1','3') AND agree_state='3' 
		GROUP BY LEFT(pay_date,7),m_nm, good_type2, cs_m_id, em_seq
) AS aa




-- 광고주id(t_cutomer_md.cs_num)와 매체(t_contract.m_nm1 or m_nm).

-- t_customer 테이블 항목
-- 사업자번호 				cs_num 
-- 대카테고리					cs_type_new1 
-- 소카테고리					cs_type_new2
-- RMS업체명				  cs_nm
-- url						  url
-- 광고담당자 이름		  	 mg_nm
-- 광고담당자 유선연락처	 mg_cell1,2,3
-- 광고담당자 무선연락처  mg_tel1,2,3
-- 광고담당자 이메일주소  mg_email

-- t_contract 테이블 항목
-- 최근 3개월 매출 		  pay_price



SELECT  cs_m_id AS 광고주ID, getcomkrname(m_nm1) AS 매체1, getcomkrname(m_nm) AS 매체2
FROM (
		select t_customer_md.cs_m_id, IF(t_contract.m_nm='302',t_contract.good_type2,t_contract.m_nm) AS m_nm1, t_contract.m_nm 
			FROM t_customer_md
		INNER JOIN t_customer ON t_customer_md.cs_seq = t_customer.cs_seq
		INNER JOIN t_contract ON t_customer_md.cs_seq = t_contract.cs_seq
			WHERE t_customer_md.cs_m_id = 'keekee73' AND t_contract.m_nm in(264, 41)
		)AS ab
	

-- cs_m_id 와 m_nm 을 통해서 해당 데이터를 불러온다. m_nm은 commons에서변환 해야한다. 

-- t_contract.pay_date > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
-- c3.cs_m_id = 'fimasia' AND 
SELECT c1.cs_num AS '사업자번호', getcomkrname(c1.cs_type_new1) AS '대카테고리', getcomkrname(c1.cs_type_new2) AS '소카테고리', 
		 c1.cs_nm AS 'rms업체명',c1.url AS url, c1.mg_nm AS '광고담당자이름', c1.cell1, c1.cell2, c1.cell3,
		 c1.mg_tel1, c1.mg_tel2, c1.mg_tel3, c1.mg_email, DATE_FORMAT(c2.pay_date,'%Y') AS 'year', round(SUM(c2.pay_price)/3,0) AS '3개월 평균 매출',
		 getcomkrname(c2.m_nm) AS '매체한글', c2.m_nm AS m_nm
FROM t_customer c1
	INNER JOIN t_contract c2 ON c1.cs_seq = c2.cs_seq
	INNER JOIN t_customer_md c3 ON c3.cs_seq = c1.cs_seq
WHERE c3.cs_m_id = 'fimasia' 
	AND c2.m_nm = '264'
	AND c2.pay_date BETWEEN '2021-10-01' AND '2021-12-31' 
GROUP BY c3.cs_m_id, m_nm
ORDER BY m_nm;



SELECT c1.cs_num AS '사업자번호', getcomkrname(c1.cs_type_new1) AS '대카테고리', getcomkrname(c1.cs_type_new2) AS '소카테고리', 
		 c1.cs_nm AS 'rms업체명',c1.url AS url, c1.mg_nm AS '광고담당자이름', c1.cell1, c1.cell2, c1.cell3,
		 c1.mg_tel1, c1.mg_tel2, c1.mg_tel3, c1.mg_email, DATE_FORMAT(c2.pay_date,'%Y') AS 'year', round(SUM(c2.pay_price)/3,0) AS '3개월 평균 매출',
		 getcomkrname(c2.m_nm) AS '매체한글', c2.m_nm AS m_nm
FROM t_customer c1
	INNER JOIN t_contract c2 ON c1.cs_seq = c2.cs_seq
WHERE c2.cs_m_id = 'fimasia' 
	AND c2.m_nm = '264'
	AND c2.pay_date BETWEEN '2021-10-01' AND '2021-12-31' 
GROUP BY c2.cs_m_id, m_nm
ORDER BY m_nm;



SELECT c1.cs_num AS '사업자번호', getcomkrname(c1.cs_type_new1) AS '대카테고리', getcomkrname(c1.cs_type_new2) AS '소카테고리', 
		 c1.cs_nm AS 'rms업체명',c1.url AS url, c1.mg_nm AS '광고담당자이름', c1.cell1, c1.cell2, c1.cell3,
		 c1.mg_tel1, c1.mg_tel2, c1.mg_tel3, c1.mg_email, DATE_FORMAT(c2.pay_date,'%Y') AS 'year', round(SUM(c2.pay_price)/3,0) AS '3개월 평균 매출',
		 getcomkrname(c2.m_nm) AS '매체한글', c2.m_nm AS m_nm
FROM t_customer c1
	INNER JOIN 
	(
		SELECT cs_seq, cs_m_id, m_nm, pay_date, pay_price
		from t_contract
		WHERE cs_m_id = 'cccssscsc' 
		AND m_nm = '264'
		AND pay_date BETWEEN '2021-10-01' AND '2021-12-31' 
	)c2 
	ON c1.cs_seq = c2.cs_seq
GROUP BY c2.cs_m_id, m_nm
ORDER BY m_nm;



SELECT 	c1.cs_num, getcomkrname(c1.cs_type_new1) AS cs_type_new1, 
			getcomkrname(c1.cs_type_new2) AS cs_type_new2,
			c1.cs_nm, c1.url AS url, 
			c1.mg_nm, 
			c1.mg_cell1, 
			c1.mg_cell2, 
			c1.mg_cell3,
			c1.mg_tel1, 
			c1.mg_tel2, 
			c1.mg_tel3, 
			c1.mg_email, 
			ROUND(SUM(c2.pay_price)/3,0) AS pay_price, 
			getcomkrname(c2.m_nm) AS m_nm, 
			if(m_nm='302', c2.good_type2, c2.m_nm) AS m_nm1, 
			if(c2.m_nm='302', getcomkrname(c2.good_type2), c2.m_nm) AS m_nm2
FROM t_customer c1
INNER JOIN (
				SELECT 	cs_seq, 
							cs_m_id, 
							m_nm, 
							good_type2, 
							pay_date, 
							pay_price
				FROM t_contract
				WHERE pay_date BETWEEN "2021-10-01" AND "2021-12-31"
			)c2
ON c1.cs_seq = c2.cs_seq
GROUP BY c2.cs_m_id, m_nm
ORDER BY m_nm

