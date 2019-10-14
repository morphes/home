# coding=utf-8
import os

class Interior():
    """
    Генерирует набор сайтмапов для Жилых интерьеров:
        - Общий сайтмап для всех общественных интерьеров
        - Сайтмап для новых интерьеров. Указываем в нем набор самых новых интерьеров.
    """

    # Имя файла, содержащего все жилые интерьеры
    name_all = 'interiors_all.xml'

    # Имя файла, содержащего 300 самых новых общественных интерьеров
    name_new = 'interiors_recent.xml'

    # Количество жилых интерьеров, складываемых в name_new
    quant_new = 300



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

        self.genAll()

        self.genNew()


    def genAll(self):
        """ Генерирует список всех жилых интерьеров """

        self.db_cursor.execute("""
            SELECT ip.id, ip.update_time
            FROM `interior` ip
            WHERE ip.status = 3 OR ip.status = 7
        """)
        interiors = self.db_cursor.fetchall()

        # Собираем все <url> теги по Интерьерам вместе
        urlTags = ''
        for idea in interiors:
            urlTags += self.__getUrlTag('/idea/interior/' + str(idea[0]), idea[1], '0.7', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_all, urlTags)


    def genNew(self):
        """ Генерирует список последних новых интерьеров """
        self.db_cursor.execute("SELECT ip.id, ip.update_time \
                FROM `interior` ip \
                WHERE ip.status = 3 OR ip.status = 7 ORDER BY update_time DESC LIMIT 0, "+ str(self.quant_new))
        interiors = self.db_cursor.fetchall()

        # Собираем все <url> теги по Общественным Интерьерам вместе
        urlTags = ''
        for idea in interiors:
            urlTags += self.__getUrlTag('/idea/interior/' + str(idea[0]), idea[1], '0.7', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_new, urlTags)



    def __getUrlTag(self, link, unix_time, priority, changefreq):
        """ Генерирует по переданным параметр тег <url> для xml файла """
        urlTag = '   <url>\n'\
                 '       <loc>http://www.myhome.ru' + link + '</loc>\n'\
                 '       <lastmod>' + str(self.datetime.datetime.fromtimestamp(unix_time).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                 '       <priority>' + priority + '</priority>\n'\
                 '       <changefreq>' + changefreq + '</changefreq>\n'\
                 '   </url>\n'

        return urlTag


    def __createXml(self, filename, body):
        """ Генерация xml файла
        @param filename Имя генерируемого файла
        @param body Тело xml файла (список тегов <url>)
        """
        f = open(filename, 'w')

        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        f.write(body)

        f.write('</urlset>')

        f.close()
