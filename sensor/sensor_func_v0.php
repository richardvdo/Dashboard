<?php


  //mysqla mettre a la place
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
  //  enregistre la puissance instantanée en V.A et en W et les valeurs HP et BASE en Wh
  //
  function collectTeleinfoData () {
          $link = mysqli_connect('192.168.1.56', 'pi', '66446644', 'teleinfo_v2')
    or die('Impossible de se connecter : ' . mysqli_error());
    
      
      $query = 'CREATE TABLE IF NOT EXISTS puissance (timestamp INTEGER, base INTEGER, va REAL, iinst REAL, watt REAL);';
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));

    $trame = getTeleinfo (); // recupere une trame teleinfo

    $data = array();
    $data['timestamp'] = time();
    $data['base']  = preg_replace('`^[0]*`','',$trame['BASE']); // conso total en Wh , on supprime les 0 en debut de chaine
    $data['va']        = preg_replace('`^[0]*`','',$trame['PAPP']); // puissance en V.A, on supprime les 0 en debut de chaine
    $data['iinst']     = preg_replace('`^[0]*`','',$trame['IINST']); // intensité instantanée en A, on supprime les 0 en debut de chaine
    $data['watt']      = $data['iinst']*220; // intensite en A X 220 V

    // stock les donnees

            $query = "INSERT INTO puissance (timestamp, base, va, iinst, watt) VALUES (".$data['timestamp'].", '".$data['base']."', ".$data['va'].", ".$data['iinst'].", ".$data['watt'].");";
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));

    mysqli_close($link);
    return 1;
  }

  //
  //  enregistre la consommation de la veille en Wh
  //
  function computeLastDayConso () {
   

    $today = strtotime('today 00:00:00');
    $yesterday = strtotime("-1 day 00:00:00");

          $link = mysqli_connect('192.168.1.56', 'pi', '66446644', 'teleinfo_v2')
    or die('Impossible de se connecter : ' . mysqli_error());
    
      
   // $query = "SELECT MAX(timestamp) AS timestamp, MAX(base) AS total_base, ((MAX(base) - MIN(base)) / 1000) AS daily_base FROM puissance 
    //                       WHERE timestamp >= $yesterday AND timestamp < $today AND base!='' GROUP BY DATE_FORMAT(timestamp, '%d-%m-%Y');";
   $query = "SELECT MAX(timestamp) AS timestamp, MAX(base) AS total_base, ((MAX(base) - MIN(base)) / 1000) AS daily_base FROM puissance 
                           WHERE timestamp >= $yesterday AND timestamp < $today AND base!='';";
  
          $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));

 
    $previousDay = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $query ='CREATE TABLE IF NOT EXISTS conso (timestamp INTEGER, total_base INTEGER, daily_base REAL);'; // cree la table conso si elle n'existe pas
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));


      $query ="INSERT INTO conso (timestamp, total_base, daily_base) VALUES 
                (".$previousDay['timestamp'].", ".$previousDay['total_base'].", ".$previousDay['daily_base'].");";
        $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));
        mysqli_close($link);
  }

  //
  //  recupere les donnees de puissance des $nb_days derniers jours et les met en forme pour les afficher sur le graphique
  //
  function getInstantConsumption ($nb_days) {

    $now  = time();
    $past = strtotime("-$nb_days day", $now);

    $link = mysqli_connect('192.168.1.56', 'pi', '66446644', 'teleinfo_v2')
    or die('Impossible de se connecter : ' . mysqli_error());
    
      
    $query = "SELECT * FROM puissance WHERE timestamp > $past ORDER BY timestamp ASC;";
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));
    $data = array();

    
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
      $year   = date("Y", $row['timestamp']);
      $month = date("n", $row['timestamp'])-1;
      $day    = date("j", $row['timestamp']);
      $hour   = date("G", $row['timestamp']);
      $minute = date("i", $row['timestamp']);
      $second = date("s", $row['timestamp']);
      $basehp_indicator = 'color: #375D81';
      $data[] = "[{v:new Date($year, $month, $day, $hour, $minute, $second), f:'".date("j", $row['timestamp'])." ".date("M", $row['timestamp'])." ".date("H\hi", $row['timestamp'])."'}, 
                  {v:".$row['va'].", f:'".$row['va']." V.A'}, '".$basehp_indicator."', {v:".$row['watt'].", f:'".$row['watt']." W'}]";
    }
  
    return implode(', ', $data);
   mysqli_close($link);
  }

    function getInstantConsumptionLight ($nb_days) {

    $now  = time();
    $past = strtotime("-$nb_days day", $now);

          $link = mysqli_connect('192.168.1.56', 'pi', '66446644', 'teleinfo_v2')
    or die('Impossible de se connecter : ' . mysqli_error());
    
      
    $query="SELECT * FROM puissance WHERE timestamp > $past ORDER BY timestamp ASC;";
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));
  

    $data = array();

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
      $year   = date("Y", $row['timestamp']);
      $month = date("n", $row['timestamp'])-1;
      $day    = date("j", $row['timestamp']);
      $hour   = date("G", $row['timestamp']);
      $minute = date("i", $row['timestamp']);
      $second = date("s", $row['timestamp']);
      $basehp_indicator = 'color: #375D81';
      $data[] = "[{v:new Date($year, $month, $day, $hour, $minute, $second), f:'".date("j", $row['timestamp'])." ".date("M", $row['timestamp'])." ".date("H\hi", $row['timestamp'])."'}, 
                  '".$basehp_indicator."', {v:".$row['watt'].", f:'".$row['watt']." W'}]";
    }

    return implode(', ', $data);
    mysqli_close($link);
  }
  
  
  //
  //  recupere les donnees de consommation des $nb_days derniers jours et les met en forme pour les afficher sur le graphique
  //
  function getDailyData ($nb_days) {
    
    $now  = time();
    $past = strtotime("-$nb_days day", $now);

              $link = mysqli_connect('192.168.1.56', 'pi', '66446644', 'teleinfo_v2')
    or die('Impossible de se connecter : ' . mysqli_error());
    
      
    $query="SELECT timestamp, daily_base FROM conso WHERE timestamp > $past ORDER BY timestamp ASC;";
    $result = mysqli_query($link,$query) or die('Échec de la requête : ' . mysqli_error($link));
  

    $data = array();

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
      $year   = date("Y", $row['timestamp']);
      $month = date("n", $row['timestamp'])-1;
      $day    = date("j", $row['timestamp']);
      $data[] = "[new Date($year, $month, $day), {v:".$row['daily_base'].", f:'".$row['daily_base']." kWh'}]";
    }

    return implode(', ', $data);
    mysqli_close($link);
  }

?>
