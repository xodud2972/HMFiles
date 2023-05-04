# -*- coding: utf-8 -*-
from media.process.abstract.abstractprocess import AbstractProcess
from media.models.advertiser.media.process.singleProcess import Management



# abstractprocess.py 상속
class SingleProcess(AbstractProcess):

    #초기화
    def __init__(self, targetDate=None, processId=None, managementUpdateCheck=False):
        # 초기화
        super().__init__(targetDate, processId,managementUpdateCheck)
        # 피이관 초기화
        self.outTransferData = {}
        # 재이관 초기화
        self.reTransferData = {}

    # 싱글 프로세스 처리용 피이관,재이관, 매체별 피이관 X 데이터 X 계정 처리
    def resultProcess(self,processId,bindData=None,hasKeyword=True):
        """ 싱글 프로세스 처리용 피이관,재이관, 매체별 피이관 X 데이터 X 계정 처리 """

        # 업데이트 체크 있으면
        if self.getManagementUpdateCheck():
            # 매체별 피이관 X 데이터 X 계정 처리
            self.leftAccountsProcess() 
            # 피이관
            self.outTransferProcess()
            # 재이관  
            self.reTransferProcess() 
        # 리포트 입력 및 로그 관리 추가
        return super().resultProcess(processId,bindData,hasKeyword)

    # 싱글 프로세스용 MANAGEMENT MODEL 반환
    def getManagementModel(self,shareDb=True):
        # managementModel 가 없으면 dbName 생성
        if self.managementModel is None:
            # 처리중 PROCESS CONFIG DICT 반환
            dbName = self.getMediaConfig()['db']['db_name'] 

            # 싱글 프로세스용 MANAGEMENT MODEL
            # shareDb true - 싱글 처리 , false - 병렬 처리
            if shareDb:
                # 싱글
                self.managementModel = Management(self.getTargetDate(),dbName,self.getManagementUpdateCheck(),self.getHelper(),self.getDb(),self.getDefaultDb())
            else:
                # 병렬
                self.managementModel = Management(self.getTargetDate(), dbName,self.getManagementUpdateCheck())
        #싱글이면 싱글 병렬이면 병렬 반환
        return self.managementModel

    # 피이관
    def outTransferProcess(self,outTransferData=None):
        """ 피이관 처리 """
        if self.getManagementUpdateCheck() == False:
            return

        # 피이관 데이터가 없으면  피이관데이터 설정
        if outTransferData is None:
            # 피이관데이터 설정
            outTransferData = self.outTransferData

        ## gfa와 같은 파일 형태일 경우 이탈 날짜가 어제가 아니라 리포트 발생 일자여야함
        if 'is_file_upload_type' in self.getMediaConfig():
            outDateString = self.getTargetDate().strftime('%Y-%m-%d')
        else:
            outDateString = self.getHelper().getBeforeDate(1, self.getHelper().getNowDate()).strftime('%Y-%m-%d')

        # 싱글 프로세스용 MANAGEMENT MODEL 반환     
        managementModel = self.getManagementModel() 
        # 상태 변경 묶음 처리
        managementModel.bulkStatusUpdate(outTransferData,outDateString,managementModel.getOutTransferStatusValue()) 
        # 피이관 30일 광고비 정보 업데이트
        self.outTransfer30CostUpdate(outTransferData) 

    # 매체별 피이관 X 데이터 X 계정 처리
    def leftAccountsProcess(self):
        """ 매체별 피이관 X 데이터 X 계정 처리 """
        if self.getManagementUpdateCheck() == False:
            return
        # 헬퍼 클래스 생성
        helper = self.getHelper() 
        # 싱글 프로세스용 MANAGEMENT MODEL 반환
        managementModel = self.getManagementModel() 
        # 피이관 X , 데이터 X 데이터 처리
        managementModel.leftAccountsProcess(self.getMediaCode(),self.getMediaConfig()['m_nm'],self.getDefaultDb(),helper) 

    # 재이관
    def reTransferProcess(self):
        """ 재이관 처리 """
         # media_history , media_management 업데이트 체크
        if self.getManagementUpdateCheck() == False:
            return
        ## gfa 같이 파일 형태일 경우 date가 어제가 아니라 리포트 발생 일자가 되야함
        if 'is_file_upload_type' in self.getMediaConfig():
            dateString = self.getTargetDate().strftime('%Y-%m-%d')
        else:
            dateString = self.getHelper().getBeforeDate(1, self.getHelper().getNowDate()).strftime('%Y-%m-%d')
         # 싱글 프로세스용 MANAGEMENT MODEL 반환
        managementModel = self.getManagementModel()
        # 상태 변경 묶음 처리
        managementModel.bulkStatusUpdate(self.reTransferData,dateString,managementModel.getReTransferStatusValue()) 
