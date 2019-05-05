#!/usr/bin/python

import RPi.GPIO as GPIO
import time
import datetime
import mysql.connector

compteur_principal = 0.0
compteur = 0
dateJour = 0


def cb_compteur_principal(channel):
    global compteur
    global compteur_principal
    global dateJour
    log = open("/tmp/log_compteur_v3.log", "a")
    now = datetime.datetime.now()
    heure = now.strftime('%Y-%m-%d %H:%M:%S.%f')
    log.write('boucle de detection %s \n') % (int(compteur))
    if now.strftime('%d') != dateJour:
        log.write('if nouveau jour \n')
        log.write('init connection DB \n')
        # conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="solaire_v1")
        # cursor = conn.cursor()
        log.write('connecte DB \n')
        dateJour = now.strftime('%d')
        dateJourComplete = now.strftime('%Y-%m-%d')
        dateVeille = dateJourComplete - datetime.timedelta(1)
        timestampVeille = int(datetime.datetime.strptime(dateVeille, '%Y-%m-%d').strftime("%s"))
        insertline = "insert into puissance.record(timestamp, watt) VALUES('%s', '%s')"
        log.write(insertline)
        log.write('\n')
        var = (timestampVeille, float(compteur_principal) / 1000.0)
        new_line = (insertline) % (var)
        log.write(new_line)
        log.write('\n')
        # print(new_line)
        log.write('envoie requete \n')
        # cursor.execute(new_line)
        compteur_principal = 1
        log.write('Nouveau jour %s \n') % (int(dateJour))
        # conn.commit()
        # conn.close()
        log.write('close connection DB \n')
    else:
        compteur_principal = compteur_principal + 1
        log.write('increment compteur principale \n')
    compteur = compteur + 1
    if compteur >= 100:
        log.write('if compteur > 100 \n')
        log.write('init connection DB \n')
        # conn = mysql.connector.connect(host="maison.lithium", user="pi", password="66446644", database="solaire_v1")
        # cursor = conn.cursor()
        log.write('connecte DB \n')
        line = "compteur principal - %s : %s kW\n" % (heure, float(compteur_principal/1000))
        log.write(line)
        timestamp = int(datetime.datetime.strptime(heure, '%Y-%m-%d %H:%M:%S.%f').strftime("%s"))
        insertline = "insert into solaire_v1.production(timestamp, watt, watt_totale) VALUES('%s',100,'%s')"
        log.write(insertline)
        log.write('\n')
        var = (timestamp, float(compteur_principal) / 1000.0)
        new_line = (insertline) % (var)
        log.write(new_line)
        log.write('\n')
        # print(new_line)
        log.write('envoie requete \n')
        # cursor.execute(new_line)
        compteur = 0
        # conn.commit()
        # conn.close()
        log.write('close connection DB \n')
    log.close()


GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
6GPIO.add_event_detect(18, GPIO.FALLING, callback=cb_compteur_principal, bouncetime=70)


while True:
    time.sleep(0.1)
