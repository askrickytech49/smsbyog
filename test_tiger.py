import requests
import json
import sqlite3
import pymysql

conn = pymysql.connect(host='localhost', user='root', password='', db='smsbyog')
cursor = conn.cursor()
cursor.execute("SELECT api_url, api_key FROM api_detail WHERE id='8'")
row = cursor.fetchone()
api_url = row[0]
api_key = row[1]

url = f"{api_url}/stubs/handler_api.php?api_key={api_key}&action=getPrices"
response = requests.get(url, verify=False)
data = response.json()
print("Total countries in prices:", len(data))
first_country = list(data.keys())[0]
print(f"Country {first_country} has {len(data[first_country])} services")
