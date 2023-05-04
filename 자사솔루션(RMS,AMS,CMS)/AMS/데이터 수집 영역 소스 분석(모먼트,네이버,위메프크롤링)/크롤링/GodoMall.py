# -*- coding: utf-8 -*-
import sys,json,time
from urllib.parse import urlparse, parse_qsl, urlencode, urlunparse
from bs4 import BeautifulSoup as bs
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import crawling.config as config

# 고돔ㄹ
class GodoMall:

	driver = None

	url = None

	type = 'godo5'

	pageLimit = 500

	bindKeys = {
		'id' : 'id',
		'title' : 'title',
		'description' : 'description',
		'link' : 'link',
		'image_link' : 'image_link',
		'availability' : 'availability',
		'price' : 'price',
		'brand' : 'brand',
		'condition' : 'condition',
	}

	#  webdriver.ChromeOptions()
	def newDriver(self):
		options = webdriver.ChromeOptions()
		options.add_argument("--user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36")
		options.add_argument('--window-size=1920x1080')
		options.add_argument("--disable-gpu")
		options.add_argument('--headless')
		options.add_argument('--no-sandbox')
		options.add_argument('--disable-dev-shm-usage')
		driver = webdriver.Chrome(config.driverPath,chrome_options=options)
		# driver.implicitly_wait(3)
		self.driver = driver
	#  newDriver reset
	def reset(self):
		if self.driver !=None:
			self.driver.quit()
		self.c()
	def leftProductChangeStock(data,advertiserId,processData):

		for key,item in processData['products'].items():
			product = json.loads(item['data'])
			if product['availability'] == 'in stock':
				product['availability'] = 'out of stock'
				jsonData = json.dumps(product, ensure_ascii=False)
				processData['update'].append([jsonData, product['id']])
				processData['change'].append([advertiserId,product['id']])


		return processData
	def bindFeedData(self,data,advertiserData):

		result = []
		bindKeysData = self.bindKeys
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
	# 로그인 URL
	def getLoginActionUrl(self,loginUrl):
		response = self.get(loginUrl)
		soup = bs(response.text, 'html.parser')
		return soup.select('form')[0]['action']
	# 로그인
	def login(self, advertiser):
		url_info = urlparse(advertiser['advertiser_feed_url'])
		self.url = url_info.scheme+'://'+url_info.netloc
		driver = self.driver
		driver.get(advertiser['advertiser_feed_url'])
		try:
			driver.find_element_by_name('managerId')
		except Exception:
			self.type = 'godo'

		if self.type=='godo':
			driver.find_element_by_id('user-id').send_keys(advertiser['mall_id'])
			driver.find_element_by_id('user-pw').send_keys(advertiser['mall_pw'])
			driver.find_element_by_class_name('bt-login').click()
		else:
			driver.find_element_by_name('managerId').send_keys(advertiser['mall_id'])
			driver.find_element_by_name('managerPw').send_keys(advertiser['mall_pw'])
			driver.find_element_by_class_name('login-btn').click()

		time.sleep(3)

	def getSiteFeedData(self,advertiserData):
		if self.type == 'godo':
			return self.getFeedDataByProductList(advertiserData['advertiser_url'])
		return self.getGoDo5FeedDataByProductList(advertiserData['advertiser_url'])
	# 고도5 피드데이터 productLIst
	def getGoDo5FeedDataByProductList(self,mallUrl):
		driver = self.driver
		baseUrl = self.url
		page = 1
		pageLimit = self.pageLimit
		productListUrl = baseUrl+'/goods/goods_list.php?'
		totalCount = None
		result = []

		while 1:
			currentCallUrl = productListUrl + urlencode(self.godo5ProductListRequestQuery(page, pageLimit))
			driver.get(currentCallUrl)
			soup = bs(driver.page_source, 'html.parser')
			time.sleep(3)

			if (totalCount == None):
				totalCount = int(soup.select('.table-header > .pull-left > strong')[0].text.replace(',',''))
			listData = soup.select('.table-responsive')[0].select('tbody')[0].select('tr')

			for key,product in enumerate(listData):

				data = product.select('td')
				id = data[2].text
				title = data[5].select('a')[0].text
				description = title

				result.append({
					'id': id,
					'title': title,
					'description': description,
					'link' : data[4].select('a')[0]['href'],
					'image_link': baseUrl + data[4].select('a')[0].select('img')[0]['src'],
					'price': data[7].select('span')[0].text.replace(',','').replace('원','').strip().split()[0],
					'availability' : 'in stock',
					'brand' : data[5].text,
					'condition' : 'new'

				})

			if (totalCount <= pageLimit * page):
				break

			page += 1

		return result
	# 고도 5가 아닌 피드데이터 productLIst
	def getFeedDataByProductList(self,mallUrl):
		driver = self.driver
		baseUrl = self.url
		page = 1
		pageLimit = self.pageLimit
		productListUrl = baseUrl+'/shop/admin/goods/adm_goods_list.php?'
		totalCount = None
		result = []
		while 1:
			currentCallUrl = productListUrl + urlencode(self.productListRequestQuery(page, pageLimit))
			driver.get(currentCallUrl)
			# WebDriverWait(driver, 10).until(
			# 	EC.presence_of_element_located((By.CLASS_NAME, "list-information"))
			# )
			soup = bs(driver.page_source, 'html.parser')

			if (totalCount == None):
				totalCount = int(soup.select('.list-information > b')[0].text.replace(',',''))

			listData = soup.select('.admin-list-table > tbody > tr')

			for key,product in enumerate(listData):

				if key%2 != 0:
					continue


				data = product.select('td')
				titleArea = data[2].select('a')
				id = data[0].select('input')[0]['value']
				title = titleArea[1].text
				description = ''

				for category in listData[key+1].select('.admin-goods-list-category > li'):
					description+=category.text+' | '

				result.append({
					'id': id,
					'title': title,
					'description': description,
					'link' : baseUrl+'/shop/goods/goods_view.php?goodsno='+id,
					'image_link': baseUrl+'/shop'+titleArea[0].select('img')[0]['src'].replace('../..',''),
					'price': data[3].text.replace(',','').strip().split()[0],
					'availability' : 'in stock',
					'brand' : title,
					'condition' : 'new'

				})

			if (totalCount <= pageLimit * page):
				break

			page += 1

		return result
	# 고도5 상품목록 요청 쿼리
	def godo5ProductListRequestQuery(self,page,pageLimit):
		return {
			'pageNum': pageLimit,
			'page' : page,
			'detailSearch': 'y',
			'delFl': 'n',
			'key': 'goodsCd',
			'searchDateFl': 'regDt',
			'searchPeriod': '-1',
			'goodsDisplayFl': 'y',
			'goodsSellFl': 'y',
			'goodsDisplayMobileFl': 'y',
			'goodsSellMobileFl': 'y',
			'soldOut': 'n',
			'goodsDeliveryFixFl[]': 'all',
			'sort': 'g.goodsNo desc',
			'searchFl': 'y',
			'applyPath': '/goods/goods_list.php'
		}
	# 고도5가 아닌 상품목록 요청 쿼리
	def productListRequestQuery(self,page,pageLimit):
		return {
			'sort': 'goodsno desc',
			'skey': 'goods.goodsnm',
			'stock_type': 'product',
			'soldout': '0',
			'x': '31',
			'y': '9',
			'page_num': pageLimit,
			'page' : page,
			'hashtag': '',
			'sword': '',
			'origin': '',
			'open': '',
			'brandno': '',
			'cate[]' : '',
			'cate[]' : '',
			'cate[]' : '',
			'cate[]' : '',
			'stock_amount[]' : '',
			'stock_amount[]' : '',
			'regdt[]' : '',
			'regdt[]' : '',
			'goods_price[]' : '',
			'goods_price[]' : ''
		}
	# 종료
	def __del__(self):
		self.driver.quit()
