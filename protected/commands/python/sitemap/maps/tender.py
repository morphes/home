
class Tender():

    def __init__(self, db_conn, db_cursor, datetime, path):
        self.db_conn = db_conn
        self.db_cursor = db_cursor
        self.datetime = datetime
        self.path = path

    def generate(self):
        self.db_cursor.execute("SELECT t.id, t.update_time \
                FROM `tender` t \
                WHERE t.send_notify = 2 AND t.status = 2;")
        tenders = self.db_cursor.fetchall()
        self.write(data=tenders)


    def write(self, data):
        f = open(self.path, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        for tender in data:
            f.write('   <url>\n'\
                    '       <loc>http://www.myhome.ru/tenders/' + str(tender[0]) + '</loc>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.fromtimestamp(int(tender[1])).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '   </url>\n')

        f.write('</urlset>')
        f.close()