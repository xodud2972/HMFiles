# -*- coding: utf-8 -*-
import time,json,re,sys

from pymysql import NULL
from media.api.naverapi import NaverApi as MediaApi
from media.process.abstract.multiprocess import MultiProcess as AbstractProcess
from media.models.database.naver import Naver as MediaRowReport

class Naver(AbstractProcess):

    # naver
    processName = 'naver'
    noneData = '-'

    # 초기화
    def __init__(self,targetDate=None, mediaHistoryId=None, managementUpdateCheck=False):
        #초기화
        super().__init__(targetDate,mediaHistoryId,managementUpdateCheck)
        # master, stateClient None처리
        self.masterReportClient,self.stateReportClient,self.stateReportClient = [None,None,None]
        # 네이버 인증 설정 가져오기
        authConfig = self.getAuthConfig()
        # mediaApi
        self.mediaApi = MediaApi(authConfig['appKey'], authConfig['appSecret'], authConfig['customerId'])
        self.masterReportClient = self.getMediaApi().getMasterReportClient()
        self.stateReportClient = self.getMediaApi().getStatReportApi()
    
    # 병렬처리
    def _getTargetList(self):
        #네이버 인증 설정 가져오기
        authConfig = self.getAuthConfig()
        # 헬퍼 클래스 생성
        helper = self.getHelper()
        # BASE_PATH + path 
        masterPath = helper.relationJoinPath('file',self.processName,'report','master')
        # 리포트데이터 다운로드
        self.downloadReportData('Account', authConfig['customerId'], authConfig['customer'])

        # 계정 리스트 파일이 생성 됐으면 계정 리스트 가져오기, 생성이 안됐다면 예외상황 발생 및 프로그램 종료
        accountList = helper.checkAccountData(helper.joinPath(masterPath,'Account','{}.tsv'.format(authConfig['customerId'])))
        # targetDate문자형태
        targetDateString = self.getTargetDate().strftime('%Y-%m-%d')
        accountData = []
        # false - 병렬 처리
        managementModel = self.getManagementModel(False)
        """ 가장 최근 상태 변경 이력 가져오기 """
        # 가장 최근 상태 변경 이력
        lastHistoryData = managementModel.getMediaLastChangeHistory(self.getMediaCode()).copy()
        inTransferAdvertiser = []
        inTransferHistory = []
        # MEDIA CODE
        mediaCode = self.getMediaCode()
        # t_customer 정보
        totalAdvertiserData = helper.getTotalAdvertiserData(self.getRmsDb())
        lastMatchingInfo = helper.getLastMatchingDataByMNm(self.getMediaConfig()['m_nm'],self.getDefaultDb())

        # 계정 리스트 개수만큼 반복
        for key,account in enumerate(accountList):
            matchingId = account[1]
            inDate = account[5][:10]
            accountData.append({
                'matching_id': matchingId,
                'in_date': inDate
            })
            # 신규이면
            if matchingId not in lastHistoryData:
                # None처리
                emSeq, csSeq, csType, division, team = [None,None,None,None,None]
                # 매체별 matching id 로 매핑된 emSeq,csSeq,csNm,csType,division,team,csTypeNew1,csTypeNew2 정보 가져오기
                emSeq,csSeq,csNm,csType,division,team,csTypeNew1,csTypeNew2 = helper.getMatchingInfo(self.processName, matchingId, self.getDefaultDb(), self.getRmsDb())
                    
                """ t_advertiser_media_management 등록 """
                inTransferAdvertiser.append([
                    mediaCode,matchingId,self.getDataTableName(account[0]),csSeq,csType,
                    matchingId,account[0],account[2],None,json.dumps(account)
                ])
                """ t_advertiser_media_history 등록 """
                inTransferHistory.append([
                    mediaCode,matchingId,mediaCode,matchingId,emSeq,inDate
                ])
                accountList[key].append(None)
            # 신규가 아니면
            else:
                """ t_advertiser_media_history data """
                accountList[key].append(lastHistoryData[matchingId])
                """ lastHistoryData 남아있을 시 피이관 """
                lastHistoryData.pop(matchingId)
        # 매체별 이관 계정 묶음 처리
        self.bulkInTransferProcess(inTransferAdvertiser, inTransferHistory, managementModel) 
        # 피이관 계정 데이터 처리
        self.outTransferProcess(lastHistoryData) 
        # 매체별 계정 데이터 수집 상태 히스토리 입력
        self.getHistoryModel().insertAdvertiserTodayTarget(targetDateString,self.getProcessId(),self.getMediaCode(),accountData,'matching_id','in_date')
        # 계정리스트 반환
        return accountList
    
    # 병렬처리
    def _execute(self,target,shareData=None):
        # 헬퍼 클래스 생성
        helper = self.getHelper()
        # 시작날짜 
        startedDate = helper.getNowDateString()
        # HISTORY MODEL CLASS 반환 HISTORY LOG 관련
        historyModel = self.getHistoryModel()
        # targetDate
        targetDate = self.getTargetDate()
        # customerId
        customerId = target[0]
        #customer
        customer = target[1]
        #계정별 데이터 수집 상태 히스토리 아이디 가져오기
        self.loadHistoryId(customer)    
        # 매체 계정 별 history id
        historyId = self.getHistoryId() 

        try:
            stateResult = self.downloadReportData('AD', customerId, customer, targetDate.strftime("%Y%m%d"), 'state')
            advertiserInfo = {
                'matching_id': target[1],
                'main_id':target[1],
                'sub_id':target[0],
                'name':target[2],
                'beforeData': target[6]
            }
            if stateResult:
                self.downloadReportData('AD_CONVERSION', customerId, customer, targetDate.strftime("%Y%m%d"), 'state')
                """ 마스터 정보 다운로드 """
                self.downloadTargetData(customerId,customer)
                bindData = self.dataResultProcess(customerId,customer)
                endDate = helper.getNowDateString()
                historyModel.updateAdvertiserData(historyId, startedDate, endDate, len(bindData))
                self.databaseProcess(customerId, customer, bindData, advertiserInfo)
            else:
                """ 휴면 30일 체크 """
                if advertiserInfo['beforeData'] is not None:
                    emSeq, csSeq, csNm, csType, division, team, csTypeNew1, csTypeNew2 = self.getMatchingInfo(customer)
                    advertiserInfo['em_seq'] = emSeq
                    hasData = False
                    managementModel = self.getManagementModel() # shareDb true - 싱글 처리 , false - 병렬 처리
                    managementModel.updateAdvertiserStatusHistory(self.getMediaCode(), advertiserInfo, hasData, advertiserInfo['beforeData']) # MANAGEMENT,HISTORY 상태 이력 업데이트 

        # 오류 반환
        except Exception as e:
            # import
            import traceback
            # history id 기준 t_advertiser_history_message 메시지 입력
            historyModel.insertHistoryMessage(historyId, traceback.format_exc())

        # True 반환
        return True

    # 리포트 파일 생성
    def makeResultReportFile(self,customerId,customer, advertiserInfo):
        # 헬퍼 클래스 생성
        helper = self.getHelper()
        # targetDate문자형태
        targetDateString = self.getTargetDate().strftime("%Y-%m-%d")
        #datatableNm
        dataTableNm = self.getDataTableName(customerId)
        # em,cs 정보 matchingId로 가져오기
        emSeq, csSeq, csNm, csType, division, team,csTypeNew1,csTypeNew2 = self.getMatchingInfo(customer)
        # mediaCode
        mediaCode = self.getMediaCode()
        # redultData 가져오기
        resultData = self.getDb().fetchAll("""
        select '{}','{}','{}','{}','{}','{}','{}','{}','{}','{}',advertiser_login_id, campaign_type,
        CAST(sum(impression_cnt) as SIGNED ),CAST(sum(click_cnt) as SIGNED ),CAST(sum(cost) as SIGNED )
        ,CAST(sum(conversion_cnt) as SIGNED ),CAST(sum(conversion_amnt) as SIGNED )
        from {} where pay_date=%s group by campaign_type
        """.format(targetDateString,mediaCode,emSeq,division,team,csSeq,csNm,csType,csTypeNew1,csTypeNew2,dataTableNm),[targetDateString])

        # advertiserInfo['beforeData']가 None이면 advertiserInfo['em_seq'] 재설정
        if advertiserInfo['beforeData'] is not None:
            advertiserInfo['em_seq']=emSeq
            # 매체 계정별 리포트 데이터 광고 비용 체크 - 휴면 30일 확인용
            hasData = self.hasDataCheckByCost(resultData) 
            # shareDb true - 싱글 처리 , false - 병렬 처리
            managementModel = self.getManagementModel() 
            # MANAGEMENT,HISTORY 상태 이력 업데이트 처리
            managementModel.updateAdvertiserStatusHistory(mediaCode,advertiserInfo,hasData,advertiserInfo['beforeData'])

        # 폴더 유무 점검 없을시 생성
        helper.checkDir(helper.relationJoinPath('file',self.processName,'report','result'))
         # CSV 파일 입력 후 저장
        helper.writeCsvFileData(helper.relationJoinPath('file',self.processName,'report','result',dataTableNm),resultData)

    ## 리포트 키워드 필터 파일생성
    def makeKeywordFilterFile(self, customerId, customer):
        mediaDb = self.getDb()
        # 헬퍼 클래스 생성
        helper = self.getHelper()
        # targetDate문자형태
        targetDateString = self.getTargetDate().strftime("%Y-%m-%d")
        #dateTableNm
        dataTableNm = self.getDataTableName(customerId)
        # em,cs 정보 matchingId로 가져오기
        emSeq, csSeq, csNm, csType, division, team,csTypeNew1,csTypeNew2 = self.getMatchingInfo(customer)
        # resultData
        resultData = mediaDb.fetchAll("""
        SELECT '{}','{}','{}','{}','{}','{}','{}','{}','{}',
        campaign_type, campaign_id, campaign, group_id, {tableName}.group, keyword_id, keyword, CAST(SUM(impression_cnt) AS SIGNED) AS impression_cnt, CAST(SUM(click_cnt) AS SIGNED) AS click_cnt,
        CAST(SUM(cost) AS SIGNED) AS cost, IF(sum(click_cnt)>0 && SUM(impression_cnt)>0,ROUND((sum(click_cnt)/sum(impression_cnt)),2),0) AS ctr 
        ,CAST(sum(conversion_cnt) as SIGNED ),CAST(sum(conversion_amnt) as SIGNED )
        FROM {} WHERE pay_date=%s and not keyword='-' GROUP BY campaign_id, campaign_type, group_id, keyword_id HAVING (impression_cnt >=100 or click_cnt>=5 or cost>=10000)
        """.format(targetDateString, emSeq, division, team, csSeq, csType,csTypeNew1,csTypeNew2, customer,
            dataTableNm, tableName = dataTableNm),[targetDateString])
        # resultData있으면
        if resultData:
            logData = mediaDb.fetchAll("""
            SELECT '{}', '{}', '{}', campaign_type, 'filter', count(keyword) FROM 
            (SELECT pay_date, CAST(SUM(impression_cnt) AS SIGNED) AS impression_cnt, 
            CAST(SUM(click_cnt) AS SIGNED) AS click_cnt, CAST(SUM(cost) AS SIGNED) AS cost, 
            campaign_id, campaign, campaign_type, group_id, {tableName}.group, keyword_id, keyword, CAST(sum(conversion_cnt) as SIGNED ),CAST(sum(conversion_amnt) as SIGNED )
            FROM {} WHERE pay_date=%s and not keyword='-' 
            GROUP BY campaign_id, campaign_type, group_id, keyword_id
            HAVING (impression_cnt >=100 or click_cnt>=5 or cost>=10000)) AS a
            """.format(targetDateString,self.processName, customer, dataTableNm, tableName = dataTableNm),[targetDateString])
             # 폴더 유무 점검 없을시 생성
            helper.checkDir(helper.relationJoinPath('file', 'keyword', self.processName, 'filter', 'log'))
            # CSV 파일 입력 후 저장
            helper.writeCsvFileData(
                helper.relationJoinPath('file', 'keyword', self.processName, 'filter', 'log', dataTableNm),
                logData)
        # 폴더 유무 점검 없을시 생성
        helper.checkDir(helper.relationJoinPath('file', 'keyword', self.processName, 'filter', 'result'))
        # CSV 파일 입력 후 저장
        helper.writeCsvFileData(
            #BASE_PATH + path 가져오기
            helper.relationJoinPath('file', 'keyword', self.processName, 'filter', 'result', dataTableNm),
            resultData)

    # 전환 데이터 추가
    def bindConversionData(self,conversionData):
        """ 전환 데이터 추가 """
        bindData = {
            'keyword': {},
            'ad': {}
        }
        # conversionData 배열 개수만큼 반복
        for row in conversionData:
            keywordId = row[4]
            adId = row[5]
            mediaCode = row[7]

            rowData = {
                'conversion_cnt': row[11],
                'conversion_amnt': row[12]
            }

            if self.noneData != keywordId:
                if mediaCode not in bindData['keyword']:
                    bindData['keyword'][mediaCode] = {}

                if adId not in bindData['keyword'][mediaCode]:
                    bindData['keyword'][mediaCode][adId] = {}
                bindData['keyword'][mediaCode][adId][keywordId] = rowData
            else:
                if mediaCode not in bindData['ad']:
                    bindData['ad'][mediaCode] = {}
                bindData['ad'][mediaCode][adId] = rowData

        return bindData

    # 데이터 형태 가공
    def dataResultProcess(self,customerId,customerName):
        """ 데이터 형태 가공 """

        keywordData, campaignData, groupData, stateData, conversionData = list(self.getTargetFileData(customerId))
        # targetDate
        targetDate = self.getTargetDate().strftime("%Y-%m-%d")
        # 전환 데이터 추가
        bindConversionData = self.bindConversionData(conversionData)

        # enumerate(stateData) 배열 개수 만큼 
        # enumerate : 인자풀기 (for 돌릴 수 있도록)
        for key, row in enumerate(stateData):
            stateData[key][0] = targetDate
            keywordId = row[4]
            mediaCode = row[7]
            adId = row[5]
            keyword = self.noneData if keywordId not in keywordData else keywordData[row[4]]
            groupName = self.noneData if row[3] not in groupData else groupData[row[3]]
            campaignName = self.noneData if row[2] not in campaignData else campaignData[row[2]]['campaign_name']
            campaignType = 0 if row[2] not in campaignData else campaignData[row[2]]['campaign_type']
            conversionCnt = 0
            conversionAmnt = 0

            if keywordId == self.noneData:
                if mediaCode in bindConversionData['ad'] and adId in bindConversionData['ad'][mediaCode]:
                    conversionCnt = bindConversionData['ad'][mediaCode][adId]['conversion_cnt']
                    conversionAmnt = bindConversionData['ad'][mediaCode][adId]['conversion_amnt']
                    bindConversionData['ad'][mediaCode].pop(adId)
                    if len(bindConversionData['ad'][mediaCode]) == 0:
                        bindConversionData['ad'].pop(mediaCode)
            else :
                if mediaCode in bindConversionData['keyword'] \
                        and adId in bindConversionData['keyword'][mediaCode]\
                        and keywordId in bindConversionData['keyword'][mediaCode][adId]:
                    conversionCnt = bindConversionData['keyword'][mediaCode][adId][keywordId]['conversion_cnt']
                    conversionAmnt = bindConversionData['keyword'][mediaCode][adId][keywordId]['conversion_amnt']
                    bindConversionData['keyword'][mediaCode][adId].pop(keywordId)
                    if len(bindConversionData['keyword'][mediaCode][adId]) == 0:
                        bindConversionData['keyword'][mediaCode].pop(adId)
                    if len(bindConversionData['keyword'][mediaCode]) == 0:
                        bindConversionData['keyword'].pop(mediaCode)

            stateData[key].insert(5, keyword)
            stateData[key].insert(4, groupName)
            stateData[key].insert(3, campaignType)
            stateData[key].insert(3, campaignName)
            stateData[key].insert(2, customerName)
            stateData[key].append(conversionCnt)
            stateData[key].append(conversionAmnt)

        return stateData

    #Campaign, Adgroup, Keyword
    def downloadTargetData(self,customerId,customer):

        self.downloadReportData('Campaign', customerId, customer)
        self.downloadReportData('Adgroup', customerId, customer)
        self.downloadReportData('Keyword', customerId, customer)

    # 생성된 파일의 데이터 가져오기
    def getTargetFileData(self,customerId):
        """ 생성된 파일의 데이터 가져오기 """

        helper = self.getHelper()
        masterPath = helper.relationJoinPath('file',self.processName,'report','master')
        statePath = helper.relationJoinPath('file',self.processName,'report','state')

        keywordFile = helper.joinPath(masterPath, 'Keyword', "{}.tsv".format(customerId))
        campaignFile = helper.joinPath(masterPath, 'Campaign', "{}.tsv".format(customerId))
        groupFile = helper.joinPath(masterPath, 'Adgroup', "{}.tsv".format(customerId))
        stateFile = helper.joinPath(statePath, 'AD', "{}.tsv".format(customerId))
        conversionFile = helper.joinPath(statePath, 'AD_CONVERSION', "{}.tsv".format(customerId))

        keywordData = {} if helper.isFile(keywordFile) == False else helper.bindTsvFileByKey({2: 3}, keywordFile)
        campaignData = {} if helper.isFile(campaignFile) == False else helper.bindTsvFileByKey(
            {1: {'campaign_name': 2, 'campaign_type': 3}}, campaignFile)
        groupData = {} if helper.isFile(groupFile) == False else helper.bindTsvFileByKey({1: 3}, groupFile)
        stateData = {} if helper.isFile(stateFile) == False else helper.getTsvFileData(stateFile)
        conversionData = {} if helper.isFile(conversionFile) == False else helper.getTsvFileData(conversionFile)

        return [
            keywordData,campaignData,groupData,stateData,conversionData
        ]

    def downloadReportCheckData(self,apiType):
        apiClient = self.getMasterReportClient()
        reportIdKey = 'id'
        # 헬퍼클래스 생성
        helper = self.getHelper()
        # 파일 PATH
        filePath = helper.relationJoinPath('file',self.processName,'report','master')

        if apiType != 'master':
            apiClient = self.getStateReportClient()
            reportIdKey = 'reportJobId'
            filePath = helper.relationJoinPath('file',self.processName,'report','state')

        return [
            apiClient,
            reportIdKey,
            filePath
        ]

    # 리포트 데이터 다운로드
    def downloadReportData(self, type, customerId, customer, date=None, apiType='master'):
        # 헬퍼클래스 생성
        helper = self.getHelper()
        returnResult = False
        apiClient,reportIdKey,filePath = list(self.downloadReportCheckData(apiType))
        apiClient.setCustomerId(customerId)
        createResult = self.createReport(apiClient,type,date)

        if createResult == False or reportIdKey not in createResult:
            self.getHistoryModel().insertHistoryMessage(self.getHistoryId()
                                                    ,"{} REPORT CREATE ERROR CUSTOMER ID : {} ,CUSTOMER : {}, RESULT : {}".format(type,customerId,customer,createResult))
            return False

        createResult = self.checkReport(type,apiClient,reportIdKey,customerId,customer,createResult)

        createStatus = createResult['status']

        # 리포트 생성중이면
        if createStatus == 'BUILT':
            # 디렉토리 확인
            helper.checkDir("{}/{}".format(filePath,type))
            # 리포트 tsv 다운로드 생성
            self.reportDownload(apiClient,createResult['downloadUrl'],helper.joinPath(filePath,type,'{}.tsv'.format(customerId)))
            returnResult = True

        ## 파일 생성 제한 오류 100 개
        if createStatus in [400, 403]:
            self.getHistoryModel().insertHistoryMessage(self.getHistoryId(),"{} ERROR CUSTOMER ID : {} ,CUSTOMER : {}, MESSAGE : {}".format(type,customerId, customer,
                                                                                       createResult['detail']))
       # none 이나 error이면 리포트 삭제                                                                                
        elif createStatus in ['NONE', 'ERROR']:
            self.deleteReport(apiClient, createResult[reportIdKey])
        # 그 밖이면 리포트 삭제
        else:
            self.deleteReport(apiClient, createResult[reportIdKey])
        return returnResult

    def checkReport(self,type,apiClient,reportIdKey,customerId,customer,createResult):
        reportId = createResult[reportIdKey]
        while True:
            if createResult['status'] in ['BUILT', 'NONE', 'ERROR', 400, 403]:
                break
            time.sleep(1)
            try:
                createResult = apiClient.get(reportId)
            #오류발생시
            except Exception as e:
                self.getHistoryModel().insertHistoryMessage(self.getHistoryId()
                                                        ,"{} REPORT CREATE ERROR CUSTOMER ID : {} ,CUSTOMER : {}, MESSAGE : {}".format(type, customerId,customer,e))
                continue
        return createResult

    # tsv파일에서 keywordId 가져오기
    def bindKeywordById(self,file):
        data = self.getHelper().getTsvFileData(file)
        bindData = {}

        for row in data:
            keywordId = row[2]
            keywordName = row[3]

            bindData[keywordId] = keywordName

        return bindData

    # 리포트 삭제
    def deleteReport(self,api, id):
        result = self.getHelper().limitedTryCalls(api.delete,reportId=id)

        if result==False:
            self.getHistoryModel().insertHistoryMessage(self.getHistoryId()
                                                        , "DELETE REPORT FAIL : {}".format(id))
        return result

    # 리포트 생성
    def createReport(self,api, type, date=None):
        result = self.getHelper().limitedTryCalls(api.create,reportType=type,statDt=date)

        if result==False:
            self.getHistoryModel().insertHistoryMessage(self.getHistoryId()
                                                        , "CREATE REPORT FAIL : {} {}".format(type,date))
        return result

    # 리포트 다운로드
    def reportDownload(self,api, url, path):
        result = self.getHelper().limitedTryCalls(api.download,url=url,file=path)

        if result==False:
            self.getHistoryModel().insertHistoryMessage(self.getHistoryId()
                                                        , "REPORT DOWNLOAD ERROR - URL : {} ,PATH : {}".format(
                                                                 url, path))
        return result

    def getMediaRowReport(self):
        return MediaRowReport()

    def getMasterReportClient(self):
        return self.masterReportClient

    def getStateReportClient(self):
        return self.stateReportClient

    # 네이버 인증 설정 가져오기
    def getAuthConfig(self):
        """
        :return: {}
        네이버 인증 설정 가져오기
        """
        return self.getMediaConfig()['auth'][0]