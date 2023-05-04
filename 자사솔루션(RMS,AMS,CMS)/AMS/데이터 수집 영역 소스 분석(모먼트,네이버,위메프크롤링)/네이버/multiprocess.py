# -*- coding: utf-8 -*-
from media.process.abstract.abstractprocess import AbstractProcess


class MultiProcess(AbstractProcess):

    def __init__(self, targetDate=None, processId=None, managementUpdateCheck=False):
        super().__init__(targetDate, processId,managementUpdateCheck)

    def getTargetList(self):
        """ 멀티 프로세스 자원 반환 공통 """
        result = super().getTargetList()
        self.closeResource()
        return result

    def execute(self,advertiser,shareData=None):
        """ 멀티 프로세스 자원 반환 공통 """
        result = super().execute(advertiser,shareData)
        self.closeResource()
        return result