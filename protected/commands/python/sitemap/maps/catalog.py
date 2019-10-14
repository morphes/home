# coding=utf-8
import os
import time
import MySQLdb
import shutil

class Catalog():
    """
    Генерирует сайтмапы для каталога (думаю, в папке /sitemaps есть смысл сделать отдельную папку /catalog)
    """

    # Имя сайтмапа для размещения в нем ссылок на все категории товаров
    name_categories = 'goods_categories.xml'

    # Имя сайтмапа, содержащего несколько самых новых товаров (новее-выше)
    name_new = 'new_goods.xml'

    # Имя сайтмапа, содержащего ссылки на всех производителей
    name_vendors = 'all_vendors.xml'

    # Имя сайтмапа, содержащего ссылки на все категории по производителям
    name_goods_vendors = 'all_goods_vendors.xml'

    # Имя файла, содержащий список сайтмапов по категориям и сайтмап новых товаров.
    name_index = 'sitemap_catalog_index.xml'

    # Список категорий, для которых делаем отдельные сайтмапы.
    list_categories = []


    def __init__(self, db_conn, db_cursor, datetime, name_xml_dir, path):
        self.db_conn = db_conn
        self.db_cursor = db_cursor
        self.datetime = datetime

        # Имя папки, в которую будут складываться все xml файлы
        self.name_xml_dir = name_xml_dir
        # Путь до папки xml_dir
        self.path = path


    def generate(self):
        """ Главный метод. Для запуска генерации всех сайтмапов """

        if not os.path.exists(self.path):
            os.mkdir(self.path, 0755)

        self.genNew()

        self.genByCategories()

        self.genVendors()

        self.genGoodsVendors()

        self.genIndex()

        self.genCatCities()

        self.genCatCountries()

        self.genStores()


    def genNew(self):
        """ Генерирует список самых новых товаров """

        self.db_cursor.execute("""
            SELECT
                CONCAT_WS('/', '/catalog', cc.eng_name, cp.id) as url
            FROM
                cat_product cp
            LEFT JOIN cat_category cc
                ON cp.category_id = cc.id
            WHERE
                cp.`status` = 2
            ORDER BY
                cp.create_time DESC
            LIMIT 300
        """)
        products = self.db_cursor.fetchall()

        # Собираем все <url> теги
        urlTags = ''
        for product in products:
            url = str(product[0]) # Ссылка на товар
            urlTags += self.__getUrlTag(url, time.time(), '0.6', 'daily')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_new, urlTags)


    def genByCategories(self):
        """ Генерирует набор сайтмапов разибвая товары по категориям """

        # Получаем данные Nestedset для рутового узла
        self.db_cursor.execute("SELECT lft, rgt FROM cat_category WHERE name = 'root'")
        root = self.db_cursor.fetchone()


        # Получаем список категорий, для которых будем генерить xml файлы
        self.db_cursor.execute("""
            SELECT
                id, eng_name
            FROM
                cat_category
            WHERE
                name <> 'root'
                AND
                (rgt - lft = 1 and rgt < """ + str(root[1]) + """ and lft > """ + str(root[0]) + """)
        """)
        categories = self.db_cursor.fetchall()


        # Для каждой категории получаем список товаров
        # и сохраняем каждый такой список в отдельном xml файле
        for cat in categories:
            cat_id = str(cat[0]) # идентификатор категории
            cat_name = str(cat[1]) # транслитерированное название категории

            self.db_cursor.execute("""
                SELECT
                    CONCAT_WS('/', '/catalog', cc.eng_name, cp.id) as url
                FROM
                    cat_product cp
                LEFT JOIN cat_category cc
                    ON cp.category_id = cc.id
                WHERE
                    cp.`status` = 2 AND cp.category_id = """ + cat_id + """
                ORDER BY
                    cp.create_time DESC
            """)
            products = self.db_cursor.fetchall()

            urlTags = ''
            for prod in products:
                url = str(prod[0]) # url на страницу товара
                urlTags += self.__getUrlTag(url, time.time(), '0.6', 'daily')

            # Генерим имя xml файла и сохраняем его в список
            xml_name = cat_name + '.xml'
            self.list_categories.append(cat_name)

            # Сохраняем список по категориям в файл с именем <eng_name>.xml
            self.__createXml(self.path + xml_name, urlTags)


        # Генерим файл со списком ссылок на категории
        self.genXmlCategories(self.list_categories)


    def genXmlCategories(self, list_cat_names):
        """ Генерирует xml файл со ссылками на категории """

        urlTags = ''
        for cat_name in list_cat_names:
            urlTags += self.__getUrlTag('/catalog/' + cat_name, time.time(), '0.7', 'daily')

        # Сохраняем список по категориям в файл с именем <eng_name>.xml
        self.__createXml(self.path + self.name_categories, urlTags)


    def genVendors(self):
        """ Генерирует xml файл со ссылками на страницы производителей """

        self.db_cursor.execute("""
            SELECT
                CONCAT_WS('/', '/catalog', 'vendor', id) as url
            FROM
                cat_vendor
        """)
        vendors = self.db_cursor.fetchall()

        # Собираем все <url> теги
        urlTags = ''
        for ven in vendors:
            url = str(ven[0]) # Ссылка на производителя
            urlTags += self.__getUrlTag(url, time.time(), '0.5', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_vendors, urlTags)


    def genGoodsVendors(self):
        """ Генерирует xml файл со ссылками на товары в категриях по прозиводителям"""

        self.db_cursor.execute("""
            SELECT
                id, eng_name
            FROM
                cat_category
            WHERE
                status = 1
        """)
        cats = self.db_cursor.fetchall()

        # Собираем все <url> теги
        urlTags = ''
        for cat in cats:

            catId = cat[0]
            catName = str(cat[1])

            self.db_cursor.execute("""
                SELECT
                    cat_vendor.name_translit
                FROM
                    `cat_product` `t`
                LEFT JOIN cat_vendor
                    ON cat_vendor.id = t.vendor_id
                WHERE
                    category_id = """ + str(catId) + """
                    AND
                    t.status = 2
                GROUP BY
                    vendor_id
            """)
            vendors = self.db_cursor.fetchall()

            for ven in vendors:
                vendorName = str(ven[0])

                url = "/catalog/" + catName + "/" + vendorName # Ссылка на категории и производителя
                urlTags += self.__getUrlTag(url, time.time(), '0.8', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_goods_vendors, urlTags)


    def genIndex(self):
        """ Генерирует индексный сайтмап со список сайтмапов """

        f = open(self.path + self.name_index, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/stores/sitemap_stores_index.xml</loc>\n'
                                                                                                  '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                          '</sitemap>\n')
        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/goods/goods_pages_index.xml</loc>\n'
                                                                                                  '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                          '</sitemap>\n')
        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + self.name_new + '</loc>\n'
                                                                                                  '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                          '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + self.name_new + '</loc>\n'
                                                                                                  '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                          '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + 'all_cities.xml' + '</loc>\n'
                                                                                                     '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                             '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + 'all_countries.xml' + '</loc>\n'
                                                                                                        '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/cities/' + 'all_cities_index.xml' + '</loc>\n'
                                                                                                                  '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                          '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + self.name_vendors + '</loc>\n'
                                                                                                      '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                              '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + self.name_goods_vendors + '</loc>\n'
                                                                                                      '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                              '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + self.name_categories + '</loc>\n'
                                                                                                         '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                 '</sitemap>\n')

        for name in self.list_categories:
            f.write('<sitemap>\n'
                    '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/' + name + '.xml</loc>\n'
                                                                                             '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                     '</sitemap>\n')

        f.write('</sitemapindex>')
        f.close()


    def genCatCountries(self):
        """ Генерируем ссылки на категории товаров по странам """


        # Получаем список конечных вершин в категориях
        self.db_cursor.execute("""
            SELECT
                eng_name, id
            FROM
                cat_category
            WHERE
                rgt - lft = 1
        """)
        lastDescendants = self.db_cursor.fetchall()

        # Строка, в которую собираем ссылки на страны
        urlCountries = ''

        # Для каждой конечной категории формируем ссылку со страной.
        for categ in lastDescendants:

            # Идентификатор категории
            catId = str(categ[1])
            # Английское название категории
            categ_name = categ[0]

            # Получаем список стран, которые продают товары из категории catId
            self.db_cursor.execute("""
                SELECT
                    t.eng_name
                    , COUNT(p.id) as pqt
                FROM
                    `country` `t`
                INNER JOIN cat_vendor v
                    ON v.country_id=t.id
                INNER JOIN
                    cat_product p ON p.vendor_id=v.id
                WHERE
                    p.category_id=""" + catId + """ AND p.status = 2
                GROUP BY t.id
                HAVING pqt > 0
            """)
            countries = self.db_cursor.fetchall()

            for country in countries:

                country_name = country[0]

                urlCountries += self.__getUrlTag('/catalog/' + categ_name + '/' + country_name, time.time(), '0.8',
                    'daily')


        # Генерим all_countries.xml
        self.__createXml(self.path + '/all_countries.xml', urlCountries)


    def genStores(self):
        # генерация сайтмапов магазина
        storePath = self.path + '/stores'

        if not os.path.exists(storePath):
            os.mkdir(storePath, 0755);

        #------------------------------------------------------------------------------------------------

        # all stores
        sql = 'SELECT id, update_time FROM cat_store'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'all_stores.xml', sitemapTags)
        # end all stores

        #------------------------------------------------------------------------------------------------

        # new 100 stores
        sql = 'SELECT id, update_time FROM cat_store ORDER BY id DESC LIMIT 100'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'new_stores.xml', sitemapTags)
        # end new 100 stores

        #------------------------------------------------------------------------------------------------

        # all VITRINA stores
        sql = 'SELECT id, update_time FROM cat_store WHERE tariff_id=2'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.7', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vitrina_stores.xml', sitemapTags)
        # end all VITRINA stores

        #------------------------------------------------------------------------------------------------

        # all VIZITKA stores
        sql = 'SELECT id, update_time FROM cat_store WHERE tariff_id=1'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vizitka_stores.xml', sitemapTags)
        # end all VIZITKA stores

        #------------------------------------------------------------------------------------------------

        # all VITRINA stores new
        sql = 'SELECT id, update_time FROM cat_store WHERE tariff_id=2 ORDER BY update_time DESC LIMIT 30'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.7', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vitrina_stores_new.xml', sitemapTags)
        # end all VITRINA stores new

        #------------------------------------------------------------------------------------------------

        # all VIZITKA stores new
        sql = 'SELECT id, update_time FROM cat_store WHERE tariff_id=1 ORDER BY update_time DESC LIMIT 30'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vizitka_stores_new.xml', sitemapTags)
        # end all VIZITKA stores new

        #------------------------------------------------------------------------------------------------

        # all VITRINA feedback
        sql = 'SELECT DISTINCT cs.id, cs.update_time '\
              + 'FROM cat_store as cs '\
              + 'INNER JOIN cat_store_feedback as csf ON csf.store_id=cs.id '\
              + 'WHERE tariff_id=2'

        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vitrina_feedback.xml', sitemapTags)
        # end all VITRINA feedback

        #------------------------------------------------------------------------------------------------

        # all VIZITKA feedback
        sql = 'SELECT DISTINCT cs.id, cs.update_time '\
              + 'FROM cat_store as cs '\
              + 'INNER JOIN cat_store_feedback as csf ON csf.store_id=cs.id '\
              + 'WHERE tariff_id=1'
        self.db_cursor.execute(sql)

        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getUrlTag('/catalog/store/index/id/' + str(store[0]), store[1], '0.6', 'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'vizitka_feedback.xml', sitemapTags)
        # end all VIZITKA feedback

        #------------------------------------------------------------------------------------------------

        #Создаем в папке /stores файл stores_goods_all.xml - в нем указываем урлы всех товарных страниц магазинов по тарифу Витрина;

        sql = 'select p.id, cc.eng_name, p.update_time, cs.id '\
              + 'from cat_store_price as csp '\
              + 'INNER JOIN cat_product as p ON p.id=csp.product_id AND p.`status`=2 '\
              + 'INNER JOIN cat_category as cc ON cc.id=p.category_id '\
              + 'INNER JOIN cat_store as cs ON cs.id = csp.store_id AND cs.tariff_id=2 '\
              + 'WHERE csp.by_vendor=0'

        self.db_cursor.execute(sql)

        products = self.db_cursor.fetchall()

        sitemapTags = ''
        for product in products:
            sitemapTags += self.__getUrlTag(
                '/catalog/' + str(product[1]) + '/' + str(product[0]) + '?store_id=' + str(product[3]), product[2], '0.7',
                'weekly')


        # Собираем и сохраняем файл
        self.__createXml(storePath + '/' + 'stores_goods_all.xml', sitemapTags)


        #------------------------------------------------------------------------------------------------

        # генерация папок магазинов тарифа витрина
        self._removeFolders(storePath)

        sql = 'SELECT id, update_time FROM cat_store WHERE tariff_id=2'
        self.db_cursor.execute(sql)
        stores = self.db_cursor.fetchall()

        sitemapTags = ''
        for store in stores:
            sitemapTags += self.__getSitemapTag('/sitemaps/catalog/stores/' + str(store[0]) + '/store_index.xml',
                time.time())
            self._genStoreData(store)

        # генерация сайтмапа на сайтмапы в магазинах
        self.__createSitemapIndex(storePath + '/vitrina_stores_index.xml', sitemapTags)

        # END генерация папок магазинов тарифа витрина

        # генерация главного сайтмапа по магазинам
        self.__genStoreIndexSitemap()


    # Генерация сайтмапов для конкретного магазина
    def _genStoreData(self, store):
        strStore = str(store[0])
        storeTime = str(self.datetime.datetime.fromtimestamp(store[1]).strftime('%Y-%m-%d'));
        unixStoreTime = store[1]

        storePath = self.path + '/stores/' + strStore

        if not os.path.exists(storePath):
            os.mkdir(storePath, 0755);

        # store_goods

        sql = 'select p.id, cc.eng_name, p.category_id, p.vendor_id, p.update_time from cat_store_price as csp '\
              + 'INNER JOIN cat_product as p ON p.id=csp.product_id AND p.`status`=2 '\
              + 'INNER JOIN cat_category as cc ON cc.id=p.category_id '\
              + 'WHERE csp.by_vendor=0 AND csp.store_id=' + strStore

        self.db_cursor.execute(sql)
        products = self.db_cursor.fetchall()

        categories = {}
        vendors = {}

        urlTags = ''
        for product in products:
            categories[product[2]] = product[2]
            vendors[product[3]] = product[3]
            urlTags += self.__getUrlTag('/catalog/' + str(product[1]) + '/' + str(product[0]) + '?store_id=' + strStore,
                product[4], '0.6', 'dayly')

        self.__createXml(storePath + '/' + 'store_goods.xml', urlTags)

        urlTags = ''
        for category in categories:
            urlTags += self.__getUrlTag('/catalog/store/products/id/' + strStore + '/category_id/' + str(category),
                unixStoreTime, '0.6', 'weekly')

        for vendor in vendors:
            urlTags += self.__getUrlTag('/catalog/store/products/id/' + strStore + '/vendor_id/' + str(vendor),
                unixStoreTime, '0.6', 'weekly')

        self.__createXml(storePath + '/' + 'store_categories_vendors.xml', urlTags)

        # END store_goods

        # Запись сайтмапа на предыдущие сайтмапы

        f = open(storePath + '/store_index.xml', 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))
        f.write('<sitemap>\n'\
                + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/' + strStore + '/store_goods.xml' + '</loc>\n'\
                + '<lastmod>' + now_str + '</lastmod>\n'\
                                          +'</sitemap>\n')

        f.write('<sitemap>\n'\
                + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/' + strStore + '/store_categories_vendors.xml' + '</loc>\n'\
                + '<lastmod>' + now_str + '</lastmod>\n'\
                                          +'</sitemap>\n')

        f.write('</sitemapindex>')
        f.close()

    # Удаление папок из директории
    def _removeFolders(self, path):
        for the_file in os.listdir(path):
            folder = os.path.join(path, the_file)
            try:
                if os.path.isdir(folder):
                    shutil.rmtree(folder)
            except Exception, e:
                print e

    def genCatCities(self):
        """ Генерирует сайтмапы категорий товаров по городам """

        path_for_cities = self.path + '/cities'

        # Создаем папку cities для хранения по городам
        if not os.path.exists(path_for_cities):
            os.mkdir(path_for_cities, 0755)


        # Получаем список городов, в которых есть магазины.
        # С большой вероятностью это те города, в которых есть товары.
        self.db_cursor.execute("""
            SELECT
                DISTINCT geo_id
            FROM
                cat_store_geo
            WHERE
                type = 1
        """)
        cities = self.db_cursor.fetchall()

        sphinx_conn = MySQLdb.connect(host="127.0.0.1", user="", passwd="", db="myhome", charset='utf8', port=9306)
        sphinx_cursor = sphinx_conn.cursor()

        # Получаем словарь с английскими именами категорий
        cat_name_dict = {}
        self.db_cursor.execute("SELECT id, eng_name FROM cat_category")
        cats = self.db_cursor.fetchall()
        for cat in cats:
            cat_name_dict[cat[0]] = cat[1]

        # Список сайтмапов вида sitemap_catalog_novosibirsk.xml, который потом добавляется в сайтмап all_cities_index.xml
        list_cities_maps = []

        # Строка в которую собираем ссылки на города
        urlCities = ''

        # Для каждого города получаем список категорий, в которых есть товары
        for city in cities:
            # Узнаем английское название города
            self.db_cursor.execute("SELECT eng_name FROM city WHERE id = '" + str(city[0]) + "'")
            city_name = self.db_cursor.fetchall()
            city_name = city_name[0][0]

            # спрашиваем у сфинкса категории товаров по городу.
            self.db_cursor.execute("""
                SELECT store_id FROM cat_store_city as c
			        INNER JOIN cat_store as s ON s.id=c.store_id AND s.type = 1
			        WHERE city_id = """+str(city[0])+"""
            """)
            storeIds = self.db_cursor.fetchall()
            storeIdsString = ''
            for storeId in storeIds:
                if storeIdsString != '':
                    storeIdsString += ','
                storeIdsString += str(storeId[0])

            sphinx_cursor.execute("""
                SELECT category_id FROM myhome_product WHERE store_ids IN ("""+storeIdsString+""") GROUP BY category_id LIMIT 100
            """)

            categories = sphinx_cursor.fetchall()

            categoryIdsStr = ''

            # Если нашлись категории в городе
            if categories:
                # Собираем xml cо ссылками на города
                urlCities += self.__getUrlTag('/catalog/' + city_name, time.time(), '0.8', 'daily')


                # Путь до папки города
                path_city = path_for_cities + '/' + city_name
                if not os.path.exists(path_city):
                    os.mkdir(path_city, 0755)

                urlTags = ''
                for cat in categories:
                    if categoryIdsStr != '':
                        categoryIdsStr += ','

                    categoryIdsStr += str(cat[0])

                    # Узнаем английское название категории
                    cat_name = cat_name_dict[cat[0]]

                    urlTags += self.__getUrlTag('/catalog/' + city_name + '/' + cat_name, time.time(), '0.8', 'daily')


                # Собираем и сохраняем файл
                self.__createXml(path_city + '/' + 'goods_pages.xml', urlTags)


            # Генерим sitemap_catalog_novosibirsk.xml
            list_cities_maps.append('sitemap_catalog_' + city_name + ".xml")
            self.__genIndexCity('sitemap_catalog_' + city_name + ".xml", city_name)


            # ========================================================================

            # Теперь формируем списки на категории товаров по конкретному городу
            if categoryIdsStr != '':
                # Получаем список всех категорий по всем уровням, в которых есть товаров в текущем городе.
                # Далее из этого списка будут формироваться нужные xml файлы.
                self.db_cursor.execute("""
		        SELECT
		            DISTINCT cc.id, cc.name, cc.eng_name, cc.level FROM cat_category as cc
		            INNER JOIN (
						SELECT DISTINCT t.id, t.lft, t.rgt FROM `cat_category` `t`
						WHERE t.id IN (""" + categoryIdsStr + """)
					    ) as tmp ON tmp.id=cc.id OR (tmp.lft > cc.lft AND tmp.rgt < cc.rgt)
					    WHERE cc.level <> 1
					    ORDER BY cc.lft
		    """)
                cats = self.db_cursor.fetchall()

                urlMainCat = urlSubCat = ''

                for cat in cats:
                    id = str(cat[0])
                    name = cat[1]
                    eng_name = str(cat[2])
                    level = cat[3]

                    if level == 2:
                        urlMainCat += self.__getUrlTag('/catalog/' + city_name + '/' + eng_name, time.time(), '0.8',
                            'daily')

                    if level == 3:
                        urlSubCat += self.__getUrlTag('/catalog/' + city_name + '/' + eng_name, time.time(), '0.8',
                            'daily')

                self.__createXml(path_for_cities + '/' + city_name + '/' + 'goods_maincategories.xml', urlMainCat)

                self.__createXml(path_for_cities + '/' + city_name + '/' + 'goods_subcategories.xml', urlSubCat)


        # Генерим all_cities.xml
        self.__createXml(self.path + '/all_cities.xml', urlCities)

        # Генерим all_cities_index.xml, в который попадают индексные xml файлы для каждого города
        self.__genAllCityIndex(list_cities_maps)


    def __getSitemapTag(self, link, unix_time):
        urlTag = '<sitemap>\n'\
                 '<loc>http://www.myhome.ru' + link + '</loc>\n'\
                                                      '<lastmod>' + str(
            self.datetime.datetime.fromtimestamp(unix_time).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                                                                                    '</sitemap>\n'

        return urlTag


    def __getUrlTag(self, link, unix_time, priority, changefreq):
        """ Генерирует по переданным параметр тег <url> для xml файла """
        urlTag = '   <url>\n'\
                 + '       <loc>http://www.myhome.ru' + link + '</loc>\n'\
                 + '       <lastmod>' + str(
            self.datetime.datetime.fromtimestamp(unix_time).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                 + '       <priority>' + priority + '</priority>\n'\
                 + '       <changefreq>' + changefreq + '</changefreq>\n'\
                 + '   </url>\n'

        return urlTag

    def __createSitemapIndex(self, filename, body):
        """ Генерация sitemapindex файла
        @param filename Имя генерируемого файла
        @param body Тело xml файла (список тегов <url>)
        """
        f = open(filename, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        f.write(body)
        f.write('</sitemapindex>')

        f.close()


    def __createXml(self, filename, body):
        """ Генерация xml файла
        @param filename Имя генерируемого файла
        @param body Тело xml файла (список тегов <url>)
        """
        f = open(filename, 'w')

        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write(
            '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        f.write(body)

        f.write('</urlset>')

        f.close()


    def __genAllCityIndex(self, list_cities_maps):
        """ Генерирует индексный сайтмап со список сайтмапов """

        f = open(self.path + '/cities/' + 'all_cities_index.xml', 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        for name in list_cities_maps:
            f.write('<sitemap>\n'
                    '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/cities/' + name + '</loc>\n'
                                                                                                    '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                            '</sitemap>\n')

        f.write('</sitemapindex>')
        f.close()


    def __genIndexCity(self, xml_name, city_name):
        """ Генерирует индексный сайтмап со списком сайтмапов по городам"""

        f = open(self.path + '/cities/' + xml_name, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/cities/' + city_name + '/' + 'goods_pages.xml' + '</loc>\n'
                                                                                                                               '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                                       '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/cities/' + city_name + '/' + 'goods_maincategories.xml' + '</loc>\n'
                                                                                                                                        '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                                                '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/' + self.name_xml_dir + '/cities/' + city_name + '/' + 'goods_subcategories.xml' + '</loc>\n'
                                                                                                                                       '<lastmod>' + now_str + '</lastmod>\n'
                                                                                                                                                               '</sitemap>\n')

        f.write('</sitemapindex>')
        f.close()


    # Генерит обобщающий сайтмап для всем xml по магазинам
    def __genStoreIndexSitemap(self):
        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))
        f = open(self.path + '/stores/sitemap_stores_index.xml', 'w')

        text = '<?xml version="1.0" encoding="UTF-8"?>\n'\
               + '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/all_stores.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/new_stores.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/stores_goods_all.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vitrina_stores_index.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vitrina_stores.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vizitka_stores.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vitrina_stores_new.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vizitka_stores_new.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vitrina_feedback.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '<sitemap>\n'\
               + '<loc>http://www.myhome.ru/sitemaps/catalog/stores/vizitka_feedback.xml</loc>\n'\
               + '<lastmod>' + now_str + '</lastmod>\n'\
               + '</sitemap>\n'\
               + '</sitemapindex>'

        f.write(text)
        f.close()