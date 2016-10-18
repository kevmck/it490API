import json
import pika
import requests

year = str(raw_input("Enter year: "))
make = str(raw_input("Enter make: "))
model = str(raw_input("Enter model: "))

linkBuild = ("http://www.nhtsa.gov/webapi/api/Recalls/vehicle/modelyear/" + year + "/make/" + make + "/model/" + model + "?format=json")

try:	
    r = requests.get(linkBuild)
    print (r.text)

except requests.exceptions.RequestException as e:
    print (e)
    credentials = pika.PlainCredentials('test', 'test')
    connection = pika.BlockingConnection(pika.ConnectionParameters('192.168.2.10', 5672, 'testHost', credentials))
    channel = connection.channel()
    channel.queue_declare('testQueue', False, True, False, False)
    channel.basic_publish(exchange='testExchange', routing_key='*', body=json.dumps({"type": "log", "message": (str(e))}))
    connection.close()

