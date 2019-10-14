# coding=utf-8
import os

class Interiorpublic():
    """
    Генерирует набор сайтмапов для Общественных интерьеров:
        - Общий сайтмап для всех общественных интерьеров
        - Отдельные сайтмапы для вывода интерьеров по каждому типу помещений: interiorpublic_office.xml, interiorpublic_frontoffice.xml и т.д.
        - Сайтмап для новых интерьеров. Указываем в нем набор самых новых интерьеров.
        - Индексный сайтмап, куда будут включены общий сайтмап и отдельные сайтмапы для типов помещений: interiorpublic_index.xml и т.д.
    """

    # Имя файла, содержащего все общественные интерьеры, независимо от типа помещения
    name_all = 'interiorpublic_all.xml'

    # Имя файла, содержащего 100 самых новых общественных интерьеров
    name_new = 'interiorpublic_new.xml'
    # Количество общественных интерьеров, складываемых в name_new
    quant_new = 100

    # Имя файла, содержащий список сайтмапов для типов помещений и общий сайтмап (name_all)
    name_index = 'interiorpublic_index.xml'

    # Список названий xml файлов, в которых лежат общественные интрерьеры по типа строений
    list_by_types = []


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

        self.genByTypes()

        self.genIndex()

    def genAll(self):
        """ Генерирует список всех общественных интерьеров """

        self.db_cursor.execute("SELECT ip.id, ip.update_time \
                FROM `interiorpublic` ip \
                WHERE ip.status = 3 OR ip.status = 7")
        interiors = self.db_cursor.fetchall()

        # Собираем все <url> теги по Общественным Интерьерам вместе
        urlTags = ''
        for idea in interiors:
            urlTags += self.__getUrlTag('/idea/interiorpublic/' + str(idea[0]), idea[1], '0.5', 'weekly')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_all, urlTags)


    def genNew(self):
        """ Генерирует список последних новых общественных интерьеров """
        self.db_cursor.execute("SELECT ip.id, ip.update_time \
                FROM `interiorpublic` ip \
                WHERE ip.status = 3 OR ip.status = 7 ORDER BY update_time DESC LIMIT 0, "+ str(self.quant_new))
        interiors = self.db_cursor.fetchall()

        # Собираем все <url> теги по Общественным Интерьерам вместе
        urlTags = ''
        for idea in interiors:
            urlTags += self.__getUrlTag('/idea/interiorpublic/' + str(idea[0]), idea[1], '0.7', 'daily')

        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_new, urlTags)


    def genByTypes(self):
        """ Генерирует набор сайтмапов разибвая общественные интерьеры по типам строений """

        # Получаем список типов строений для которых надо сгенерить xml файлы
        self.db_cursor.execute("SELECT ih.id, ih.eng_name \
                FROM `idea_heap` ih \
                WHERE ih.parent_id = 84 AND ih.option_key = 'building_type'")
        build_types = self.db_cursor.fetchall()


        # Для каждого типа строения получаем список общественных интерьеров
        # и сохраняем каждый такой список в отдельном xml файле
        for type in build_types:
            self.db_cursor.execute("SELECT ip.id, ip.update_time \
                FROM `interiorpublic` ip \
                WHERE (ip.status = 3 OR ip.status = 7) AND building_type_id = '"+ str(type[0]) +"'")
            ideas = self.db_cursor.fetchall()

            urlTags = ''
            for idea in ideas:
                urlTags += self.__getUrlTag('/idea/interiorpublic/'+ str(idea[0]), idea[1], '0.5', 'weekly')

            # Генерим имя xml файла и сохраняем его в список
            xml_name = 'interiorpublic_'+type[1]+'.xml'
            self.list_by_types.append(xml_name)

            # Сохраняем список по типу помещения в файл с именем interiorpublic_<eng_name>.xml
            self.__createXml(self.path+xml_name, urlTags)


    def genIndex(self):
        """ Генерирует индексный сайтмап, содержащий список xml файлов лежащих в self.list_by_types и общий сайтмап
        содержащий список всех общественных интерьеров """

        f = open(self.path+self.name_index, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        now_str = str(self.datetime.datetime.now().strftime('%Y-%m-%d'))

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_all+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')


        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_new+'</loc>\n'
                '<lastmod>'+now_str+'</lastmod>\n'
                '</sitemap>\n')

        for name in self.list_by_types:
            f.write('<sitemap>\n'
                    '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+name+'</loc>\n'
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
