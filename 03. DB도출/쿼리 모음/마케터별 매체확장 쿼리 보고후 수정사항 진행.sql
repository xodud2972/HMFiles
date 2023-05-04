SELECT getEmkrName(t1.em_seq) AS '담당자', t1.cs_num AS '사업자번호', getCsKrName(t1.cs_nm) AS '광고주명', getcomkrname(t1.m_nm) AS '매채', t1.cs_m_id AS '아이디', t2.pay_price AS '매출액'
FROM (				
		SELECT b.cs_num, a.cs_seq AS 'cs_nm', IF(a.m_nm='302', a.good_type2, a.m_nm) AS 'm_nm', a.cs_m_id, MAX(c.em_seq) AS 'em_seq'
		FROM t_customer_md AS a
		INNER JOIN t_customer AS b ON (a.cs_seq = b.cs_seq)
		INNER JOIN t_customer_mg AS c ON c.cs_seq = b.cs_seq
		WHERE a.del_date IS NULL 
		AND IF(a.m_nm='302', a.good_type2, a.m_nm) IN ('264', '496', '808', '820', '847', '844')
		GROUP BY a.cs_m_id, a.m_nm
)t1
LEFT JOIN(
		SELECT IF(m_nm='302', good_type2, m_nm) AS 'm_nm', cs_m_id, SUM(pay_price) AS 'pay_price'
		FROM t_contract
		WHERE del_date IS NULL AND sales_type IN ('1') AND agree_state IN ('3') 
		AND pay_date BETWEEN '2022-02-01' AND '2022-03-01'
		AND IF(m_nm='302', good_type2, m_nm) IN ('264', '496', '808', '820', '847', '844')
		GROUP BY cs_m_id, m_nm
)t2  
ON t1.cs_m_id = t2.cs_m_id 
AND t1.m_nm=t2.m_nm
GROUP BY t1.em_seq, t1.cs_m_id, t1.m_nm
ORDER BY t1.em_seq, t1.cs_num

// 한글로 조인 하지 말 것
// md 와 mg 를 조인한 이유 ? 
-- md에customer를 조인한 것은 사업자번호를 가져오기 위해 
-- customer에 mg를 조인한 것은 최신 담당자를 가져오기 위해 
// 함수는 마지막 셀렉트 단에서 사용할 것
-- 수정완료 
// 기간 조회는 시작일~종료일 형태로 사용할 것
-- 수정완료 
// GROUP BY t1.em_seq, t1.cs_m_id, t1.m_nm 계정을 2번쨰, 매체를 3번쨰에 둔 이유는 ?
-- m_nm과cs_m_id는 순서가 중요하지 않다고 생각하였지만, 확인해보니 속도최적화를 위해서 
-- 인덱스 순서대로 조정는게 좋을거같아, 순서를 조정하였습니다.
// 그룹바이 항목 순서별로 데이터 나열 확인해 볼 것 (차이점 확인 할 것)
-- group by 컬럼의 순서대로 order by 가 진행되는 것을 확인하였습니다.



















// GROUP BY t1.em_seq, t1.cs_m_id, t1.m_nm 계정을 2번쨰, 매체를 3번쨰에 둔 이유는 ?
-- m_nm과cs_m_id는 순서가 중요하지 않다고 생각하였지만, 확인해보니 속도최적화를 위해서 
-- 인덱스 순서대로 조정는게 좋을거같아, 순서를 조정하였습니다.
ㄴ 실제로 속도에 차이기 잇엇나요? 잇엇다면 얼마나 ??


ㄴ mg 테이블이 담당자가 있는건 맞는데, 실제 담당자가 등록안된 경우가 있기 때문에, 
ㄴ....실담당자를 찾는 것은 contract 테이블에서 최근 담당자를 찾아오는게 정확성에 가까울 것입니다.
ㄴ 참고 하세요. 앞으로 최신 담당자 뽑을 떄....


-- mg 테이블 에서는 실제 담당자가 등록이 되지 않는 경우도 있기때문에 
-- contract에서 최신담당자를 가져오도록 하겠습니다.

-- m_nm과 cs_m_id 속도는 차이가 거의없었습니다. 다만 이론적인 부분을 공부해보았을때, 인데스의 순서애따라서 정의하는것이 속도적인 측면에서
-- 더 좋다고 내용을 확인하여서 진행하였습니다.
