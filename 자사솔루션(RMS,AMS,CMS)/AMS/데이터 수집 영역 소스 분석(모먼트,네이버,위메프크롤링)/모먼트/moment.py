# -*- coding: utf-8 -*-
from media.process.abstract.singleprocess import SingleProcess as AbstractProcess
from media.api.momentapi import MomentApi as MediaApi
from media.models.database.moment import Moment as MediaRowReport
import time, sys

# singleProcess.py 상속
class Moment(AbstractProcess):

    #모먼트
    processName = 'moment'

    #초기화
    def __init__(self,targetDate=None, processId=None, managementUpdateCheck=False):
        super().__init__(targetDate,processId,managementUpdateCheck)
        self.mediaApi = MediaApi()

    # 싱글처리
    def getTargetList(self):
        return []
    
    # 싱글처리
    def execute(self,advertiser,shareData=None):
        return True

    # 병렬처리
    def _getTargetList(self):
        # helper클래스 생성
        helper = self.getHelper() 
        # 오늘날짜
        todayDate = helper.getNowDate()
        # 오늘날짜 문자형태
        inDateString = todayDate.strftime('%Y-%m-%d')
        # 어제날짜 문자형태
        yesterDateString = self.getHelper().getYesterdayDate().strftime('%Y-%m-%d')
        # 싱글프로세스용
        managementModel = self.getManagementModel(False) 
        # 매체별 광고주 최신 상태 변경 이력 값 
        lastHistoryData = managementModel.getMediaLastChangeHistory(self.getMediaCode()).copy() 
        lastMatchingInfo = helper.getLastMatchingDataByMNm(self.getMediaConfig()['m_nm'], self.getDevDb())
        # t_customer 정보 
        totalAdvertiserData = helper.getTotalAdvertiserData(self.getRmsDb()) 
        # MEDIA CODE
        mediaCode = self.getMediaCode()
        # DB
        db = self.getDefaultDb() 
        db_live = self.getLiveDb()
        isDev = True

        try:
            ##Token 만들기
            # self.createMomentTokens(inDateString,db)
            if(isDev != True):
                if(self.refreshMomentTokens(inDateString, db) == False):
                    # 에러 출력
                    raise Exception('Moment token Validity Error')

            #TargetList 가져오기
            targetList = self.getAccountList(db_live)
            # 1차 for문
            for targetListData in targetList:
                inTransferAdvertiser = []
                inTransferHistory = []
                # 2차 for문
                for targetData in targetListData:
                    # matching ID
                    matchingId = targetData['matching_id'] 
                    # 신규이면
                    if str(matchingId) not in lastHistoryData:
                        # None 처리
                        emSeq, csSeq, csType, division, team = [None, None, None, None, None]
                        #if str(matchingId) in lastMatchingInfo:
                        emSeq,csSeq,csNm,csType,division,team,csTypeNew1,csTypeNew2 = helper.getMatchingInfo(self.processName, matchingId, self.getDefaultDb(), self.getRmsDb())
                        #inTransferAdvertiser 배열에 넣기
                        inTransferAdvertiser.append([
                            mediaCode, matchingId, self.getDataTableName(matchingId), csSeq, csType,
                            matchingId, None, matchingId, None, self.dataToJson(targetData)
                        ])
                        # inTransferHistory 배열에 넣기
                        inTransferHistory.append([
                            mediaCode, matchingId, mediaCode, matchingId, emSeq, yesterDateString
                        ])
                    # 신규가 아니면
                    else:
                        if str(matchingId) in lastHistoryData:
                            if lastHistoryData[str(matchingId)]['status'] == managementModel.getOutTransferStatusValue():
                                self.reTransferData[matchingId] = lastHistoryData[str(matchingId)]
                            lastHistoryData.pop(str(matchingId))
                # 이관 묶음 처리
                managementModel.bulkInTransferProcess(inTransferAdvertiser,inTransferHistory) 
                # targetDate 문자형태
                targetDateString = self.getTargetDate().strftime('%Y-%m-%d')
                # 매체별 계정 데이터 수집 상태 히스토리 입력
                self.getHistoryModel().insertAdvertiserTodayTarget(targetDateString, self.getProcessId(),
                                                                   self.getMediaCode(), targetListData, 'matching_id')
            # 피이관 데이터
            outTransferData = {}
            # 피이관 상태코드
            outTransferValue = managementModel.getOutTransferStatusValue() 
            for matchingId, row in lastHistoryData.items():
                managementModel.getMediaLastChangeHistory(self.getMediaCode()).pop(str(matchingId))

                if row['status'] != outTransferValue:
                    outTransferData[str(matchingId)] = row

            self.outTransferData = outTransferData

            return targetList
        #에러시
        except Exception as e:
            # 에러 출력
            print(e)
            # 에러 출력
            raise Exception(e)

    # 병렬처리
    def _execute(self,targetList,shareData=None):
        # 시작 날짜
        startedDate = self.helper.getNowDateString() 
        # 끝 날짜
        endDate = self.helper.getNowDateString()
        #리포트 데이터
        report_data = self.getReportData(targetList)
        #리포트 데이터 있으면
        if report_data:
            # data수 만큼 반복
            for data in report_data:
                # HISTORY MODEL CLASS 반환 HISTORY LOG 관련
                historyModel = self.getHistoryModel()
                # advertiser ID
                advertiserId = data['matching_id']
                # 매체 계정 별 history id
                self.loadHistoryId(advertiserId)
                #history id
                historyId = self.getHistoryId()
                try:
                    bindData = self.bindingData(data,advertiserId)
                    # 광고주 히스토리 정보 업데이트
                    historyModel.updateAdvertiserData(historyId, startedDate, endDate, len(report_data))
                    # 매체 계정별 디비 입력 처리
                    self.databaseProcess(advertiserId, advertiserId, bindData, targetList, hasKeyword=False)
                # 에러 발생시
                except Exception as e:
                    import traceback
                    # 에러 출력
                    historyModel.insertHistoryMessage(historyId, traceback.format_exc())

    # 싱글 프로세스 처리용 피이관,재이관, 매체별 피이관 X 데이터 X 계정 처리
    def resultProcess(self,processId,bindData=None,hasKeyword=False):
        # 타겟리스트
        targetList = self._getTargetList()

        id_array = []
        # 타겟리스트 갯수만큼
        for id_bundle in targetList:

            count_size = len(id_bundle)/5
            total_count = 0

            line = []
            count = 0

            # 배열 안에 배열 수만큼
            for id in id_bundle:
                line.append(id)

                if total_count == int(count_size)-1:
                    id_array.append(line)
                    line = []
                    count = 0
                    total_count=0
                    continue
                if count == 4:
                    id_array.append(line)
                    line = []
                    count = 0
                    total_count = total_count + 1
                    continue

                count = count + 1

        for advertiser in id_array:
            # 대상 처리(데이터 가공 및 계정단 디비 입력) + 휴면 체크
            self._execute(advertiser)
            time.sleep(6) ##Moment api request limit time 10s

        # 싱글 프로세스 처리용 피이관,재이관, 매체별 피이관 X 데이터 X 계정 처리 반환
        return super().resultProcess(processId, bindData, hasKeyword)

    # 리포트 파일생성
    def makeResultReportFile(self,customerId,customer, advertiserInfo):
        try:
            # 헬퍼 클래스 생성
            helper = self.getHelper()
            # targetDate 문자형태
            targetDateString = self.getTargetDate().strftime("%Y-%m-%d")
            # 매체별 계정별 DB TABLE NAME
            dataTableNm = self.getDataTableName(customerId)
            # em,cs..... 정보
            emSeq, csSeq, csNm, csType, division, team, csTypeNew1, csTypeNew2 = self.getMatchingInfo(customer) 
            # MEDIA CODE 반환
            mediaCode=self.getMediaCode()
            #resultData 쿼리 
            resultData = self.getDb().fetchAll("""
                            select '{}','{}','{}','{}','{}','{}','{}','{}','{}','{}',matching_id, product_type,
                            CAST(sum(impression_cnt) as SIGNED ),CAST(sum(click_cnt) as SIGNED ),CAST(sum(cost)/1.1 as SIGNED ),
                            CAST(sum(conv_px_purchase_dir + conv_px_purchase_indir + conv_sdk_purchase_dir + conv_sdk_iap_dir + conv_sdk_purchase_indir + conv_sdk_iap_indir + conv_purchase_1d) as SIGNED) as conversion_cnt,
                            CAST(sum(conv_px_purchase_p_dir + conv_px_purchase_p_indir + conv_sdk_purchase_p_dir + conv_sdk_iap_p_dir + conv_sdk_purchase_p_indir + conv_sdk_iap_p_indir + conv_purchase_p_1d) as SIGNED) as conversion_amnt
                            from {} where pay_date=%s group by product_type
                            """.format(targetDateString, mediaCode, emSeq, division, team, csSeq, csNm, csType, csTypeNew1,csTypeNew2,
                                       dataTableNm), [targetDateString])

            # row[14] 개수가 0보다 작으면 hasData = false
            hasData = False
            totalCost = 0
            # resultData 개수만큼
            for row in resultData:
                totalCost += row[14]
            # row[14] 개수가 0보다 크면
            if totalCost > 0:
                hasData = True
            # 함수인자인 advertiserInfo 개수만큼 반복
            for adInfo in advertiserInfo:
                # 직원코드
                adInfo['em_seq'] = emSeq
                # 싱글 프로세스
                managementModel = self.getManagementModel() 

                # MANAGEMENT,HISTORY 상태 이력 업데이트 처리되었으면
                if managementModel.updateAdvertiserStatusHistory(mediaCode,adInfo,hasData): 
                    # 매체별 광고주 최신 상태 변경 이력 값 가져오기
                    managementModel.getMediaLastChangeHistory(self.getMediaCode()).pop(customer)
            # 폴더 유무 점검 없을시 생성
            helper.checkDir(helper.relationJoinPath('file',self.processName,'report','result')) 
            # CSV 파일 입력 후 저장
            helper.writeCsvFileData(helper.relationJoinPath('file',self.processName,'report','result',dataTableNm),resultData) 
        # 에러발생시
        except Exception as e: 
            # 에러 출력
            print(e)

    # 카카오모먼트 고유 ACCESS토큰 생성 및 해당 데이터 UPDATE
    def createMomentTokens(self,inDateString,db):
        token = db.fetchAll("""
        select code from t_moment_auth where used_code='N'
        """)
        # token 배열 개수만큼
        for code in token:
            # auth_code
            auth_code = code[0]
            # token_data 생성
            token_data = self.mediaApi.createToken(auth_code)
            # token_data 있으면 배열내용 변수설정
            if token_data:
                # access_token
                access_token = token_data['access_token']
                # refresh_token
                refresh_token = token_data['refresh_token']
                # t_moment_auth UPDATE처리
                db.execute("""
                    UPDATE t_moment_auth set access_token = '{}', refresh_token = '{}', updated_at='{}', used_code = 'Y' WHERE code = '{}'
                    """.format(access_token, refresh_token, inDateString, auth_code))

    # 카카오모먼트 고유 ACCESS토큰 refresh 및 해당 데이터 UPDATE  
    def refreshMomentTokens(self, inDateString, db):
        # checkUpdateTime 확인
        checkUpdateTime = db.fetchAll("""
            SELECT TIMESTAMPDIFF(hour, update_time, CURRENT_TIMESTAMP()) as date_diff from information_schema.tables
            where table_schema='hm' and
            table_name='t_moment_auth'
        """,  cursor='dict')[0]
        # updateTime
        updateTime = 0 if checkUpdateTime.get('date_diff') == None else checkUpdateTime.get('date_diff')
        if(updateTime >= 3) :
            # ref_token
            ref_token = db.fetchAll("""select increment_id, refresh_token, updated_at from t_moment_auth where updated_at >= DATE_ADD(CURDATE(), INTERVAL -1 DAY)""")
            # ref_token 배열 개수 만큼
            for rt in ref_token:
                # increment_id
                increment_id = rt[0]
                # refresh_token
                refresh_token = rt[1]
                data = self.mediaApi.refreshToken(refresh_token)
                if data:
                    # refresh_access_token
                    refresh_access_token = data['access_token']
                    db.execute("""UPDATE t_moment_auth set access_token = '{}',updated_at='{}' where increment_id = {}""".format(refresh_access_token,inDateString,increment_id))
                    # refresh token이 1개월 미만의 유효기간일 경우만 들어옴
                    if 'refresh_token' in data:
                        # refresh_token_new
                        refresh_token_new = data['refresh_token']
                        db.execute("""UPDATE t_moment_auth set refresh_token = '{}',updated_at='{}' where increment_id = {}""".format(refresh_token_new, inDateString, increment_id))
                else:
                    print(refresh_token)
                    print(data)
                    print('--------------------------------------------------')
                    return False
        
    # accountList과 계정 ID 이름이 담긴 배열 RETURN
    def getAccountList(self,db):
        accountList = []
        account_dup_list = []
        list = []
        # auth_token 조회
        auth_token = db.fetchAll("""select access_token from t_moment_auth  where updated_at >= DATE_ADD(CURDATE(), INTERVAL -1 DAY)""")

        # auth_token 배열 개수만큼
        for token in auth_token:
            # token[0]이 있으면
            if token[0]:
                # access_token
                access_token = token[0]
                # accounts
                accounts = self.mediaApi.getAccountApi(access_token)
                # accounts 가 있으면
                if accounts:
                    # accounts[content] 개수만큼
                    for account in accounts['content']:
                        # account_dup_list에 account[id]가 없을때
                        if account['id'] not in account_dup_list:
                            # id넣어주기
                            account_dup_list.append(account['id'])
                            # accountList에 access_token, id, name 넣어주기
                            accountList.append({'access_token': access_token, 'matching_id': account['id'], 'name': account['name']})

            list.append(accountList)
            accountList = []
        # return accountList
        return list

    # 리포트 데이터 가져오기
    def getReportData(self,targetList):
        reportData = []
        # targetDate
        targetDate = self.getTargetDate().strftime('%Y%m%d')
        # report_data
        report_data = self.mediaApi.getReportApi(targetList,targetDate)
        # report_data 있으면
        if report_data:
            data = report_data['data']
            # data 배열의 개수만큼
            for rows in data:
                if rows['dimensions']:
                    # reportData에 matching_id, data 넣어주기
                    reportData.append({
                        'matching_id' : rows['dimensions']['ad_account_id'],
                        'data': rows
                    })
        #  matching_id, data 넣은 배열 반환
        return reportData

    # 데이터 변수처리 및 배열에 담기
    def bindingData(self,reportRow,advertiserId):
        bindingData = []
        rowData = [
            reportRow['data']['start'],
            advertiserId,
            advertiserId,
            reportRow['data']['dimensions']['creative_format'],
            reportRow['data']['metrics']['imp'],
            reportRow['data']['metrics']['click'],
            round(reportRow['data']['metrics']['ctr'],2),
            reportRow['data']['metrics']['cost'],
            reportRow['data']['metrics']['reach'],
            round(reportRow['data']['metrics']['cost_per_imp'],2),
            round(reportRow['data']['metrics']['cost_per_click'],2),
            round(reportRow['data']['metrics']['cost_per_reach'],2),
            reportRow['data']['metrics']['conv_px_purchase_dir'] if 'conv_px_purchase_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_px_purchase_indir'] if 'conv_px_purchase_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_purchase_dir'] if 'conv_sdk_purchase_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_iap_dir'] if 'conv_sdk_iap_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_purchase_indir'] if 'conv_sdk_purchase_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_iap_indir'] if 'conv_sdk_iap_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_px_purchase_p_dir'] if 'conv_px_purchase_p_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_px_purchase_p_indir'] if 'conv_px_purchase_p_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_purchase_p_dir'] if 'conv_sdk_purchase_p_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_iap_p_dir'] if 'conv_sdk_iap_p_dir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_purchase_p_indir'] if 'conv_sdk_purchase_p_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_sdk_iap_p_indir'] if 'conv_sdk_iap_p_indir' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_cmpt_reg_1d'] if 'conv_cmpt_reg_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_cmpt_reg_7d'] if 'conv_cmpt_reg_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_view_cart_1d'] if 'conv_view_cart_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_view_cart_7d'] if 'conv_view_cart_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_purchase_1d'] if 'conv_purchase_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_purchase_7d'] if 'conv_purchase_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_purchase_p_1d'] if 'conv_purchase_p_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_purchase_p_7d'] if 'conv_purchase_p_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_participation_1d'] if 'conv_participation_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_participation_7d'] if 'conv_participation_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_signup_1d'] if 'conv_signup_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_signup_7d'] if 'conv_signup_7d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_app_install_1d'] if 'conv_app_install_1d' in reportRow['data']['metrics'] else 0,
            reportRow['data']['metrics']['conv_app_install_7d'] if 'conv_app_install_7d' in reportRow['data']['metrics'] else 0
        ]
        bindingData.append(rowData)
        return bindingData

    def getMediaRowReport(self):
        return MediaRowReport()