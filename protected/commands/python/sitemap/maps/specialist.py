from core import trans
import os

class Specialist():

    def __init__(self, db_conn, db_cursor, datetime, path, serv_index_file, city_index_file, servicecity_index_file):
        self.db_conn = db_conn
        self.db_cursor = db_cursor
        self.datetime = datetime
        self.path = path
        self.service_files = []
        self.city_files = []
        self.servicecity_files = []
        self.servicecity_urls = []
        self.serv_index_file = serv_index_file
        self.city_index_file = city_index_file
        self.servicecity_index_file = servicecity_index_file
        if not os.path.exists(self.path):
            os.mkdir(self.path, 0755)

    def getServices(self):
        self.db_cursor.execute("SELECT t.id, t.name, t.url FROM `service` t WHERE parent_id <> 0")
        return self.db_cursor.fetchall()

    def getCities(self):
        self.db_cursor.execute("""SELECT c.id, c.name
                                    FROM `user_servicecity` usc
                                    LEFT JOIN `city` c ON usc.city_id = c.id
                                    WHERE c.id IS NOT NULL
                                    GROUP BY c.id""")
        return self.db_cursor.fetchall()

    def getSpecialistsByService(self, service_id):
        self.db_cursor.execute("""SELECT u.id, u.login, u.update_time FROM user_service us
                               LEFT JOIN user u ON us.user_id = u.id
                               WHERE us.service_id = '%(sid)s'"""%{"sid": str(service_id)})
        return self.db_cursor.fetchall()

    def getSpecialistsByCity(self, city_id):
        self.db_cursor.execute("""SELECT u.id, u.login, u.update_time FROM user_servicecity usc
                               LEFT JOIN user u ON usc.user_id = u.id
                               WHERE usc.city_id = '%(cid)s'"""%{"cid": str(city_id)})
        return self.db_cursor.fetchall()

    def generate(self):
        self.generateByService()
        self.generateByCity()
        self.generateByServiceCity()


    def generateByService(self):
        services = self.getServices()
        for service in services:
            specs = self.getSpecialistsByService(service_id=service[0])
            service_name = service[1]
            file_name=service_name[0:35].strip().encode('trans/slug')+'.xml'
            self.service_files.append(file_name)
            self.writeByService(specs=specs, file_name=file_name)
        self.createIndexFile(self.serv_index_file, files=self.service_files)

    def generateByCity(self):
        cities = self.getCities()
        for city in cities:
            specs = self.getSpecialistsByCity(city_id=city[0])
            city_name = city[1]
            file_name=city_name[0:35].strip().encode('trans/slug')+'.xml'
            self.city_files.append(file_name)
            self.writeByCity(specs=specs, file_name=file_name)
        self.createIndexFile(self.city_index_file, files=self.city_files)


    def writeByService(self, specs, file_name):
        try:
            f = open(self.path+file_name, 'w')
            f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
            f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
            for spec in specs:
                f.write('   <url>\n'\
                        '       <loc>http://www.myhome.ru/users/' + str(spec[1]) + '</loc>\n'\
                        '       <lastmod>' + str(self.datetime.datetime.fromtimestamp(int(spec[2])).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                        '       <changefreq>weekly</changefreq>\n'\
                        '   </url>\n')

            f.write('</urlset>')
            f.close()
        except:
            pass

    def writeByCity(self, specs, file_name):
        f = open(self.path+file_name, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        for spec in specs:
            f.write('   <url>\n'\
                    '       <loc>http://www.myhome.ru/users/' + str(spec[1]) + '</loc>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.fromtimestamp(int(spec[2])).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '       <priority>0.6</priority>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '   </url>\n')

        f.write('</urlset>')
        f.close()


    def createIndexFile(self, index_file, files):
        try:
            f = open(index_file, 'w')
            f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
            f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
            for file in files:
                f.write('   <sitemap>\n'\
                        '       <loc>http://www.myhome.ru/sitemaps/specialist/' + file + '</loc>\n'\
                        '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                        '   </sitemap>\n')

            f.write('</sitemapindex>')
            f.close()
        except:
            pass



    def getCityByService(self, service_id):
        try:
            self.db_cursor.execute("""SELECT DISTINCT c.id, c.eng_name FROM city c
                                        INNER JOIN user_servicecity usc ON usc.city_id = c.id
                                        INNER JOIN user_service us ON us.user_id = usc.user_id
                                        WHERE usc.city_id is not null AND us.service_id = '%(sid)s'"""%{"sid": str(service_id)})
            return self.db_cursor.fetchall()
        except:
            pass

    def generateByServiceCity(self):
        services = self.getServices()
        for service in services:
            cities = self.getCityByService(service[0])
            file_name=service[2]+'.xml'
            self.servicecity_files.append(file_name)
            self.writeByServiceCity(service, cities, file_name=file_name)
        self.writeAllServiceCity(self.servicecity_urls, 'sitemap_service_cities.xml')
        self.createIndexFile(self.servicecity_index_file, files=self.servicecity_files)

    def writeByServiceCity(self, service, cities, file_name):
        try:
            f = open(self.path+file_name, 'w')
            f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
            f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
            for city in cities:
                url = 'http://www.myhome.ru/specialist/' + str(service[2]) + '/' + str(city[1])
                self.servicecity_urls.append(url)
                f.write('   <url>\n'\
                        '       <loc>' + url + '</loc>\n'\
                        '       <priority>0.6</priority>\n'\
                        '       <changefreq>weekly</changefreq>\n'\
                        '   </url>\n')

            f.write('</urlset>')
            f.close()
        except:
            pass

    def writeAllServiceCity(self, urls, file_name):
        self.servicecity_files.append('sitemap_service_cities.xml')
        f = open(self.path+file_name, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        for url in urls:
            f.write('   <url>\n'\
                    '       <loc>' + url + '</loc>\n'\
                    '       <priority>0.6</priority>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '   </url>\n')

        f.write('</urlset>')
        f.close()