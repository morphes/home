# coding=utf-8
import os
import time

class Interiorstatic():
    """
    Генерирует набор сайтмапов для статических страниц жилых интерьеров по цветам, типам и стилям:
        - Сайтмап для цветовых вариантов интерьеров (например: /idea/interior/brown)
        - Сайтмап для стилевых вариантов интерьеров (например: /idea/interior/baroque)
        - Сайтмап для урлов формата "тип интерьера+цвет" (например: /idea/interior/diningroom-yellow)
        - Сайтмап для урлов формата "тип интерьера+стиль" (например: /idea/interior/nursery-classic)
        - Сделать индексный сайтмап для всех этих карт
    """

    # Имя файла, содержащего ссылки на варианты интерьеров по цветам
    name_colors = 'interiors_colors.xml'

    # Имя файла, содержащего ссылки на варианты интерьеров по стилям
    name_styles = 'interiors_styles.xml'

    # Имя файла, содержащего ссылки на варианты интерьеров по типам помещений с цветами
    name_types_colors = 'interior_types_colors.xml'

    # Имя файла, содержащего ссылка на варианты интерьеров по типам помещений со стилями
    name_types_styles = 'interior_types_styles.xml'

    # Имя файла, содержащий список сайтмапов
    name_index = 'interiors_colors_styles_index.xml'


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

        self.genTypesColors()

        self.genTypesStyles()



        self.genIndex()

    def genColors(self):
        """ Генерирует список урлов на интерьеры по цветам """

        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'color' AND ih.idea_type_id = 1
        """)
        name_colors = self.db_cursor.fetchall()

        # Собираем все <url> теги
        urlTags = ''
        for idea in name_colors:
            urlTags += self.__getUrlTag('/idea/interior/' + str(idea[0]), time.time(), '0.5', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_colors, urlTags)


    def genStyles(self):
        """ Генерирует список урлов по стилям"""
        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'style' AND ih.idea_type_id = 1
        """)
        name_styles = self.db_cursor.fetchall()

        # Собираем все <url> теги по Общественным Интерьерам вместе
        urlTags = ''
        for idea in name_styles:
            urlTags += self.__getUrlTag('/idea/interior/' + str(idea[0]), time.time(), '0.5', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_styles, urlTags)


    def genTypesColors(self):
        """ Генерирует список урлов по помещениям и цветам"""

        # Получаем список помещений
        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM `idea_heap` ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'room'
        """)
        name_rooms = self.db_cursor.fetchall()

        # Получаем список цветов
        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'color' AND ih.idea_type_id = 1
        """)
        name_colors = self.db_cursor.fetchall()


        # Для каждого типа строения получаем список общественных интерьеров
        # и сохраняем каждый такой список в отдельном xml файле
        urlTags = ''
        for room in name_rooms:
            for color in name_colors:
                urlTags += self.__getUrlTag('/idea/interior/'+ str(room[0])+'-'+str(color[0]), time.time(), '0.5', 'weekly')

            # Сохраняем список по типу помещения в файл с именем interiorpublic_<eng_name>.xml
            self.__createXml(self.path+self.name_types_colors, urlTags)

    def genTypesStyles(self):
        """ Генерирует список урлов по помещениям и Стилям"""

        # Получаем список помещений
        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM `idea_heap` ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'room'
        """)
        name_rooms = self.db_cursor.fetchall()

        # Получаем список цветов
        self.db_cursor.execute("""
            SELECT ih.eng_name
            FROM idea_heap ih
            WHERE ih.parent_id = 72 AND ih.option_key = 'style' AND ih.idea_type_id = 1
        """)
        name_styles = self.db_cursor.fetchall()


        # Для каждого типа строения получаем список общественных интерьеров
        # и сохраняем каждый такой список в отдельном xml файле
        urlTags = ''
        for room in name_rooms:
            for style in name_styles:
                urlTags += self.__getUrlTag('/idea/interior/'+ str(room[0])+'-'+str(style[0]), time.time(), '0.5', 'weekly')

            # Сохраняем список по типу помещения в файл с именем interiorpublic_<eng_name>.xml
            self.__createXml(self.path+self.name_types_styles, urlTags)


    def genIndex(self):
        """ Генерирует индексный сайтмап со список сайтмапов """

        f = open(self.path+self.name_index, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_colors+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')


        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_styles+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_types_colors+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')


        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_types_styles+'</loc>\n'
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
