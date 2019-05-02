#!/usr/bin/python

import RPi.GPIO as GPIO
import time
import datetime

compteur_principal = 0.0
compteur = 0
dateJour = 0


def cb_compteur_principal(channel):
    global compteur
    global compteur_principal
    global dateJour
    log = open("/tmp/log_compteur.log", "a")
    now = datetime.datetime.now()
    heure = now.strftime('%Y-%m-%d %H:%M:%S.%f')
    log.write('boucle de detection %s \n' % int(compteur))
    if now.strftime('%d') != dateJour:
        log.write('if nouveau jour \n')
        log.write('init connection DB \n')
        log.write('connecte DB \n')
        dateJour = now.strftime('%d')
        dateveille = now - datetime.timedelta(1)
        timestampveille = int(datetime.datetime.strptime(dateveille, '%Y-%m-%d').strftime("%s"))
        insertline = "insert into puissance.record(timestamp, watt) VALUES('%s', '%s')"
        log.write(insertline)
        log.write('\n')
        var = (timestampveille, float(compteur_principal) / 1000.0)
        new_line = insertline % var
        log.write(new_line)
        log.write('\n')
        # print(new_line)
        log.write('envoie requete \n')
        dateJour = now.strftime('%d')
        compteur_principal = 1
        log.write('Nouveau jour \n')
    else:
        compteur_principal = compteur_principal + 1
    compteur = compteur + 1
    if compteur >= 100:
        line = "compteur principal - %s : %s kW" % (heure, compteur_principal/1000)
        log.write(line)
        compteur = 0
    log.close()


GPIO.setmode(GPIO.BCM)
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.add_event_detect(18, GPIO.FALLING, callback=cb_compteur_principal, bouncetime=70)

while True:
    time.sleep(0.1)
