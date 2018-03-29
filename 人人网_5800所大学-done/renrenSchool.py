#!/usr/local/bin/python3.2
#-*- coding:utf-8 -*-
import urllib.request,re,http.cookiejar,codecs
from urllib.parse import urlencode
from xml.dom.minidom import parseString
from html.parser import HTMLParser
import sqlite3
class hp(HTMLParser):
    a_txt = False
    ul_txt = 0
    uldata = []
    ulliid = []
    schooldata = []
    schoolid = []
    i = 0 
    def handle_starttag(self,tag,attr):
        r = re.compile('city_qu_([\d]*)')
        if tag=='a':
            self.a_txt=True 
            for (att,value) in attr:
                if att=='href' and value !='#highschool_anchor':
                    self.schoolid.append(int(value,10))         #school id
        if tag == 'ul':
            for (att,value) in attr:
                if att == 'id' and value != 'schoolCityQuList':
                    subvalue = int(''.join(r.findall(value)),10)
                    self.ulliid.append(subvalue)        #district id
                    # print(self.getpos())
                    self.i = subvalue
                if att == 'id' and value == 'schoolCityQuList':
                    self.ul_txt = 1
                    self.uldata = []
                    self.ulliid = []
                    self.schooldata = []
                    self.schoolid = []
                if att == 'style' and value == 'display:none;':
                    self.ul_txt = 2
    def handle_endtag(self,tag):  
        if tag == 'a':  
            self.a_txt = False
        if tag == 'ul':
            self.ul_txt = 0
    def handle_data(self,data):  
        if self.a_txt:
            adata = data
        if self.ul_txt == 1:
            self.uldata.append(data) # district东城区
        if self.ul_txt == 2:
            if data == '湖南对外经济贸易职业学院':
                self.schooldata.append(['rawData',self.i])   # school
            if data != '\n' and data != '':
                self.schooldata.append([data,self.i])   # school
