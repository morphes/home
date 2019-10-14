# coding=utf-8
import os

class Images():
    """
    Генерирует набор сайтмапов для индексации большеразмерных фотографий Общественных интерьеров.
    В сайтмапы будут попадать картинки с перфиксом 1920x1080. Весь русский текст в карте сайта должен
    быть обязательно в кодировке UTF-8. Все спец. символы в названиях идей (кавычки, амперсанды, апострофы и т.д.)
    заменяем на соотв. коды.
        - Общая карта со всеми картинками
        - Делаем картиночные карты сайта для всех типов нежилых помещений (interiorpublic_beauty_images.xml, interiorpublic_office_images.xml и т.д.)
        - Делаем индексную карту сайта для картиночных карт: interiorpublic_images_index.xml и указываем в ней все созданные картиночные карты.
    """

    # Имя файла, содержащего все фотографии общественных интерьеров
    name_all = 'interiorpublic_all_images.xml'

    # Имя файла, содержащий список сайтмапов для типов помещений и общий сайтмап (name_all)
    name_index = 'interiorpublic_images_index.xml'

    # Список названий xml файлов, в которых лежат фотографии общественных интрерьеров по типа строений
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

        self.genByTypes()

        self.genIndex()

    def genAll(self):
        """ Генерирует список всех фотографий общественных интерьеров """


        # Получаем список тегов с картинками для всех типов строений
        urlTags = self.__getTagsImages()


        # Собираем и сохраняем файл
        self.__createXml(self.path + self.name_all, urlTags)


    def genByTypes(self):
        """ Генерирует набор сайтмапов разибвая общественные интерьеры по типам строений """

        # Получаем список типов строений для которых надо сгенерить xml файлы
        self.db_cursor.execute("SELECT ih.id, ih.eng_name \
                FROM `idea_heap` ih \
                WHERE ih.parent_id = 84 AND ih.option_key = 'building_type'")
        build_types = self.db_cursor.fetchall()


        # Для каждого типа строения получаем список картинок общественных интерьеров
        # и сохраняем каждый такой список в отдельном xml файле
        for type in build_types:


            # Получаем список тегов картинок для конкретного типа помещения
            urlTags = self.__getTagsImages(type[0])


            # Генерим имя xml файла и сохраняем его в список
            xml_name = 'interiorpublic_'+type[1]+'_images.xml'
            self.list_by_types.append(xml_name)

            # Сохраняем список по типу помещения в файл с именем interiorpublic_<eng_name>.xml
            self.__createXml(self.path+xml_name, urlTags)


    def genIndex(self):
        """ Генерирует индексный сайтмап, содержащий список xml файлов лежащих в self.list_by_types и общий сайтмап
        содержащий список всех общественных интерьеров """

        f = open(self.path+self.name_index, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        f.write('<sitemap>\n'
                '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+self.name_all+'</loc>\n'
                '</sitemap>\n')

        for name in self.list_by_types:
            f.write('<sitemap>\n'
                    '<loc>http://www.myhome.ru/sitemaps/'+self.name_xml_dir+'/'+name+'</loc>\n'
                    '</sitemap>\n')

        f.write('</sitemapindex>')
        f.close()


    def __getTagsImages(self, build_type = 0):
        """Делает запрос к БД, получая картинки для общественных интерьеров. Если параметр build_type не указан,
        то выбираются картинки для всех типов общественных интерьеров, иначе только конкретного build_type"""

        # Получаем список картинок общественных интерьеров, с сортировкой по идеям.
        sql_select = """SELECT
                ipub.id,
                ipub.name,
                CONCAT_WS(' ', user.firstname, user.lastname),
                ih.option_value,
                uf.path,
                uf.name,
                uf.`desc`,
                uf.update_time,
                ipub.building_type_id,
                user.role"""

        sql_from = "FROM uploaded_file uf"

        sql_join = """
            LEFT JOIN idea_uploaded_file iuf
                ON iuf.uploaded_file_id = uf.id
            LEFT JOIN interiorpublic ipub
                ON ipub.id = iuf.item_id
            LEFT JOIN user
                ON user.id = ipub.author_id
            LEFT JOIN idea_heap ih
                ON ih.id = ipub.building_type_id"""

        sql_where = "WHERE iuf.idea_type_id = 3 AND (ipub.`status` = 3 OR ipub.`status` = 7)"

        # Если указан тип строения, ужесточаем условия выборки
        if build_type > 0:
            sql_where += " AND ipub.building_type_id = '"+str(build_type)+"'"

        sql_order = "ORDER BY ipub.id ASC"


        self.db_cursor.execute(sql_select+" "+sql_from+" "+sql_join+" "+sql_where+" "+sql_order)
        images = self.db_cursor.fetchall()


        # Собираем все <url> теги по Общественным Интерьерам вместе
        urlTags = ''
        tempImgs = []
        lastIdeaId = 0
        for image in images:

            if (image[0] == lastIdeaId) or (lastIdeaId == 0):
                tempImgs.append(image)

            elif (lastIdeaId > 0):
                urlTags += self.__getUrlTag(tempImgs)
                tempImgs = []
                tempImgs.append(image)

            lastIdeaId = image[0]

        return urlTags


    def __getUrlTag(self, images):
        """ Генерирует по переданным параметр тег <url> с тегами картинок для xml файла """

        urlTag = '<url>\n'
        urlTag += '<loc>http://www.myhome.ru/idea/interiorpublic/'+str(images[0][0])+'</loc>\n'

        seoCase = {
            232: 'Офисов',
            224: 'Административных зданий',
            225: 'Торгово-выставочных комплексов',
            226: 'Развлекательных центров',
            227: 'Киноконцертных комплексов, театров',
            228: 'Ресторанов, кафе, баров',
            229: 'Салонов красоты, саун, spa',
            230: 'Спортивных сооружений',
            231: 'Промышленных объектов'
        }

        for image in images:

            # Для админских ролей имя автора меняется
            if image[9] in [5, 7, 8, 9, 10]:
                author_name = 'Редакция MyHome'
            else:
                author_name = image[2].encode('utf-8')

            # Пишем тег для картинки
            urlTag += "<image:image>\n" \
                      "\t<image:loc>http://www.myhome.ru/uploads/public/"+str(image[4])+"/1920x1080resize_"+str(image[5])+".jpg</image:loc>\n" \
                      "\t<image:caption>"+image[1].encode('utf-8')+": "+image[6].encode('utf-8')+"</image:caption>\n" \
                      "\t<image:title>"+image[1].encode('utf-8')+". Идея опубликована: "+author_name+". Интерьеры "+seoCase[image[8]]+" на MyHome.ru</image:title>\n" \
                      "</image:image>\n"
        urlTag += '</url>\n'


        return urlTag


    def __createXml(self, filename, body):
        """ Генерация xml файла
        @param filename Имя генерируемого файла
        @param body Тело xml файла (список тегов <url>)
        """
        f = open(filename, 'w')

        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">\n')

        f.write(body)

        f.write('</urlset>')

        f.close()
