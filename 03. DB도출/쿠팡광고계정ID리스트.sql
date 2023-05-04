SELECT DISTINCT
	getcomkrname(good_type2) '매체',
	getcomkrname(enter_division1) '부문', 
	getcomkrname(enter_division2) '팀', 
	getEmkrName(reg_emp) '담당자',
	getCsKrName(a.cs_seq) '광고주 명',	
	a.cs_m_id
FROM t_customer_md a 
WHERE good_type2 = 983;
