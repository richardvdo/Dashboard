import os
import glob
import time
import mysql.connector


while True:
    sensor_line = ("insert into sensor_v1.record(timestamp, sensor_name, sensor_type, sensor_place, value) VALUES(NOW(), %s , %s, %s, %s)")
    hello = ("'28-truc'", "'temp'", "'garage'", "'10,28'")
    new_line = (sensor_line) % (hello)

    # world = "world"
    # test = "machin %s et %s fregt" % (hello, world)
    # print hello + " " + world
    # print "machin %s et %s fregt" % (hello, world)
    # print "{} {}".format(hello, world)
    # print ' '.join([hello, world])
    # print (test)


    # insert into sensor_v1.record(timestamp, sensor_name, sensor_type, sensor_place, value) VALUES(NOW(), '28-azerty', 'temp', 'garage', '10,28');

    print (new_line)
    time.sleep(1)

