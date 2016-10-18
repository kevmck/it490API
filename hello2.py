#!/usr/bin/env python2

import json
import pika

credentials = pika.PlainCredentials('test', 'test')

connection = pika.BlockingConnection(pika.ConnectionParameters('192.168.2.10', 5672, 'testHost', credentials))

channel = connection.channel()

channel.queue_declare('testQueue', False, True, False, False)

channel.basic_publish(exchange='testExchange', routing_key='*', body=json.dumps({"message":'Hello World!'}))

print(" [x] Sent 'Hello World!'")

connection.close()
