import os
import glob
import time
import mysql.connector

os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

device_folder = []
device_file = []
temp_c = []
sonde = [['28-0517c1764bff', 'temperature', 'garage'], ['28-0417c13d9dff', 'temperature', 'exterieur']]
# sonde[0] = '28-0517c1764bff'
# sonde[1] = '28-0417c13d9dff'
# sonde2 = ''
base_dir = '/sys/bus/w1/devices/'
end_dir = '/w1_slave'


for device in sonde:
    device_folder.append(str(base_dir) + device[0])

for folder in device_folder:
    device_file.append(str(folder) + end_dir)

def read_temp_raw(file):
    f = open(file, 'r')
    lines = f.readlines()
    f.close()
    return lines
 
def read_temp():
    i = 0
    for sensor in device_file:
        lines = read_temp_raw(sensor)
        while lines[0].strip()[-3:] != 'YES':
            time.sleep(0.2)
            lines = read_temp_raw()
        equals_pos = lines[1].find('t=')
        if equals_pos != -1:
            temp_string = lines[1][equals_pos+2:]
            temp_c.append(float(temp_string) / 1000.0)
            conn = mysql.connector.connect(host="192.168.1.56", user="pi", password="66446644", database="sensor_v1")
            cursor = conn.cursor()

            sensor_line = ("insert into sensor_v1.record(timestamp, sensor_name, sensor_type, sensor_place, value) VALUES(NOW(), '%s' , '%s', '%s', '%s')")
            var = (sensor, sonde[i][1], sonde[i][2], (float(temp_string) / 1000.0))
            new_line = (sensor_line) % (var)
            #print (new_line)
            cursor.execute(new_line)
            conn.commit()
            i = i + 1
    conn.close()
    return temp_c

while True:
    read_temp()
    temp_c = []
    time.sleep(600)

