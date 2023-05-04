<?
@session_start();

@include_once $_SERVER['DOCUMENT_ROOT']."/include/db.php";
@include_once $_SERVER['DOCUMENT_ROOT'].'/include/inc.common.php';
@include_once $_SERVER['DOCUMENT_ROOT'].'/include/inc.arrayvalues.php';
@include_once $_SERVER['DOCUMENT_ROOT'].'/function/func.sendmail.php';
@include_once $_SERVER['DOCUMENT_ROOT'].'/function/func.response.php';
db_open();



class clsWork {

//-----외근보고 인서트시작----------------------------

	public function funcIptWorkReport(){	
	
	if($_FILES['filename1']['name']){
		$file_tmp	= $_FILES['filename1']['tmp_name'];
		$file1		= $_FILES['filename1']['name'];
		$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
		$file_size	= $_FILES['filename1']['size'];

		//limit the size of the file to 200KB
		if($file_size > 204800) {
			funcMsgError('keep the filesize under (200KB 이상!!)', '', '', '', '', '' );
			exit();
		}

		$fname_arr = explode(".",$file1);
		$fname1		= $fname_arr[0];
		$extension	= $fname_arr[1];
		
		/*
		$pattern = "/(\xls|xlsx|doc|docx\b)$/i";
		  if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			  exit;
		  }
		 */

		$file1 = $fname1."-".rand().".".$extension;
		
		if(move_uploaded_file($file_tmp,$dir_path.$file1)){
			$B_RETURNVAL = 1;
		}else{
			$B_RETURNVAL = -1;
			exit();
		}
	}

	if($_FILES['filename2']['name']){
		$file_tmp	= $_FILES['filename2']['tmp_name'];
		$file2		= $_FILES['filename2']['name'];
		$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
		$file_size	= $_FILES['filename2']['size'];

		//limit the size of the file to 200KB
		if($file_size > 204800) {
			funcMsgError('keep the filesize under (200KB 이상!!)', '', '', '', '', '' );
			exit();
		}

		$fname_arr = explode(".",$file2);
		$fname2		= $fname_arr[0];
		$extension	= $fname_arr[1];

		/*
		$pattern = "/(\xls|xlsx|doc|docx\b)$/i";
		  if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			  exit;
		  }
		 */

		$file2 = $fname2."-".rand().".".$extension;
		
		if(move_uploaded_file($file_tmp,$dir_path.$file2)){
			$B_RETURNVAL = 1;
		}else{
			$B_RETURNVAL = -1;
			exit();
		}
	}

		$Qry = "INSERT INTO t_work_report
				(work_type, work_date, work_subject, work_subject_key, work_title, ir1, filename1, filename2, reg_date, reg_time, reg_emp)
				VALUES ('".$_POST['work_type']."',
						'".$_POST['work_date']."',
						'".$_POST['work_subject']."',
						'".$_POST['work_subject_key']."',
						'".$_POST['work_title']."',
						'".$_POST['ir1']."',
						'".$file1."',
						'".$file2."',
						CURDATE(),
						CURTIME(),
						'".$_SESSION['s_em_seq']."')";
		//echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
	}

//-----외근보고 인서트 끝----------------------------

//-----외근보고 리스트 시작--------------------------
	public function funcListWorkReport($nOrdPagingNum){	
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		/*
		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_nm'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}
		*/

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}
		
		if($_GET['type']=="1"){
			$where_emp = "AND reg_emp = '".$_SESSION['s_em_seq']."'";
		}
		else if($_GET['type']=="2"){
		//	$where_emp = "AND work_subject_key like '%".$_SESSION['s_em_seq']."%'";
			$where_emp = "AND CONCAT(',',work_subject_key)REGEXP '(//)?,".$_SESSION['s_em_seq']."[[:>:]]'";
		}		

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_work_report 
					WHERE del_date IS NULL
					".$where_s_item."
					".$where_d_item."
					".$where_emp."
					
