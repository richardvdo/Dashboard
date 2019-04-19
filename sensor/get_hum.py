import os
import glob
import time
import mysql.connector
import sys
import Adafruit_DHT

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

device_folder = []
device_file = []
temp_c = []
sonde = [['27', 'humidite', 'garage']]



def read_temp_raw(pin):


    humidity, temperature = Adafruit_DHT.read_retry(Adafruit_DHT.DHT11, pin)

    if humidity is not None and temperature is not None:
        # print humidity
        return humidity
    else:
        return "error"
 
def read_temp():
    i = 0
    conn = mysql.connector.connect(host="192.168.1.56", user="pi", password="66446644", database="sensor_v1")
    cursor = conn.cursor()
    for sensor in sonde:
        lines = read_temp_raw(sonde[i][0])
        # print lines
        # print sonde[i][0]
        # print sonde[i][1]
        # print sonde[i][2]



        if lines != 'error':

            sensor_line = ("insert into sensor_v1.record(timestamp, sensor_name, sensor_type, sensor_place, value) VALUES(NOW(), 'DHT11' , '%s', '%s', '%s')")
            # print sensor_line
            var = (sonde[i][1], sonde[i][2], lines)
            # var = ("humi", "gara", lines)
            # print var
            new_line = (sensor_line) % (var)
            #print (new_line)
            cursor.execute(new_line)
            conn.commit()
            i = i + 1
    conn.close()
    return lines

while True:
    read_temp()
    time.sleep(600)

