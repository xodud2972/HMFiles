# Import 영역
from datetime import datetime
import sys, argparse, time, json
sys.path.append(".")
from media.multiprocess import MediaMultiProcess
from media.mediashwitcher import MediaSwitcher
from media.helpers import helper as mainHelper
from media.models import history
from media.config import Config
from recollecting import Recollecting


def mediaProcess():
    """ 매체별 mutilprocessing main
    """
    # 시작 시간 저장
    start = time.time()  
    # 공통 HELPER CLASS
    helper = mainHelper.Helper() 
    ## 프로세스 로그 메시지, 프로세스 시작 시간, 수집 일자, 상태 초기 설정
    processMessage,startDate,targetDate,status = list(['SUCCESS',helper.getNowDateString(),None,True])
    ## 설정 정보 관련 CLASS
    config = Config() 
    # 명령어 인자체크
    media,targetDate,updateCheck = list(processCheckArgv(helper,config)) 
    # history CLASS
    historyModel = history.HistoryModel() 
    # 매체 한글명
    mediaKr = historyModel.getMediaCode(config.getAllConfig()[media]['name_kr'])  
    # history ID 
    historyId = historyModel.insertMediaHistory(targetDate.strftime('%Y-%m-%d'),mediaKr) 

    try: #정상적으로 실행되는 경우
        # 병렬처리 CLASS
        multiProcess = MediaMultiProcess() 
         # 함수의 인자들을 이용하여 매체별로 분기
        processer = MediaSwitcher().getMedia(media,targetDate,historyId,updateCheck)
        # 병럴처리 진행할 매체들 리스트
        targetList = processer.getTargetList() 
        # 병렬처리 진행
        multiProcess.startMultiProcess(targetList, processer,{})  
         # 작업 중 내용
        processMessage = processer.resultProcess(historyId)
        # 작업 중 내용이 있으면 출력하고
        if len(processMessage) > 0: 
            processMessage = "\r\n".join(processMessage)
        # 작업 중 내용이 없으면 성공
        else:                      
            processMessage = "SUCCESS"
        endDate = helper.getNowDateString()
        # 프로세스 테이블 업데이트
        historyModel.updateMediaProcess(historyId,endDate,processMessage)

    except Exception as e:
        # import
        import traceback    
        #에러메세지
        processMessage = traceback.format_exc()
        #에러 발생의 경우 에러내용을 출력
        print(processMessage)
         #에러 발생의 경우 에러내용을 출력
        print(e)
        # 현재 일시 반환 (끝 날짜)
        endDate = helper.getNowDateString()
        #프로세스 테이블 업데이트
        historyModel.updateMediaProcess(historyId,endDate,processMessage)
        #print("time :", time.time() - start)  # 현재시각 - 시작시간 = 실행 시간
# 명령어 인자 체크 함수
def processCheckArgv(helper,config):
    """
    :param helper: Helper
    :param config: Config
    :return: []
    명령어 인자 체크 함수
    """

    # Config CLASS
    allConfig = config.getAllConfig()
    parser = argparse.ArgumentParser()
    # 매체명
    parser.add_argument("media", help="target media - {}".format(' , '.join(allConfig.keys()))) 
    # 수집 일자
    parser.add_argument("-d", "--date", help="target date format YYYY-MM-DD example) 2020-11-20") 
    # t_advertiser_media_management,t_advertiser_media_history 테이블 업데이트
    parser.add_argument("-uc", "--update-check", help="advertiser media history update check - true or false")
    
    args = parser.parse_args()

    #날짜가 있으면 해당날짜
    if args.date is not None:
        targetDate = helper.getDateFromString(args.date).date()
    #날짜가 없으면 어제날짜
    else:
        targetDate = helper.getYesterdayDate()
    # default는 true
    if args.update_check == 'true':
        updateCheck = True
    # update_check이 false면 updateCheck도 False
    else:
        updateCheck = False

    media = args.media
    
    #배열형태로 리턴
    return [ media, targetDate,updateCheck]

# main일 떄 해당함수 실행
if __name__ == '__main__':
    mediaProcess()
