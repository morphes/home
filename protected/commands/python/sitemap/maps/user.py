
class User():

    def __init__(self, db_conn, db_cursor, datetime, path):
        self.db_conn = db_conn
        self.db_cursor = db_cursor
        self.datetime = datetime
        self.path = path

    def generate(self):
        self.db_cursor.execute("SELECT t.id, t.login, t.update_time FROM `user` t WHERE t.status=2 AND (t.role = 3 OR t.role=4) ORDER BY t.update_time DESC LIMIT 1000")
        users = self.db_cursor.fetchall()
        self.write(data=users)

    def write(self, data):
        f = open(self.path, 'w')
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n')
        for user in data:
            f.write('   <url>\n'\
                    '       <loc>http://www.myhome.ru/users/' + str(user[1]) + '</loc>\n'\
                    '       <lastmod>' + str(self.datetime.datetime.fromtimestamp(int(user[2])).strftime('%Y-%m-%d')) + '</lastmod>\n'\
                    '       <changefreq>weekly</changefreq>\n'\
                    '   </url>\n')

        f.write('</urlset>')
        f.close()