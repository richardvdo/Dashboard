<?php

  $sqlite = '/home/pi/DashScreen/PiHomeDashScreen/teleinfo/teleinfo.sqlite';

  //
  //  renvoie une trame teleinfo complete sous forme d'array
  //
  function getTeleinfo () {

    $handle = fopen ('/dev/ttyAMA0', "r"); // ouverture du flux

    if (!$handle) die ("'/dev/ttyAMA0' not found");

    while (fread($handle, 1) != chr(2)); // on attend la fin d'une trame pour commencer a avec la trame suivante

    $char  = '';
    $trame = '';
    $datas = '';

    while ($char != chr(2)) { // on lit tous les caracteres jusqu'a la fin de la trame
      $char = fread($handle, 1);
      if ($char != chr(2)){
        $trame .= $char;
      }
    }

    fclose ($handle); // on ferme le flux

    $trame = chop(substr($trame,1,-1)); // on supprime les caracteres de debut et fin de trame

    $messages = explode(chr(10), $trame); // on separe les messages de la trame

    foreach ($messages as $key => $message) {
      $message = explode (' ', $message, 3); // on separe l'etiquette, la valeur et la somme de controle de chaque message
      if(!empty($message[0]) && !empty($message[1])) {
        $etiquette = $message[0];
        $valeur    = $message[1];
        $datas[$etiquette] = $valeur; // on stock les etiquettes et les valeurs de l'array datas
      }
    }

    return $datas;
  }

  //
  //  enregistre la puissance instantanée en V.A et en W et les valeurs HP et HC en Wh
  //
  function collectTeleinfoData () {
    global $sqlite;
    $db = new SQLite3($sqlite);
    $db->exec('CREATE TABLE IF NOT EXISTS puissance (timestamp INTEGER, hc INTEGER, hp INTEGER, hchp TEXT, va REAL, iinst REAL, watt REAL);'); // cree la table puissance si elle n'existe pas

    $trame = getTeleinfo (); // recupere une trame teleinfo

    $data = array();
    $data['timestamp'] = time();
    $data['hc']  = preg_replace('`^[0]*`','',$trame['HCHC']); // conso total en Wh heure creuse, on supprime les 0 en debut de chaine
    $data['hp']  = preg_replace('`^[0]*`','',$trame['HCHP']); // conso total en Wh heure pleine, on supprime les 0 en debut de chaine
    $data['hchp']      = substr($trame['PTEC'],0,2); // indicateur heure pleine/creuse, on garde seulement les carateres HP (heure pleine) et HC (heure creuse)
    $data['va']        = preg_replace('`^[0]*`','',$trame['PAPP']); // puissance en V.A, on supprime les 0 en debut de chaine
    $data['iinst']     = preg_replace('`^[0]*`','',$trame['IINST']); // intensité instantanée en A, on supprime les 0 en debut de chaine
    $data['watt']      = $data['iinst']*220; // intensite en A X 220 V

    if($db->busyTimeout(5000)){ // stock les donnees
      $db->exec("INSERT INTO puissance (timestamp, hc, hp, hchp, va, iinst, watt) VALUES (".$data['timestamp'].", '".$data['hc']."', '".$data['hp']."', '".$data['hchp']."', ".$data['va'].", ".$data['iinst'].", ".$data['watt'].");");    }

    return 1;
  }

  //
  //  enregistre la consommation de la veille en Wh
  //
  function computeLastDayConso () {
    global $sqlite;

    $today = strtotime('today 00:00:00');
    $yesterday = strtotime("-1 day 00:00:00");

    $db = new SQLite3($sqlite);
    $results = $db->query("SELECT MAX(timestamp) AS timestamp, MAX(hc) AS total_hc, MAX(hp) AS total_hp, ((MAX(hc) - MIN(hc)) / 1000) AS daily_hc, ((MAX(hp) - MIN(hp)) / 1000) AS daily_hp FROM puissance 
                           WHERE timestamp >= $yesterday AND timestamp < $today GROUP BY strftime('%d-%m-%Y', timestamp, 'unixepoch', 'localtime');");

    $previousDay = $results->fetchArray(SQLITE3_ASSOC);

    $db->exec('CREATE TABLE IF NOT EXISTS conso (timestamp INTEGER, total_hc INTEGER, total_hp INTEGER, daily_hc REAL, daily_hp REAL);'); // cree la table conso si elle n'existe pas

    if($db->busyTimeout(5000)){ // stock les donnees
      $db->exec("INSERT INTO conso (timestamp, total_hc, total_hp, daily_hc, daily_hp) VALUES 
                (".$previousDay['timestamp'].", ".$previousDay['total_hc'].", ".$previousDay['total_hp'].", ".$previousDay['daily_hc'].", ".$previousDay['daily_hp'].");");
    }
  }

  //
  //  recupere les donnees de puissance des $nb_days derniers jours et les met en forme pour les afficher sur le graphique
  //
  function getInstantConsumption ($nb_days) {
    global $sqlite;
    $now  = time();
    $past = strtotime("-$nb_days day", $now);

    $db = new SQLite3($sqlite);
    $results = $db->query("SELECT * FROM puissance WHERE timestamp > $past ORDER BY timestamp ASC;");

    $data = array();

    while($row = $results->fetchArray(SQLITE3_ASSOC)){
      $year   = date("Y", $row['timestamp']);
      $month = date("n", $row['timestamp'])-1;
      $day    = date("j", $row['timestamp']);
      $hour   = date("G", $row['timestamp']);
      $minute = date("i", $row['timestamp']);
      $second = date("s", $row['timestamp']);
      if ($row['hchp'] == 'HP') {$hchp_indicator ='color: #e0440e';} else {$hchp_indicator = 'color: #375D81';}
      $data[] = "[{v:new Date($year, $month, $day, $hour, $minute, $second), f:'".date("j", $row['timestamp'])." ".date("M", $row['timestamp'])." ".date("H\hi", $row['timestamp'])."'}, 
                  {v:".$row['va'].", f:'".$row['va']." V.A'}, '".$hchp_indicator."', {v:".$row['watt'].", f:'".$row['watt']." W'}]";
    }

    return implode(', ', $data);
  }

  //
  //  recupere les donnees de consommation des $nb_days derniers jours et les met en forme pour les afficher sur le graphique
  //
  function getDailyData ($nb_days) {
    global $sqlite;
    $now  = time();
    $past = strtotime("-$nb_days day", $now);

    $db = new SQLite3($sqlite);
    $results = $db->query("SELECT timestamp, daily_hc, daily_hp FROM conso WHERE timestamp > $past ORDER BY timestamp ASC;");

    $data = array();

    while($row = $results->fetchArray(SQLITE3_ASSOC)){
      $year   = date("Y", $row['timestamp']);
      $month = date("n", $row['timestamp'])-1;
      $day    = date("j", $row['timestamp']);
      $data[] = "[new Date($year, $month, $day), {v:".$row['daily_hp'].", f:'".$row['daily_hp']." kWh'}, {v:".$row['daily_hc'].", f:'".$row['daily_hc']." kWh'}]";
    }

    return implode(', ', $data);
  }

?>
