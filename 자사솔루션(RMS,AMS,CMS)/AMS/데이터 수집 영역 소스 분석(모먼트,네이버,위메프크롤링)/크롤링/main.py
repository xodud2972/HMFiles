# -*- coding: utf-8 -*-
import sys,os,csv,base64,time,datetime,traceback
sys.path.append(".")
from crawling.FeedData import FeedData
from crawling.MallSwitcher import Driver

def main(target=None):

    resultFileData = []
    targetFeedData = []
    driverSwitcher = Driver()


    try:
        # feedDate
        feedData = FeedData()
        # 상태값에 따른 대상 가져오기
        targetFeedData = feedData.getTargetAdvetiser(target)
        # targetFeedData가 있으면
        if len(targetFeedData) > 0:
            targetFeedData = feedData.targetProcessingUpdate(data=targetFeedData)
    #에러시
    except (Exception) as e:
        # 아래문구 출력
        print('ERROR :',e,'관리자 확인 필요')
        # 에러출력
        traceback.print_exc()
        return
    # targetFeedData 배열 개수만큼
    for feed in targetFeedData:
        # 시작날짜 
        startDate = datetime.datetime.now()
        # 빌더형에 따른 driver
        driver = driverSwitcher.getDriver(feed['builder_type'])
        # driver reset
        driver.reset()

        try:
            # 로그인
            driver.login(feed['mall_id'], feed['mall_pw'], feed['advertiser_url'])
        #에러시 
        except (Exception) as e:
            resultFileData.append(errorResultDataBind(feed,startDate,'계정 정보 오류'))
            # 아래문구 출력
            print('ERROR :',e,'계정 정보 오류')
            # 에러출력
            traceback.print_exc()
            continue

        try:
            # siteFeedData
            siteFeedData = driver.getSiteFeedData(feed)

            feedData.feedDataUpdate(feed['advertiser_id'],siteFeedData)
            fileData = feedData.getMakeFileData(feed['advertiser_id'])

            bindData = driver.bindFeedData(fileData,feed)
            rowFileData = feedData.makeFile(feed['advertiser_url'],feed['advertiser_name'],feed['created_at'],bindData)
            # 끝 날짜
            endDate = datetime.datetime.now()
            rowFileData = resultDataBind(
                rowFileData,feed,startDate,endDate,len(bindData),'처리 성공'
            )

            resultFileData.append(rowFileData)
        # 에러발생시
        except (Exception) as e:
            resultFileData.append(errorResultDataBind(feed,startDate,'관리자 확인 필요'))
            # 아래문구 출력
            print('ERROR :',e,'관리자 확인 필요')
            # 에러 출력
            traceback.print_exc()
            continue
    #resultFileData 이 1개 이상이면 
    if len(resultFileData) > 0:
        # feedData update
        try:
            feedData.updateFileData(resultFileData)
        # 에러발생시 에러출력
        except (Exception) as e:
            print(resultFileData)
            print('ERROR :',e)
            traceback.print_exc()

    feedData.close()

# 에러 결과 데이터
def errorResultDataBind(feed,startDate,message):
    endDate = datetime.datetime.now()
    return resultDataBind(
        {},feed,startDate,endDate,0,message
    )

# resultData 묶기
def resultDataBind(rowFileData,feed,startDate,endDate,productCount,message):
    rowFileData['advertiser_id'] = feed['advertiser_id']
    rowFileData['history_id'] = feed['increment_id']
    rowFileData['start_date'] = startDate
    rowFileData['end_date'] = endDate
    rowFileData['product_count'] = productCount
    rowFileData['message'] = message
    return rowFileData

# main이면 실행
if __name__ == '__main__':
    try:
        while(True):
            print('START',time.strftime('%c', time.localtime(time.time())))
            main()
            print('END',time.strftime('%c', time.localtime(time.time())))
            time.sleep(60)
    except KeyboardInterrupt:
        print('Interrupted')
        try:
            sys.exit(0)
        except SystemExit:
            os._exit(0)

