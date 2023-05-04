# -*- coding: utf-8 -*-
import requests,sys,json,re
from urllib.parse import urlparse, parse_qsl, urlencode, urlunparse
from bs4 import BeautifulSoup as bs


class Cafe24:

    session = None

    id = None

    password = None

    url = None

    mode = None

    pageLimit = 100

    headers = {}

    urlInfo = {
        'prepareLoginUrl':'https://eclogin.cafe24.com/Shop/index.php',
        'modeCheckUrl': '/disp/admin/shop1/product/DashboardMain',
        'modeChangeUrl': '/admin/php/influencer.php',
        'proModeProductUrl' : '/disp/admin/shop1/product/productmanage'
    }

    bindKeys = {
        'id' : 'id',
        'title' : 'title',
        'description' : 'description',
        'link' : 'link',
        'image_link' : 'image_link',
        'availability' : 'availability',
        'sale_price' : 'price',
        'brand' : 'brand',
        # 'gtin' : 'gtin',
        # 'mpn' : 'MPN',
        'condition' : 'condition',
    }
    # 초기화
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
            'accept-encoding': 'gzip, deflate, br',
            'accept-language': 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
            'cache-control': 'max-age=0',
            'origin': 'https://eclogin.cafe24.com',
            'referer': 'https://eclogin.cafe24.com/Shop',
            'sec-fetch-mode': 'navigate',
            'sec-fetch-site': 'same-origin',
            'sec-fetch-user': '?1',
            'upgrade-insecure-requests': '1',
        }


    def leftProductChangeStock(data,advertiserId,processData):

        for key,item in processData['products'].items():
            product = json.loads(item['data'])
            if product['availability'] == 'in stock':
                product['availability'] = 'out of stock'
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

    def getMode(self):

        if(self.mode == None):
            response = self.get(self.headers['origin']+self.urlInfo['modeCheckUrl'])
            soup = bs(response.text, 'html.parser')
            if len(soup.select('.icoProMode')) > 0:
                self.mode = 'pro'
            else:
                self.mode = 'smart'

        return self.mode

    def setMode(self,mode=None):
        test = self.get(self.headers['origin']+self.urlInfo['modeChangeUrl'],params=mode)
    # 피드데이터
    def getFeedData(self):
        response = self.get(self.headers['origin']+'/disp/admin/shop1/external/ExternalscriptFacebookdburl')
        soup = bs(response.text, 'html.parser')
        feedUrl = soup.select('.mBoard')[0].select('td')[1].text.strip()
        response = self.get(feedUrl)
        tsv = response.text
        result = []
        keys = []
        keyLength = 0

        for key, line in enumerate(tsv.split('\n')):
            lineData = line.split('\t')
            lineRow = {}

            if len(lineData) < keyLength:
                continue

            if key == 0:
                keys = lineData
                keyLength = len(lineData)
                continue

            for i in range(0, keyLength - 1):
                lineRow[keys[i]] = lineData[i]

            result.append(lineRow)

        return result
    # 묶은 피드데이터
    def bindFeedData(self,data,advertiserData):

        result = []
        bindKeysData = self.bindKeys;
        advertiserUrl = urlparse(advertiserData['advertiser_url'])


        for row in data :

            dictData = json.loads(row[0])
            subResult = {}

            for key,value in bindKeysData.items():
                valueData = dictData[key]
                if value == 'link' or value == 'image_link':
                    url_info = urlparse(valueData)
                    url_info = url_info._replace(scheme='https',netloc=advertiserUrl.netloc)
                    valueData = url_info.geturl()
                elif value == 'price':
                    valueData = str(valueData) + ' KRW'
                elif value == 'condition':
                    valueData = valueData.lower()
                elif value == 'description':
                    valueData = valueData[0:5000]

                subResult[value] = valueData

            result.append([json.dumps(subResult,ensure_ascii=False),advertiserData['advertiser_id'],dictData['id']])

        return result
    # 프로모드
    def setProMode(self):
        self.setMode()
        self.mode=None
    # 스마트 모드
    def setSmartMode(self):
        self.setMode({
            'mode':'smart'
        })
        self.mode = None
    # 로그인준비
    def prepareLogin(self):

        response = self.post(self.urlInfo['prepareLoginUrl'], {
            'url': 'Run',
            'IS_DEVSERVER': '',
            'login_mode': '1',
            'use_second_auth': 'F',
            'second_auth_method': '',
            'caller_id': '',
            'userid': self.id,
            'mall_id': self.id,
            'userpasswd': self.password,
            'auth_number': '',
            'onnode': '',
            'menu': '',
            'submenu': '',
            'mode': '',
            'c_name': '',
            'loan_type': '',
            'addsvc_suburl': '',
            'is_multi': 'F',
            'appID': '',
        })

        return response
    # 로그인
    def login(self, advertiser):

        self.id = advertiser['mall_id']
        self.password = advertiser['mall_pw']
        self.url = advertiser['advertiser_url']

        prepareHtml = bs(self.prepareLogin().text, 'html.parser')

        encData = prepareHtml.select('input[name="CAFE24EncData"]')[0]['value']

        userIdCheckPage = self.post(prepareHtml.select('form')[0]['action'],{
            'is_auth': 'T',
            'CAFE24EncData': encData
        });

        userIdCheckHtml = bs(userIdCheckPage.text, 'html.parser')

        lastCheckPage = self.post(userIdCheckHtml.select('form')[0]['action'],{
            'EncData': userIdCheckHtml.select('input[name="EncData"]')[0]['value'],
            'EncKey': userIdCheckHtml.select('input[name="EncKey"]')[0]['value'],
            'loginId': userIdCheckHtml.select('input[name="loginId"]')[0]['value'],
            'loginPasswd': userIdCheckHtml.select('input[name="loginPasswd"]')[0]['value'],
            'ecEncData': userIdCheckHtml.select('input[name="ecEncData"]')[0]['value'],
        })

        lastLoginUrl = lastCheckPage.text.replace('<script type="text/javascript">', '').replace('</script>', '').replace("document.location = '", '').replace("';", '').strip()

        if 'document.location.pathname' in lastLoginUrl:
            lastLoginUrl = self.chagePasswordPagePass(id)

        self.get(lastLoginUrl)
        self.setProMode()
    def chagePasswordPagePass(self,id):
        lastLoginUrl = self.headers['referer']+'?action=comForce&req=hosting'
        response = self.get(self.headers['referer'],params={
            'action' : 'comForce',
            'req' : 'hosting'
        })

        response = self.post(self.headers['origin']+'/comLogin/?action=comForceSkip',{
            'userId':id
        })
        response = self.get(self.headers['origin']+'/comLogin/',params={
            'page' : 'com_auth_return'
        })

        lastLoginUrl = response.text.replace('<script type="text/javascript">', '').replace('</script>', '').replace("document.location = '", '').replace("';", '').strip()

        return lastLoginUrl
    def getSiteFeedData(self,advertiserData):
        # return self.getFeedData();
        return self.getFeedDataByProductList(advertiserData['advertiser_url'])
    # 피드데이터 상품목록
    def getFeedDataByProductList(self,mallUrl):

        page = 1
        pageLimit = self.pageLimit
        productListUrl = self.headers['origin']+self.urlInfo['proModeProductUrl']
        totalCount = None
        result = []
        cleaner = re.compile('<.*?>')

        while 1:

            requestData = self.productListRequestQuery(page,pageLimit)
            response = self.get(productListUrl,requestData)
            soup = bs(response.text, 'html.parser')

            if (totalCount == None):
                totalCount = int(soup.select('.total > strong')[0].text)


            for product in soup.select('.ec-product-manage-list'):
                data = product.select('td')
                id = data[0].select('input')[0]['value']
                title = data[4].select('p > a')[0].text

                result.append({
                    'id': id,
                    # 'division': data[2].text,
                    # 'sku': data[3].text,
                    'title': title,
                    'description': " | ".join([re.sub(cleaner,'',str(item)) for item in data[4].select('.etc > li')]),
                    'link' : data[4].select('li')[1].select('a')[0]['url'],
                    'image_link': data[4].select('img')[0]['src'],
                    # 'price': data[5].text,
                    'sale_price': data[7].text.replace('\n', '').replace(',',''),
                    # 'm_discount_price': data[7].text.replace('\n', ''),
                    'availability' : 'in stock',
                    'brand' : title,
                    'condition' : 'new'

                })

            if (totalCount <= pageLimit * page):
                break

            page += 1

        return result
    # 상품목록 요청쿼리
    def productListRequestQuery(self,page,pageLimit):
        return {
            'category': '' ,
            'display_division': '' ,
            'addService': '' ,
            'nMileAddUse': '' ,
            'nCheckOutUse': '' ,
            'bIsSearchClickAction': 'T',
            'date': 'regist',
            'date_type': '-1',
            'display': 'T',
            'selling': 'T',
            'product_type': 'all',
            'market_selecter': 'A',
            'market_search_type': 'market',
            'UseStock': 'A',
            'stock_importance': 'T',
            'use_soldout': 'A',
            'soldout_status': 'A',
            'item_display': 'A',
            'item_selling': 'A',
            'naver_co_use_flag_type': 'C',
            'page': page,
            'orderby': 'regist_d',
            'limit': pageLimit,
            'Trend[]':'',
            'Brand[]':'',
            'Supplier[]':'',
            'eValue[]':'',
            'Manufacturer[]':'',
            'Classification[]':'',
            'Globalorigin[]':'',
            'origin_place_no[]':'',
            'made_in[]':'',
            'categorys[]':'',
            'categorys[]':'',
            'categorys[]':'',
            'categorys[]':'',
            'stock_min[]':'',
            'stock_max[]':'',
            'price_min[]':'',
            'price_max[]':'',
            'eField[]': 'product_name',
            'Condition[]': 'N',
            'origin_level1[]': 'F',
            'stockcount[]': 'stock',
            'price[]': 'product',
            'default_column[no]': 'T',
            'default_column[product_type]': 'T',
            'default_column[product_code]': 'T',
            'default_column[shop_product_name]': 'T',
            'default_column[sale_price]': 'T',
            'default_column[mobile_sale_price]': 'T',
            'place_parent_no[F][]':'',
            'place_parent_no[T][]': ''
        }
    # 종료
    def __del__(self):
        self.session.close()