					ORDER BY wr_seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

if($_GET['type']=="1"){
	while ($Row = @mysql_fetch_assoc($Rst)) {
		$S_RETURNVAL['wr_seq'][]					= $Row['wr_seq'];
			$S_RETURNVAL['work_type'][]			= $Row['work_type'];
			$S_RETURNVAL['work_date'][]			= $Row['work_date'];
			$S_RETURNVAL['work_subject'][]		= $Row['work_subject'];
			$S_RETURNVAL['work_subject_key'][]	= $Row['work_subject_key'];
			$S_RETURNVAL['work_title'][]			= $Row['work_title'];
			$S_RETURNVAL['comment_cnt'][]		= $Row['comment_cnt'];
			$S_RETURNVAL['reg_date'][]				= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]				= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]				= $Row['reg_emp'];
			$nListCnt ++;
	}
}
else if($_GET['type']=="2"){

		while ($Row = @mysql_fetch_assoc($Rst)) {

			$work_subject_key = explode(",",$Row['work_subject_key']);
			//if(in_array($_SESSION['s_em_seq'],$work_subject_key)==true){

			$S_RETURNVAL['wr_seq'][]			= $Row['wr_seq'];
			$S_RETURNVAL['work_type'][]			= $Row['work_type'];
			$S_RETURNVAL['work_date'][]			= $Row['work_date'];
			$S_RETURNVAL['work_subject'][]		= $Row['work_subject'];
			$S_RETURNVAL['work_subject_key'][]	= $Row['work_subject_key'];
			$S_RETURNVAL['work_title'][]		= $Row['work_title'];
			$S_RETURNVAL['comment_cnt'][]		= $Row['comment_cnt'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;

		//}
		}
}	
		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['wr_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();
	}
	
	//-----외근보고 리스트 끝----------------------------

	//-----외근보고 셀렉트 시작----------------------------
	public function funcSelectWorkReport(){	
		$Qry = "SELECT * FROM t_work_report WHERE wr_seq='".$_GET['wr_seq']."'";
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);

		$S_RETURNVAL['wr_seq']				= $Row['wr_seq'];
		$S_RETURNVAL['work_type']			= $Row['work_type'];
		$S_RETURNVAL['work_date']			= $Row['work_date'];
		$S_RETURNVAL['work_subject']		= $Row['work_subject'];
		$S_RETURNVAL['work_subject_key']	= $Row['work_subject_key'];
		$S_RETURNVAL['work_title']			= $Row['work_title'];
		$S_RETURNVAL['ir1']					= $Row['ir1'];
		$S_RETURNVAL['filename1']			= $Row['filename1'];
		$S_RETURNVAL['filename2']			= $Row['filename2'];
		$S_RETURNVAL['reg_date']			= $Row['reg_date'];
		$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];

		if(!$S_RETURNVAL['wr_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
		que_close();
	}
	//-----외근보고 셀렉트 끝----------------------------

	//-----외근보고 수정 시작----------------------------

	public function funcUpdWorkReport(){	
	
	if(!$_POST['old_filename1']){
	if($_FILES['filename1']['name']){
		$file_tmp	= $_FILES['filename1']['tmp_name'];
		$file1		= $_FILES['filename1']['name'];
		$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
		$file_size	= $_FILES['filename1']['size'];

		//limit the size of the file to 200KB
		if($file_size > 204800) {
			funcMsgError('keep the filesize under (200KB 이상!!)', '', '', '', '', '' );
			exit();
		}

		$fname_arr = explode(".",$file1);
		$fname1		= $fname_arr[0];
		$extension	= $fname_arr[1];
		
		/*
		$pattern = "/(\xls|xlsx|doc|docx\b)$/i";
		  if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			  exit;
		  }
		 */

		$file1 = $fname1."-".rand().".".$extension;
		
		if(move_uploaded_file($file_tmp,$dir_path.$file1)){
			$B_RETURNVAL = 1;
		}else{
			$B_RETURNVAL = -1;
			exit();
		}
	}
	}else{$file1=$_POST['old_filename1'];}
	if(!$_POST['old_filename2']){
	if($_FILES['filename2']['name']){
		$file_tmp	= $_FILES['filename2']['tmp_name'];
		$file1		= $_FILES['filename2']['name'];
		$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
		$file_size	= $_FILES['filename2']['size'];

		//limit the size of the file to 200KB
		if($file_size > 204800) {
			funcMsgError('keep the filesize under (200KB 이상!!)', '', '', '', '', '' );
			exit();
		}

		$fname_arr = explode(".",$file2);
		$fname2		= $fname_arr[0];
		$extension	= $fname_arr[1];

		/*
		$pattern = "/(\xls|xlsx|doc|docx\b)$/i";
		  if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			  exit;
		  }
		 */

		$file2 = $fname2."-".rand().".".$extension;
		
		if(move_uploaded_file($file_tmp,$dir_path.$file2)){
			$B_RETURNVAL = 1;
		}else{
			$B_RETURNVAL = -1;
			exit();
		}
	}
	}else{$file2=$_POST['old_filename2'];}

		$Qry = "UPDATE t_work_report SET 
				work_type			= '".$_POST['work_type']."', 
				work_date			= '".$_POST['work_date']."', 
				work_subject		= '".$_POST['work_subject']."', 
				work_subject_key	= '".$_POST['work_subject_key']."', 
				work_title			= '".$_POST['work_title']."', 
				ir1					= '".$_POST['ir1']."', 
				filename1			= '".$file1."', 
				filename2			= '".$file2."',
				upd_date			= CURDATE(),
				upd_time			= CURTIME(),
				upd_emp				= '".$_SESSION['s_em_seq']."'
				WHERE wr_seq		= '".$_POST['wr_seq']."'";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}

	//-----외근보고 수정 끝----------------------------


	//-----외근보고 삭제 시작----------------------------

	public function funcDelWorkReport(){		

		$Qry = "UPDATE t_work_report SET 
				del_date			= CURDATE(),
				del_time			= CURTIME(),
				del_emp				= '".$_SESSION['s_em_seq']."'
				WHERE wr_seq		= '".$_POST['wr_seq']."'";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}

	//-----외근보고 삭제 끝----------------------------

	//-----코멘트 인서트 시작----------------------------
	public function funcIptWorkReportComment(){
		$Qry = "SELECT MAX(child) AS child FROM t_work_report_comment WHERE wr_seq = '".$_POST['wr_seq']."'";
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);
		$child = $Row['child']+1;

		$Qry = "INSERT INTO t_work_report_comment (wr_seq, child, work_comment, reg_date, reg_time, reg_emp)
				VALUES ('".$_POST['wr_seq']."',
						'".$child."',
						'".$_POST['work_comment']."',
						CURDATE(),
						CURTIME(),
						'".$_SESSION['s_em_seq']."')";

		$Qry1 = "UPDATE t_work_report SET comment_cnt=comment_cnt+1 WHERE wr_seq = '".$_POST['wr_seq']."'";
		//echo $Qry;
		//echo $Qry1;
		$Rst = que($Qry);
		$Rst1 = que($Qry1);
		if($Rst && $Rst1){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
		//-----코멘트 인서트 끝----------------------------
	}

	//-----코멘트 리스트 시작----------------------------
	public function funcListWorkReportComment($wr_seq){
		
		$Qry = "SELECT * FROM t_work_report_comment WHERE wr_seq = '".$wr_seq."' AND del_date IS NULL ORDER BY child DESC";
		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;
		while($Row = @mysql_fetch_assoc($Rst)){

		$S_RETURNVAL_CO['wr_seq'][]			= $Row['wr_seq'];
		$S_RETURNVAL_CO['wrc_seq'][]			= $Row['wrc_seq'];
		$S_RETURNVAL_CO['work_comment'][]		= $Row['work_comment'];
		$S_RETURNVAL_CO['reg_date'][]			= $Row['reg_date'];
		$S_RETURNVAL_CO['reg_time'][]			= $Row['reg_time'];
		$S_RETURNVAL_CO['reg_emp'][]			= $Row['reg_emp'];
		$nListCnt ++;
		}
		$S_RETURNVAL_CO['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL_CO['wr_seq']){
			$S_RETURNVAL_CO = -1;
		}else{
			return $S_RETURNVAL_CO;
		}
		return $S_RETURNVAL_CO;
		que_close();
		
	}
	//-----코멘트 리스트 끝----------------------------


	//-----코멘트 삭제 시작----------------------------
	public function funcDelWorkReportComment(){		
		$Qry = "UPDATE t_work_report_comment SET 
				del_date			= CURDATE(),
				del_time			= CURTIME(),
				del_emp				= '".$_SESSION['s_em_seq']."'
				WHERE wrc_seq		= '".$_GET['v']."'";

		$Qry1 = "UPDATE t_work_report SET comment_cnt=comment_cnt-1 WHERE wr_seq = '".$_POST['wr_seq']."'";

		echo $Qry;

		$Rst = que($Qry);
		$Rst1 = que($Qry1);
		if($Rst&&$Rst1){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}
	//-----코멘트 수정 끝----------------------------


//인바운드보고-----------------------------------------------------------------------------------------------


	//-----인바운드보고 셀렉트 시작----------------------------
	public function funcSelectWorkInbound(){	
		$Qry = "SELECT * FROM t_work_inbound WHERE win_seq='".$_GET['win_seq']."'";
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);

		$S_RETURNVAL['win_seq']				= $Row['win_seq'];
		$S_RETURNVAL['work_type']			= $Row['work_type'];
		$S_RETURNVAL['work_date']			= $Row['work_date'];
		$S_RETURNVAL['work_subject']		= $Row['work_subject'];
		$S_RETURNVAL['work_subject_key']	= $Row['work_subject_key'];
		$S_RETURNVAL['work_title']			= $Row['work_title'];
		$S_RETURNVAL['ir1']					= $Row['ir1'];
		$S_RETURNVAL['filename1']			= $Row['filename1'];
		$S_RETURNVAL['filename2']			= $Row['filename2'];
		$S_RETURNVAL['reg_date']			= $Row['reg_date'];
		$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];

		if(!$S_RETURNVAL['wr_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
		que_close();
	}
	//-----인바운드보고 셀렉트 끝----------------------------
	




	//-----인바운드보고 리스트 시작--------------------------
	public function funcListWorkInbound($nOrdPagingNum){	
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		/*
		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_nm'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}
		*/

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		if($_GET['cs_type']){
			$where_cs_type = "AND cs_type = '".$_GET['cs_type']."'";
		}
		
		if($_GET['type']=="1"){
			$where_emp = "AND reg_emp = '".$_SESSION['s_em_seq']."'";
		}
		else if($_GET['type']=="2"){
		//	$where_emp = "AND work_subject_key like '%".$_SESSION['s_em_seq']."%'";
			$where_emp = "AND CONCAT(',',work_subject_key)REGEXP '(//)?,".$_SESSION['s_em_seq']."[[:>:]]'";
		}		

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_work_inbound 
					WHERE del_date IS NULL
					".$where_cs_type."
					".$where_s_item."
					".$where_d_item."
					".$where_emp."					
					ORDER BY win_seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

if($_GET['type']=="1"){
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['win_seq'][]			= $Row['win_seq'];
			$S_RETURNVAL['work_subject'][]		= $Row['work_subject'];
			$S_RETURNVAL['work_subject_key'][]	= $Row['work_subject_key'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['cs_type'][]			= $Row['cs_type'];
			$S_RETURNVAL['homepage'][]			= $Row['homepage'];
			$S_RETURNVAL['mg_nm'][]				= $Row['mg_nm'];
			$S_RETURNVAL['mg_email'][]			= $Row['mg_email'];
			$S_RETURNVAL['mg_tel'][]			= $Row['mg_tel'];
			$S_RETURNVAL['m_nm1'][]				= $Row['m_nm1'];
			$S_RETURNVAL['m_nm2'][]				= $Row['m_nm2'];
			$S_RETURNVAL['comment1'][]			= $Row['comment1'];
			$S_RETURNVAL['work_date1'][]		= $Row['work_date1'];
			$S_RETURNVAL['comment2'][]			= $Row['comment2'];
			$S_RETURNVAL['work_date2'][]		= $Row['work_date2'];
			$S_RETURNVAL['ad_yn'][]				= $Row['ad_yn'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}
}
else if($_GET['type']=="2"){
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$work_subject_key = explode(",",$Row['work_subject_key']);
			//if(in_array($_SESSION['s_em_seq'],$work_subject_key)==true){

			$S_RETURNVAL['win_seq'][]			= $Row['win_seq'];
			$S_RETURNVAL['work_subject'][]		= $Row['work_subject'];
			$S_RETURNVAL['work_subject_key'][]	= $Row['work_subject_key'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['cs_type'][]			= $Row['cs_type'];
			$S_RETURNVAL['homepage'][]			= $Row['homepage'];
			$S_RETURNVAL['mg_nm'][]				= $Row['mg_nm'];
			$S_RETURNVAL['mg_email'][]			= $Row['mg_email'];
			$S_RETURNVAL['mg_tel'][]			= $Row['mg_tel'];
			$S_RETURNVAL['m_nm1'][]				= $Row['m_nm1'];
			$S_RETURNVAL['m_nm2'][]				= $Row['m_nm2'];
			$S_RETURNVAL['comment1'][]			= $Row['comment1'];
			$S_RETURNVAL['work_date1'][]		= $Row['work_date1'];
			$S_RETURNVAL['comment2'][]			= $Row['comment2'];
			$S_RETURNVAL['work_date2'][]		= $Row['work_date2'];
			$S_RETURNVAL['ad_yn'][]				= $Row['ad_yn'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;

		//}
		}
}	
		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['win_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();
	}
	
	//-----인바운드보고 리스트 끝----------------------------



	//-----인바운드보고 인서트시작----------------------------

	public function funcIptWorkInbound(){		

		echo "asdfasdf";

		$Qry = "INSERT INTO t_work_inbound
				(work_subject, work_subject_key, cs_nm, cs_type, homepage, mg_nm, mg_email, mg_tel, m_nm1, m_nm2, comment1, work_date1, comment2, work_date2, reg_date, reg_time, reg_emp)
				VALUES ('".$_POST['work_subject']."',
						'".$_POST['work_subject_key']."',
						'".$_POST['cs_nm']."',
						'".$_POST['cs_type']."',
						'".$_POST['homepage']."',
						'".$_POST['mg_nm']."',
						'".$_POST['mg_email']."',
						'".$_POST['mg_tel']."',
						'".$_POST['m_nm1']."',
						'".$_POST['m_nm2']."',
						'".$_POST['comment1']."',
						'".$_POST['work_date1']."',
						'".$_POST['comment2']."',
						'".$_POST['work_date2']."',
						CURDATE(),
						CURTIME(),
						'".$_SESSION['s_em_seq']."')";
		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
	}

	//-----인바운드보고 인서트 끝----------------------------



	//-----외근보고 수정 시작----------------------------

	public function funcUpdWorkInbound(){	
	
		$Qry = "UPDATE t_work_inbound SET 
				cs_nm				= '".$_POST['cs_nm']."', 
				cs_type				= '".$_POST['cs_type']."', 
				work_subject		= '".$_POST['work_subject']."', 
				work_subject_key	= '".$_POST['work_subject_key']."', 
				homepage			= '".$_POST['homepage']."', 
				mg_nm				= '".$_POST['mg_nm']."', 
				mg_email			= '".$_POST['mg_email']."', 
				mg_tel				= '".$_POST['mg_tel']."', 
				m_nm1				= '".$_POST['m_nm1']."', 
				m_nm2				= '".$_POST['m_nm2']."', 
				comment1			= '".$_POST['comment1']."', 
				work_date1			= '".$_POST['work_date1']."', 
				comment2			= '".$_POST['comment2']."', 
				work_date2			= '".$_POST['work_date2']."', 
				ad_yn				= '".$_POST['ad_yn']."', 
				upd_date			= CURDATE(),
				upd_time			= CURTIME(),
				upd_emp				= '".$_SESSION['s_em_seq']."'
				WHERE win_seq		= '".$_POST['win_seq']."'";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}

	//-----외근보고 수정 끝----------------------------


	//-----업무지원 등록 시작----------------------------
	public function funcIptWorkSupport(){	
		if($_FILES['filename1']['name']){
			$file_tmp	= $_FILES['filename1']['tmp_name'];
			$file1		= trim($_FILES['filename1']['name']);
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
			$file_size1	= round($_FILES['filename1']['size']/1024);

			//limit the size of the file to 10MB
			if($file_size1 > 10500) {//204800
				funcMsgError('keep the filesize under (10MB 이상1!!)', '', '', '', '', '' );
				exit();
			}
			
			$fname_arr = explode(".",$file1);
			$fname1		= $fname_arr[0];
			$extension	= $fname_arr[1];

			//$pattern = "/(\zip|ZIP|jpg|JPG|gif|GIF|png|PNG\b)$/i";
			//if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			//	  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			//	  exit;
			//}

			$file1 = $fname1."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file1)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}		

		if($_GET['type']=="1"){
			$lastdate1 = date("Y-m-01", mktime(0,0,0,date("m")-1,1,date("Y")));
			$lastdate2 = date("Y-m-31", mktime(0,0,0,date("m")-1,1,date("Y")));

			$Qry2 = "SELECT SUM(pay_price) AS pay_price FROM t_contract 
					 WHERE cs_seq = '".$_POST['cs_seq']."' 
					 AND del_date IS NULL 
					 AND agree_state='3' 
					 AND pay_date BETWEEN '".$lastdate1."' AND '".$lastdate2."'";
			//echo $Qry2;
			$Rst2 = que($Qry2);
			$Row2 = @mysql_fetch_assoc($Rst2);

			$Qry = "INSERT INTO t_support_virul ( support_type,
						division1,
						division2,
						em_seq,
						em_tel,
						em_email,
						cs_seq,
						cs_nm,
						cs_tel,
						ceo_nm,
						cs_email,
						cs_addr,
						cs_url,
						cs_num,
						sales,
						request_type,
						title,
						must_item,
						landing_url,
						keyword,
						contents,
						file1,
						reg_date,
						reg_time,
						reg_emp,
						browser) VALUES (
						'".$_POST['type']."',
						'".$_POST['division1']."',
						'".$_POST['division2']."',
						'".$_POST['em_seq']."',
						'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						'".$_POST['em_email']."',
						'".$_POST['cs_seq']."',
						'".$_POST['cs_nm']."',
						'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						'".$_POST['ceo_nm']."',
						'".$_POST['cs_email']."',
						'".$_POST['cs_addr']."',
						'".$_POST['cs_url']."',
						'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						'".$Row2['pay_price']."',
						'".$_POST['request_type']."',
						'".$_POST['title']."',
						'".$_POST['must_item']."',
						'".$_POST['landing_url']."',
						'".$_POST['keyword']."',
						'".$_POST['contents']."',
						'".$file1."',
						CURDATE(),
						CURTIME(),
						'".$_POST['em_seq']."',
						'".$_SERVER['HTTP_USER_AGENT']."')";

			$Qry1 = "INSERT INTO t_support_state (support_seq, support_type, reg_date, reg_time, reg_emp) VALUES 
					(last_insert_id(), '".$_GET['type']."', CURDATE(), CURTIME(), '".$_POST['em_seq']."');";

			//$to = join(",",$GLOBALS['support_email_virul']);
			$to = join(",",array_values($GLOBALS['support_mem_virul']));

			$to			= $to;
			$fromnm		= funSelectEmpNm($_POST['em_seq']);
			$from		= $_POST['em_email'];
			$subject	= "바이럴신청이 등록되었습니다.";
			$contents	= "신청유형 : 바이럴신청 <br> 신청자 : ".$fromnm."<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=1'>바로가기</a>";

			//echo "------<br>".$to."<br>";
			//echo $from."<br>";
			//echo $subject."<br>";
			//echo $contents."------<br><br><br>";

		}else if($_GET['type']=="2"){
			$lastdate1 = date("Y-m-01", mktime(0,0,0,date("m")-1,1,date("Y")));
			$lastdate2 = date("Y-m-31", mktime(0,0,0,date("m")-1,1,date("Y")));

			$Qry2 = "SELECT SUM(pay_price) AS pay_price FROM t_contract 
					 WHERE cs_seq = '".$_POST['cs_seq']."' 
					 AND del_date IS NULL 
					 AND agree_state='3' 
					 AND pay_date BETWEEN '".$lastdate1."' AND '".$lastdate2."'";
			//echo $Qry2;
			$Rst2 = que($Qry2);
			$Row2 = @mysql_fetch_assoc($Rst2);

			$Qry = "INSERT INTO t_support_google ( cs_seq, support_type,
						division1,
						division2,
						em_seq,
						em_tel,
						em_email,
						cs_nm,
						cs_tel,
						ceo_nm,
						cs_email,
						cs_addr,
						cs_url,
						cs_num,
						sales,
						keyword,
						tad,
						title_url,
						landing_url,
						day_budget,
						ad_schedule,
						local_targeting,
						cpc_limit,
						reg_date,
						reg_time,
						reg_emp ) VALUES (
						'".$_POST['cs_seq']."',
						'".$_POST['type']."',
						'".$_POST['division1']."',
						'".$_POST['division2']."',
						'".$_POST['em_seq']."',
						'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						'".$_POST['em_email']."',
						'".$_POST['cs_nm']."',
						'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						'".$_POST['ceo_nm']."',
						'".$_POST['cs_email']."',
						'".$_POST['cs_addr']."',
						'".$_POST['cs_url']."',
						'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						'".$Row2['pay_price']."',
						'".$_POST['keyword']."',
						'".$_POST['tad']."',
						'".$_POST['title_url']."',
						'".$_POST['landing_url']."',
						'".$_POST['day_budget']."',
						'".$_POST['ad_schedule']."',
						'".$_POST['local_targeting']."',
						'".$_POST['cpc_limit']."',
						CURDATE(),
						CURTIME(),
						'".$_POST['em_seq']."')";
			$Qry1 = "INSERT INTO t_support_state (support_seq, support_type, reg_date, reg_time, reg_emp) VALUES 
					(last_insert_id(), '".$_GET['type']."', CURDATE(), CURTIME(), '".$_POST['em_seq']."')";

			//$to = join(",",$GLOBALS['support_email_google']);
			$to = join(",",array_values($GLOBALS['support_mem_google']));
			//echo $to;

			//exit;


			$to			= $to;
			$fromnm		= funSelectEmpNm($_POST['em_seq']);
			$from		= $_POST['em_email'];
			$subject	= "구글SA계정신청이 등록되었습니다.";
			$contents	= "신청유형 : 구글SA계정신청 <br> 신청자 : ".$fromnm."<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=2'>바로가기</a>";

		}else if($_GET['type']=="3"){
			$Qry = "INSERT INTO t_support_proposal ( support_type,
						division1,
						division2,
						em_seq,
						em_tel,
						em_email,
						cs_nm,
						cs_tel,
						ceo_nm,
						cs_email,
						cs_addr,
						cs_url,
						cs_num,
						sales,
						ad_budget,
						ad_m_nm,
						ad_pay,
						rival_url1,
						rival_url2,
						rival_url3,
						main_keyword1,
						powerlink1_1,					
						powerlink1_2,
						powerlink1_3,
						powerlink1_4,
						powerlink1_5,
						main_keyword2,
						powerlink2_1,					
						powerlink2_2,
						powerlink2_3,
						powerlink2_4,
						powerlink2_5,
						main_keyword3,
						powerlink3_1,					
						powerlink3_2,
						powerlink3_3,
						powerlink3_4,
						powerlink3_5,
						reg_date,
						reg_time,
						reg_emp ) VALUES (
						'".$_POST['type']."',
						'".$_POST['division1']."',
						'".$_POST['division2']."',
						'".$_POST['em_seq']."',
						'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						'".$_POST['em_email']."',
						'".$_POST['cs_nm']."',
						'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						'".$_POST['ceo_nm']."',
						'".$_POST['cs_email']."',
						'".$_POST['cs_addr']."',
						'".$_POST['cs_url']."',
						'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						'".$_POST['sales']."',
						'".$_POST['ad_budget']."',
						'".$_POST['ad_m_nm']."',
						'".$_POST['ad_pay']."',
						'".$_POST['rival_url1']."',
						'".$_POST['rival_url2']."',
						'".$_POST['rival_url3']."',
						'".$_POST['main_keyword1']."',
						'".$_POST['powerlink1_1']."',
						'".$_POST['powerlink1_2']."',
						'".$_POST['powerlink1_3']."',
						'".$_POST['powerlink1_4']."',
						'".$_POST['powerlink1_5']."',
						'".$_POST['main_keyword2']."',
						'".$_POST['powerlink2_1']."',
						'".$_POST['powerlink2_2']."',
						'".$_POST['powerlink2_3']."',
						'".$_POST['powerlink2_4']."',
						'".$_POST['powerlink2_5']."',
						'".$_POST['main_keyword3']."',
						'".$_POST['powerlink3_1']."',
						'".$_POST['powerlink3_2']."',
						'".$_POST['powerlink3_3']."',
						'".$_POST['powerlink3_4']."',
						'".$_POST['powerlink3_5']."',
						CURDATE(),
						CURTIME(),
						'".$_POST['em_seq']."')";
			
			$Qry1 = "INSERT INTO t_support_state (support_seq, support_type,reg_date, reg_time, reg_emp) VALUES 
					(last_insert_id(), '".$_POST['type']."',  CURDATE(), CURTIME(), '".$_POST['em_seq']."')";

			//$to = join(",",$GLOBALS['support_email_proposal']);
			$to = join(",",array_values($GLOBALS['support_mem_proposal']));
			
			$to			= $to;
			$fromnm		= funSelectEmpNm($_POST['em_seq']);
			$from		= $_POST['em_email'];
			$subject	= "제안서신청이 등록되었습니다.";
			$contents	= "신청유형 : 제안서신청 <br> 신청자 : ".$fromnm."<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=3'>바로가기</a>";
		}	
			
			
			funSendMail($to,$fromnm,$from,$subject,$contents);

			echo funSendMail;
			//echo $Qry;
			
			//exit();
			
			$Rst = que($Qry);
			$Rst1 = que($Qry1);

			if($Rst){
					$N_RETURNVAL = "1";
			}else{
				$N_RETURNVAL = "-1";
			}
			echo $Rst;

			return $N_RETURNVAL;
			que_close();
		
	}
	//-----업무지원 등록 끝------------------------------


	//-----업무지원 리스트 시작------------------------------
	public function funcListWorkSupport($nOrdPagingNum){
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;
		
		if($_GET['type']=="1"){
			$admin_mem = array_keys($GLOBALS['support_mem_virul']);
		}else if($_GET['type']=="2"){
			$admin_mem = array_keys($GLOBALS['support_mem_google']);
		}else if($_GET['type']=="3"){
			$admin_mem = array_keys($GLOBALS['support_mem_proposal']);
		}

		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_nm'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		if($_GET['state']){
			$where_state = "AND state = '".$_GET['state']."'";
		}

		if($_GET['type']=="1"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_virul 
					WHERE del_date IS NULL 
					$where_division1
					$where_division2
					$where_em_seq
					$where_d_item
					$where_s_item
					$where_state
					ORDER BY seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		}else if($_GET['type']=="2"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_google 
					WHERE del_date IS NULL 
					$where_division1
					$where_division2
					$where_em_seq
					$where_d_item
					$where_s_item
					$where_state
					ORDER BY seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		}else if($_GET['type']=="3"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_proposal 
					WHERE del_date IS NULL 
					$where_division1
					$where_division2
					$where_em_seq
					$where_d_item
					$where_s_item
					$where_state
					ORDER BY seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		}
		
		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		if($_GET['type']=="1"){
			while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['support_type'][]		= $Row['support_type'];
			$S_RETURNVAL['em_seq'][]			= $Row['em_seq'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['state'][]				= $Row['state'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$S_RETURNVAL['com_date'][]			= $Row['com_date'];
			$S_RETURNVAL['com_emp'][]			= $Row['com_emp'];
			$S_RETURNVAL['upd_date'][]			= $Row['upd_date'];
			$S_RETURNVAL['upd_emp'][]			= $Row['upd_emp'];

			$nListCnt ++;
			}
		}else if($_GET['type']=="2"){
			while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['support_type'][]		= $Row['support_type'];
			$S_RETURNVAL['em_seq'][]			= $Row['em_seq'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['state'][]				= $Row['state'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$S_RETURNVAL['com_date'][]			= $Row['com_date'];
			$S_RETURNVAL['com_emp'][]			= $Row['com_emp'];
			$nListCnt ++;
			}
		}else if($_GET['type']=="3"){
			while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['support_type'][]		= $Row['support_type'];
			$S_RETURNVAL['em_seq'][]			= $Row['em_seq'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['state'][]				= $Row['state'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$S_RETURNVAL['com_date'][]			= $Row['com_date'];
			$S_RETURNVAL['com_emp'][]			= $Row['com_emp'];
			$S_RETURNVAL['file1'][]				= $Row['file1'];
			$S_RETURNVAL['file2'][]				= $Row['file2'];
			$nListCnt ++;
			}
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
		que_close();

	}	
	//-----업무지원 리스트 끝------------------------------



	//-----업무지원 셀렉트 시작------------------------------
	public function funcSelectWorkSupport(){
		if($_GET['type']=="1"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_virul WHERE seq='".$_GET['seq']."'";
		}else if($_GET['type']=="2"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_google WHERE seq='".$_GET['seq']."'";
		}else if($_GET['type']=="3"){
			$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_support_proposal WHERE seq='".$_GET['seq']."'";
		}
		
		//echo $Qry;
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);
		
		if($_GET['type']=="1"){
			$S_RETURNVAL['seq']					= $Row['seq'];
			$S_RETURNVAL['support_type']		= $Row['support_type'];
			$S_RETURNVAL['division1']			= $Row['division1'];
			$S_RETURNVAL['division2']			= $Row['division2'];
			$S_RETURNVAL['em_seq']				= $Row['em_seq'];
			$S_RETURNVAL['em_tel']				= $Row['em_tel'];
			$S_RETURNVAL['em_email']			= $Row['em_email'];
			$S_RETURNVAL['cs_seq']				= $Row['cs_seq'];
			$S_RETURNVAL['cs_nm']				= $Row['cs_nm'];
			$S_RETURNVAL['cs_tel']				= $Row['cs_tel'];
			$S_RETURNVAL['ceo_nm']				= $Row['ceo_nm'];
			$S_RETURNVAL['cs_email']			= $Row['cs_email'];
			$S_RETURNVAL['cs_addr']				= $Row['cs_addr'];
			$S_RETURNVAL['cs_url']				= $Row['cs_url'];
			$S_RETURNVAL['cs_num']				= $Row['cs_num'];
			$S_RETURNVAL['sales']				= $Row['sales'];
			$S_RETURNVAL['request_type']		= $Row['request_type'];
			$S_RETURNVAL['state']				= $Row['state'];

			$S_RETURNVAL['title']				= $Row['title'];
			$S_RETURNVAL['must_item']			= $Row['must_item'];
			$S_RETURNVAL['landing_url']			= $Row['landing_url'];
			$S_RETURNVAL['keyword']				= $Row['keyword'];
			$S_RETURNVAL['contents']			= $Row['contents'];
			$S_RETURNVAL['file1']				= $Row['file1'];

			$S_RETURNVAL['reg_date']			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];
			$S_RETURNVAL['com_date']			= $Row['com_date'];
			$S_RETURNVAL['com_emp']				= $Row['com_emp'];
		}else if($_GET['type']=="2"){
			$S_RETURNVAL['seq']					= $Row['seq'];
			$S_RETURNVAL['cs_seq']				= $Row['cs_seq'];
			$S_RETURNVAL['support_type']		= $Row['support_type'];
			$S_RETURNVAL['division1']			= $Row['division1'];
			$S_RETURNVAL['division2']			= $Row['division2'];
			$S_RETURNVAL['em_seq']				= $Row['em_seq'];
			$S_RETURNVAL['em_tel']				= $Row['em_tel'];
			$S_RETURNVAL['em_email']			= $Row['em_email'];
			$S_RETURNVAL['cs_nm']				= $Row['cs_nm'];
			$S_RETURNVAL['cs_tel']				= $Row['cs_tel'];
			$S_RETURNVAL['ceo_nm']				= $Row['ceo_nm'];
			$S_RETURNVAL['cs_email']			= $Row['cs_email'];
			$S_RETURNVAL['cs_addr']				= $Row['cs_addr'];
			$S_RETURNVAL['cs_url']				= $Row['cs_url'];
			$S_RETURNVAL['cs_num']				= $Row['cs_num'];
			$S_RETURNVAL['sales']				= $Row['sales'];
			$S_RETURNVAL['state']				= $Row['state'];

			$S_RETURNVAL['keyword']				= $Row['keyword'];
			$S_RETURNVAL['tad']					= $Row['tad'];
			$S_RETURNVAL['title_url']			= $Row['title_url'];
			$S_RETURNVAL['landing_url']			= $Row['landing_url'];
			$S_RETURNVAL['day_budget']			= $Row['day_budget'];
			$S_RETURNVAL['ad_schedule']			= $Row['ad_schedule'];
			$S_RETURNVAL['local_targeting']		= $Row['local_targeting'];
			$S_RETURNVAL['cpc_limit']			= $Row['cpc_limit'];

			$S_RETURNVAL['reg_date']			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];
			$S_RETURNVAL['com_date']			= $Row['com_date'];
			$S_RETURNVAL['com_emp']				= $Row['com_emp'];
		}else if($_GET['type']=="3"){
			$S_RETURNVAL['seq']					= $Row['seq'];
			$S_RETURNVAL['support_type']		= $Row['support_type'];
			$S_RETURNVAL['division1']			= $Row['division1'];
			$S_RETURNVAL['division2']			= $Row['division2'];
			$S_RETURNVAL['em_seq']				= $Row['em_seq'];
			$S_RETURNVAL['em_tel']				= $Row['em_tel'];
			$S_RETURNVAL['em_email']			= $Row['em_email'];
			$S_RETURNVAL['cs_nm']				= $Row['cs_nm'];
			$S_RETURNVAL['cs_tel']				= $Row['cs_tel'];
			$S_RETURNVAL['ceo_nm']				= $Row['ceo_nm'];
			$S_RETURNVAL['cs_email']			= $Row['cs_email'];
			$S_RETURNVAL['cs_addr']				= $Row['cs_addr'];
			$S_RETURNVAL['cs_url']				= $Row['cs_url'];
			$S_RETURNVAL['cs_num']				= $Row['cs_num'];
			$S_RETURNVAL['sales']				= $Row['sales'];
			$S_RETURNVAL['state']				= $Row['state'];

			$S_RETURNVAL['ad_budget']			= $Row['ad_budget'];
			$S_RETURNVAL['ad_m_nm']				= $Row['ad_m_nm'];
			$S_RETURNVAL['ad_pay']				= $Row['ad_pay'];
			$S_RETURNVAL['rival_url1']			= $Row['rival_url1'];
			$S_RETURNVAL['rival_url2']			= $Row['rival_url2'];
			$S_RETURNVAL['rival_url3']			= $Row['rival_url3'];
			$S_RETURNVAL['main_keyword1']		= $Row['main_keyword1'];
			$S_RETURNVAL['powerlink1_1']		= $Row['powerlink1_1'];
			$S_RETURNVAL['powerlink1_2']		= $Row['powerlink1_2'];
			$S_RETURNVAL['powerlink1_3']		= $Row['powerlink1_3'];
			$S_RETURNVAL['powerlink1_4']		= $Row['powerlink1_4'];
			$S_RETURNVAL['powerlink1_5']		= $Row['powerlink1_5'];
			$S_RETURNVAL['powerlink1_6']		= $Row['powerlink1_6'];

			$S_RETURNVAL['main_keyword2']		= $Row['main_keyword2'];
			$S_RETURNVAL['powerlink2_1']		= $Row['powerlink2_1'];
			$S_RETURNVAL['powerlink2_2']		= $Row['powerlink2_2'];
			$S_RETURNVAL['powerlink2_3']		= $Row['powerlink2_3'];
			$S_RETURNVAL['powerlink2_4']		= $Row['powerlink2_4'];
			$S_RETURNVAL['powerlink2_5']		= $Row['powerlink2_5'];

			$S_RETURNVAL['main_keyword3']		= $Row['main_keyword3'];
			$S_RETURNVAL['powerlink3_1']		= $Row['powerlink3_1'];
			$S_RETURNVAL['powerlink3_2']		= $Row['powerlink3_2'];
			$S_RETURNVAL['powerlink3_3']		= $Row['powerlink3_3'];
			$S_RETURNVAL['powerlink3_4']		= $Row['powerlink3_4'];
			$S_RETURNVAL['powerlink3_5']		= $Row['powerlink3_5'];

			$S_RETURNVAL['file1']				= $Row['file1'];
			$S_RETURNVAL['file2']				= $Row['file2'];

			$S_RETURNVAL['reg_date']			= $Row['reg_date'];
			$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];
			$S_RETURNVAL['com_date']			= $Row['com_date'];
			$S_RETURNVAL['com_emp']				= $Row['com_emp'];
		}

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
		que_close();
	}
	//-----업무지원 셀렉트 끝--------------------------------


	//-----업무지원 제안서 인서트 시작------------------------------
	public function funcIptFileSupport(){
		if($_FILES['filename1']['name']){
			$file = $_FILES['filename1'];
		}else if($_FILES['filename2']['name']){
			$file = $_FILES['filename2'];
		}

		if($file){
			$file_tmp	= $file['tmp_name'];
			$file		= $file['name'];
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
			$file_size	= $file['size'];

			
			//if(strpos($file,".")){
			//	funcMsgError('파일명에 특수문자(.)는 포함될 수 없습니다. 수정 후 다시 등록하십시오.', '', '', '', '', '' );
			//	exit();				
			//}

			//limit the size of the file to 200KB
			if($file_size > 204800) {
				funcMsgError('keep the filesize under (200KB 이상!!)', '', '', '', '', '' );
				exit();
			}

			$fname_arr = explode(".",$file);
			$fname		= $fname_arr[0];
			$extension	= $fname_arr[1];
			
			/*
			$pattern = "/(\xls|xlsx|pdf|PDF|ppt|PPT|pptx|PPTX|doc|docx\b)$/i";
			  if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
				  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
				  exit;
			}
			*/

			$file = $fname."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}
		echo "Asdf";

		if($_GET['filetype']=="1"){$file = "SET file1='".$file."'";}
		else if($_GET['filetype']=="2"){$file = "SET file2='".$file."'";}

		$Qry = "UPDATE t_support_proposal ".$file." WHERE seq='".$_POST['seq']."'";

		//echo $Qry1.$Qry2;

		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}
	//-----업무지원 제안서 인서트 끝--------------------------------

	//-----업무지원 진행상태 인서트 시작------------------------------
	public function funcIptStateSupport(){
		$Qry = "INSERT INTO t_support_state (support_seq, support_type, state, reg_date, reg_time, reg_emp) VALUES
				('".$_POST['seq']."', '".$_POST['type']."', '".$_POST['state']."', CURDATE(), CURTIME(), '".$_SESSION['s_em_seq']."')";
		
		if($_POST['state']=="4"){
			$Qry1.=",com_date=CURDATE(),com_time=CURTIME(),com_emp='".$_SESSION['s_em_seq']."'";
		}

		if($_GET['type']=="1"){
			$Qry1 = "UPDATE t_support_virul SET state='".$_POST['state']."' $Qry1 WHERE seq='".$_POST['seq']."'";
			
			$to = $_POST['em_email'];

			$fromnm		= "바이럴서비스담당자";
			$from		= join(",",array_values($GLOBALS['support_mem_virul']));
			$subject	= "신청한 바이럴서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.";
			$contents	= "신청한 바이럴서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.<br>바이럴서비스담당자에 문의하세요.<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=1'>바로가기</a>";				

		}else if($_GET['type']=="2"){
			$Qry1 = "UPDATE t_support_google SET state = '".$_POST['state']."' $Qry1 WHERE seq='".$_POST['seq']."'";

			$to = $_POST['em_email'];

			$fromnm		= "구글SA계정서비스담당자";
			$from		= join(",",array_values($GLOBALS['support_mem_google']));
			$subject	= "신청한 구글SA계정서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.";
			$contents	= "신청한 구글SA계정서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.<br>구글SA계정서비스담당자에 문의하세요.<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=2'>바로가기</a>";		

		}else if($_GET['type']=="3"){
			$Qry1 = "UPDATE t_support_proposal SET state = '".$_POST['state']."' $Qry1 WHERE seq='".$_POST['seq']."'";

			$to = $_POST['em_email'];

			$fromnm		= "제안서서비스담당자";
			$from		= join(",",array_values($GLOBALS['support_mem_proposal']));
			$subject	= "신청한 제안서서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.";
			$contents	= "신청한 제안서서비스가 ".$GLOBALS['state'][$_POST['state']]." 중 입니다.<br>제안서서비스담당자에 문의하세요.<br><a href='http://rms.hmcorp.co.kr/work/?nc=06&type=3'>바로가기</a>";		

		}
		
		echo $Qry1;
		$Rst = que($Qry);
		$Rst1 = que($Qry1);

		echo "--------------<br>".$GLOBALS['state'][$_POST['state']]."<br>";
		echo $to."<br>";
		echo $from."----------------";

		
		
		funSendMail($to,$fromnm,$from,$subject,$contents);
//exit();
		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();
	}
	//-----업무지원 진행상태 인서트 끝--------------------------------


	//-----업무지원 진행상태 LIST 시작------------------------------
	public function funcListStateSupport(){
		$Qry = "SELECT * FROM t_support_state WHERE support_seq='".$_GET['seq']."' AND  support_type='".$_GET['type']."' ORDER BY seq DESC";
		//echo $Qry; 
		$Rst = que($Qry);
		$nListCnt = 0 ;
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL_STATE['seq'][]				= $Row['seq'];
			$S_RETURNVAL_STATE['support_type'][]	= $Row['support_type'];
			$S_RETURNVAL_STATE['state'][]			= $Row['state'];
			$S_RETURNVAL_STATE['reg_date'][]		= $Row['reg_date'];
			$S_RETURNVAL_STATE['reg_time'][]		= $Row['reg_time'];
			$S_RETURNVAL_STATE['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}

		$S_RETURNVAL_STATE['nListCnt'] = $nListCnt;

		if(!$S_RETURNVAL_STATE['seq']){
			$S_RETURNVAL_STATE = -1;
		}else{
			return $S_RETURNVAL_STATE;
		}
		return $S_RETURNVAL_STATE;
		que_close();
	}
	//-----업무지원 진행상태 LIST 끝--------------------------------

	//-----업무지원 수정상태 LIST 시작------------------------------
	public function funcListUpdateStateSupport(){
		$Qry = "SELECT * FROM t_support_update_state WHERE support_seq='".$_GET['seq']."' AND  support_type='".$_GET['type']."' ORDER BY seq DESC";
		//echo $Qry; 
		$Rst = que($Qry);
		$nListCnt = 0 ;
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL_UPDATE['seq'][]				= $Row['seq'];
			$S_RETURNVAL_UPDATE['support_type'][]	= $Row['support_type'];
			$S_RETURNVAL_UPDATE['reg_date'][]		= $Row['reg_date'];
			$S_RETURNVAL_UPDATE['reg_time'][]		= $Row['reg_time'];
			$S_RETURNVAL_UPDATE['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}

		$S_RETURNVAL_UPDATE['nListCnt'] = $nListCnt;

		if(!$S_RETURNVAL_UPDATE['seq']){
			$S_RETURNVAL_UPDATE = -1;
		}else{
			return $S_RETURNVAL_UPDATE;
		}
		return $S_RETURNVAL_UPDATE;
		que_close();
	}
	//-----업무지원 수정상태 LIST 끝--------------------------------


	//-----업무지원 수정 시작--------------------------------
	public function funcUpdWorkSupport(){
		if($_FILES['filename1']['name']){
			$file_tmp	= $_FILES['filename1']['tmp_name'];
			$file1		= trim($_FILES['filename1']['name']);
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/work_file/";
			$file_size1	= round($_FILES['filename1']['size']/1024);

			//limit the size of the file to 10MB
			if($file_size1 > 10500) {//204800
				funcMsgError('keep the filesize under (10MB 이상1!!)', '', '', '', '', '' );
				exit();
			}

			$fname_arr = explode(".",$file1);
			$fname1		= $fname_arr[0];
			$extension	= $fname_arr[1];

			//$pattern = "/(\zip|ZIP|jpg|JPG|gif|GIF|png|PNG\b)$/i";
			//if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			//	  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			//	  exit;
			//}

			$file1 = $fname1."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file1)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}

		

		if($file1){$Qry .= "file1		=	'".$file1."',";}

		if($_GET['type']=="1"){
			$Qry = "UPDATE t_support_virul SET 
						em_tel		=	'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						em_email	=	'".$_POST['em_email']."',	
						cs_nm		=	'".$_POST['cs_nm']."',
						cs_tel		=	'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						ceo_nm		=	'".$_POST['ceo_nm']."',	
						cs_email	=	'".$_POST['cs_email']."',
						cs_addr		=	'".$_POST['cs_addr']."',
						cs_url		=	'".$_POST['cs_url']."',
						cs_num		=	'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						sales		=	'".$_POST['sales']."',
						request_type=	'".$_POST['request_type']."',
						title		=	'".$_POST['title']."',
						must_item	=	'".$_POST['must_item']."',
						landing_url	=	'".$_POST['landing_url']."',
						keyword		=	'".$_POST['keyword']."',
						contents	=	'".$_POST['contents']."',
						$Qry
						upd_date	=	CURDATE(),
						upd_time	=	CURTIME(),
						upd_emp		=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}else if($_GET['type']=="2"){
			$Qry = "UPDATE t_support_google SET 
						cs_seq			=   '".$_POST['cs_seq']."',
						em_tel			=	'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						em_email		=	'".$_POST['em_email']."',	
						cs_nm			=	'".$_POST['cs_nm']."',	
						cs_tel			=	'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						ceo_nm			=	'".$_POST['ceo_nm']."',
						cs_email		=	'".$_POST['cs_email']."',
						cs_addr			=	'".$_POST['cs_addr']."',
						cs_url			=	'".$_POST['cs_url']."',
						cs_num			=	'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						sales			=	'".$_POST['sales']."',
						keyword			=	'".$_POST['keyword']."',
						tad				=	'".$_POST['tad']."',
						title_url		=	'".$_POST['title_url']."',
						landing_url		=	'".$_POST['landing_url']."',
						day_budget		=	'".$_POST['day_budget']."',
						ad_schedule		=	'".$_POST['day_budget']."',
						local_targeting	=	'".$_POST['local_targeting']."',
						cpc_limit		=	'".$_POST['cpc_limit']."', 	
						upd_date		=	CURDATE(),
						upd_time		=	CURTIME(),
						upd_emp			=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}else if($_GET['type']=="3"){
			$Qry = "UPDATE t_support_proposal SET 
						em_tel			=	'".$_POST['em_tel1']."-".$_POST['em_tel2']."-".$_POST['em_tel3']."',
						em_email		=	'".$_POST['em_email']."',
						cs_nm			=	'".$_POST['cs_nm']."',
						cs_tel			=	'".$_POST['cs_tel1']."-".$_POST['cs_tel2']."-".$_POST['cs_tel3']."',
						ceo_nm			=	'".$_POST['ceo_nm']."',
						cs_email		=	'".$_POST['cs_email']."',
						cs_addr			=	'".$_POST['cs_addr']."',
						cs_url			=	'".$_POST['cs_url']."',
						cs_num			=	'".$_POST['cs_num1']."-".$_POST['cs_num2']."-".$_POST['cs_num3']."',
						sales			=	'".$_POST['sales']."',
						ad_budget		=	'".$_POST['ad_budget']."',
						ad_m_nm			=	'".$_POST['ad_m_nm']."',
						ad_pay			=	'".$_POST['ad_pay']."',
						rival_url1		=	'".$_POST['rival_url1']."',
						rival_url2		=	'".$_POST['rival_url2']."',
						rival_url3		=	'".$_POST['rival_url3']."',
						main_keyword1	=	'".$_POST['main_keyword1']."',
						powerlink1_1	=	'".$_POST['powerlink1_1']."',					
						powerlink1_2	=	'".$_POST['powerlink1_2']."',
						powerlink1_3	=	'".$_POST['powerlink1_3']."',
						powerlink1_4	=	'".$_POST['powerlink1_4']."',
						powerlink1_5	=	'".$_POST['powerlink1_5']."',
						main_keyword2	=	'".$_POST['main_keyword2']."',
						powerlink2_1	=	'".$_POST['powerlink2_1']."',					
						powerlink2_2	=	'".$_POST['powerlink2_2']."',
						powerlink2_3	=	'".$_POST['powerlink2_3']."',
						powerlink2_4	=	'".$_POST['powerlink2_4']."',
						powerlink2_5	=	'".$_POST['powerlink2_5']."',
						main_keyword3	=	'".$_POST['main_keyword3']."',
						powerlink3_1	=	'".$_POST['powerlink3_1']."',					
						powerlink3_2	=	'".$_POST['powerlink3_2']."',
						powerlink3_3	=	'".$_POST['powerlink3_3']."',
						powerlink3_4	=	'".$_POST['powerlink3_4']."',
						powerlink3_5	=	'".$_POST['powerlink3_5']."',
						upd_date		=	CURDATE(),
						upd_time		=	CURTIME(),
						upd_emp			=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}

			$Qry1 = "INSERT INTO t_support_update_state (support_seq, support_type, reg_date, reg_time, reg_emp) VALUES
				('".$_POST['seq']."', '".$_GET['type']."', CURDATE(), CURTIME(), '".$_SESSION['s_em_seq']."')";

			echo $Qry;

			$Rst = que($Qry);
			$Rst1 = que($Qry1);

			if($Rst){
					$N_RETURNVAL = "1";
			}else{
				$N_RETURNVAL = "-1";
			}
			echo $Rst;

			return $N_RETURNVAL;
			que_close();

	}
	//-----업무지원 수정 끝--------------------------------



	//-----업무지원 삭제 시작--------------------------------
	public function funcDelWorkSupport(){
		
		if($_GET['type']=="1"){
			$Qry = "UPDATE t_support_virul SET 
						del_date	=	CURDATE(),
						del_time	=	CURTIME(),
						del_emp		=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}else if($_GET['type']=="2"){
			$Qry = "UPDATE t_support_google SET 
						del_date		=	CURDATE(),
						del_time		=	CURTIME(),
						del_emp			=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}else if($_GET['type']=="3"){
			$Qry = "UPDATE t_support_proposal SET 
						del_date		=	CURDATE(),
						del_time		=	CURTIME(),
						del_emp			=	'".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		}
			echo $Qry;
			$Rst = que($Qry);

			if($Rst){
					$N_RETURNVAL = "1";
			}else{
				$N_RETURNVAL = "-1";
			}
			echo $Rst;

			return $N_RETURNVAL;
			que_close();

	}
	//-----업무지원 삭제 끝--------------------------------





	//----광고주명 찾기 시작--------
	public function funcSearchCust(){
		if ($_GET['cs_nm']){
			$Qry = "SELECT a.cs_seq, a.cs_nm, a.url, ceo_nm, a.cs_num, tel1,tel2,tel3, addr1,addr2 FROM t_customer a INNER JOIN t_customer_mg b
					ON a.cs_seq = b.cs_seq
					WHERE a.cs_nm like '%".$_GET['cs_nm']."%'
					AND b.em_seq='".$_SESSION['s_em_seq']."'
					AND a.del_date IS NULL
					GROUP BY a.cs_seq";
			//echo $Qry;
			$Rst = que($Qry);
			
		}
		$nListCnt = 0 ;
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['cs_seq'][]	= $Row['cs_seq'];
			$S_RETURNVAL['cs_nm'][]		= $Row['cs_nm'];
			$S_RETURNVAL['url'][]		= $Row['url'];
			$S_RETURNVAL['ceo_nm'][]	= $Row['ceo_nm'];
			$S_RETURNVAL['cs_num'][]	= $Row['cs_num'];
			$S_RETURNVAL['tel1'][]		= $Row['tel1'];
			$S_RETURNVAL['tel2'][]		= $Row['tel2'];
			$S_RETURNVAL['tel3'][]		= $Row['tel3'];
			$S_RETURNVAL['addr1'][]		= $Row['addr1'];
			$S_RETURNVAL['addr2'][]		= $Row['addr2'];
			$nListCnt++;
		}
		$S_RETURNVAL['nListCnt'] = $nListCnt;
		@mysql_free_result($Rst);

		if (!$S_RETURNVAL['cs_seq']) {
			$S_RETURNVAL = -1;
		} else {
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
		que_close();
	}
//----광고주명 찾기 끗--------



//----리포트발송 시작--------
	public function funcIptReportSend(){
		
		if($_FILES['file1']['name']){
			$file_tmp	= $_FILES['file1']['tmp_name'];
			$file1		= trim($_FILES['file1']['name']);
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/send_report_file/";
			$file_size1	= round($_FILES['file1']['size']/1024);

			//limit the size of the file to 10MB
			if($file_size1 > 10500) {//204800
				funcMsgError('keep the filesize under (10MB 이상1!!)', '', '', '', '', '' );
				exit();
			}

			$fname_arr = explode(".",$file1);
			$fname1		= $fname_arr[0];
			$extension	= $fname_arr[1];

			//$pattern = "/(\zip|ZIP|jpg|JPG|gif|GIF|png|PNG\b)$/i";
			//if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			//	  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			//	  exit;
			//}

			$file1 = $fname1."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file1)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}		

		if($_FILES['file2']['name']){
			$file_tmp	= $_FILES['file2']['tmp_name'];
			$file2		= trim($_FILES['file2']['name']);
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/send_report_file/";
			$file_size2	= round($_FILES['file2']['size']/1024);

			//limit the size of the file to 10MB
			if($file_size2 > 10500) {//204800
				funcMsgError('keep the filesize under (10MB 이상1!!)', '', '', '', '', '' );
				exit();
			}

			$fname_arr = explode(".",$file2);
			$fname2		= $fname_arr[0];
			$extension	= $fname_arr[1];

			//$pattern = "/(\zip|ZIP|jpg|JPG|gif|GIF|png|PNG\b)$/i";
			//if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			//	  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			//	  exit;
			//}

			$file2 = $fname2."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file2)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}		

		if($_FILES['file3']['name']){
			$file_tmp	= $_FILES['file3']['tmp_name'];
			$file3		= trim($_FILES['file3']['name']);
			$dir_path	= $_SERVER['DOCUMENT_ROOT']."/data/send_report_file/";
			$file_size3	= round($_FILES['file3']['size']/1024);

			//limit the size of the file to 10MB
			if($file_size3 > 10500) {//204800
				funcMsgError('keep the filesize under (10MB 이상1!!)', '', '', '', '', '' );
				exit();
			}

			$fname_arr = explode(".",$file3);
			$fname3		= $fname_arr[0];
			$extension	= $fname_arr[1];

			//$pattern = "/(\zip|ZIP|jpg|JPG|gif|GIF|png|PNG\b)$/i";
			//if(!$extension || !(preg_match($pattern, $extension, $matches, PREG_OFFSET_CAPTURE))) {
			//	  funcMsgError('업로드가 금지되어 있는 파일입니다.', '', '', '', '', 'frm1.request_file.value=""' );
			//	  exit;
			//}

			$file3 = $fname3."-".rand().".".$extension;
			
			if(move_uploaded_file($file_tmp,$dir_path.$file3)){
				$B_RETURNVAL = 1;
			}else{
				$B_RETURNVAL = -1;
				exit();
			}
		}		

		//-메일보내기-----------------------------------------

		$to				= $_POST['to'];
		$to1			= $_POST['to1'];
		$name			= $_POST['name'];
		$email			= $_POST['email'];
		$subject		= $_POST['subject'];
		$content		= $_POST['content'];	

		$now = date('YmdHi');//날짜 201202201230 (12자리)
		$rand = rand(1000,9999);//난수(4자리)
		$pdfpath = $_SERVER['DOCUMENT_ROOT']."/data/send_report_file/";//pdf파일 저장 폴더(file/)
		// $pdffile = $now."_".$rand.".pdf";//pdf파일명(날짜+난수)
		$pdffile1 = $file1;



		$attach[0] = attach_file($file1, $pdfpath.$file1);//pdf파일 내용 읽어서 메일에 첨부
		$attach[1] = attach_file($file2, $pdfpath.$file2);//pdf파일 내용 읽어서 메일에 첨부
		$attach[2] = attach_file($file3, $pdfpath.$file3);//pdf파일 내용 읽어서 메일에 첨부

		

		$ok = mailer($name, $email, $to, $to1, $subject, $content, $type=2, $attach);//>메일발송

		if($ok=="1"){
			$send_yn="Y";
		}else{
			$send_yn="N";
		}

		//-메일보내기-----------------------------------------

		//if($file1){$Qry .= "file1		=	'".$file1."',";}
		//if($file2){$Qry .= "file2		=	'".$file2."',";}
		//if($file3){$Qry .= "file3		=	'".$file3."',";}

		$Qry = "INSERT INTO t_send_report (cs_seq, report_type, tomail, tomail1, title, contents, filename,filename2,filename3, division1, division2, em_seq, send_yn, reg_date, reg_time, reg_emp) 
				VALUES
				('".$_POST['cs_seq']."',
				'".$_POST['report_type']."',
				'".$to."',
				'".$to1."',
				'".$_POST['subject']."',
				'".$_POST['content']."',
				'".$file1."',
				'".$file2."',
				'".$file3."',
				'".$_SESSION['em_division1']."',
				'".$_SESSION['em_division2']."',
				'".$_SESSION['s_em_seq']."',
				'".$send_yn."',
				CURDATE(),
				CURTIME(),
				'".$_SESSION['s_em_seq']."')";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();


	}
//----리포트발송 끗--------

//----리포트리스트 시작--------
	public function funcListSendReport($nOrdPagingNum){
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;
		
		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_nm'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS cs_seq, report_type, title, filename, filename2, filename3, division1, division2, em_seq, send_yn, reg_date, reg_time , contents
				FROM t_send_report 
				WHERE del_date IS NULL
				".$where_s_item."
				".$where_d_item."
				".$where_division1."
				".$where_division2."
				".$where_em_seq."
				ORDER BY sr_seq DESC 
				LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
	
		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['cs_seq'][]		= $Row['cs_seq'];
			$S_RETURNVAL['title'][]			= $Row['title'];
			$S_RETURNVAL['report_type'][]	= $Row['report_type'];
			$S_RETURNVAL['filename'][]		= $Row['filename'];
			$S_RETURNVAL['filename2'][]		= $Row['filename2'];
			$S_RETURNVAL['filename3'][]		= $Row['filename3'];
			$S_RETURNVAL['division1'][]		= $Row['division1'];
			$S_RETURNVAL['division2'][]		= $Row['division2'];
			$S_RETURNVAL['em_seq'][]		= $Row['em_seq'];
			$S_RETURNVAL['send_yn'][]		= $Row['send_yn'];
			$S_RETURNVAL['reg_date'][]		= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]		= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]		= $Row['reg_emp'];
			$S_RETURNVAL['contents'][]		= $Row['contents'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['cs_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----리포트리스트 끗--------


//----주소록 등록 시작--------
	public function funcIptAddr(){
		$Qry = "INSERT INTO t_addr_list (title, addr, em_seq, reg_date, reg_time, reg_emp) 
				VALUES 
				('".$_POST['title']."','".$_POST['addr']."','".$_SESSION['s_em_seq']."',CURDATE(), CURTIME(),'".$_SESSION['s_em_seq']."')";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();

	}
//----주소록 등록 끗--------


//----주소록 리스트 시작--------
	public function funcListAddr($nOrdPagingNum){
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS ad_seq, title, addr, reg_date FROM t_addr_list 
				WHERE del_date IS NULL
				AND em_seq = '".$_SESSION['s_em_seq']."'
				".$where_s_item."
				ORDER BY ad_seq DESC 
				LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";

		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['ad_seq'][]		= $Row['ad_seq'];
			$S_RETURNVAL['title'][]			= $Row['title'];
			$S_RETURNVAL['addr'][]			= $Row['addr'];
			$S_RETURNVAL['reg_date'][]		= $Row['reg_date'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['ad_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----주소록 리스트 끗--------


//-----주소록 리스트 수정 시작------------------------------
	public function funcUpdateAddr(){
		echo "<pre>";
		print_R($_POST);
		echo "</pre>";

		foreach($_POST['checklist'] as $k=>$v){
			$Qry = "UPDATE t_addr_list 
					SET title		= '".$_POST['title'][$v]."',
						addr		= '".$_POST['addr'][$v]."',
						upd_date	= CURDATE(),
						upd_time	= CURTIME(),
						upd_emp		= '".$_SESSION['s_em_seq']."'
					WHERE ad_seq	= '".$_POST['ad_seq'][$v]."'";

			echo $Qry;
			$Rst = que($Qry);
		}	

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//-----주소록 리스트 수정 끗------------------------------

//-----주소록 리스트 삭제 시작------------------------------
	public function funcDeleteAddr(){
		echo "<pre>";
		print_R($_POST);
		echo "</pre>";

		foreach($_POST['checklist'] as $k=>$v){
			$Qry = "UPDATE t_addr_list 
					SET del_date	= CURDATE(),
						del_time	= CURTIME(),
						del_emp		= '".$_SESSION['s_em_seq']."'
					WHERE ad_seq	= '".$_POST['ad_seq'][$v]."'";

			echo $Qry;
			$Rst = que($Qry);
		}	

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//-----주소록 리스트 삭제 끗------------------------------

//----주소록 리스트 시작--------
	public function funcSelectAddr(){

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS ad_seq, title, addr, reg_date FROM t_addr_list 
				WHERE del_date IS NULL
				AND em_seq = '".$_SESSION['s_em_seq']."'
				$where_s_item
				ORDER BY ad_seq DESC";

		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['ad_seq'][]		= $Row['ad_seq'];
			$S_RETURNVAL['title'][]			= $Row['title'];
			$S_RETURNVAL['addr'][]			= $Row['addr'];
			$S_RETURNVAL['reg_date'][]		= $Row['reg_date'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['ad_seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----주소록 리스트 끗--------


//----주소록 리스트 시작--------

	public function funcSelectAddrContent(){

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS seq, byname, title, contents, reg_date FROM t_addr_content 
				WHERE del_date IS NULL
				AND (reg_emp = '".$_SESSION['s_em_seq']."' OR reg_emp IS NULL)
				".$where_s_item."
				ORDER BY seq DESC";

	//	echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]		= $Row['seq'];
			$S_RETURNVAL['byname'][]	= $Row['byname'];
			$S_RETURNVAL['title'][]		= $Row['title'];
			$S_RETURNVAL['contents'][]	= $Row['contents'];
			$S_RETURNVAL['reg_date'][]	= $Row['reg_date'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----주소록 리스트 끗--------


//----보고서 코멘트 서치 시작--------

	public function funcCommentSearch($nOrdPagingNum){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = " AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}
		
		if($_GET['type']){
			$where_type = " AND (type = '".$_GET['type']."')";
		}
		
		if(count($_GET['category'])==1){
			$Qry .= " AND category LIKE '%".$_GET['category'][0]."%'";
		}else if (count($_GET['category'])>1){
			$Qry .= " AND category LIKE '%".$_GET['category'][0]."%'";
		}

		$Qry  = "SELECT SQL_CALC_FOUND_ROWS seq,category,comment,reg_date FROM t_comment WHERE del_date IS NULL ";
		$Qry .= $where_type . $where_s_item;
		
		if(count($_GET['category'])==1){
			$Qry .= " AND category LIKE '%".$_GET['category'][0]."%'";
		}else if (count($_GET['category'])>1){
			$Qry .= " AND (category LIKE '%".$_GET['category'][0]."%'";
			for($i=1;$i<count($_GET['category']);$i++){
			$Qry .= " OR category LIKE '%".$_GET['category'][$i]."%'";
			
			}
			$Qry .= ")";
		}
		/*
		if(is_array($_GET['category'])){
		foreach($_GET['category'] as $k=>$v){
		$Qry .= " AND category LIKE '%".$v."%'";
		}}
		*/
		$Qry .= " ORDER BY seq DESC 
				  LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		

		/*
		$Qry = "SELECT SQL_CALC_FOUND_ROWS seq, byname, title, contents, reg_date FROM t_addr_content 
				WHERE del_date IS NULL
				".$where_s_item."
				ORDER BY seq DESC";
		*/
//echo $Qry;
//echo $_GET['nc'];
if($_GET['nc']=="11" || $_GET['type']){
		$Rst = que($Qry);
}else{
		$Rst = que($Qry);
}
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]		= $Row['seq'];
			$S_RETURNVAL['category'][]	= $Row['category'];
			$S_RETURNVAL['comment'][]	= $Row['comment'];
			$S_RETURNVAL['reg_date'][]	= $Row['reg_date'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----보고서 코멘트 서치 끗--------



//----보고서 코멘트 업데이트 시작--------

	public function funcInsertComment(){
		
		$category1 = join(",",$_POST['category']);

		$Qry = "INSERT INTO t_comment (category,comment) VALUES ('".$category1."','".$_POST['comment']."')";
		echo $Qry;

		$Rst = que($Qry);
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//----보고서 코멘트 업데이트 끗--------

//----보고서 코멘트 업데이트 시작--------

	public function funcUpdateComment(){
		
		$catogory1 = join(",",$_POST['category']);
		$Qry = "UPDATE t_comment set 
				category	= '".$catogory1."' , 
				comment		= '".$_POST['comment']."', 
				upd_date	= CURDATE(), 
				upd_time	= CURTIME(), 
				upd_emp		= '".$_SESSION['s_em_seq']."'
				WHERE seq	= '".$_POST['seq']."'";
		echo $Qry;

		$Rst = que($Qry);
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//----보고서 코멘트 업데이트 끗--------



//----보고서 코멘트 업데이트 시작--------

	public function funcDeleteComment(){
		
		$Qry = "UPDATE t_comment set  
				del_date	= CURDATE(), 
				del_time	= CURTIME(), 
				del_emp		= '".$_SESSION['s_em_seq']."'
				WHERE seq	= '".$_POST['seq']."'";
		echo $Qry;

		$Rst = que($Qry);
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//----보고서 코멘트 업데이트 끗--------



//----즐겨쓰는 제목 & 내용 등록 시작--------
	public function funcIptMyMailContent(){
		$Qry = "INSERT INTO t_addr_content (title, contents, reg_date, reg_time, reg_emp) 
				VALUES 
				('".$_POST['title']."','".$_POST['contents']."',CURDATE(), CURTIME(),'".$_SESSION['s_em_seq']."')";

		echo $Qry;
		$Rst = que($Qry);

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		echo $Rst;

		return $N_RETURNVAL;
		que_close();

	}
//----즐겨쓰는 제목 & 내용 등록 끗--------


//----즐겨쓰는 제목 & 내용 리스트 시작--------
	public function funcListMyMailContent($nOrdPagingNum){
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		if(!$_SESSION['em_level']<=10){
			$where_em_seq = "AND reg_emp = '".$_SESSION['s_em_seq']."'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS seq, title, contents, reg_date 
				FROM t_addr_content 
				WHERE del_date IS NULL
				".$where_s_item."
				".$where_em_seq."
				ORDER BY seq DESC 
				LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";

		//echo $Qry;
		$Rst = que($Qry);
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]			= $Row['seq'];
			$S_RETURNVAL['contents'][]		= $Row['contents'];
			$S_RETURNVAL['title'][]			= $Row['title'];
			$S_RETURNVAL['reg_date'][]		= $Row['reg_date'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		//echo "N_CNT : ".$Row1['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		//echo ($S_RETURNVAL);
		
		que_close();

	}
//----즐겨쓰는 제목 & 내용 리스트 끗--------



//-----즐겨쓰는 제목 & 내용 수정 시작------------------------------
	public function funcUpdMyMailContent(){
		echo "<pre>";
		print_R($_POST);
		echo "</pre>";

		foreach($_POST['checklist'] as $k=>$v){
			$Qry = "UPDATE t_addr_content 
					SET title		= '".$_POST['title'][$v]."',
						contents	= '".$_POST['contents'][$v]."',
						upd_date	= CURDATE(),
						upd_time	= CURTIME(),
						upd_emp		= '".$_SESSION['s_em_seq']."'
					WHERE seq	= '".$_POST['seq'][$v]."'";

			echo $Qry;
			$Rst = que($Qry);
		}	

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//-----즐겨쓰는 제목 & 내용 수정 끗------------------------------

//-----즐겨쓰는 제목 & 내용 삭제 시작------------------------------
	public function funcDelMyMailContent(){
		echo "<pre>";
		print_R($_POST);
		echo "</pre>";

		foreach($_POST['checklist'] as $k=>$v){
			$Qry = "UPDATE t_addr_content 
					SET del_date	= CURDATE(),
						del_time	= CURTIME(),
						del_emp		= '".$_SESSION['s_em_seq']."'
					WHERE seq	= '".$_POST['seq'][$v]."'";

			echo $Qry;
			$Rst = que($Qry);
		}	

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
//-----즐겨쓰는 제목 & 내용 삭제 끗------------------------------


//-----보고서 보내기 통계 시작------------------------------

	public function funcReportSendReport(){
		
		$sdate1 = $_GET['syear']."-".$_GET['smonth']."-01";
		$edate1 = $_GET['eyear']."-".$_GET['emonth']."-31";

		$sdate2 = date("Y-m-d", mktime(0,0,0,substr($sdate1,5,2)-1,substr($sdate1,8,2),substr($sdate1,0,4)));
		$edate2 = date("Y-m-31", mktime(0,0,0,substr($edate1,5,2)-1,1,substr($edate1,0,4)));

		$edate3 = date("Y-m-31", mktime(0,0,0,substr($edate1,5,2)-1,1,substr($edate1,0,4)));

		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}

		$Qry1 = "SELECT live_date, COUNT(cs_seq) AS live_cnt, division1, division2, em_seq FROM (
					SELECT LEFT(pay_date,7) AS live_date, cs_seq , division1, division2, em_seq 
					FROM t_contract 
					WHERE del_date IS NULL 
					AND sales_type='1' AND agree_state='3' AND m_nm IN (41,264) AND pay_date BETWEEN '".$sdate2."' AND '".$edate2."' 
					".$where_division1."
					".$where_division2."
					".$where_em_seq."
					GROUP BY LEFT(pay_date,7), cs_seq, division1, division2, em_seq
				) AS a
				GROUP BY live_date, division1, division2, em_seq";

		$Qry2 = "SELECT send_date, COUNT(*) AS send_cnt, division1, division2, em_seq FROM (
					SELECT LEFT(reg_date,7) AS send_date, COUNT(*) AS send_cnt, cs_seq, division1, division2, em_seq 
					FROM t_send_report 
					WHERE del_date IS NULL 
					AND reg_date BETWEEN '".$sdate1."' AND '".$edate1."'  
					".$where_division1."
					".$where_division2."
					".$where_em_seq."
					GROUP BY LEFT(reg_date,7), cs_seq, division1, division2, em_seq
				) AS a
				GROUP BY send_date, division1, division2, em_seq";

		//echo $Qry1."<br><br>".$Qry2;

		$Rst1 = que($Qry1);
		$Rst2 = que($Qry2);

		while ($Row1 = @mysql_fetch_assoc($Rst1)) {
			$real_live_date =  date("Y-m", mktime(0,0,0,substr($Row1['live_date'],5,2)+1,1,substr($Row1['live_date'],0,4)));


			/*
			if($Row1['division1']=="1" || $Row1['division1']=="3"){$division1="617";
			}else if($Row1['division1']=="2" || $Row1['division1']=="402" && $Row1['division2']!="485"){$division1="616";
			}else if($Row1['division1']=="213" && $Row1['division2']=="210"|| $Row1['division1']=="462" && $Row1['division2']=="464"){$division1="618";
			}else if($Row1['division1']=="402" && $Row1['division2']=="485"){$division1="619";
			}else if($Row1['division1']=="427"){$division1="620";
			}else{$division1 = $Row1['division1'];
			}
			*/

			

			$S_RETURNVAL['d1_live_cnt'][$Row1['division1']][$real_live_date]												+= $Row1['live_cnt'];
			$S_RETURNVAL['d2_live_cnt'][$Row1['division1']][$Row1['division2']][$real_live_date]							+= $Row1['live_cnt'];
			$S_RETURNVAL['d3_live_cnt'][$Row1['division1']][$Row1['division2']][$Row1['em_seq']][$real_live_date]			+= $Row1['live_cnt'];

			$S_RETURNVAL['live_date'][$real_live_date] = $real_live_date;
		}

		while ($Row2 = @mysql_fetch_assoc($Rst2)) {

			/*
			if($Row2['division1']=="1" || $Row2['division1']=="3"){$division1="617";
			}else if($Row2['division1']=="2" || $Row2['division1']=="402" && $Row2['division2']!="485"){$division1="616";
			}else if($Row2['division1']=="213" && $Row2['division2']=="210"|| $Row2['division1']=="462" && $Row2['division2']=="464"){$division1="618";
			}else if($Row2['division1']=="402" && $Row2['division2']=="485"){$division1="619";
			}else if($Row2['division1']=="427"){$division1="620";
			}else{$division1 = $Row2['division1'];
			}
			*/

			$S_RETURNVAL['division2'][$Row2['division1']][$Row2['division2']]												= $Row2['division2'];
			$S_RETURNVAL['division3'][$Row2['division1']][$Row2['division2']][$Row2['em_seq']]								= $Row2['em_seq'];

			$S_RETURNVAL['d1_send_cnt'][$Row2['division1']][$Row2['send_date']]												+= $Row2['send_cnt'];
			$S_RETURNVAL['d2_send_cnt'][$Row2['division1']][$Row2['division2']][$Row2['send_date']]							+= $Row2['send_cnt'];
			$S_RETURNVAL['d3_send_cnt'][$Row2['division1']][$Row2['division2']][$Row2['em_seq']][$Row2['send_date']]		+= $Row2['send_cnt'];
			
			$S_RETURNVAL['send_date'][$Row2['send_date']] = $Row2['send_date'];
		}

		return $S_RETURNVAL;
		que_close();

	}
//-----보고서 보내기 통계 끗------------------------------


//-----보고서 보내기 발송광고주리스트 시작------------------------------

	public function funcListSendCust($nOrdPagingNum){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;
		
		$sdate1 = $_GET['syear']."-".$_GET['smonth']."-01";
		$edate1 = $_GET['eyear']."-".$_GET['emonth']."-31";

		$sdate2 = date("Y-m-d", mktime(0,0,0,substr($sdate1,5,2)-1,substr($sdate1,8,2),substr($sdate1,0,4)));
		$edate2 = date("Y-m-31", mktime(0,0,0,substr($edate1,5,2)-1,1,substr($edate1,0,4)));

		$edate3 = date("Y-m-31", mktime(0,0,0,substr($edate1,5,2)-1,1,substr($edate1,0,4)));

		if($_SESSION['em_level']<=10){
			if($_GET['division1'])	{$where_division1	= "AND division1 ='".$_GET['division1']."'";}
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=20){
			$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
			if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=30){
			$where_division2 = "AND division1 ='".$_SESSION['em_division1']."' 
								AND division2 ='".$_SESSION['em_division2']."'";
			if($_GET['em_seq'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_seq']."'";}
		}else if($_SESSION['em_level']<=40){
			$where_em_seq = "AND em_seq = '".$_SESSION['s_em_seq']."'";
		}

		$Qry1 = "SELECT SQL_CALC_FOUND_ROWS LEFT(pay_date,7) AS live_date, COUNT(*) AS live_cnt, cs_seq , division1, division2, em_seq 
					FROM t_contract 
					WHERE del_date IS NULL 
					AND sales_type='1' AND agree_state='3' AND m_nm IN (41,264) AND pay_date BETWEEN '".$sdate2."' AND '".$edate2."' 
					".$where_division1."
					".$where_division2."
					".$where_em_seq."
					GROUP BY LEFT(pay_date,7), cs_seq, division1, division2, em_seq
					ORDER BY LEFT(pay_date,7), division1, division2, em_seq
					LIMIT ".$nOrdStart.", ".$nOrdPagingNum."
					";

		$Qry2 = "SELECT LEFT(reg_date,7) AS send_date, COUNT(*) AS send_cnt, cs_seq, division1, division2, em_seq 
					FROM t_send_report 
					WHERE del_date IS NULL 
					AND reg_date BETWEEN '".$sdate1."' AND '".$edate1."'  
					".$where_division1."
					".$where_division2."
					".$where_em_seq."
					GROUP BY LEFT(reg_date,7), cs_seq, division1, division2, em_seq
					ORDER BY LEFT(reg_date,7), division1, division2, em_seq";

		//echo $Qry."<br><br>".$Qry1."<br><br>".$Qry2;

		$Rst1 = que($Qry1);

		$Qry3 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst3 = que($Qry3);
		$Row3 = @mysql_fetch_assoc($Rst3);

		//echo "N_CNT : ".$Row3['N_CNT'];
		$S_RETURNVAL['N_CNT'] = $Row3['N_CNT'];

		$Rst2 = que($Qry2);

		$nListCnt1 = 0 ;
		while ($Row1 = @mysql_fetch_assoc($Rst1)) {
			$real_live_date =  date("Y-m", mktime(0,0,0,substr($Row1['live_date'],5,2)+1,1,substr($Row1['live_date'],0,4)));

			$S_RETURNVAL['live_cnt'][$Row1['division1']][$Row1['division2']][$Row1['em_seq']][$Row1['cs_seq']][$real_live_date]= $Row1['live_cnt'];
		
			$S_RETURNVAL['d1_live_cnt'][$Row1['division1']][$real_live_date]												+= $Row1['live_cnt'];
			$S_RETURNVAL['d2_live_cnt'][$Row1['division1']][$Row1['division2']][$real_live_date]							+= $Row1['live_cnt'];
			$S_RETURNVAL['d3_live_cnt'][$Row1['division1']][$Row1['division2']][$Row1['em_seq']][$real_live_date]			+= $Row1['live_cnt'];

			$S_RETURNVAL['live_date'][$real_live_date] = $real_live_date;

			$nListCnt1 ++;
		}
		
		$S_RETURNVAL['nListCnt1'] = $nListCnt1;

		$nListCnt2 = 0 ;
		while ($Row2 = @mysql_fetch_assoc($Rst2)) {

			$S_RETURNVAL['send_cnt'][$Row2['division1']][$Row2['division2']][$Row2['em_seq']][$Row2['cs_seq']][$Row2['send_date']]= $Row2['send_cnt'];

			$S_RETURNVAL['send_cnt_1'][$Row2['send_date']]+= $Row2['send_cnt'];
			$S_RETURNVAL['send_cnt_2']+= $Row2['send_cnt'];

			$S_RETURNVAL['d1_send_cnt'][$Row2['division1']][$Row2['send_date']]												+= $Row2['send_cnt'];
			$S_RETURNVAL['d2_send_cnt'][$Row2['division1']][$Row2['division2']][$Row2['send_date']]							+= $Row2['send_cnt'];
			$S_RETURNVAL['d3_send_cnt'][$Row2['division1']][$Row2['division2']][$Row2['em_seq']][$Row2['send_date']]		+= $Row2['send_cnt'];
			
			$S_RETURNVAL['send_date'][$Row2['send_date']] = $Row2['send_date'];

			$nListCnt2 ++;
		}
		$S_RETURNVAL['nListCnt2'] = $nListCnt2;

		return $S_RETURNVAL;

		que_close();

	}
//-----보고서 보내기 발송광고주리스트 끗------------------------------


	//---내부이관프로세스 등록-------------------------------------------
	public function funcIptInsideTransferRequest(){
		$Qry = "INSERT INTO t_inside_transfer_request (
					cs_seq,
					cs_nm,
					ceo_nm,
					cs_num,
					cs_email,
					m_nm,
					cs_m_id,	
					existing_em_nm,
					change_em_nm,
					hm_comment,
					send_date,
					send_time,
					reg_date,
					reg_time,
					reg_emp
				) VALUES (
					'".$_POST['cs_seq']."',
					'".$_POST['cs_nm']."',
					'".$_POST['ceo_nm']."',
					'".$_POST['cs_num']."',
					'".$_POST['cs_email']."',
					'".$_POST['m_nm']."',
					'".$_POST['cs_m_id']."',
					'".$_POST['check_em_seq']."',
					'".$_SESSION['em_kr_nm']."',
					'".$_POST['hm_comment']."',
					CURDATE(),
					CURTIME(),
					CURDATE(),
					CURTIME(),
					'".$_SESSION['s_em_seq']."'
				)";
		//echo $Qry;
		$Rst=que($Qry);

		

		$cs_request_seq = mysql_insert_id();
		$result_cs_request_seq = crypt($cs_request_seq);

		// 암호화된 seq를 crypt_seq에 업데이트 한다.
		$Qry1 = "UPDATE t_inside_transfer_request set crypt_seq = '".$result_cs_request_seq."' WHERE seq='".$cs_request_seq."'"; 
		// 기존 담당자의 본부, 이메일, 이름, 해당 본부장의 이메일을 가져온다.
		$Qry2 = "SELECT em_seq, em_nm, division1, email, (SELECT email FROM t_employee a WHERE a.division1=b.division1 AND position2='82' and del_date is null and work_stat='70') AS mg_email 
				 FROM t_employee b WHERE em_seq='".$_POST['check_em_seq']."'";		

		//echo $Qry1;
		$Rst1=que($Qry1);
		$Rst2=que($Qry2);
		$Row2 = @mysql_fetch_assoc($Rst2);

		switch($_POST['m_nm'])	{
			case "264,"		: $m_nm = "네이버"; break;
			case "41,"		: $m_nm = "클릭스"; break;
			case "496,"		: $m_nm = "이베이"; break;
			case "881,"		: $m_nm = "모먼트"; break;
			case "265,"		: $m_nm = "구글"; break;
			case "845,"		: $m_nm = "페이스북"; break;
			case "302,438"	: $m_nm = "TG"; break;
			case "302,454"	: $m_nm = "크리테오"; break;
			case "808,"		: $m_nm = "11번가"; break;
			case "302,820"	: $m_nm = "티몬"; break;
			case "302,847"	: $m_nm = "위메프"; break;
			case "302,844"	: $m_nm = "인터파크"; break;
			case "302,885"	: $m_nm = "신세계"; break;
			default			: $m_nm = $_POST['m_nm'];break;
		}
		
$contents = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
<style type='text/css'>p { margin:0px;padding:0px;line-height:1.5; }</style>
</head>
<body style='font-size:12px;font-family:gulim,arial;margin:20;'>

<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta http-equiv='X-UA-Compatible' content='ie=edge'>
<title>트리플하이엠</title>

<table width=\"700\"  style=\"border-collapse: collapse;border-bottom:1px solid #d8d8d8 ; border-top:1px solid #d8d8d8 ; border-left:1px solid #d8d8d8; border-right:1px solid #d8d8d8; font-family: 'Nanum Square', 'NanumSquare',sans-serif;background-color:#ffffff;\" align=\"center\" >
<tbody>
<tr style='padding: 0; margin: 0;'>
<td width='39' height='88'></td>
<td width='39' height='88' style='border-bottom:1px solid #ebebeb;'>
<img src='http://rms.hmcorp.co.kr/images/main/logo3.png' alt='' style='border: 0; vertical-align: top; margin: 0; padding: 0;'>
</td>
<td width='39' height='88'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='60' colspan='3'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td></td>
<td width='700' style='font-weight:bold;font-size:19px;text-align:center;'><span style='color:#37BEF6'>트리플하이엠 담당자 변경 동의 안내 메일입니다.</span></td>
<td></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='60' colspan='3'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td></td>
<td width='700' style='font-size:15px;color:#333;line-height:1.2;text-align:center'>
<span style='color:#37BEF6'>".$_POST['cs_nm']."님</span><br/><br/>

안녕하세요. 트리플하이엠입니다.<br/><br/>

담당자 변경신청이 접수되어 안내 드립니다.<br/>
동의하기 클릭 후 내용 확인하시기 바랍니다.
</td>
<td></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='30' colspan='3'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='20' colspan='3'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='30' colspan='3' align='center'>
<table cellpadding='0' cellspacing='0' align='center' style='border-top:2px #71717f solid;font-size:13px;line-height:1.2' width='620'>
<tbody>
<tr style='padding: 0; margin: 0;' height='55'>
<th width='15' style='border-bottom:2px #71717f solid;background-color:#f0f0f0'></th>
<th align='left' style='font-weight:bold;border-bottom:2px #71717f solid;background-color:#f0f0f0' colspan='3'>| 변경 신청 내용</th>

</tr>
<tr style='padding: 0; margin: 0;' height='55'>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<th width='150' align='left' style='border-bottom:1px #d7dae3 solid;'>매체</th>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<td align='left' style='border-bottom:1px #d7dae3 solid;color:#666'>".$m_nm."</td>
</tr>
<tr style='padding: 0; margin: 0;' height='55'>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<th align='left' style='border-bottom:1px #d7dae3 solid;'>변경 담당자</th>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<td align='left' style='border-bottom:1px #d7dae3 solid;color:#666'>".$_SESSION['em_kr_nm']."</td>
</tr>
<tr style='padding: 0; margin: 0;' height='55'>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<th align='left' style='border-bottom:1px #d7dae3 solid;'>접수일</th>
<th width='15' style='border-bottom:1px #d7dae3 solid;'></th>
<td align='left' style='border-bottom:1px #d7dae3 solid;color:#666'>".date("Y-m-d")."</td>
</tr>

</tbody>
</table>
</td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='50' colspan='3'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td></td>
<td height='50' align='center'>
	<table width='300' cellpadding='0' cellspacing='0' style='background:#00529B;' align='center'>
	<tr>
	<td height='50' align='center'>
<!--
<a href=\"javascript:Frameset('http://rms.hmcorp.co.kr/work/cs_agreement.php?send_type=m&cs_request=".$result_cs_request_seq."')\" style='display:inline-block;background-color:#00529B;text-decoration:none;line-height:50px;color:#ffffff;font-size:18px; text-align: center;' >링크</a> 
<br/><br/>
-->
<a href='http://rms.hmcorp.co.kr/work/cs_agreement.php?send_type=m&cs_request=".$result_cs_request_seq."' target='_blank' style='display:inline-block;background-color:#00529B;text-decoration:none;line-height:50px;color:#ffffff;font-size:18px; text-align: center;' >동의하기</a>
<!--
<a href='http://rms.hmcorp.co.kr/work/cs_agreement/send_type=m&cs_request=".$result_cs_request_seq."' target='_blank' style='display:inline-block;background-color:#00529B;text-decoration:none;line-height:50px;color:#ffffff;font-size:18px; text-align: center;' >동의하기</a>
-->
	</td>
	</tr>
	</table>
</td>
<td></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td height='60' colspan='3' style='border-bottom:1px #ebebeb solid;'></td>
</tr>
<tr style='padding: 0; margin: 0;'>
<td colspan='3' style='font-size:12px;color:#666666;line-height:1.2;background-color:#efefef' align='center' height='120'>
궁금하신 점이나 불편한 사항은 영업담당자에 문의해 주시기 바랍니다.<br/><br/>

Copyright ⓒ TripleHM Co.Ltd. All Rights Reserved.
</td>
</tr>
</tbody>
</table>
<br />
</body>
</html>
";

$contents1 = "[RMS] 내부이관 요청이 등록되었습니다.<p/><br/>

".$Row2['em_nm']."님, 내부이관 요청이 등록되었습니다.<br/><br/>

내부이관 요청일 : ".date("Y-m-d")." <br/>
기존담당자명  : ".$Row2['em_nm']."  <br/>
광고주명		: ".$_POST['cs_nm']." <br/>
매체			: ".$m_nm." <br/>
계정			: ".$_POST['cs_m_id']." <br/>
";
/*
너의 이름		: ".$Row2['em_nm']." <br/>
너의 이메일	: ".$Row2['email']."@hmcorp.co.kr <br/>
너의 본부		: ".$Row2['division1']." <br/>
너의 본부	장	: ".$Row2['mg_email']."@hmcorp.co.kr <br/>
*/

		//-----광고주에 메일 보내기
		$tocc		= "hm_dev@hmcorp.co.kr";
		$to			= $_POST['cs_email'];
		$fromnm		= "트리플하이엠";
		$from		= "hm@hmcorp.co.kr";
		$subject	= "[트리플하이엠] 담당자 변경 동의 안내 메일입니다.";
		//-----


		//-----기존 담당자/ 기존 담당자 본부장에 메일 보내기
		$tocc1		= "hm_dev@hmcorp.co.kr,".trim($Row2['mg_email'])."@hmcorp.co.kr";
		//$tocc1	= "hm_dev@hmcorp.co.kr,yuchigirl@naver.com";
		$to1		= trim($Row2['email'])."@hmcorp.co.kr";
		//$to1		= "mjkim@hmcorp.co.kr";
		$fromnm1	= "트리플하이엠";
		//$from		= $_POST['cs_email']
		$from1		= "hm@hmcorp.co.kr";
		$subject1	= "[트리플하이엠] 내부이관 요청이 등록되었습니다.";
		//-----

		//mailer($name, $mail, $to, $to1, $subject, $content, 1, $file);

//이베이, 모먼트 예외 처리 - 광고주에 메일 미발송, 기존 담당자에는 발송
$arr_exception_m_nm = array("496,","881,");
in_array($_POST['m_nm'], $arr_exception_m_nm, true) ? $msg='true' : $msg='false';

if($msg=='false'){ 
		$ok = funSendMail($to,$fromnm,$from,$subject,$contents,1,$tocc);
}
		$ok1 = funSendMail($to1,$fromnm1,$from1,$subject1,$contents1,1,$tocc1);

		if($Rst&&$Rst1){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		//echo $Rst;

		return $N_RETURNVAL;

	}

	//---내부이관프로세스 등록-------------------------------------------

	//---내부이관프로세스 목록-------------------------------------------
	public function funcListInsideTransferRequest($nOrdPagingNum){
		
		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['s_item'] &&  $_GET['s_string']){$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";}
		if($_GET['cs_agree_state']) {$where_cs_agree_state = "AND cs_agree_state='".$_GET['cs_agree_state']."'";}
		if($_GET['hm_agree_state']) {$where_hm_agree_state = "AND hm_agree_state='".$_GET['hm_agree_state']."'";}

		if($_SESSION['em_level']==10){
		
			$get_emseq	= funGetEmSeq($_GET['division1'],$_GET['division2']);
			$join_emseq = join($get_emseq,",");

			$where_division1 = "";
			$where_division2 = "";
			$_GET['em_nm']==""?$where_em_seq = "AND reg_emp IN (".$join_emseq.")":$where_em_seq= "AND reg_emp ='".$_GET['em_nm']."'";

		/*
		}else if($_SESSION['em_level']==20){
		
			$get_emseq	= funGetEmSeq($_SESSION['em_division1'],$_GET['division2']);
			$join_emseq = join($get_emseq,",");

			$where_division1 = "";
			$where_division2 = "";
			$_GET['em_nm']==""?$where_em_seq = "AND reg_emp IN (".$join_emseq.")":$where_em_seq= "AND reg_emp ='".$_GET['em_nm']."'";
			
		}else if($_SESSION['em_level']==30){			
				
			$get_emseq	= funGetEmSeq($_SESSION['em_division1'],$_SESSION['em_division2']);
			$join_emseq = join($get_emseq,",");

			$where_division1 = "";
			$where_division2 = "";
			$_GET['em_nm']==""?$where_em_seq = "AND reg_emp IN (".$join_emseq.")":$where_em_seq= "AND reg_emp ='".$_GET['em_nm']."'";
		*/
		}else if($_SESSION['em_level']==40){
			$where_em_seq = "AND reg_emp = '".$_SESSION['s_em_seq']."'";
		
		}

		$Qry = "SELECT * FROM t_inside_transfer_request 
				WHERE 1=1 
				".$where_s_item."
				".$where_cs_agree_state."
				".$where_hm_agree_state."
				".$where_division1."
				".$where_division2."
				".$where_em_seq."
				ORDER BY seq DESC";

		//echo $Qry;
		$Rst = que($Qry);
		
		$nListCnt = 0 ;

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['cs_seq'][]			= $Row['cs_seq'];
			$S_RETURNVAL['cs_nm'][]				= $Row['cs_nm'];
			$S_RETURNVAL['ceo_nm'][]			= $Row['ceo_nm'];
			$S_RETURNVAL['cs_num'][]			= $Row['cs_num'];
			$S_RETURNVAL['cs_email'][]			= $Row['cs_email'];
			//$S_RETURNVAL['m_nm'][]				= $Row['m_nm'];

			$S_RETURNVAL['cs_m_id'][]			= $Row['cs_m_id'];      
			$S_RETURNVAL['existing_em_nm'][]	= $Row['existing_em_nm'];
			$S_RETURNVAL['change_em_nm'][]		= $Row['change_em_nm'];    
			$S_RETURNVAL['send_date'][]			= $Row['send_date'];
			$S_RETURNVAL['send_time'][]			= $Row['send_time']; 
			$S_RETURNVAL['cs_agree_state'][]	= $Row['cs_agree_state'];
			$S_RETURNVAL['cs_comment'][]		= $Row['cs_comment'];       
			$S_RETURNVAL['hm_comment'][]		= $Row['hm_comment'];       
			$S_RETURNVAL['cs_agree_date'][]		= $Row['cs_agree_date'];
			$S_RETURNVAL['cs_agree_time'][]		= $Row['cs_agree_time'];
			$S_RETURNVAL['hm_agree_state'][]	= $Row['hm_agree_state'];   
			$S_RETURNVAL['hm_agree_emp'][]		= $Row['hm_agree_emp'];
			$S_RETURNVAL['hm_agree_date'][]		= $Row['hm_agree_date'];
			$S_RETURNVAL['hm_agree_time'][]		= $Row['hm_agree_time'];

			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;

			switch($Row['m_nm'])	{
				case "264,"		: $S_RETURNVAL['m_nm'][] = "네이버"; break;
				case "41,"		: $S_RETURNVAL['m_nm'][] = "클릭스"; break;
				case "496,"		: $S_RETURNVAL['m_nm'][] = "이베이"; break;
				case "881,"		: $S_RETURNVAL['m_nm'][] = "모먼트"; break;
				case "265,"		: $S_RETURNVAL['m_nm'][] = "구글"; break;
				case "845,"		: $S_RETURNVAL['m_nm'][] = "페이스북"; break;
				case "302,438"	: $S_RETURNVAL['m_nm'][] = "TG"; break;
				case "302,454"	: $S_RETURNVAL['m_nm'][] = "크리테오"; break;
				case "808,"		: $S_RETURNVAL['m_nm'][] = "11번가"; break;
				case "302,820"	: $S_RETURNVAL['m_nm'][] = "티몬"; break;
				case "302,847"	: $S_RETURNVAL['m_nm'][] = "위메프"; break;
				case "302,844"	: $S_RETURNVAL['m_nm'][] = "인터파크"; break;
				case "302,885"	: $S_RETURNVAL['m_nm'][] = "신세계"; break;
				case "302,891"	: $S_RETURNVAL['m_nm'][] = "롯데닷컴"; break;
				default : $S_RETURNVAL['m_nm'][] = $Row['m_nm'];break;
			}

		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		//echo $Qry1;
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);
				
		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
	
		que_close();

	}
	//---내부이관프로세스 목록-------------------------------------------

	//---내부이관프로세스 셀렉트-------------------------------------------
	public function funcSelectInsideTransferRequest(){

		$crypt_seq = funcResponse($_GET['cs_request']);
		 
		$Qry = "SELECT * FROM t_inside_transfer_request WHERE crypt_seq = '".$crypt_seq."'";

		//echo $Qry;
		$Rst = que($Qry);
		
		$nListCnt = 0 ;

		$Row = mysql_fetch_assoc($Rst);

		$S_RETURNVAL['seq']				= $Row['seq'];
		$S_RETURNVAL['cs_seq']			= $Row['cs_seq'];
		$S_RETURNVAL['cs_nm']			= $Row['cs_nm'];
		$S_RETURNVAL['ceo_nm']			= $Row['ceo_nm'];
		$S_RETURNVAL['cs_num']			= $Row['cs_num'];
		$S_RETURNVAL['cs_email']		= $Row['cs_email'];
		//$S_RETURNVAL['m_nm']			= $Row['m_nm'];

		$S_RETURNVAL['cs_m_id']			= $Row['cs_m_id'];      
		$S_RETURNVAL['existing_em_nm']	= $Row['existing_em_nm'];
		$S_RETURNVAL['change_em_nm']	= $Row['change_em_nm'];    
		$S_RETURNVAL['send_date']		= $Row['send_date'];
		$S_RETURNVAL['send_time']		= $Row['send_time']; 
		$S_RETURNVAL['cs_agree_state']	= $Row['cs_agree_state'];
		$S_RETURNVAL['cs_comment']		= $Row['cs_comment'];       
		$S_RETURNVAL['cs_agree_date']	= $Row['cs_agree_date'];		
		$S_RETURNVAL['hm_agree_state']	= $Row['hm_agree_state'];   
		$S_RETURNVAL['hm_agree_emp']	= $Row['hm_agree_emp'];
		$S_RETURNVAL['hm_agree_date']	= $Row['hm_agree_date'];

		$S_RETURNVAL['reg_date']		= $Row['reg_date'];
		$S_RETURNVAL['reg_time']		= $Row['reg_time'];
		$S_RETURNVAL['reg_emp']			= $Row['reg_emp'];

		switch($Row['m_nm'])	{
			case "264,"		: $S_RETURNVAL['m_nm'] = "네이버"; break;
			case "41,"		: $S_RETURNVAL['m_nm'] = "클릭스"; break;
			case "496,"		: $S_RETURNVAL['m_nm'] = "이베이"; break;
			case "881,"		: $S_RETURNVAL['m_nm'] = "모먼트"; break;
			case "265,"		: $S_RETURNVAL['m_nm'] = "구글"; break;
			case "845,"		: $S_RETURNVAL['m_nm'] = "페이스북"; break;
			case "302,438"	: $S_RETURNVAL['m_nm'] = "TG"; break;
			case "302,454"	: $S_RETURNVAL['m_nm'] = "크리테오"; break;
			case "808,"		: $S_RETURNVAL['m_nm'] = "11번가"; break;
			case "302,820"	: $S_RETURNVAL['m_nm'] = "티몬"; break;
			case "302,847"	: $S_RETURNVAL['m_nm'] = "위메프"; break;
			case "302,844"	: $S_RETURNVAL['m_nm'] = "인터파크"; break;
			case "302,885"	: $S_RETURNVAL['m_nm'] = "신세계"; break;
			default : $S_RETURNVAL['m_nm'] = $Row['m_nm'];break;
		}
	
		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;
	
		que_close();

	}
	//---내부이관프로세스 셀렉트-------------------------------------------

	//---광고주 동의여부 등록 -------------------------------------------
	public function funcCustInsideTransferRequest(){

		$crypt_seq = funcResponse($_POST['cs_request']);
		 
		$Qry1 = "SELECT cs_nm, reg_emp FROM t_inside_transfer_request WHERE crypt_seq = '".$crypt_seq."'";
		$Qry2 = "UPDATE t_inside_transfer_request set cs_ip='".$_SERVER['REMOTE_ADDR']."', 
						cs_agree_state = '".$_GET['cs_agree_state']."', 
						cs_agree_date = CURDATE(), 
						cs_agree_time = CURTIME() , 
						cs_comment = '".nl2br($_POST['cs_comment'])."'
				 WHERE crypt_seq = '".$crypt_seq."'";

		//echo $Qry;
		$Rst1 = que($Qry1);
		$Rst2 = que($Qry2);

		$Row1 = mysql_fetch_assoc($Rst1);

		$to			= funSelectEmpEmail($Row1['reg_emp'])."@hmcorp.co.kr";
		$to1		= "hm_dev@hmcorp.co.kr";
		$fromnm		= "트리플하이엠";
		$from		= "hm@hmcorp.co.kr";
		$subject	= "[트리플하이엠] 내부이관 담당자 변경 동의여부가 등록되었습니다.";
		$contents	= "
[RMS] 담당자 동의여부가 등록되었습니다.<p/>

".$Row1['cs_nm']." 광고주님의 담당자 동의여부가 등록되었습니다.<br/>
확인하십시오.<p/>

RMS 내부이관목록으로 바로가기  : <a href='http://rms.hmcorp.co.kr/work/?nc=18&category=5'>바로가기</a><p/>";

		$ok = funSendMail($to,$fromnm,$from,$subject,$contents,1,$to1);
		
		if($Rst2){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		//echo $Rst;

		return $N_RETURNVAL;

		que_close();

	}
	//---광고주 동의여부 등록-------------------------------------------

	//---HM 승인여부 -------------------------------------------
	public function funcHMAgreementInsideTransferRequest(){
		 
		$Qry1 = "SELECT cs_nm, reg_emp FROM t_inside_transfer_request WHERE seq = '".$_GET['seq']."'";
		$Qry2 = "UPDATE t_inside_transfer_request set hm_agree_state = '".$_GET['hm_agree_state']."', hm_agree_date = CURDATE(), hm_agree_emp=".$_SESSION['s_em_seq'].", hm_agree_time = CURTIME() WHERE seq = '".$_GET['seq']."'";

		//echo $Qry;
		$Rst1 = que($Qry1);
		$Rst2 = que($Qry2);

		$Row1 = mysql_fetch_assoc($Rst1);

		$to			= funSelectEmpEmail($Row1['reg_emp'])."@hmcorp.co.kr";
		$to1		= "hm_dev@hmcorp.co.kr";
		$fromnm		= "트리플하이엠";
		$from		= "hm@hmcorp.co.kr";
		$subject	= "[트리플하이엠] 내부이관 담당자 변경 확인여부가 등록되었습니다.";
		$contents	= "
[RMS] 담당자 변경 확인여부가 등록되었습니다.<p/>

".$Row1['cs_nm']." 광고주님의 담당자 변경 확인여부가 등록되었습니다.<br/>
확인하십시오.<p/>

RMS 내부이관목록으로 바로가기  : <a href='http://rms.hmcorp.co.kr/work/?nc=18&category=5'>바로가기</a><p/>";

		$ok = funSendMail($to,$fromnm,$from,$subject,$contents,1,$to1);
		
		if($Rst2){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}
		//echo $Rst;

		return $N_RETURNVAL;

		que_close();
	}
	//---HM 승인여부 -------------------------------------------


	//---기존 담당자 찾기 시작 -------------------------------------------
	public function funcFindEmp(){

		if($_POST['good_type2']){$where_good_type2	= "AND good_type2='".$_POST['good_type2']."'";}
		if($_POST['cs_m_id'])	{$where_cs_m_id		= "AND cs_m_id='".$_POST['cs_m_id']."'";}
		if($_POST['cs_seq'])	{$where_cs_seq		= "AND cs_seq='".$_POST['cs_seq']."'";}
		if($_POST['m_nm'])		{$where_m_nm		= "AND m_nm='".$_POST['m_nm']."'";}
/*
		$Qry = "SELECT m_nm, cs_m_id, em_seq, division1 
				FROM t_contract 
				WHERE del_date is null 
				AND sales_type='1' 
				AND m_nm='".$_POST['m_nm']."' 
				AND cs_seq='".$_POST['cs_seq']."' 
				$where_good_type2
				$where_cs_m_id
				GROUP BY cs_seq, m_nm, cs_m_id
				ORDER BY ct_seq	DESC";

		$Qry = "SELECT m_nm, cs_seq, cs_m_id, em_seq, division1, ct_seq FROM t_contract 
				WHERE ct_seq = (
					SELECT MAX(ct_seq)
					FROM t_contract 
					WHERE del_date IS NULL  
					AND sales_type IN ('1')  
					AND cs_seq='".$_POST['cs_seq']."' 
					AND m_nm='".$_POST['m_nm']."'
					$where_good_type2
					AND cs_m_id='".$_POST['cs_m_id']."'
					GROUP BY m_nm, cs_seq, cs_m_id
					)
				AND del_date IS NULL
				AND sales_type='1'  
				AND cs_seq='".$_POST['cs_seq']."' 
				AND m_nm='".$_POST['m_nm']."'
				$where_good_type2
				AND cs_m_id='".$_POST['cs_m_id']."'";
*/
		$Qry = "SELECT m_nm, cs_seq, cs_m_id, reg_emp as em_seq, (SELECT division1 FROM t_employee a WHERE a.em_seq=b.reg_emp AND del_date IS NULL) AS division1 
				FROM t_customer_md b
				WHERE del_date IS NULL 
				AND cs_seq='".$_POST['cs_seq']."' 
				AND m_nm='".$_POST['m_nm']."'
				$where_good_type2
				AND cs_m_id='".$_POST['cs_m_id']."'
				GROUP BY m_nm, cs_seq, cs_m_id";


		//echo $Qry;
		$Rst = que($Qry);
		$Row = mysql_fetch_assoc($Rst);

		if($Row['division1']=='6'){return "-2";}

		if($Row['m_nm']){
			return $Row['em_seq'];
		}else{
			return "-1";
		}		

		//print_R($S_RETURNVAL);
		//return $N_RETURNVAL;
		que_close();
	}
	//---기존 담당자/해당 본부/해당 본부장 찾기 끝 ---------------------------------------------


// ------------123---------------
	//----매체공지.취합 히스토리 등록 시작----
	public function funcCheckForm(){

		ini_set('memory_limit','-1'); // 메모리 제한 해제 

		ob_start(); 

		setlocale(LC_CTYPE, "ko_KR.eucKR");
//		setlocale(LC_CTYPE, "ko_KR.utf-8");

		$dir_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_file/";

		if(preg_match("/(\.(csv|CSV))$/i", $_FILES['file_nm']['name'])){
			if($_FILES){		
				/*공지, 취합파일 업로드*/
				if( $_FILES['file_nm']) {
					$files_upload_tmp	= $_FILES['file_nm']['tmp_name'];
					$files_upload_nm	= $_FILES['file_nm']['name'];				
					$files_upload_size	= $_FILES['file_nm']['size'];

					$file_info = pathinfo($files_upload_nm);

					$file_name_real = preg_replace( '/,/', '_', $file_info['basename']);
					$file_name_real = preg_replace( '/\s+/', '', $file_name_real);
					$file_name_real = $files_upload_nm;	
					$file_name_new	= strtotime("now").".".$file_info['extension'];
					
					move_uploaded_file($files_upload_tmp,$dir_path.$file_name_new);

					if($_FILES['file_nm']){

						$B_RETURNVAL = 1;
					
						$handle = fopen($dir_path.$file_name_new, "r"); // 파일읽기

						while($data = fgetcsv($handle, 1000)){
							foreach($data as $val){
								$val = iconv("euckr", "utf8", $val);
								$datas[] = $val;
							}
							$temp[] = $datas; //임시배열 데이터 넣어주기
							$datas = ''; // 임시배열 비워주기
						}
					}else{
						$B_RETURNVAL = -1;
						exit();
					}
	//print_r($temp);
				}
				/*공지, 취합파일 업로드*/
				$upload_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_etcFile/";			
				if( $_FILES['etc_file_nm']){	
						$etc_files_upload_tmp	= $_FILES['etc_file_nm']['tmp_name'][0];
						$etc_files_upload_nm	= $_FILES['etc_file_nm']['name'][0];				
						$etc_files_upload_size	= $_FILES['etc_file_nm']['size'][0];
						$etc_file_info = pathinfo($etc_files_upload_nm[0]);
						$etc_file_name_real = preg_replace( '/,/', '_', $etc_file_info['basename']);
						$etc_file_name_real = preg_replace( '/\s+/', '', $etc_file_name_real);
						$etc_file_name_real = $etc_files_upload_nm;
						$etc_file_name_new	= strtotime("now").".".$etc_file_info['extension'];
					/*기타파일 업로드 1번째 파일 업로드*/
						move_uploaded_file($_FILES['etc_file_nm']['tmp_name'][0], $upload_path.iconv("UTF-8","EUC-KR",basename($_FILES['etc_file_nm']['tmp_name'][0])));
					/*기타파일 업로드 1번째 파일 업로드*/
				}
			}
			//t_project_history insert
			$Qry = "INSERT INTO t_project_history (
						project_nm,
						file_nm,
						file_nm_new,
						etc_file_nm,
						etc_file_nm_new,
						start_date,
						end_date,
						end_time,
						import_is,
						project_type,
						project_type2,
						open_yn,
						project_memo,
						reg_date,
						reg_time,
						reg_emp
					) VALUES (
						'".$_POST['project_nm']."',
						'".$file_name_real."',	
						'".$file_name_new."',		
						'".$etc_file_name_real."',	
						'".$etc_file_name_new."',		
						'".$_POST['start_date']."',
						'".$_POST['end_date']."',
						'".$_POST['end_time']."',
						'".$_POST['import_is']."',
						'".$_POST['project_type']."',
						'".$_POST['project_type2']."',
						'".$_POST['open_yn']."',
						'".$_POST['project_memo']."',
						CURDATE(),
						CURTIME(),
						'".$_SESSION['s_em_seq']."')";	
			$Rst = que($Qry);
			$ph_seq = mysql_insert_id();

			//t_project_title_setting INSERT
	// ------------123---------------
			if($_FILES['etc_file_nm']){
				$upload_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_etcFile/";

				for($i=0; $i < count($_FILES['etc_file_nm']['name']); $i++){
					$etc_tmp	= $_FILES['etc_file_nm']['tmp_name'][$i];
					$etc_nm		= $_FILES['etc_file_nm']['name'][$i];		
					$name = basename($etc_nm);
					$tmp_name = basename($etc_tmp);
					/*기타파일 업로드 2번째 이후 파일 업로드*/
					move_uploaded_file($_FILES['etc_file_nm']['tmp_name'][$i], $upload_path.iconv("UTF-8","EUC-KR",basename($_FILES['etc_file_nm']['tmp_name'][$i])));
					/*기타파일 업로드 2번째 이후 파일 업로드*/
				if($etc_nm !== ''){
					$QryEtc = "INSERT INTO t_project_history_etcfile (
											project_history_seq,
											etc_names,
											etc_tmp_names,
											file_regdate,
											file_regtime
										) VALUES (
											'".$ph_seq."',
											'".$etc_nm."',
											'".$tmp_name."',
											CURDATE(),
											CURTIME())";

					$RstEtc = que($QryEtc);
				}
				}
	}

			//exit();

			//1열을 제목으로 인서트
	$titleCnt = 1;
			foreach($temp[0]  as $k=>$v){
	// utf-8 인것으로 확인 되었음
				$title_kr_name = $v;

				$Qry1 = "INSERT INTO t_project_title_setting (
							ph_seq,
							title,
							title_kr_name,
							open_yn,
							reg_date,
							reg_time,
							reg_emp
						) VALUES (
							'".$ph_seq."',
							'title$titleCnt',
							'$title_kr_name',
							'Y',
							CURDATE(),
							CURTIME(),
							'".$_SESSION['s_em_seq']."')";
				//echo $Qry1;
				$Rst1 = que($Qry1);

				$title_create .= "`title$titleCnt`  varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,";
				$title_value  .= "title$titleCnt,  ";		
				$titleCnt++;
			}	
			//추가항목 10개 만들기	
			
			for($i=($k+2);$i<=($k+11);$i++){
				$Qry1_3 = "INSERT INTO t_project_title_setting (
							ph_seq,
							title,
							open_yn,
							reg_date,
							reg_time,
							reg_emp
						) VALUES (
							'".$ph_seq."',
							'title$i',
							'N',
							CURDATE(),
							CURTIME(),
							'".$_SESSION['s_em_seq']."')";
				//echo $i.'<br>';
				$Qry1_3 = que($Qry1_3);
				$title_create .= "title$i  varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,";
				//$title_value  .= "title$i,  ";	
				//echo $title_create;
			}


			//t_project_content_(n) CREATE
			$Qry2 = "CREATE TABLE t_project_content_$ph_seq (
						`seq` int(11) NOT NULL AUTO_INCREMENT,
						`ph_seq` int(11) DEFAULT NULL,
						`pts_seq` int(11) DEFAULT NULL,
						 $title_create
						`overplus1`	char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'N' COMMENT 'Y:활성화,N:비활성화',
						`reg_date` date DEFAULT NULL,
						`reg_time` time DEFAULT NULL,
						`reg_emp` int(11) DEFAULT NULL,
						`upd_date` date DEFAULT NULL,
						`upd_time` time DEFAULT NULL,
						`upd_emp` int(11) DEFAULT NULL,
						PRIMARY KEY (`seq`))
					ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
			if(que($Qry2)===TRUE){

				foreach($temp as $k=>$v){			
					foreach($v as $k1=>$v1){
						$value[$k] .=  "'".addslashes($v1)."',";
					}
				}
				//t_project_content_(n) INSERT
				foreach($temp as $k=>$v){
					$Qry3 = "INSERT INTO t_project_content_$ph_seq (
								ph_seq,
								$title_value
								reg_date,
								reg_time,
								reg_emp
							) VALUES (
								'".$ph_seq."',
								".$value[$k+1]."
								CURDATE(),
								CURTIME(),
								'".$_SESSION['s_em_seq']."'
								)";
					que($Qry3);
				//echo '$k : '.$k.'<br>';
				}
	//print_r($temp);
			}else{echo "F";}

		//**----------------------------------------------------------------------------------------------------------------------------
		}else{
			die('확장자가 csv 인 경우만 업로드가 가능합니다.');
		}

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
 
		que_close();

	//**----------------------------------------------------------------------------------------------------------------------------
	}



function isDate($value) 
{
    if (!$value) {
        return false;
    }

    try {
        new \DateTime($value);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

	//----등록 끝----

	//----매체공지.취합 히스토리 리스트 시작----
	public function funcListProjectHistory($nOrdPagingNum){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($nOrdPagingNum=="copy_setting"){}else{$limit = "LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";}

		if($_GET['d_item'] && $_GET['sdate'] && $_GET['edate']){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_SESSION['em_level']>10){$where_open_yn = "AND open_yn='Y'";}

		if($_GET['s_item'] && $_GET['s_string']){
			$where__item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}


if($_GET['project_type']){
	$where_poject_type = "AND project_type like '%".$_GET['project_type']."%'";
}
		if($_GET['project_type2']){
			$where_poject_type2 = "AND project_type2 like '%".$_GET['project_type2']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_project_history
				WHERE del_date IS NULL
				$where_s_item
				$where_d_item
				$where_open_yn
				$where_poject_type
				$where_poject_type2
				ORDER BY seq DESC 
				$limit";
//		echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['project_nm'][]		= $Row['project_nm'];
			$S_RETURNVAL['file_nm'][]			= $Row['file_nm'];
			$S_RETURNVAL['file_nm_new'][]		= $Row['file_nm_new'];
			$S_RETURNVAL['etc_file_nm'][]		= $Row['etc_file_nm'];
			$S_RETURNVAL['etc_file_nm_new'][]	= $Row['etc_file_nm_new'];
			$S_RETURNVAL['start_date'][]		= $Row['start_date'];
			$S_RETURNVAL['end_date'][]			= $Row['end_date'];
			$S_RETURNVAL['end_time'][]			= $Row['end_time'];
			$S_RETURNVAL['import_is'][]			= $Row['import_is'];
			$S_RETURNVAL['project_type'][]		= $Row['project_type'];
			$S_RETURNVAL['project_type2'][]		= $Row['project_type2'];
			$S_RETURNVAL['open_yn'][]			= $Row['open_yn'];
			$S_RETURNVAL['project_memo'][]		= $Row['project_memo'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}

		que_close();
		return $S_RETURNVAL;

		
	}
	//----매체공지.취합 히스토리 리스트 끝----

public function funcSelectEtcFile($id){
	$Qry = "SELECT * FROM t_project_history_etcfile WHERE project_history_seq = '".$id."'";

		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$F_RETURNVAL['seq'][]						= $Row['seq'];
			$F_RETURNVAL['project_history_seq'][]		= $Row['project_history_seq'];
			$F_RETURNVAL['file_name2'][]				= $Row['etc_names'];
			$F_RETURNVAL['etc_name2'][]					= $Row['etc_tmp_names'];
		}
		
		return $F_RETURNVAL;
}



	//----매체공지.취합 히스토리 셀렉트 시작----
	public function funcGetProjectHistory(){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$seq = $_POST['seq']?$_POST['seq']:$_GET['seq'];
//inner join t_project_history_etcfile t2 ON t1.seq = t2.project_history_seq


//		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_project_history t1 left join t_project_history_etcfile t2 ON t1.seq = t2.project_history_seq WHERE t1.del_date IS NULL AND t1.seq='".$seq."'";

$Qry = "SELECT SQL_CALC_FOUND_ROWS t1.seq as seq, project_nm, file_nm, file_nm_new, etc_file_nm, etc_file_nm_new, 
								start_date, end_date, end_time, import_is, project_type, project_type2, open_yn,
								project_memo, reg_date, reg_time, reg_emp, etc_names, etc_tmp_names 
		FROM t_project_history t1 
		left join t_project_history_etcfile t2 ON t1.seq = t2.project_history_seq 
		WHERE t1.del_date IS NULL AND t1.seq='".$seq."'";

		//echo $Qry;
		$Rst = que($Qry);
		while($Row = @mysql_fetch_assoc($Rst)){
		
		$S_RETURNVAL['seq']				= $Row['seq'];

		$S_RETURNVAL['project_nm']		= $Row['project_nm'];
		$S_RETURNVAL['file_nm']			= $Row['file_nm'];
		$S_RETURNVAL['file_nm_new']		= $Row['file_nm_new'];
		$S_RETURNVAL['etc_file_nm']		= $Row['etc_file_nm'];
		$S_RETURNVAL['etc_file_nm_new']	= $Row['etc_file_nm_new'];
		$S_RETURNVAL['start_date']		= $Row['start_date']<>'0000-00-00'?$Row['start_date']:'';
		$S_RETURNVAL['end_date']		= $Row['end_date']<>'0000-00-00'?$Row['end_date']:'';
		$S_RETURNVAL['end_time']		= $Row['end_time'];
		$S_RETURNVAL['import_is']		= $Row['import_is'];
		$S_RETURNVAL['project_type']	= $Row['project_type'];
		$S_RETURNVAL['project_type2']	= $Row['project_type2'];
		$S_RETURNVAL['open_yn']			= $Row['open_yn'];
		$S_RETURNVAL['project_memo']	= $Row['project_memo'];
		$S_RETURNVAL['reg_date']		= $Row['reg_date'];
		$S_RETURNVAL['reg_time']		= $Row['reg_time'];
		$S_RETURNVAL['reg_emp']			= $Row['reg_emp'];

		$S_RETURNVAL['etc_names'][]		= $Row['etc_names'];
		$S_RETURNVAL['etc_tmp_names'][]	= $Row['etc_tmp_names'];
		}
		$resultData						= $S_RETURNVAL;		

		$jsonTable = json_encode($resultData);
		return $jsonTable;

		que_close();
	}
	//----매체공지.취합 히스토리 셀렉트 끝----

	//----매체공지.취합 히스토리 삭제 시작----
	public function funcDelProjectHistory(){

		$Qry = "UPDATE t_project_history SET del_date = CURDATE(), del_time = CURTIME(), del_emp = '".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		$Qry1 = "DROP TABLE t_project_content_".$_POST['seq']."";
		$Quy2 = "DELETE t_project_history_etcfile where project_history_seq = ".$_POST['seq']."";

		//echo $Qry;
		$Rst = que($Qry);
		que($Qry1);
		que($Qury2);

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체공지.취합 히스토리 삭제 끝----

	//----매체공지.취합 히스토리 수정 시작----
	public function funcUpdProjectHistory(){
// ------------123---------------
		require_once $_SERVER['DOCUMENT_ROOT'].'/phpExcelReader/Excel/reader.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP949');
		$dir_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_file/";
		
		//print_R($_FILES);

		if($_FILES){
			$upload_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_etcFile/";
			/*기타파일 업로드*/
			if( $_FILES['etc_file_nm']){
				$etc_files_upload_tmp	= $_FILES['etc_file_nm']['tmp_name'][0];
				$etc_files_upload_nm	= $_FILES['etc_file_nm']['name'][0];				
				$etc_files_upload_size	= $_FILES['etc_file_nm']['size'][0];
				$etc_file_info = pathinfo($etc_files_upload_nm[0]);
				$etc_file_name_real = preg_replace( '/,/', '_', $etc_file_info['basename']);
				$etc_file_name_real = preg_replace( '/\s+/', '', $etc_file_name_real);
				$etc_file_name_real = $etc_files_upload_nm;
				$etc_file_name_new	= strtotime("now").".".$etc_file_info['extension'];
				move_uploaded_file($_FILES['etc_file_nm']['tmp_name'][0], $upload_path.iconv("UTF-8","EUC-KR",basename($_FILES['etc_file_nm']['tmp_name'][0])));
				$QryEtc2 = "INSERT INTO t_project_history_etcfile (
							project_history_seq,
							etc_names,
							etc_tmp_names,
							file_regdate,
							file_regtime
						) VALUES (
							'".$_POST['seq'] ."',
							'".$_FILES['etc_file_nm']['tmp_name'][0]."',
							'".$_FILES['etc_file_nm']['size'][0]."',
							CURDATE(),
							CURTIME())";

				que($QryEtc2);
			/*기타파일 업로드*/	
			}
		}




		if($file_name_real)		{$is_file_nm = "file_nm		= '".$file_name_real."', file_nm_new		= '".$file_name_new."',";}
		if($etc_file_name_real)	{$is_etc_file_nm = "etc_file_nm	= '".$etc_file_name_real."',					etc_file_nm_new	= '".$etc_file_name_new."',";}

		

		$Qry = "UPDATE t_project_history SET 
					project_nm		= '".$_POST['project_nm']."',
					$is_file_nm
					$is_etc_file_nm
					start_date		= '".$_POST['start_date']."',
					end_date		= '".$_POST['end_date']."',
					end_time		= '".$_POST['end_time']."',
					import_is		= '".$_POST['import_is']."',
					project_type	= '".$_POST['project_type']."',
					project_type2	= '".$_POST['project_type2']."',
					open_yn			= '".$_POST['open_yn']."',
					project_memo	= '".$_POST['project_memo']."',
					upd_date		= CURDATE(),
					upd_time		= CURTIME(),
					upd_emp			= '".$_SESSION['s_em_seq']."'
				WHERE seq='".$_POST['seq']."'";

		//echo $Qry;
		$Rst = que($Qry);
		

// ------------123---------------
if($_FILES['etc_file_nm']){

	$upload_path = $_SERVER['DOCUMENT_ROOT']."/data/work_project_etcFile/";

	$QryEtc1 = "DELETE FROM t_project_history_etcfile WHERE project_history_seq = '".$_POST['seq']."'";
	que($QryEtc1);	

	for($i=0; $i < count($_FILES['etc_file_nm']['name']); $i++){
		$etc_tmp	= $_FILES['etc_file_nm']['tmp_name'][$i];
		$etc_nm		= $_FILES['etc_file_nm']['name'][$i];		
		$name = basename($etc_nm);
		$tmp_name = basename($etc_tmp);
		/*기타파일 업로드 2번째 이후 파일 업로드*/
				move_uploaded_file($_FILES['etc_file_nm']['tmp_name'][$i], $upload_path.iconv("UTF-8","EUC-KR",basename($_FILES['etc_file_nm']['tmp_name'][$i])));
		/*기타파일 업로드 2번째 이후 파일 업로드*/

	

		$QryEtc2 = "INSERT INTO t_project_history_etcfile (
								project_history_seq,
								etc_names,
								etc_tmp_names,
								file_regdate,
								file_regtime
							) VALUES (
								'".$_POST['seq'] ."',
								'".$etc_nm."',
								'".$tmp_name."',
								CURDATE(),
								CURTIME())";

		que($QryEtc2);

//없데이트가 아니라 delete후 insert로 수정
	}
}



		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체공지.취합 히스토리 수정 끝----


	//----매체공지.취합 항목 설정 시작----
	public function funcSelectProjectTitleSetting(){
		$Qry = "SELECT * FROM t_project_title_setting WHERE ph_seq='".$_GET['seq']."'";
		//echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['ph_seq'][]			= $Row['ph_seq'];
			$S_RETURNVAL['title'][]				= $Row['title'];
			$S_RETURNVAL['title_memo'][]		= $Row['title_memo'];
			$S_RETURNVAL['title_kr_name'][]		= $Row['title_kr_name'];
			$S_RETURNVAL['open_yn'][]			= $Row['open_yn'];
			$S_RETURNVAL['activation_yn'][]		= $Row['activation_yn'];
			$S_RETURNVAL['input_type'][]		= $Row['input_type'];
			$S_RETURNVAL['character_type'][]	= $Row['character_type'];
			$S_RETURNVAL['option_type'][]		= $Row['option_type'];
			$S_RETURNVAL['necessary_yn'][]		= $Row['necessary_yn'];
			$S_RETURNVAL['bg_color'][]			= $Row['bg_color'];
			$S_RETURNVAL['connection_title'][]	= $Row['connection_title'];
			$S_RETURNVAL['connection_option'][]	= $Row['connection_option'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}
		$S_RETURNVAL['nListCnt'] = $nListCnt;

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		que_close();
	}
	//----매체공지.취합 항목 설정 끝----
	

	//----매체공지.취합 옵션 리스트 시작----
	public function funcListProjectOption($nOrdPagingNum){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;
		
		if($_GET['d_item'] && $_GET['sdate']  && $_GET['d_edateitem'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_project_option WHERE del_date IS NULL
				$where_s_item
				$where_d_item
				ORDER BY seq DESC LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		//echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['option_code'][]		= $Row['option_code'];
			$S_RETURNVAL['option_code_nm'][]	= $Row['option_code_nm'];
			$S_RETURNVAL['option_value'][]		= $Row['option_value'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];


		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		que_close();
	}
	//----매체공지.취합 옵션 리스트 끝----

	//----매체공지.취합 옵션 셀렉트 시작----
	public function funcSelectProjectOption(){
		
		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_project_option WHERE seq='".$_POST['seq']."'";
		//echo $Qry;
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);

		$S_RETURNVAL['seq']				= $Row['seq'];
		$S_RETURNVAL['option_code']		= $Row['option_code'];
		$S_RETURNVAL['option_code_nm']	= $Row['option_code_nm'];
		$S_RETURNVAL['option_value']	= $Row['option_value'];
		$S_RETURNVAL['reg_date']		= $Row['reg_date'];
		$S_RETURNVAL['reg_time']		= $Row['reg_time'];
		$S_RETURNVAL['reg_emp']			= $Row['reg_emp'];

		$resultData						= $S_RETURNVAL;		

		$jsonTable = json_encode($resultData);
		return $jsonTable;

		que_close();
	}
	//----매체공지.취합 옵션 셀렉트 끝----

	//----매체공지.취합 옵션 수정 시작----
	public function funcUpdProjectOption(){

		$Qry = "UPDATE t_project_option SET
				option_code		='".$_POST['option_code']."', 
				option_code_nm	='".$_POST['option_code_nm']."', 
				option_value	='".$_POST['option_value']."', 
				upd_date		= CURDATE(),
				upd_time		= CURTIME(),
				upd_emp			= '".$_SESSION['s_em_seq']."'
				WHERE seq='".$_POST['seq']."'";
		
		//echo $Qry;
		$Rst = que($Qry);

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체공지.취합 옵션 수정 끝----

	//----매체공지.취합 옵션 삭제 시작----
	public function funcDelProjectOption(){

		$Qry = "UPDATE t_project_option SET
				del_date		= CURDATE(),
				del_time		= CURTIME(),
				del_emp			= '".$_SESSION['s_em_seq']."'
				WHERE seq='".$_POST['seq']."'";
		
		$Rst = que($Qry);

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체공지.취합 옵션 삭제 끝----

	//----매체공지.취합 옵션 등록 시작----
	public function funcIptProjectOption(){

		if($_POST['option_value']){
			if(strpos($_POST['option_value'],",")!==false){ //콤마포함
				$option_value = explode(",",$_POST['option_value']);
			}else{
				$option_value = $_POST['option_value'];
			}
		}

		if(is_array($option_value)){
			foreach($option_value as $v){
				$Qry = "INSERT INTO t_project_option
						(option_code, option_code_nm, option_value, reg_date, reg_time, reg_emp)
						VALUES 
						('".$_POST['option_code']."','".$_POST['option_code_nm']."','".$v."',CURDATE(), CURTIME(),'".$_SESSION['s_em_seq']."')";
					
				//echo $Qry;
				$Rst = que($Qry);
			}
		}else{
			$Qry = "INSERT INTO t_project_option
				(option_code, option_code_nm, option_value, reg_date, reg_time, reg_emp)
				VALUES 
				('".$_POST['option_code']."','".$_POST['option_code_nm']."','".$_POST['option_value']."',CURDATE(), CURTIME(),'".$_SESSION['s_em_seq']."')";
			//echo $Qry;
			$Rst = que($Qry);	
		}

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;

		que_close();
	}
	//----매체공지.취합 옵션 등록 끝----


	//----매체공지.취합 옵션 리스트 시작----
	public function funcArrayProjectOption($code,$value){

		if($code){$code="AND option_code=".$code."";}
		if($value){$value="AND option_code_num=".$value."";}

		$Qry = "SELECT * FROM t_project_option WHERE del_date IS NULL $code $value";
		//echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['optoin_value'][$Row['option_code']][$Row['seq']] = $Row['option_value'];
			//$S_RETURNVAL['optoin_code'][$Row['option_code']] = $Row['option_code']." ".$Row['option_code_nm'];
			$S_RETURNVAL['option_code_nm'][$Row['option_code']] = $Row['option_code_nm'];

			$S_RETURNVAL['optoin_code'][$Row['option_code']][] = $Row['option_code'].",".$Row['seq'].",".$Row['option_value'];
		}

		$S_RETURNVAL['code_cnt'] = count($S_RETURNVAL['optoin_value']);
		return $S_RETURNVAL;

		que_close();
	}
	//----매체공지.취합 옵션 리스트 끝----


	//----매체공지.취합 항목 설정 수정 시작----
	public function funcUptProjectTitleSetting(){
		
		for($i=0;$i<$_POST['cnt'];$i++){

		$connection_title =	explode(",",$_POST['connection_title'.$i]);

		$Qry = "UPDATE t_project_title_setting SET 
				title_memo			= '".$_POST['title_memo'.$i]."',
				title_kr_name		= '".$_POST['title_kr_name'.$i]."',
				open_yn				= '".$_POST['open_yn'.$i]."',
				activation_yn		= '".$_POST['activation_yn'.$i]."',
				input_type			= '".$_POST['input_type'.$i]."',
				character_type		= '".$_POST['character_type'.$i]."',
				option_type			= '".$_POST['option_type'.$i]."',
				necessary_yn		= '".$_POST['necessary_yn'.$i]."',
				bg_color			= '".$_POST['bg_color'.$i]."',
				connection_title	= '".$connection_title[1]."',
				connection_option	= '".$_POST['connection_option'.$i]."'
				WHERE seq			= '".$_POST['seq'.$i]."'";
		//echo $Qry;
		$Rst = que($Qry);

		}
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		que_close();

		return $N_RETURNVAL; 
	}

	//----매체공지.취합 항목 설정 수정 끝----


	//----매체공지.취합 항목 내용 시작----
	public function funcSelectProjectContent($nOrdPagingNum){

		if($_GET['overplus1']=="Y")	{$where_overplus1		= "AND upd_emp IS NOT NULL";}else if($_GET['overplus1']=="N") {$where_overplus1		= "AND upd_emp IS NULL";}
		
		if($nOrdPagingNum!="down"){
			if(!$_GET['nPage']) $_GET['nPage'] = 1;
			$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

			$limit = "LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		}

		if($_SESSION['em_level']==10){
			if($_GET["type_member"]=="2"){
				if($_GET['em_nm'])		{$where_em_seq		= "AND title3    ='".$_GET['em_nm']."'";}
				if($_GET['em_seq1'])	{$where_em_seq1		= "AND title3    ='".$_GET['em_seq1']."'";}

				if($_GET['division1']){
				$division1 = implode(",",$_GET["division1"]);
				$where_division1 = "AND title1 IN (".$division1.")";
				}

				if($_GET['division2']){
					$division2 = implode(",",$_GET["division2"]);
					$where_division2 = "AND title2 IN (".$division2.")";
				}

			}else{

				$get_emseq	= funGetEmSeq($_GET['division1'],$_GET['division2']);
				$join_emseq = join($get_emseq,",");

				$where_division1 = "";
				$where_division2 = "";
				$_GET['em_seq']==""?$where_em_seq = "AND title3 IN (".$join_emseq.")":$where_em_seq= "AND title3 ='".$_GET['em_seq']."'";
				//$_GET['em_seq']==""?$where_em_seq1 = "":$where_em_seq1= "AND title3 ='".$_GET['em_seq']."'";
				
			}
		/*
		}else if($_SESSION['em_level']==20){
			if($_GET["type_member"]=="2"){		
				$where_division1 = "AND division1 ='".$_SESSION['em_division1']."'";
				
				if($_GET['division2'])	{$where_division2	= "AND division2 ='".$_GET['division2']."'";}
				if($_GET['em_nm'])		{$where_em_seq		= "AND em_seq    ='".$_GET['em_nm']."'";}
				if($_GET['em_seq1'])	{$where_em_seq1		= "AND em_seq    ='".$_GET['em_seq1']."'";}
			}else{
				$get_emseq	= funGetEmSeq($_SESSION['em_division1'],$_GET['division2']);
				$join_emseq = join($get_emseq,",");

				$where_division1 = "";
				$where_division2 = "";
				$_GET['em_nm']==""?$where_em_seq = "AND em_seq IN (".$join_emseq.")":$where_em_seq= "AND em_seq ='".$_GET['em_seq1']."'";
				$_GET['em_seq']==""?$where_em_seq1 = "":$where_em_seq1= "AND em_seq ='".$_GET['em_seq']."'";
			}
		*/
		}else if($_SESSION['em_level']==30){
			if($_GET["type_member"]=="2"){
				$where_division2 = "AND title1 ='".$_SESSION['em_division1']."' 
									AND title2 ='".$_SESSION['em_division2']."'";

				if($_GET['em_seq'])		{$where_em_seq		= "AND title3    ='".$_GET['em_seq']."'";}
				if($_GET['em_seq1'])	{$where_em_seq1		= "AND title3    ='".$_GET['em_seq1']."'";}

				//$get_emseq	= funGetEmSeq($_SESSION['em_division1'],$_SESSION['em_division2']);
				
			}else{
				$get_emseq	= funGetEmSeq($_SESSION['em_division1'],$_SESSION['em_division2']);
				$join_emseq = join($get_emseq,",");

				$where_division1 = "";
				$where_division2 = "";
				$_GET['em_seq']==""?$where_em_seq = "AND title3 IN (".$join_emseq.")":$where_em_seq= "AND title3 ='".$_GET['em_seq']."'";
				//$_GET['em_seq']==""?$where_em_seq1 = "":$where_em_seq1= "AND title3 ='".$_GET['em_seq']."'";

			}
		/*
		}else if($_SESSION['em_level']==40){
			*/
		
		//3본부는 팀장이 전체 열람 가능
		/*
		}else if($_SESSION['em_level']=='30' && $_SESSION['em_division1']=='618'){
			/*
			$get_emseq	= funGetEmSeq($_SESSION['em_division1'],null);
			$join_emseq = join($get_emseq,",");

			$where_division1 = "";
			$where_division2 = "";
			$_GET['em_seq']==""?$where_em_seq = "AND title3 IN (".$join_emseq.")":$where_em_seq= "AND title3 ='".$_GET['em_seq']."'";
			*/
			/*
			$where_division2 = "AND title1 ='".$_SESSION['em_division1']."' AND title2 ='".$_SESSION['em_division2']."'";

			if($_GET['em_seq'])		{$where_em_seq		= "AND title3    ='".$_GET['em_seq']."'";}
			if($_GET['em_seq1'])	{$where_em_seq1		= "AND title3    ='".$_GET['em_seq1']."'";}
		*/
		}else{
			$where_em_seq = "AND title3 = '".$_SESSION['s_em_seq']."'";
		}	
//				".$where_em_seq."
		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_project_content_".$_GET['seq']." WHERE 1=1
				".$where_division1."
				".$where_division2."
				".$where_em_seq1."
				".$where_overplus1."
				order by title3 ASC
				".$limit." ";
		
		//echo $Qry."<br/>";
		$Rst = que($Qry);    

		$numfields = mysql_num_fields($Rst);

		for ($i = 0; $i<$numfields; $i += 1) {
			$field = mysql_fetch_field($Rst, $i);
			//echo '<th>' . $field->name . '</th>';
			$resultData['title'][$field->name] = $field->name;
		}

		while ($fielddata = mysql_fetch_array($Rst)) {
			for ($i = 0; $i<$numfields; $i += 1) {
				$field = mysql_fetch_field($Rst, $i);			
				$new_row[$resultData['title'][$field->name]] = $fielddata[$field->name];
			}
			$resultData['content'][]=$new_row; 
		}
		
		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$resultData['N_CNT'] = $Row1['N_CNT'];

		return $resultData; 

		que_close();

	}

	//----매체공지.취합 항목 내용 끝----

	//----매체공지.취합 항목 설정 가져오기 시작----
	public function funcJoinProjectGetTitle(){
		$Qry = "SELECT * FROM t_project_title_setting WHERE ph_seq = '".$_GET['seq']."'";

		//echo $Qry."<br>".$Qry2;

		$Rst = que($Qry);    
		
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][$Row['title']]				= $Row['seq'];
			$S_RETURNVAL['ph_seq'][]						= $Row['ph_seq'];
			$S_RETURNVAL['title'][]							= $Row['title'];
			$S_RETURNVAL['title_memo'][$Row['title']]		= $Row['title_memo'];
			$S_RETURNVAL['title_kr_name'][$Row['title']]	= $Row['title_kr_name'];
			$S_RETURNVAL['open_yn'][$Row['title']]			= $Row['open_yn'];
			$S_RETURNVAL['activation_yn'][$Row['title']]	= $Row['activation_yn'];
			$S_RETURNVAL['input_type'][$Row['title']]		= $Row['input_type'];
			$S_RETURNVAL['character_type'][$Row['title']]	= $Row['character_type'];
			$S_RETURNVAL['option_type'][$Row['title']]		= $Row['option_type'];
			$S_RETURNVAL['necessary_yn'][$Row['title']]		= $Row['necessary_yn'];
			$S_RETURNVAL['bg_color'][$Row['title']]			= $Row['bg_color'];
			$S_RETURNVAL['connection_title'][$Row['title']]	= $Row['connection_title'];
			$S_RETURNVAL['connection_option'][$Row['title']]= $Row['connection_option'];
			
			//$resultData[]=$S_RETURNVAL; 
		}
		
		return $S_RETURNVAL; 
		que_close();
	}
	//----매체공지.취합 항목 설정 가져오기 끝----


	//----매체공지.취합 내용 수정 시작----
	public function funcUpdProjectContent(){

		//echo "title3_4::".$_POST['title3_4'];
		//print_R($_POST);

		$Qry = "SELECT * FROM t_project_content_".$_POST['seq']."";
		
		//echo $Qry."<br/>";
		$Rst = que($Qry);    

		$numfields = mysql_num_fields($Rst);

		for ($i = 0; $i<$numfields; $i += 1) {
			$field = mysql_fetch_field($Rst, $i);
			//echo '<th>' . $field->name . '</th>';
			$resultData['title'][$field->name] = $field->name;
			//$resultData['title'][] = $field->name;
		}
		
		//체크된 수만큼 돌리기
		foreach($_POST['check_list'] as $k=>$v){		
			//echo "k::".$k;
			//echo "v::".$v;			

			//어드민 수정시
			if($_POST['title1_'.$v]){
				$i=4;
				while ($i <= $_POST['cnt_pos_title']) {	
					
					//활성화되어 있는 항목만 업데이트
					if($_POST['activation_yn_title'.$i."_".$v]=="Y"){
						$Qry = "UPDATE t_project_content_".$_POST['seq']."
								SET title".$i." = '".$_POST['title'.$i."_".$v]."'
								WHERE seq='".$v."'";

						//echo $Qry;
						$Rst = que($Qry);    
					}
						
				$i++;
				}

				for($ii=1;$ii<=3;$ii++){
						$Qry = "UPDATE t_project_content_".$_POST['seq']."
								SET title".$ii." = '".$_POST['title'.$ii."_".$v]."'
								WHERE seq='".$v."'";

						//echo $Qry;
						$Rst = que($Qry);    
				}

			//마케터 수정시
			} else {
				$i=4;
				while ($i <= $_POST['cnt_pos_title']) {	

					if($_POST['activation_yn_title'.$i."_".$v]=="Y"){
						$Qry = "UPDATE t_project_content_".$_POST['seq']."
								SET title".$i." = '".$_POST['title'.$i."_".$v]."'
								WHERE seq='".$v."'";

						//echo $Qry;
						$Rst = que($Qry);    
					}
						
				$i++;
				}

			}

			//마케터 수정시
/*

			//어드민이 수정시 i=1, 마케터가 수정시 i=4
			if($_POST['title1_'.$v]){$i=1;}else{$i=4;}
			$i=4;
			while ($i <= $_POST['cnt_pos_title']) {	

				if($_POST['activation_yn_title'.$i."_".$v]=="Y"){
					$Qry = "UPDATE t_project_content_".$_POST['seq']."
							SET title".$i." = '".$_POST['title'.$i."_".$v]."'
							WHERE seq='".$v."'";

					echo $Qry;
					$Rst = que($Qry);    
				}
					
			$i++;
			}
*/

			if($_POST['overplus1'."_".$v]){$overplus1 = ",overplus1 = '".$_POST['overplus1'."_".$v]."'";}
			$Qry1 = "UPDATE t_project_content_".$_POST['seq']." 
						SET
						upd_date = CURDATE()
						,upd_time = CURTIME()
						,upd_emp = '".$_SESSION['s_em_seq']."'
						$overplus1
						WHERE seq='".$v."'";
			//echo $Qry1;
			$Rst1 = que($Qry1);

		}

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		que_close();

		return $N_RETURNVAL; 
	}
	//----매체공지.취합 내용 수정 끝----


// ------------123---------------
//------------------개별행 삭제 매체공지.취합내용-------------------------------
	public function funcDelProjectContent(){
		
		$Qry = "SELECT * FROM t_project_content_".$_POST['seq']."";
		$Rst = que($Qry);    
		$numfields = mysql_num_fields($Rst);
		for ($i = 0; $i<$numfields; $i += 1) {
			$field = mysql_fetch_field($Rst, $i);
			$resultData['title'][$field->name] = $field->name;
		}
		foreach($_POST['check_list'] as $k=>$v){		
			$Qry1 = "DELETE FROM t_project_content_".$_POST['seq']." 
						WHERE seq='".$v."'";
			$Rst1 = que($Qry1);
		}


		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		que_close();

		return $N_RETURNVAL; 
	}


	//----매체공지.취합 항목 설정 복사 시작----
	public function funcCopyTitleSetting(){
		
		$Qry1  = "SELECT * FROM (
					SELECT 1 AS t, title, title_kr_name FROM t_project_title_setting WHERE ph_seq='".$_POST['copy_seq']."'
					UNION ALL
					SELECT 2 AS t, title, title_kr_name FROM t_project_title_setting WHERE ph_seq='".$_POST['now_seq']."'
				) AS aa";

		$Qry2 = "UPDATE t_project_title_setting as t1, 
					(SELECT seq, ph_seq, title, title_kr_name, title_memo, open_yn, activation_yn, input_type, character_type, option_type, necessary_yn, bg_color, connection_title, connection_option 
						FROM t_project_title_setting 
						WHERE ph_seq='".$_POST['copy_seq']."') as t2
				SET t1.open_yn=t2.open_yn, 
					t1.activation_yn=t2.activation_yn, 
					t1.input_type=t2.input_type, 
					t1.character_type=t2.character_type, 
					t1.option_type=t2.option_type, 
					t1.necessary_yn=t2.necessary_yn, 
					t1.bg_color=t2.bg_color, 
					t1.connection_title=t2.connection_title, 
					t1.connection_option=t2.connection_option,
					t1.title_memo=t2.title_memo,
					t1.title_kr_name=t2.title_kr_name
				WHERE t1.ph_seq = '".$_POST['now_seq']."' AND t1.title=t2.title";

		//echo $Qry;
		$Rst1 = que($Qry1);
		
		while ($Row1 = @mysql_fetch_assoc($Rst1)) {
			$resultData[$Row1['t']][] = $Row1['title'].$Row1['title_kr_name'];
		}

		$intersect = array_diff($resultData[1],$resultData[2]);

		if($intersect){ //현재 프로젝트와 복사 프로젝트의 차이가 있으면,
			$N_RETURNVAL = "-2";
		}else{
			$Rst2 = que($Qry2);
			if($Rst2){
				$N_RETURNVAL = "1";
			}else{
				$N_RETURNVAL = "-1";
			}
		}

		que_close();

		return $N_RETURNVAL; 

	}
	//----매체공지.취합 항목 설정 복사 끝----



	//----매체오픈캠페인 등록 시작----
	public function funcMnmOpenCampaignCheckForm(){

		require_once $_SERVER['DOCUMENT_ROOT'].'/phpExcelReader/Excel/reader.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP949');
		$dir_path = $_SERVER['DOCUMENT_ROOT']."/data/campaign_project_file/";
		
		//print_R($_FILES);

		if($_FILES){
			
			/*공지, 취합파일 업로드*/
			if( $_FILES['file_nm']){
				$files_upload_tmp	= $_FILES['file_nm']['tmp_name'];
				$files_upload_nm	= $_FILES['file_nm']['name'];				
				$files_upload_size	= $_FILES['file_nm']['size'];

				$file_info = pathinfo($files_upload_nm);

				$file_name_real = preg_replace( '/,/', '_', $file_info['basename']);
				$file_name_real = preg_replace( '/\s+/', '', $file_name_real);
				$file_name_new	= rand().".".$file_info['extension'];
			
				if(move_uploaded_file($files_upload_tmp,$dir_path.$file_name_new)){
					$B_RETURNVAL = 1;
					$data->read($dir_path.$file_name_new);
					error_reporting(E_ALL ^ E_NOTICE);

					for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {						
							//echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";								
						for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
							$temp[$i][$j] = $data->sheets[0]['cells'][$i][$j];
						}
					}

				}else{
					$B_RETURNVAL = -1;
					exit();
				}
			}
			/*공지, 취합파일 업로드*/
		}
		
		setlocale(LC_CTYPE, "ko_KR.eucKR");

		//t_project_history insert
		$Qry = "INSERT INTO t_campaign_history (
					campaign_nm,					
					file_nm,
					file_nm_new,
					measure_start_date,
					measure_end_date,
					open_start_date,
					open_end_date,					
					view_status,
					campaign_memo,
					reg_date,
					reg_time,
					reg_emp
				) VALUES (
					'".$_POST['campaign_nm']."',
					'".$file_name_real."',	
					'".$file_name_new."',		
					'".$_POST['measure_start_date']."',
					'".$_POST['measure_end_date']."',
					'".$_POST['open_start_date']."',
					'".$_POST['open_end_date']."',
					'".$_POST['view_status']."',
					'".$_POST['campaign_memo']."',
					CURDATE(),
					CURTIME(),
					'".$_SESSION['s_em_seq']."')";	
		//echo "<br>".$Qry;
		$Rst = que($Qry);

		$ch_seq = mysql_insert_id();

		//t_project_title_setting INSERT
		foreach($temp[1]  as $k=>$v){
			$title_kr_name = iconv("euc-kr","utf-8",$v);

			$Qry1 = "INSERT INTO t_campaign_title_setting (
						ch_seq,
						title,
						title_kr_name,
						open_yn,
						reg_date,
						reg_time,
						reg_emp
					) VALUES (
						'".$ch_seq."',
						'title$k',
						'$title_kr_name',
						'Y',
						CURDATE(),
						CURTIME(),
						'".$_SESSION['s_em_seq']."')";
			//echo $Qry1;
			$Rst1 = que($Qry1);

			$title_create .= "`title$k`  varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,";
			$title_value  .= "title$k,  ";					
		}

		
		//t_project_content_(n) CREATE
		$Qry2 = "CREATE TABLE t_campaign_content_$ch_seq (
					`seq` int(11) NOT NULL AUTO_INCREMENT,
					`ch_seq` int(11) DEFAULT NULL,
					`cts_seq` int(11) DEFAULT NULL,
					 $title_create
					`contact_cnt` int(11) DEFAULT 0,
					`click_cnt` int(11) DEFAULT 0,
					`view_status` char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '1' COMMENT '1:on,2:off',
					`reg_date` date DEFAULT NULL,
					`reg_time` time DEFAULT NULL,
					`reg_emp` int(11) DEFAULT NULL,
					`upd_date` date DEFAULT NULL,
					`upd_time` time DEFAULT NULL,
					`upd_emp` int(11) DEFAULT NULL,
					PRIMARY KEY (`seq`))
				ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		//echo "<br>".$Qry2;

		if(que($Qry2)===TRUE){		

			foreach($temp as $k=>$v){			
				foreach($v as $k1=>$v1){
				$value[$k] .= "'".iconv('euc-kr','utf-8',$v1)."',";
			}}
			
			//t_project_content_(n) INSERT
			foreach($temp as $k=>$v){
				$Qry3 = "INSERT INTO t_campaign_content_$ch_seq (
							ch_seq,
							$title_value
							reg_date,
							reg_time,
							reg_emp
						) VALUES (
							'".$ch_seq."',
							".$value[$k+1]."
							CURDATE(),
							CURTIME(),
							'".$_SESSION['s_em_seq']."'
							)";
				echo "<br>".$Qry3;
				que($Qry3);
			}

		}else{echo "F";}
		
	//**----------------------------------------------------------------------------------------------------------------------------

		exit();

		if($Rst){
				$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
 
		que_close();

	//**----------------------------------------------------------------------------------------------------------------------------
	}
	//----매체오픈캠페인 등록 끝----




	//----매체오픈캠페인 히스토리 리스트 시작----
	public function funcListMnmOpenCampaignHistory($nOrdPagingNum){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($nOrdPagingNum=="copy_setting"){}else{$limit = "LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";}

		if($_GET['d_item'] && $_GET['sdate'] && $_GET['edate']){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		if($_SESSION['em_level']>10){$where_view_status = "AND view_status='1'";}

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_campaign_history 
				WHERE del_date IS NULL
				$where_s_item
				$where_d_item
				$where_view_status
				ORDER BY seq DESC 
				$limit";
		//echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['campaign_nm'][]		= $Row['campaign_nm'];
			$S_RETURNVAL['file_nm'][]			= $Row['file_nm'];
			$S_RETURNVAL['file_nm_new'][]		= $Row['file_nm_new'];

			$S_RETURNVAL['measure_start_date'][]= $Row['measure_start_date']=='0000-00-00'?'':$Row['measure_start_date'];
			$S_RETURNVAL['measure_end_date'][]	= $Row['measure_end_date']=='0000-00-00'?'':$Row['measure_end_date'];

			$S_RETURNVAL['open_start_date'][]	= $Row['open_start_date'];
			$S_RETURNVAL['open_end_date'][]		= $Row['open_end_date'];

			$S_RETURNVAL['view_status'][]		= $Row['view_status'];
			$S_RETURNVAL['campaign_memo'][]		= $Row['campaign_memo'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}

		$S_RETURNVAL['nListCnt'] = $nListCnt;

		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$S_RETURNVAL['N_CNT'] = $Row1['N_CNT'];

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}

		que_close();
		return $S_RETURNVAL;

		
	}
	//----매체오픈캠페인 히스토리 리스트 끝----


	//----매체오픈캠페인 히스토리 셀렉트 시작----
	public function funcGetMnmOpenCampaignHistory(){

		if(!$_GET['nPage']) $_GET['nPage'] = 1;
		$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

		if($_GET['d_item'] ){
			$where_d_item = "AND ".$_GET['d_item']." between '".$_GET['sdate']."' AND '".$_GET['edate']."'";
		}

		if($_GET['s_item'] && $_GET['s_string']){
			$where_s_item = "AND ".$_GET['s_item']." like '%".$_GET['s_string']."%'";
		}

		$seq = $_POST['seq']?$_POST['seq']:$_GET['seq'];

		$Qry = "SELECT SQL_CALC_FOUND_ROWS * FROM t_campaign_history WHERE del_date IS NULL AND seq='".$seq."'";
		//echo $Qry;
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);

		$S_RETURNVAL['seq']					= $Row['seq'];
		$S_RETURNVAL['campaign_nm']			= $Row['campaign_nm'];
		$S_RETURNVAL['file_nm']				= $Row['file_nm'];
		$S_RETURNVAL['file_nm_new']			= $Row['file_nm_new'];

		$S_RETURNVAL['measure_start_date']	= $Row['measure_start_date']<>'0000-00-00'?$Row['measure_start_date']:'';
		$S_RETURNVAL['measure_end_date']	= $Row['measure_end_date']<>'0000-00-00'?$Row['measure_end_date']:'';

		$S_RETURNVAL['open_start_date']		= $Row['open_start_date']<>'0000-00-00'?$Row['open_start_date']:'';
		$S_RETURNVAL['open_end_date']		= $Row['open_end_date']<>'0000-00-00'?$Row['open_end_date']:'';

		$S_RETURNVAL['view_status']			= $Row['view_status'];
		$S_RETURNVAL['campaign_memo']		= $Row['campaign_memo'];
		$S_RETURNVAL['reg_date']			= $Row['reg_date'];
		$S_RETURNVAL['reg_time']			= $Row['reg_time'];
		$S_RETURNVAL['reg_emp']				= $Row['reg_emp'];

		$resultData							= $S_RETURNVAL;		

		$jsonTable = json_encode($resultData);
		return $jsonTable;

		que_close();
	}
	//----매체오픈캠페인 히스토리 셀렉트 끝----



	//----매체오픈캠페인 히스토리 수정 시작----
	public function funcUpdMnmOpenCampaignHistory(){

		require_once $_SERVER['DOCUMENT_ROOT'].'/phpExcelReader/Excel/reader.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP949');
		$dir_path = $_SERVER['DOCUMENT_ROOT']."/data/campaign_project_file/";
		
		//print_R($_FILES);

		if($_FILES){
			
			/*공지, 취합파일 업로드*/
			if( $_FILES['file_nm']){
				$files_upload_tmp	= $_FILES['file_nm']['tmp_name'];
				$files_upload_nm	= $_FILES['file_nm']['name'];				
				$files_upload_size	= $_FILES['file_nm']['size'];

				$file_info = pathinfo($files_upload_nm);

				$file_name_real = preg_replace( '/,/', '_', $file_info['basename']);
				$file_name_real = preg_replace( '/\s+/', '', $file_name_real);
				$file_name_new	= rand().".".$file_info['extension'];
			
				if(move_uploaded_file($files_upload_tmp,$dir_path.$file_name_new)){
					$B_RETURNVAL = 1;
					$data->read($dir_path.$file_name_new);
					error_reporting(E_ALL ^ E_NOTICE);

					for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {						
							//echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";								
						for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
							$temp[$i][$j] = $data->sheets[0]['cells'][$i][$j];
						}
					}

				}else{
					$B_RETURNVAL = -1;
					exit();
				}
			}
			/*공지, 취합파일 업로드*/
		}

		if($file_name_real)		{$is_file_nm = "file_nm		= '".$file_name_real."', file_nm_new		= '".$file_name_new."',";}

		$Qry = "UPDATE t_campaign_history SET 
					campaign_nm		= '".$_POST['campaign_nm']."',
					$is_file_nm
					measure_start_date	= '".$_POST['measure_start_date']."',
					measure_end_date	= '".$_POST['measure_end_date']."',
					open_start_date		= '".$_POST['open_start_date']."',
					open_end_date		= '".$_POST['open_end_date']."',
					view_status			= '".$_POST['view_status']."',
					campaign_memo		= '".$_POST['campaign_memo']."',
					upd_date			= CURDATE(),
					upd_time			= CURTIME(),
					upd_emp				= '".$_SESSION['s_em_seq']."'
				WHERE seq='".$_POST['seq']."'";

		//echo $Qry;
		$Rst = que($Qry);
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체오픈캠페인 히스토리 수정 끝----

	//----매체오픈캠페인 히스토리 삭제 시작----
	public function funcDelMnmOpenCampaignHistory(){

		$Qry = "UPDATE t_campaign_history SET del_date = CURDATE(), del_time = CURTIME(), del_emp = '".$_SESSION['s_em_seq']."' WHERE seq='".$_POST['seq']."'";
		$Qry1 = "DROP TABLE t_campaign_content_".$_POST['seq']."";

		//echo $Qry;
		$Rst = que($Qry);
		que($Qry1);

		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		return $N_RETURNVAL;
		que_close();
	}
	//----매체오픈캠페인 히스토리 삭제 끝----


	//----매체오픈캠페인 항목 내용 시작----
	public function funcSelectCampaignContent($nOrdPagingNum){

		if($nOrdPagingNum!="down"){
			if(!$_GET['nPage']) $_GET['nPage'] = 1;
			$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

			//$limit = "LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";
		}
		
		if($_SESSION['em_level']>10){$where = "AND view_status='1'";}
		
		$Qry = "SELECT * FROM t_campaign_content_".$_GET['seq']." WHERE 1=1 $where order by title1 ASC ".$limit." ";
		
		//echo $Qry."<br/>";
		$Rst = que($Qry);    

		$numfields = mysql_num_fields($Rst);

		for ($i = 0; $i<$numfields; $i += 1) {
			$field = mysql_fetch_field($Rst, $i);
			//echo '<th>' . $field->name . '</th>';
			$resultData['title'][$field->name] = $field->name;
		}

		while ($fielddata = mysql_fetch_array($Rst)) {
			for ($i = 0; $i<$numfields; $i += 1) {
				$field = mysql_fetch_field($Rst, $i);			
				$new_row[$resultData['title'][$field->name]] = $fielddata[$field->name];
			}
			$resultData['content'][]=$new_row; 
		}
		
		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$resultData['N_CNT'] = $Row1['N_CNT'];

		return $resultData; 

		que_close();

	}

	//----매체오픈캠페인 항목 내용 끝----


	//----매체오픈캠페인 항목 내용 시작----
	public function funcSelectCampaignContent1($nOrdPagingNum){

		if($nOrdPagingNum!="down"){
			if(!$_GET['nPage']) $_GET['nPage'] = 1;
			$nOrdStart = ($_GET['nPage'] -1 ) * $nOrdPagingNum;

			$limit = "LIMIT ".$nOrdStart.", ".$nOrdPagingNum."";

			//$limit = "LIMIT 0, 1000";
		}

		//echo funSelectEmpNm(11);
		
		if($_SESSION['em_level']>10){$where = "AND view_status='1'";}
		if($_GET['title3']) {$where_title3 = "AND title3 like '%".$_GET['title3']."%'";}
		if($_GET['orderby'] && $_GET['sort']){
			if($_GET['orderby']=='title1'){
				$orderby='title1_kr';
				$orderby=" order by ".$orderby." ".$_GET['sort'].""; 
			}else{
				$orderby=$_GET['orderby'];
				$orderby=" order by LENGTH(".$orderby.") ".$_GET['sort'].", ".$orderby." ".$_GET['sort'].""; 
			}
		}else{
			$orderby=" order by seq asc"; 
		}
		
		$Qry = "SELECT SQL_CALC_FOUND_ROWS *,getemkrname(title1) as title1_kr FROM t_campaign_content_".$_GET['seq']." WHERE 1=1 $where $where_title3 $orderby ".$limit." ";
		
		//echo $Qry."<br/>";
		$Rst = que($Qry);    

		$numfields = mysql_num_fields($Rst);

		for ($i = 0; $i<$numfields; $i += 1) {
			$field = mysql_fetch_field($Rst, $i);
			//echo '<th>' . $field->name . '</th>';
			$resultData['title'][$field->name] = $field->name;
		}

		while ($fielddata = mysql_fetch_array($Rst)) {
			for ($i = 0; $i<$numfields; $i += 1) {
				$field = mysql_fetch_field($Rst, $i);			
				$new_row[$resultData['title'][$field->name]] = $fielddata[$field->name];
			}
			$resultData['content'][]=$new_row; 
		}
		
		$Qry1 = 'SELECT FOUND_ROWS() N_CNT ';
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);

		$resultData['N_CNT'] = $Row1['N_CNT'];

		$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( 1200 ),  
			"recordsFiltered" => intval(1200),
			"data"            => $resultData
		);

		//print_R($json_data);
		//echo "<br>".json_encode($json_data);
		
		return $resultData; 

		que_close();

	}

	//----매체오픈캠페인 항목 내용 끝----



	//----매체오픈캠페인 항목 설정 가져오기 시작----
	public function funcJoinCampaignGetTitle(){
		$Qry = "SELECT * FROM t_campaign_title_setting WHERE ch_seq = '".$_GET['seq']."'";
		//echo $Qry."<br>".$Qry2;
		$Rst = que($Qry);    
		
		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][$Row['title']]				= $Row['seq'];
			$S_RETURNVAL['ch_seq'][]						= $Row['ch_seq'];
			$S_RETURNVAL['title'][]							= $Row['title'];
			$S_RETURNVAL['title_memo'][$Row['title']]		= $Row['title_memo'];
			$S_RETURNVAL['title_kr_name'][$Row['title']]	= $Row['title_kr_name'];
			$S_RETURNVAL['open_yn'][$Row['title']]			= $Row['open_yn'];
			
			//$resultData[]=$S_RETURNVAL; 
		}
		
		return $S_RETURNVAL; 
		que_close();
	}
	//----매체오픈캠페인 항목 설정 가져오기 끝----


	//----매체오픈캠페인 컨택 히스토리 셀렉트 시작----
	public function funcSelectCampaignContactHistory($ct_seq,$cc_seq){

		$Qry = "UPDATE t_campaign_content_".$ct_seq." SET click_cnt = (click_cnt+1) WHERE ch_seq = '".$ct_seq."' AND seq = '".$cc_seq."'" ;
		que($Qry);
		
		$Qry1 = "SELECT click_cnt FROM t_campaign_content_".$ct_seq." WHERE ch_seq = '".$ct_seq."' AND seq = '".$cc_seq."'";
		$Rst1 = que($Qry1);
		$Row1 = @mysql_fetch_assoc($Rst1);
		$S_RETURNVAL['click_cnt'] = $Row1['click_cnt'];
				
		$Qry2 = "SELECT * FROM t_campaign_contact_history WHERE ch_seq = '".$ct_seq."' AND cc_seq = '".$cc_seq."' ORDER BY seq DESC";
		$Rst2 = que($Qry2);    		
		while ($Row2 = @mysql_fetch_assoc($Rst2)) {
			//$S_RETURNVAL['seq']			= $Row['seq'];
			//$S_RETURNVAL['ch_seq']		= $Row['ch_seq'];
			//$S_RETURNVAL['cc_seq']		= $Row['cc_seq'];
			$S_RETURNVAL['contact_date']	= $Row2['contact_date'];
			$S_RETURNVAL['contact_time']	= $Row2['contact_time'];
			$S_RETURNVAL['contact_emp']		= funSelectEmpNm($Row2['contact_emp']);
			$resultData[]					= $S_RETURNVAL;		
		}

		if(!$resultData) {$resultData					= $S_RETURNVAL['click_cnt'];	}

		$jsonTable = json_encode($resultData);
		
		//print_R($jsonTable);
		
		return $jsonTable;

		//echo json_encode($CM_RETURNVAL);
		

		que_close();
	}
	//----매체오픈캠페인 컨택 히스토리 셀렉트 끝----


	//----매체오픈캠페인 컨택 히스토리 인서트 시작----
	public function funcInsertCampaignContactHistory(){
		$Qry = "SELECT COUNT(*) as cnt FROM t_campaign_contact_history WHERE ch_seq = '".$_GET['ct_seq']."' AND cc_seq = '".$_GET['cc_seq']."' AND contact_emp = '".$_SESSION['s_em_seq']."'";
		//echo $Qry;
		$Rst = que($Qry);
		$Row = @mysql_fetch_assoc($Rst);

		if($Row['cnt']>0){
			$S_RETURNVAL="1"; 
		}else{
			//iconv("EUC-KR","UTF-8",$_GET['contact_time'])

			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
				//echo 'Internet Explorer를 사용하고 있습니다.<br />';
				$contact_time = iconv("EUC-KR","UTF-8",$_GET['contact_time']);
			}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0') !== FALSE){
				//echo "<p>Internet Explorer11</p>";
				$contact_time = iconv("EUC-KR","UTF-8",$_GET['contact_time']);
			}else{
				//echo "<p>Internet Explorer를 미사용.</p>";
				$contact_time = $_GET['contact_time'];
			}

			$Qry1 = "INSERT INTO t_campaign_contact_history (
						ch_seq, cc_seq, contact_date, contact_time, contact_emp, reg_date, reg_time
					) VALUES (
						'".$_GET['ct_seq']."','".$_GET['cc_seq']."','".$_GET['contact_date']."','".$contact_time."','".$_SESSION['s_em_seq']."', CURDATE(), CURTIME()
					)";

			//echo $Qry1;
			$Rst1 = que($Qry1);

			$Qry = "UPDATE t_campaign_content_".$_GET['ct_seq']." SET contact_cnt = (contact_cnt+1) WHERE ch_seq = '".$_GET['ct_seq']."' AND seq = '".$_GET['cc_seq']."'" ;
			que($Qry);

			if($Rst1) {$S_RETURNVAL="2";}	
		}

		return $S_RETURNVAL; 
		que_close();
	}
	//----매체오픈캠페인 컨택 히스토리 인서트 끝----




	//----매체오픈캠페인 항목 설정 시작----
	public function funcSelectCampaignTitleSetting(){
		$Qry = "SELECT * FROM t_campaign_title_setting WHERE ch_seq='".$_GET['seq']."'";
		//echo $Qry;
		$Rst = que($Qry);

		while ($Row = @mysql_fetch_assoc($Rst)) {
			$S_RETURNVAL['seq'][]				= $Row['seq'];
			$S_RETURNVAL['ph_seq'][]			= $Row['ph_seq'];
			$S_RETURNVAL['title'][]				= $Row['title'];
			$S_RETURNVAL['title_memo'][]		= $Row['title_memo'];
			$S_RETURNVAL['title_kr_name'][]		= $Row['title_kr_name'];
			$S_RETURNVAL['reg_date'][]			= $Row['reg_date'];
			$S_RETURNVAL['reg_time'][]			= $Row['reg_time'];
			$S_RETURNVAL['reg_emp'][]			= $Row['reg_emp'];
			$nListCnt ++;
		}
		$S_RETURNVAL['nListCnt'] = $nListCnt;

		if(!$S_RETURNVAL['seq']){
			$S_RETURNVAL = -1;
		}else{
			return $S_RETURNVAL;
		}
		return $S_RETURNVAL;

		que_close();
	}
	//----매체오픈캠페인 항목 설정 끝----


	//----매체오픈캠페인 항목 설정 수정 시작----
	public function funcUptCampaignTitleSetting(){
		
		for($i=0;$i<$_POST['cnt'];$i++){

		$Qry = "UPDATE t_campaign_title_setting SET 
				title_memo			= '".$_POST['title_memo'.$i]."',
				title_kr_name		= '".$_POST['title_kr_name'.$i]."'
				WHERE seq			= '".$_POST['seq'.$i]."'";
		//echo $Qry;
		$Rst = que($Qry);

		}
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		que_close();

		return $N_RETURNVAL; 
	}

	//----매체오픈캠페인 항목 설정 수정 끝----


	//----매체오픈캠페인 viewstatus 수정 시작----
	public function funcUptCampaignViewstatus(){

		if($_GET['submitType']=='update_on') {$value='1';} else {$value='2';}
		foreach($_POST['check_list'] as $k=>$v){
			$Qry = "UPDATE t_campaign_content_".$_POST['seq']." SET view_status = '".$value."' WHERE seq = '".$v."'";
			//echo $Qry;
			$Rst = que($Qry);
		}
		
		if($Rst){
			$N_RETURNVAL = "1";
		}else{
			$N_RETURNVAL = "-1";
		}

		que_close();

		return $N_RETURNVAL; 
	}

	//----매체오픈캠페인 viewstatus 수정 끝----

}

?>