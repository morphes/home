#!/usr/bin/env python
# -*- coding: utf-8

import argparse
import MySQLdb
import datetime

# Парсинг аргументов запуска приложения
parser = argparse.ArgumentParser(description='Path to xml file (full or relative), where sitemap will be saved')
parser.add_argument('--output_file', '-o', dest='output_file', default="sitemap.xml", help='Path to file for output xml')
parser.add_argument('--types', '-t', dest='types', default="tender", help='Types of sitemap')
args = parser.parse_args()

maps = args.types.split(',')

if len(maps) > 0 :
    db_conn = MySQLdb.connect(host="localhost", user="myhome", passwd="3555131", db="myhome", charset='utf8')
    db_cursor = db_conn.cursor()
else :
    exit(0)

for map in maps :
    if map == 'tender':
        from maps import tender
        sitemap = tender.Tender(db_conn, db_cursor, datetime, '../tenders.xml')
    if map == 'service':
        from maps import service
        sitemap = service.Service(db_conn, db_cursor, datetime, '../service.xml')
    if map == 'spec':
        from maps import specialist
        sitemap = specialist.Specialist(db_conn, db_cursor, datetime, '../specialist/', serv_index_file='../specialist_index.xml', city_index_file='../cities_index_file.xml', servicecity_index_file='../specialist_service_city_index.xml ')
    if map == 'user':
        from maps import user
        sitemap = user.User(db_conn, db_cursor, datetime, '../specialist/new_user.xml')
    if map == 'idea':
        from maps import idea
        sitemap = idea.Idea(db_conn, db_cursor, datetime, '../sitemap_interiors.xml')
    if map == 'interiorpublic':
        from maps import interiorpublic
        sitemap = interiorpublic.Interiorpublic(db_conn, db_cursor, datetime, 'interiors', '../interiors/')
    if map == 'images':
        from maps import images
        sitemap = images.Images(db_conn, db_cursor, datetime, 'interiors', '../interiors/')
    if map == 'interiorstatic':
        from maps import interiorstatic
        sitemap = interiorstatic.Interiorstatic(db_conn, db_cursor, datetime, 'interiors', '../interiors/')
    if map == 'interiorportfolio':
        from maps import interiorportfolio
        sitemap = interiorportfolio.Interiorportfolio(db_conn, db_cursor, datetime, 'portfolio', '../portfolio/')
    if map == 'publicinterior':
        from maps import publicinterior
        sitemap = publicinterior.Publicinterior(db_conn, db_cursor, datetime, 'interiors', '../interiors/')
    if map == 'catalog':
        from maps import catalog
        sitemap = catalog.Catalog(db_conn, db_cursor, datetime, 'catalog', '../catalog/')
    if map == 'interior':
        from maps import interior
        sitemap = interior.Interior(db_conn, db_cursor, datetime, 'interiors', '../interiors/')



    sitemap.generate()

db_conn.close()
