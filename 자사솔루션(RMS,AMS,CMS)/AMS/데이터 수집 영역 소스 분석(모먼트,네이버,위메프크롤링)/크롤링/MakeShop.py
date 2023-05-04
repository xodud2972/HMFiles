# -*- coding: utf-8 -*-
import requests,sys,json,re
import xml.etree.ElementTree as ET
from urllib.parse import urlparse, parse_qsl, urlencode, urlunparse
from bs4 import BeautifulSoup as bs


class MakeShop:

    session = None

    id = None

    url = None

    password = None

    pageLimit = 100

    headers = {}

    urlInfo = {
        'prepareLoginUrl':'https://www.makeshop.co.kr/main/ssllogin.html',
        'productListUrl' : '/makeshop/newmanager/product_list.action.html'
    }

    ## GoogleUrl Bind Key
    bindKeys = {
        'ID' : 'id',
        '제목' : 'title',
        '설명' : 'description',
        '링크' : 'link',
        '이미지링크' : 'image_link',
        '재고수량' : 'availability',
        '가격' : 'price',
        '상표' : 'brand',
        'google 상품 카테고리' : 'google_product_category',
        '상태' : 'condition',
        'mpn' : 'mpn',
    }
    #초기화
    def __init__(self):
        self.session = requests.Session()
    # reset
    def reset(self):
        self.session.close()
        self.session = requests.Session()
        self.setDefaultHeader()
    # header setting
    def setDefaultHeader(self):
        self.headers = {
            'user-agent':'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
            'accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Connection': 'keep-alive',
            'accept-language': 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
            'cache-control': 'max-age=0',
            'origin': 'https://www.makeshop.co.kr',
            'referer': 'https://www.makeshop.co.kr/newmakeshop/front/login.html?suburl=&onmenutype=',
            'sec-fetch-mode': 'navigate',
            'sec-fetch-site': 'same-origin',
            'sec-fetch-user': '?1',
            'upgrade-insecure-requests': '1',
        }
    def leftProductChangeStock(data,advertiserId,processData):

        for key,item in processData['products'].items():
            product = json.loads(item['data'])
            if product['재고수량'] == 'in stock':
                product['재고수량'] = 'out of stock'
                jsonData = json.dumps(product, ensure_ascii=False)
                processData['update'].append([jsonData, product['id']])
                processData['change'].append([advertiserId,product['id']])


        return processData

    def refererSet(self,url):
        url_info = urlparse(url)
        self.headers['origin'] = url_info.scheme + '://' + url_info.netloc
        self.headers['referer'] = url
    # response
    def post(self,url,form_data=None):
        headers = self.headers
        headers['content-type'] = 'application/x-www-form-urlencoded'
        response = self.session.post(url,data=form_data,headers=headers)
        self.refererSet(response.url)
        return response
    def get(self,url,params=None):
        response = self.session.get(url,params=params,headers=self.headers)
        self.refererSet(response.url)
        return response
    # 피드 데이터 
    def getFeedData(self):
        response = self.get(self.url+'/facebookbusiness.xml')
        response.encoding='utf-8'
        xml = response.text
        root = ET.fromstring(xml)
        result = []
        tsv = ''

        for channel in root:
            for count,item in enumerate(channel.findall('item')):
                rowData = {}

                for row in item:
                    rowData[row.tag.replace('{http://base.google.com/ns/1.0}', '')] = row.text.replace('\t','')

                result.append(rowData)

        return tsv
    # 묶은 피드데이터
    def bindFeedData(self, data, advertiserData):

        result = []
        bindKeysData = self.bindKeys
        advertiserUrl = urlparse(advertiserData['advertiser_url'])

        for row in data :

            dictData = json.loads(row[0])
            subResult = {}

            for key,value in bindKeysData.items():

                if (key in dictData) == False:
                    continue
                valueData = dictData[key]

                if value == 'link' or value == 'image_link':
                    url_info = urlparse(valueData)
                    url_info = url_info._replace(scheme='https',netloc=advertiserUrl.netloc)
                    valueData = url_info.geturl()
                elif value == 'description':
                    valueData = valueData[0:5000]

                subResult[value] = valueData

            result.append([json.dumps(subResult,ensure_ascii=False),advertiserData['advertiser_id'],dictData['id']])

        return result
    # 로그인 준비
    def prepareLogin(self):

        response = self.post(self.urlInfo['prepareLoginUrl'], {
            'shopurl':  self.url,
            'subid': self.id,
            'passwd': self.password,
            'logtype': 'sublogin',
            'easypass':'',
            'smslogin_key': 'smslogin',
            'ret_type': 'smslogin_send',
        })

        return response
    # 로그인
    def login(self, advertiser):
        return
        self.id = advertiser['mall_id']
        self.password = advertiser['mall_pw'][0:12]
        url_info = urlparse(advertiser['advertiser_url'])
        self.url = url_info.scheme + '://' + url_info.netloc
        html = self.prepareLogin().text

        loginUrl = re.search(r'document.ssllogin.action=(.*);', html, re.S).group(1).split(';')[0].replace('"', '')

        self.post(loginUrl,{
            'type': '',
            'cid': '',
            'userid': '',
            'sub_pop_url': '',
            'sellpia_cookie': '',
            'authnum': '',
            'msecure_key': '',
            'easypass': '',
            'subid': '',
            'sslip': self.url,
            'formname': 'loginform',
            'sendfunc': 'sslnewsend',
            'smslogin_key': 'smslogin',
            'sub_selected': 'Y',
            'etclogin': 'ok',
            'url': 'goodstore.or.kr',
            'initLoginAuthState': 'Y',
            'submanager': 'sub',
            'id': self.id,
            'passwd': self.password,
            'ssl': 'Y',
            'https': 'Y'
        })
    #피드데이터 url링크
    def getFeedDataByUrlLink(self,builderFeedUrl):

        response = self.get(builderFeedUrl)
        response.encoding = 'euc-kr'
        soup = bs(response.text, 'html.parser')
        keys = []
        result = []

        for key,item in enumerate(soup.select('tr')):

            row = {}

            if key == 0:
                for column in item.select('td'):
                    keys.append(column.text)
                continue

            for key,column in enumerate(item.select('td')):

                if key == 0:
                    row['id'] = column.text
                row[keys[key]] = column.text

            result.append(row)

        return result
    # 사이트 피드데이터 link
    def getSiteFeedData(self,advertiserData):
        return self.getFeedDataByUrlLink(advertiserData['advertiser_feed_url'])
    # 피드데이터 상품목록 리스트
    def getFeedDataByProductList(self,mallUrl):
        page = 1
        pageLimit = self.pageLimit
        productListUrl = self.headers['origin']+self.urlInfo['productListUrl']
        totalCount = None
        result = []

        while 1:

            requestData = self.productListRequestQuery(page,pageLimit)
            response = self.post(productListUrl,requestData)
            json_string = response.text
            jsonObj = json.loads(json_string)
            data = jsonObj['data']

            if (totalCount == None):
                totalCount = int(jsonObj['ext_data']['list_cnt'])

            for key,row in enumerate(data):
                data[key]['link'] = mallUrl+'/index.html?branduid='+row['uid']
                data[key]['availability'] = 'in stock'
                data[key]['condition'] = 'new'
                data[key]['id'] = row['uid']



            result.extend(data)

            if (totalCount <= pageLimit * page):
                break

            page += 1

        return result
    # 상품목록 요청쿼리
    def productListRequestQuery(self,page,pageLimit):
        return {
            'command': 'GetProductList',
            'page': page,
            'sub_supply': 'N',
            'search_chk_val': 'N',
            'xcode': 'none',
            'mcode': 'none',
            'scode': 'none',
            'datecheck': 'REG',
            'list_keyword': 'brandname',
            'select_order': 'regdate-desc',
            'checkprice': 'sellprice',
            'select_percent': 'ALL',
            'provider': 'all',
            'list_limit': pageLimit,
            'regdate_start': '',
            'regdate_end': '',
            'keyword': '',
            'price_start': '',
            'price_end': '',
            'fsoldout': '',
            'fdisplay': '',
            'flist_keyword': '',
            'fkeyword': '',
            'page_type': '',
            'display': 'Y',
            'special': '',
            'provider_sort_a': '',
            'provider_sort_b': '',
            'soldout': 'N',
            'sell_accept': 'Y',
            'dicker': '',
            'list_order': ''
        }
    # 종료
    def __del__(self):
        self.session.close()