class renrenSchool:
    def __init__(self):
        self.allUnivlisturl='http://s.xnimg.cn/a13819/allunivlist.js'
        self.getDepurl='http://www.renren.com/GetDep.do?id='
        self.getDormurl='http://www.renren.com/GetDorm.do?id='
        self.cityurl = 'http://s.xnimg.cn/a13817/js/cityArray.js'
        self.incityurl = 'http://s.xnimg.cn/a13818/js/inCityArray.js'
        self.juniorschoolurl = 'http://support.renren.com/juniorschool/'  # http://support.renren.com/juniorschool/1101.html
        self.highschoolurl = 'http://support.renren.com/highschool/'   #1301.html
        self.collegeschoolurl = 'http://support.renren.com/collegeschool/'  #1101.html
        self.allUnividArray=[]
        self.subCityDict = {}
        self.subCityArray=[]
        self._dataDistrict = []
        self._dataHighSchool = []
        self._dataJDistrict = []
        self._dataJuniorSchool = []
        self._dataCDistrict = []
        self._dataCollegeSchool = []
        self.totalDatabase = sqlite3.connect('totalDatabase.db')
        self.totalC = self.totalDatabase.cursor()
        self.forSchoolsubCityDict={'4300': '湖南省', '1300': '河北省', '1200': '天津市', '4100': '河南省', '3600': '江西省', '4200': '湖北省', '5100': '四川省', '3300': '浙江省', '2200': '吉林省', '2300': '黑龙江省','3100': '上海市', '6100': '陕西省', '1500': '内蒙古自治区', '6200': '甘肃省', '1400': '山西省', '6300': '青海省', '5000': '重庆市',  '8100': '香港特别行政区', '5200': '贵州省', '4500': '广西壮族自治区', '6500': '新疆维吾尔自治区', '3700': '山东省', '4600': '海南省',  '6400': '宁夏回族自治区', '3400': '安徽省', '3200': '江苏省', '5300': '云南省', '5400': '西藏自治区', '4400': '广东省', '3500': '福建省', '1100': '北京市', '2100': '辽宁省'}  # '8200': '澳门特别行政区', '7100': '台湾省 ', 
        self.cityDict={}
        self.loginURL = 'http://www.renren.com/PLogin.do'
        self.cityinChina = {'24': '贵州', '25': '广西', '26': '内蒙古', '27': '宁夏', '20': '云南', '21': '河北', '22': '江西', '23': '山西', '28': '青海', '29': '新疆', '1': '北京', '3': '黑龙江', '2': '上海', '5': '辽宁', '4': '吉林', '7': '安徽', '6': '天津', '9': '浙江', '8': '江苏', '11': '湖北', '10': '陕西', '13': '湖南', '12': '广东', '15': '四川', '14': '甘肃', '17': '福建', '16': '山东', '19': '重庆', '18': '河南', '31': '西藏', '30': '海南', '34': '台湾', '33': '澳门', '32': '香港'}
        self.cityinUSA = {'42': 'SD', '48': 'WA', '43': 'TN', '49': 'WI', '24': 'MN', '25': 'MO', '26': 'MS', '27': 'MT', '20': 'MA', '21': 'MD', '22': 'ME', '23': 'MI', '46': 'VA', '47': 'VT', '44': 'TX', '45': 'UT', '28': 'NC', '29': 'ND', '40': 'RI', '41': 'SC', '1': 'AK', '3': 'AR', '2': 'AL', '5': 'CA', '4': 'AZ', '7': 'CT', '6': 'CO', '9': 'DE', '8': 'DC', '51': 'WY', '39': 'PA', '38': 'OR', '11': 'GA', '10': 'FL', '13': 'IA', '12': 'HI', '15': 'IL', '14': 'ID', '17': 'KS', '16': 'IN', '19': 'LA', '18': 'KY', '31': 'NH', '30': 'NE', '37': 'OK', '36': 'OH', '35': 'NY', '34': 'NV', '33': 'NM', '32': 'NJ', '50': 'WV'}
        self.countryinWorld = {'21': '瑞典', '12': '意大利', '04': '新西兰', '27': '波兰', '08': '德国', '23': '比利时', '18': '乌克兰', '29': '奥地利', '00': '中国', '14': '荷兰', '02': '法国', '10': '俄罗斯', '06': '加拿大', '25': '丹麦', '16': '瑞士', '20': '芬兰', '05': '英国', '13': '爱尔兰', '22': '西班牙', '19': '南非', '09': '韩国', '28': '印度', '17': '泰国', '03': '新加坡', '15': '马来西亚 ', '26': '菲律宾', '07': '美国', '11': '日本', '24': '挪威', '01': '澳大利亚'}
        self.cookie=http.cookiejar.CookieJar()
        self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookie))
        urllib.request.install_opener(self.opener)
    def getlittle(self,a,b):
        if a>b:
            return b
        else:
            return a
    def getAllUnivlist(self,start):
        self.totalC.execute('drop table if exists allUniv')
        self.totalC.execute('''create table if not exists allUniv (
                            uid integer primary key autoincrement,
                            univid integer,
                            univname string,
                            countryid integer,
                            country string,
                            cityid integer,
                            cityname string
                            )''')
        self.totalC.execute('drop table if exists country')
        self.totalC.execute('''create table if not exists country(
                            cid integer primary key autoincrement,
                            countryid integer,
                            countryname string,
                            cityid integer,
                            cityname string
                            )''')
        conn = sqlite3.connect('allUniv.db')
        sc=conn.cursor()
        sc.execute('drop table if exists allUniv')
        sc.execute('''create table if not exists allUniv (
                            uid integer primary key autoincrement,
                            univid integer,
                            univname string,
                            countryid integer,
                            country string,
                            cityid integer,
                            cityname string
                            )''')
        sc.execute('drop table if exists country')
        sc.execute('''create table if not exists country (
                            cid integer primary key autoincrement,
                            countryid integer,
                            countryname string,
                            cityid integer,
                            cityname string
                            )''')
        for d,v in self.cityinChina.items():
            self.totalC.execute('insert into country values (NULL,?,?,?,?)',[1,'中国',int(d,10),v])
            sc.execute('insert into country values (NULL,?,?,?,?)',[1,'中国',int(d,10),v])
        for d,v in self.cityinUSA.items():
            self.totalC.execute('insert into country values (NULL,?,?,?,?)',[2,'美国',int(d,10),v])
            sc.execute('insert into country values (NULL,?,?,?,?)',[2,'美国',int(d,10),v])
        for d,v in self.countryinWorld.items():
            self.totalC.execute('insert into country values (NULL,?,?,?,?)',[3,'国家',int(d,10),v])
            sc.execute('insert into country values (NULL,?,?,?,?)',[3,'国家',int(d,10),v])
        _dataArray=[]
        _response = urllib.request.urlopen(self.allUnivlisturl)
        _rawData = _response.read().decode('utf-8')# 
        # print(self.raw(_rawData))
        pos = 0
        pos = _rawData.find('\\u',pos)
        while pos >-1 :
            tmp = _rawData[pos:pos+6]
            _rawData=_rawData.replace(tmp,chr(int(tmp[2:6],16)),1)
            pos = _rawData.find('\\u',pos+1)
        # print(_rawData)
        _rawData = _rawData.split('\"id\"')
        tmpcountryid = ''
        tmpcountryname = ''
        _data = ''
        for t in _rawData:
            t = t.split(':')
            if len(t) > 2:
                    tmpt3=''
                    tmpt4=''
                    t[1] = t[1].split(',\"')
                    t[2] = t[2].split('\"')
                    if len(t[2])>1:
                        tmpt2=t[2][1]
                    else:
                        tmpt2=''
                    if len(t)>3 and len(t[3])>0:
                        t[3] = t[3].split(',')
                        tmpt3 = t[3][0]
                    if tmpt2 == '':
                        tmpcountryid = t[1][0]
                    ########################################
                    # generate the dictionary
                    # cityinChina    =   countrylist1 
                    # cityinUSA      =   countrylist2 
                    # countryinWorld =   countrylist3
                    # countrylist1 = {'1':'北京'}
                    # countrylist2 = {}
                    # countrylist3 = {}
                    ########################################
                    # if tmpt3 !='' or (len(t)>4 and t[4]!=''):
                    #     tmpcountryname = tmpt3
                    #     # 
                    #     if len(t)>4:
                    #         t[4]=t[4].split('\"')
                    #         if len(t[4])>1 and t[4][1]!='':
                    #             # print(t[4][1])
                    #             tmpcountryname = t[4][1]
                    #     if tmpt3=='0':
                    #         countrylist1[tmpcountryid]=tmpcountryname
                    #     elif tmpt3 == '7':
                    #         countrylist2[tmpcountryid]=tmpcountryname
                    #     else:
                    #         countrylist3[tmpcountryid]=tmpcountryname
                    
                    #convert t[1][0] to integer ,gt 100 is ok
                    if t[1][0][0] !='\"' and int(t[1][0],10) >100:
                        if int(t[1][0],10)>1000 and int(t[1][0],10) < 34060: # tmpt3 == '0' and 
                            _data = [int(t[1][0],10),tmpt2,1,'中国',int(tmpcountryid,10),self.cityinChina[tmpcountryid]]    # [id,name,country,cityid,cityname]
                        elif int(t[1][0],10)>701000 and int(t[1][0],10) < 751010: # tmpt3 == '7':
                            _data = [int(t[1][0],10),tmpt2,2,'美国',int(tmpcountryid,10),self.cityinUSA[tmpcountryid]] 
                        else:
                            _data = [int(t[1][0],10),tmpt2,3,self.countryinWorld[tmpcountryid[1:3]],int(tmpcountryid[1:3],10),self.countryinWorld[tmpcountryid[1:3]]]
                        self.allUnividArray.append(int(t[1][0],10))
                        self.totalC.execute('insert into allUniv values (NULL,?,?,?,?,?,?)',_data)
                        sc.execute('insert into allUniv values (NULL,?,?,?,?,?,?)',_data)
                        # print(_data)
        # print(self.allUnividArray)
        conn.commit()
        sc.close()
    def getDep(self,start):
        self.totalC.execute('drop table if exists department')
        self.totalC.execute('''create table if not exists department (
                            did integer primary key autoincrement,
                            depname string,
                            univid integer
                            )''')
        conn = sqlite3.connect('allUniv.db')
        sc = conn.cursor()
        sc.execute('drop table if exists department')
        sc.execute('''create table if not exists department (
                    did integer primary key autoincrement,
                    depname string,
                    univid integer
                    )''')
        for depid in self.allUnividArray:
            _response = urllib.request.urlopen(self.getDepurl+str(depid))
            _rawData = _response.read().decode('utf-8')
            _domRawdata = parseString(_rawData)
            for _element in _domRawdata.getElementsByTagName('option'):
                if _element.getAttribute('value')!='' and _element.getAttribute('value') != '其它院系':
                    self.totalC.execute('insert into department values (NULL,?,?)',[_element.getAttribute('value'),depid])
                    sc.execute('insert into department values (NULL,?,?)',[_element.getAttribute('value'),depid])
                    # print([_element.getAttribute('value'),depid])
        conn.commit()
        sc.close()
    def login(self,username,password):
        _str = urllib.parse.urlencode({'email':username,'password':password,'origURL':'','domain':'renren.com','formName':'','method':'','isplogin':'true','submit':'%E7%99%BB%E5%BD%95'}).encode('utf-8')
        _response = urllib.request.urlopen(self.loginURL,_str)
    def getDorm(self,start):
        self.totalC.execute('drop table if exists dorm')
        self.totalC.execute('''create table if not exists dorm (
                            mid integer primary key autoincrement,
                            dormname string,
                            univid integer
                            )''')
        conn = sqlite3.connect('allUniv.db')
        sc = conn.cursor()
        sc.execute('drop table if exists dorm')
        sc.execute('''create table if not exists dorm (
                    mid integer primary key autoincrement,
                    dormname string,
                    univid integer
                    )''')
        self.login('llb0536@qq.com','98398110')
        for dormid in self.allUnividArray:
            _response = urllib.request.urlopen(self.getDormurl+str(dormid))
            _rawData = _response.read().decode('utf-8','ignore')
            _domRawdata = parseString(_rawData)
            for _element in _domRawdata.getElementsByTagName('option'):
                if _element.getAttribute('value')!='' and _element.getAttribute('value') != '其它宿舍':
                    self.totalC.execute('insert into dorm values (NULL,?,?)',[_element.getAttribute('value'),dormid])
                    sc.execute('insert into dorm values (NULL,?,?)',[_element.getAttribute('value'),dormid])
                    # print([_element.getAttribute('value'),dormid])
        conn.commit()
        sc.close()
    def getCity(self,start):
        # ([^ : "[]+) *: *("[^"]*"|[^,"]*)
        # (\A.*)
        self.totalC.execute('drop table if exists city')
        self.totalC.execute('''create table if not exists city (
                            cid integer primary key autoincrement,
                            citynum integer,
                            cityname string
                            )''')
        self.totalC.execute('drop table if exists province')
        self.totalC.execute('''create table if not exists province (
                            pid integer primary key autoincrement,
                            provid integer,
                            provname string
                            )''')
        conn = sqlite3.connect('city.db')
        sc = conn.cursor()
        sc.execute('drop table if exists city')
        sc.execute('''create table if not exists city (
                    cid integer primary key autoincrement,
                    citynum integer,
                    cityname string
                    )''')
        sc.execute('drop table if exists province')
        sc.execute('''create table if not exists province (
                    pid integer primary key autoincrement,
                    provid integer,
                    provname string
                    )''')
        conns1 = sqlite3.connect('highschool.db')
        scs1 = conns1.cursor()
        scs1.execute('drop table if exists province')
        scs1.execute('''create table if not exists province (
                    pid integer primary key autoincrement,
                    provid integer,
                    provname string
                    )''')
        conns2 = sqlite3.connect('juniorschool.db')
        scs2 = conns2.cursor()
        scs2.execute('drop table if exists province')
        scs2.execute('''create table if not exists province (
                    pid integer primary key autoincrement,
                    provid integer,
                    provname string
                    )''')
        conns3 = sqlite3.connect('collegeschool.db')
        scs3 = conns3.cursor()
        scs3.execute('drop table if exists province')
        scs3.execute('''create table if not exists province (
                    pid integer primary key autoincrement,
                    provid integer,
                    provname string
                    )''')
        _response = urllib.request.urlopen(self.cityurl)
        _rawData = _response.read().decode('utf-8')
        r0 = re.compile('(^[^a-z]+$)', re.MULTILINE)
        r = re.compile('([^ : "[]+) *: *("[^"]*"|[^,"]*)')
        _subraw = ''.join(r0.findall(_rawData))
        i = 0
        for c,p in r.findall(_subraw):
            self.subCityDict[c]=p
            if p != '市辖区' and p != '县' and p[2:3] !='县' :
                i = i+1
                # print([i,c,p])
                self.totalC.execute('insert into province values (NULL,?,?)',[int(c,10),p])
                sc.execute('insert into province values (NULL,?,?)',[int(c,10),p])
                scs1.execute('insert into province values (NULL,?,?)',[int(c,10),p])
                scs2.execute('insert into province values (NULL,?,?)',[int(c,10),p])
                scs3.execute('insert into province values (NULL,?,?)',[int(c,10),p])
            # self.subCityArray.append(p)
        for k,v in r.findall(_rawData):
            self.cityDict[v]=k
            self.totalC.execute('insert into city values (NULL,?,?)',[int(k,10),v])
            sc.execute('insert into city values (NULL,?,?)',[int(k,10),v])
            # print([k,v])
        conn.commit()
        sc.close()
        conns1.commit()
        scs1.close()
        conns2.commit()
        scs2.close()
        conns3.commit()
        scs3.close()
    def getinCity(self,start):
        self.totalC.execute('drop table if exists incity')
        self.totalC.execute('''create table if not exists incity (
                            cid integer primary key autoincrement,
                            citynum integer,
                            cityname string,
                            provnum integer
                            )''')        
        conn = sqlite3.connect('city.db')
        sc = conn.cursor()
        sc.execute('drop table if exists incity')
        sc.execute('''create table if not exists incity (
                            cid integer primary key autoincrement,
                            citynum integer,
                            cityname string,
                            provnum integer
                            )''')
        _response = urllib.request.urlopen(self.incityurl)
        _rawData = _response.read().decode('utf-8')
        r = re.compile('([^ : "[]+) *: *("[^"]*"|[^,"]*)')
        former = 60000001
        n = 0 
        for k,v in r.findall(_rawData):
            if int(k,10) - former >800:
                n = n+1
                former = int(k,10)
            # print([k,v,n])
            self.totalC.execute('insert into incity values (NULL,?,?,?)',[int(k,10),v,n])
            sc.execute('insert into incity values (NULL,?,?,?)',[int(k,10),v,n])
        conn.commit()
        sc.close()
    def getHighSchool(self,start):
        self.totalC.execute('drop table if exists highschoolDistrict')
        self.totalC.execute('''create table if not exists highschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        self.totalC.execute('drop table if exists highSchool')
        self.totalC.execute('''create table if not exists highSchool (
                            hid integer primary key autoincrement,
                            hsid integer,
                            hsname string,
                            districtid integer
                            )''')
        conn = sqlite3.connect('highschool.db')
        sc = conn.cursor()
        sc.execute('drop table if exists highschoolDistrict')
        sc.execute('''create table if not exists highschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        sc.execute('drop table if exists highSchool')
        sc.execute('''create table if not exists highSchool (
                            hid integer primary key autoincrement,
                            hsid integer,
                            hsname string,
                            districtid integer
                            )''')
        cityDict = self.forSchoolsubCityDict
        onlyKeys = sorted(cityDict.keys())
        for rawnum in onlyKeys:
            num = int(rawnum,10)+1
            _response = urllib.request.urlopen(self.highschoolurl+str(num)+'.html')
            _rawData = _response.read().decode('utf-8')
            pos = 0
            pos = _rawData.find('&#',pos)
            while pos > -1 :
                tmp = _rawData[pos:pos+8]
                _rawData = _rawData.replace(tmp,chr(int(tmp[2:7],10)),2)
                pos = _rawData.find('&#',pos)
            # print(_rawData)
            yk = hp()
            yk.feed(_rawData)
            # print(yk.ulliid,len(yk.ulliid))
            # print(yk.uldata,len(yk.uldata))
            # print(yk.schooldata,len(yk.schooldata))
            # print(yk.schoolid,len(yk.schoolid))
            for i in range(len(yk.ulliid)):
                self._dataDistrict.append([yk.ulliid[i],yk.uldata[i], int(rawnum,10)])
            for i in range(len(yk.schoolid)):
                self._dataHighSchool.append([yk.schoolid[i],yk.schooldata[i][0],yk.schooldata[i][1]])
            for [i,j,k] in self._dataDistrict:
                self.totalC.execute('insert into highschoolDistrict values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into highschoolDistrict values (NULL,?,?,?)',[i,j,k])
                # print([i,j,k])
            for [i,j,k] in self._dataHighSchool:
                self.totalC.execute('insert into highSchool values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into highSchool values (NULL,?,?,?)',[i,j,k])
                # print([i,j,k])
            # print(self._dataDistrict,len(self._dataDistrict))
            # print(self._dataHighSchool,len(self._dataHighSchool))
            yk.close()
        conn.commit()
        sc.close()
    def getJuniorSchool(self,start):
        self.totalC.execute('drop table if exists juniorschoolDistrict')
        self.totalC.execute('''create table if not exists juniorschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        self.totalC.execute('drop table if exists juniorSchool')
        self.totalC.execute('''create table if not exists juniorSchool (
                            jid integer primary key autoincrement,
                            jsid integer,
                            jsname string,
                            districtid integer
                            )''')
        conn = sqlite3.connect('juniorschool.db')
        sc = conn.cursor()
        sc.execute('drop table if exists juniorschoolDistrict')
        sc.execute('''create table if not exists juniorschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        sc.execute('drop table if exists juniorSchool')
        sc.execute('''create table if not exists juniorSchool (
                            jid integer primary key autoincrement,
                            jsid integer,
                            jsname string,
                            districtid integer
                            )''')
        cityDict = self.forSchoolsubCityDict
        onlyKeys = sorted(cityDict.keys())
        for rawnum in onlyKeys:
            num = int(rawnum,10)+1
            _response = urllib.request.urlopen(self.juniorschoolurl+str(num)+'.html')
            _rawData = _response.read().decode('utf-8')
            pos = 0
            pos = _rawData.find('&#',pos)
            while pos > -1 :
                tmp = _rawData[pos:pos+8]
                if tmp[6:7] != ';':
                    _rawData = _rawData.replace(tmp,chr(int(tmp[2:7],10)),2)
                else:
                    _rawData = _rawData.replace(tmp[0:7],chr(int(tmp[2:6],10)),2)
                pos = _rawData.find('&#',pos)
            yk = hp()
            yk.feed(_rawData)
            for i in range(len(yk.ulliid)):
                self._dataJDistrict.append([yk.ulliid[i],yk.uldata[i], int(rawnum,10)])
            for i in range(len(yk.schoolid)):
                self._dataJuniorSchool.append([yk.schoolid[i],yk.schooldata[i][0],yk.schooldata[i][1]])
            # print(self._dataJDistrict,len(self._dataJDistrict))
            # print(self._dataJuniorSchool,len(self._dataJuniorSchool))
            for [i,j,k] in self._dataJDistrict:
                self.totalC.execute('insert into juniorschoolDistrict values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into juniorschoolDistrict values (NULL,?,?,?)',[i,j,k])
                # print([i,j,k])
            for [i,j,k] in self._dataJuniorSchool:
                self.totalC.execute('insert into juniorSchool values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into juniorSchool values (NULL,?,?,?)',[i,j,k])
            yk.close()
        conn.commit()
        sc.close()
    def getCollegeSchool(self,start):
        self.totalC.execute('drop table if exists collegeschoolDistrict')
        self.totalC.execute('''create table if not exists collegeschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        self.totalC.execute('drop table if exists collegeSchool')
        self.totalC.execute('''create table if not exists collegeSchool (
                            cid integer primary key autoincrement,
                            csid integer,
                            csname string,
                            districtid integer
                            )''')
        conn = sqlite3.connect('collegeschool.db')
        sc = conn.cursor()
        sc.execute('drop table if exists collegeschoolDistrict')
        sc.execute('''create table if not exists collegeschoolDistrict (
                            sid integer primary key autoincrement,
                            districtid integer,
                            districtname string,
                            provinceid integer
                            )''')
        sc.execute('drop table if exists collegeSchool')
        sc.execute('''create table if not exists collegeSchool (
                            cid integer primary key autoincrement,
                            csid integer,
                            csname string,
                            districtid integer
                            )''')
        cityDict = self.forSchoolsubCityDict
        onlyKeys = sorted(cityDict.keys())
        # print(onlyKeys)
        for rawnum in onlyKeys:
            num = int(rawnum,10)+1
            _response = urllib.request.urlopen(self.collegeschoolurl+str(num)+'.html')
            _rawData = _response.read().decode('utf-8')
            # print(_rawData)
            pos = 0
            pos = _rawData.find('&#',pos)
            while pos > -1 :
                tmp = _rawData[pos:pos+8]
                if tmp[6:7] != ';':
                    _rawData = _rawData.replace(tmp,chr(int(tmp[2:7],10)),2)
                else:
                    _rawData = _rawData.replace(tmp[0:7],chr(int(tmp[2:6],10)),2)
                pos = _rawData.find('&#',pos)
            yk = hp()
            yk.feed(_rawData)
            for i in range(len(yk.ulliid)):
                self._dataCDistrict.append([yk.ulliid[i],yk.uldata[i], int(rawnum,10)])
            for i in range(len(yk.schoolid)):# self.getlittle(len(yk.schoolid),len(yk.schooldata),)):
                self._dataCollegeSchool.append([yk.schoolid[i],yk.schooldata[i][0],yk.schooldata[i][1]])
            for [i,j,k] in self._dataCDistrict:
                self.totalC.execute('insert into collegeschoolDistrict values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into collegeschoolDistrict values (NULL,?,?,?)',[i,j,k])
                # print([i,j,k])
            for [i,j,k] in self._dataCollegeSchool:
                self.totalC.execute('insert into collegeSchool values (NULL,?,?,?)',[i,j,k])
                sc.execute('insert into collegeSchool values (NULL,?,?,?)',[i,j,k])
            # print(yk.schooldata,len(yk.schooldata))            
            # print(yk.schoolid,len(yk.schoolid))
            # print(self._dataCDistrict,len(self._dataCDistrict))
            # print(self._dataCollegeSchool,len(self._dataCollegeSchool))
            yk.close()   
        conn.commit()
        sc.close()
if __name__ == "__main__":
    renren = renrenSchool()
    renren.getAllUnivlist('start')
    # renren.getDep('start')
    # renren.getDorm('start')
    renren.getCity('start')
    # renren.getinCity('start')
    renren.getHighSchool('start')
    renren.getJuniorSchool('start')
    renren.getCollegeSchool('start')
    renren.totalDatabase.commit()
    renren.totalC.close()
    print('Done')
    
    
    

# for num in self.
# ">(.*)<.a>
# tihuan.'([^']+)'.">([^<]*)<.a>
# href="([^"]+)">([^<]*)<.a>
# r = re.compile('tihuan.\'city_qu_([^\']+)\'.">([^<]*)<.a>',re.MULTILINE)
# r2 = re.compile('href="([^"]+)">([^<]*)<.a>',re.MULTILINE)
# for p in rc.findall(_rawData):
#     for k,v in  r.findall(_rawData):
# _domRawdata = lxml.html.fromstring(_rawData)
# for _element in _domRawdata.getElementsByTagName('option'):
# for k,v in r.findall(_rawData):
#     print([k,v])
# for p,q in r2.findall(_rawData):
#     print([p,q,])
