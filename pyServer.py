import pika

credentials = pika.PlainCredentials('test', 'test')

connection = pika.BlockingConnection(pika.ConnectionParameters('127.0.0.1', 5672, 'testHost', credentials))

channel = connection.channel()

channel.queue_declare('testQueue', False, True, False, False)

def callback(ch, method, properties, body):
    print(" [x] Received %r" % body)

channel.basic_consume(callback,
                      queue='testQueue',
                      no_ack=True)

print(' [*] Waiting for messages. To exit press CTRL+C')
channel.start_consuming()
