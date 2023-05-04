# -*- coding: utf-8 -*-
from crawling.mallDriver.Cafe24 import Cafe24
from crawling.mallDriver.MakeShop import MakeShop
from crawling.mallDriver.GodoMall import GodoMall

# 빌더형에 따른 드라이버 카페24, 메이크샵, 고도몰
class Driver:

    def getDriver(self,builder_type):
        self.driverName = 'driver_'+str(builder_type)
        self.driver = getattr(self,self.driverName,lambda :"default")
        return self.driver()

    def driver_1(self):
        return Cafe24()

    def driver_2(self):
        return MakeShop()

    def driver_3(self):
        return GodoMall()
