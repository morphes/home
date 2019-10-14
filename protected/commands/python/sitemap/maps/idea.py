
class Idea():

    def __init__(self, db_conn, db_cursor, datetime, path):
        self.db_conn = db_conn
        self.db_cursor = db_cursor
        self.datetime = datetime
        self.path = path

    def generate(self):
        self.db_cursor.execute("SELECT ih.eng_name \
                FROM `idea_heap` ih \
                WHERE ih.parent_id = 72 AND ih.option_key = 'room';")
        interiors = self.db_cursor.fetchall()

        self.db_cursor.execute("SELECT ih.eng_name \
                FROM `idea_heap` ih \
                WHERE ih.parent_id = 84 AND ih.option_key = 'building_type';")
        interiorspublic = self.db_cursor.fetchall()

        self.write(inter=interiors, interpub=interiorspublic)


    def write(self, inter, interpub):
        f = open(self.path, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')

        f.write('   <url>\n'\
                '       <loc>http://www.myhome.ru/idea/interior</loc>\n'\
                '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                '       <priority>0.8</priority>\n'\
                '       <changefreq>weekly</changefreq>\n'\
                '   </url>\n'\
                '   <url>\n'\
                '       <loc>http://www.myhome.ru/idea/interiorpublic</loc>\n'\
                '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                '       <priority>0.8</priority>\n'\
                '       <changefreq>weekly</changefreq>\n'\
                '   </url>\n')

        for idea in inter:
            f.write('   <url>\n'\
                    '       <loc>http://www.myhome.ru/idea/interior/' + str(idea[0]) + '</loc>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '       <priority>0.7</priority>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '   </url>\n')

        for idea in interpub:
            f.write('   <url>\n'\
                    '       <loc>http://www.myhome.ru/idea/interiorpublic/' + str(idea[0]) + '</loc>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.now().strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '       <priority>0.7</priority>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '   </url>\n')

        f.write('</urlset>')


        f.close()