# coding=utf-8
import os
import time

class Interiorportfolio():
    """

    """

    # Имя главного индексного файла
    name_index = 'interiors_portfolio_colors_styles_types_index.xml'

    # Имя индексного файла для разбиения по цветам
    name_colors_index = 'interiors_portfolio_colors_index.xml'

    # Имя индексного файла для разбиения по стилям
    name_styles_index = 'interiors_portfolio_styles_index.xml'

    # Имя индексного файла для разбиения по типам
    name_types_index = 'interiors_portfolio_types_index.xml'


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

        self.genColors()

        self.genStyles()

        self.genTypes()


        self.genIndex()


    def genColors(self):
        """ Генерирует список урлов на интерьеры по цветам """

        self.db_cursor.execute("""
            SELECT ih.eng_name, ih.id
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'color' AND ih.idea_type_id = 1
        """)
        name_colors = self.db_cursor.fetchall()

        # Список xml файлов, которые будут собраны в промежуточный индексный файл
        list_xml = []

        # Получасем список всех жилых интерьеров только активных физ. и юр.лиц
        # Фильтрация по цветами
        for color in name_colors:
            self.db_cursor.execute("""
                SELECT user.login, i.service_id, ic.interior_id, i.update_time
                FROM interior_content ic
                LEFT JOIN user
                 ON user.id = ic.author_id
                LEFT JOIN interior i
                 ON ic.interior_id = i.id
                WHERE
                    (user.role = '3' OR user.role = '4')
                    AND
                    user.status = 2
                    AND
                    i.`status`IN(2, 3, 4, 6, 7)
                    AND
                    ic.color_id = """+str(color[1])+"""
                GROUP BY ic.interior_id
            """)
            ideas = self.db_cursor.fetchall()

            # Формал ссылки на элемент
            # /users/<user_login>/project/<service_id>/<interior_id>?t=1
            urlTags = ''
            for idea in ideas:
                urlTags += self.__getUrlTag('/users/'+str(idea[0])+'/project/'+str(idea[1])+'/'+str(idea[2])+'/?t=1', idea[3], '0.5', 'weekly')

            # Генерим имя xml файла и сохраняем его в список
            xml_name = 'interiors_portfolio_'+color[0]+'.xml'
            list_xml.append(xml_name)

            # Сохраняем список по цветам interiors_portfolio_<eng_name>.xml
            self.__createXml(self.path+xml_name, urlTags)

        self.genSubIndex(list_xml, self.name_colors_index)

    def genStyles(self):
        """ Генерирует список урлов по стилям"""
        self.db_cursor.execute("""
            SELECT ih.eng_name, ih.id
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'style' AND ih.idea_type_id = 1
        """)
        name_styles = self.db_cursor.fetchall()

        # Список xml файлов, которые будут собраны в промежуточный индексный файл
        list_xml = []

        # Получасем список всех жилых интерьеров только активных физ. и юр.лиц
        # Фильтрация по стилям
        for style in name_styles:
            self.db_cursor.execute("""
                SELECT user.login, i.service_id, ic.interior_id, i.update_time
                FROM interior_content ic
                LEFT JOIN user
                 ON user.id = ic.author_id
                LEFT JOIN interior i
                 ON ic.interior_id = i.id
                WHERE
                    (user.role = '3' OR user.role = '4')
                    AND
                    user.status = 2
                    AND
                    i.`status`IN(2, 3, 4, 6, 7)
                    AND
                    ic.style_id = """+str(style[1])+"""
                GROUP BY ic.interior_id
            """)
            ideas = self.db_cursor.fetchall()

            # Формал ссылки на элемент
            # /users/<user_login>/project/<service_id>/<interior_id>?t=1
            urlTags = ''
            for idea in ideas:
                urlTags += self.__getUrlTag('/users/'+str(idea[0])+'/project/'+str(idea[1])+'/'+str(idea[2])+'/?t=1', idea[3], '0.5', 'weekly')

            # Генерим имя xml файла и сохраняем его в список
            xml_name = 'interiors_portfolio_'+style[0]+'.xml'
            list_xml.append(xml_name)

            # Сохраняем список по цветам interiors_portfolio_<eng_name>.xml
            self.__createXml(self.path+xml_name, urlTags)

        self.genSubIndex(list_xml, self.name_styles_index)


    def genTypes(self):
        """ Генерирует список урлов по помещениям и цветам"""

        # Получаем список помещений
        self.db_cursor.execute("""
            SELECT ih.eng_name, ih.id
            FROM `idea_heap` ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'room'
        """)
        name_rooms = self.db_cursor.fetchall()

        # Список xml файлов, которые будут собраны в промежуточный индексный файл
        list_xml = []

        # Для каждого типа строения получаем список общественных интерьеров
        # и сохраняем каждый такой список в отдельном xml файле
        urlTags = ''
        for room in name_rooms:
            self.db_cursor.execute("""
                SELECT user.login, i.service_id, ic.interior_id, i.update_time
                FROM interior_content ic
                LEFT JOIN user
                 ON user.id = ic.author_id
                LEFT JOIN interior i
                 ON ic.interior_id = i.id
                WHERE
                    (user.role = '3' OR user.role = '4')
                    AND
                    user.status = 2
                    AND
                    i.`status`IN(2, 3, 4, 6, 7)
                    AND
                    ic.room_id = """+str(room[1])+"""
                GROUP BY ic.interior_id
            """)
            ideas = self.db_cursor.fetchall()

            # Формал ссылки на элемент
            # /users/<user_login>/project/<service_id>/<interior_id>?t=1
            urlTags = ''
            for idea in ideas:
                urlTags += self.__getUrlTag('/users/'+str(idea[0])+'/project/'+str(idea[1])+'/'+str(idea[2])+'/?t=1', idea[3], '0.5', 'weekly')

            # Генерим имя xml файла и сохраняем его в список
            xml_name = 'interiors_portfolio_'+room[0]+'.xml'
            list_xml.append(xml_name)

            # Сохраняем список по цветам interiors_portfolio_<eng_name>.xml
            self.__createXml(self.path+xml_name, urlTags)

        self.genSubIndex(list_xml, self.name_types_index)


    def genSubIndex(self, urls, name_file):
        """ Генерирует промежуточные индексные файлы
        @param urls Список ссылок на xml файлы
        @param name_file Имя индексного файла, в который будут добавлены все urls
        """

        f = open(self.path+name_file, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        for url in urls:
            f.write('<sitemap>\n'
                    '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+url+'</loc>\n'
                    '<lastmod>'+now_str+'</lastmod>\n'
                    '</sitemap>\n')


        f.write('</sitemapindex>')
        f.close()


    def genIndex(self):
        """ Генерирует индексный сайтмап со список сайтмапов """

        f = open(self.path+self.name_index, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_colors_index+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')


        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_styles_index+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_types_index+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')


        f.write('</sitemapindex>')
        f.close()


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
