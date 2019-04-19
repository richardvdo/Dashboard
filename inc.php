<?php

function htmlspecialchars_array(array $array) {
    foreach ($array as $key => $val) {
        $array[$key] = (is_array($val)) ? htmlspecialchars_array($val) : htmlspecialchars($val);
    }
    return $array;
}

/////////////////////////////////////////////////
//  Meteo
/////////////////////////////////////////////////

function meteo() {
   // $meteo = '<div id="cont_af627f0dc074db1899209cfbf1e92d8e"><script type="text/javascript" async src="https://www.tameteo.com/wid_loader/af627f0dc074db1899209cfbf1e92d8e"></script></div>';
    $meteo = '<div id="cont_40e43adcaa77cf55c9ead56d2ca31c60"><script type="text/javascript" async src="https://www.tameteo.com/wid_loader/40e43adcaa77cf55c9ead56d2ca31c60"></script></div>';
    return $meteo;
}

/////////////////////////////////////////////////
//  cal
/////////////////////////////////////////////////

function cal() {

    // $html       = '<iframe src="https://calendar.google.com/calendar/embed?showNav=0&amp;showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;showTz=0&amp;mode=AGENDA&amp;height=300&amp;wkst=2&amp;bgcolor=%23000000&amp;src=richardvdo%40gmail.com&amp;color=%232952A3&amp;src=%23contacts%40group.v.calendar.google.com&amp;color=%230D7813&amp;src=marjoriedurand85%40gmail.com&amp;color=%23B1365F&amp;ctz=Europe%2FParis" style="border-width:0" width="500" height="300" frameborder="0" scrolling="no"></iframe>';
    $nbEvtTot = 0;
    $nb_jours = 20;
    $jours = date('Ymd');
    for ($i = 1; $i < $nb_jours; ++$i) {
        $jours .= '|' . date('Ymd', mktime(0, 0, 0, date('m'), date('d') + $i, date('Y')));
    }
    //Récupération des données brutes de X calendriers
    //ou, pour un seul fichier .ics :
    //$calendrier = file_get_contents("nom_du_fichier.ics");
    $ics_cals = array(
        'basic_mvd.ics',
        'basic_mdu.ics',
        'basic_rva.ics',
        'basic_vdo.ics'
    );
    $calendrier = '';
    foreach ($ics_cals as $val) {
        $calendrier .= file_get_contents('ical/' . $val);
    }
    $regExpMatch = '/SUMMARY:(.*)/';
    $regExpDate = '/DTSTART:(.*)/';
    $n = preg_match_all($regExpMatch, $calendrier, $matchTableau, PREG_PATTERN_ORDER);
    $m = preg_match_all($regExpDate, $calendrier, $dateTableau, PREG_PATTERN_ORDER);
  //  echo("(preg match all subject) n = ".$n."<br />");	//Impression du nb de résultats pour contrôle
  //  echo("(preg match all date) m = ".$m."<br />");	//Impression du nb de résultats pour contrôle
    for ($p = 0; $p < $n; ++$p) {
        $evtCalendar[$p] = array(
            "date" => substr($dateTableau[1][$p], 0, 8),
            "subject" => substr($matchTableau[1][$p], 0, 85),
            "heure" => substr($dateTableau[1][$p], 9, 6)
        );
    }
    foreach ($evtCalendar as $k => $v) {
        $evtTrieDateKey[$k] = $v['date'];
        $evtTrieHeureKey[$k] = $v['heure'];
    }
    array_multisort($evtTrieDateKey, SORT_DESC, $evtTrieHeureKey, SORT_DESC, $evtCalendar);
    $nbEvt = 0;
    $html = '';
    $html .= '<table class="ical">';
    $date0 = '';


    //compteur evt total a afficher (boucle qui doit pouvoir s'enlever)
   // echo("(count avant fonction bugger) n = ".count($evtCalendar)."<br />");
    //count($evtCalendar)
    //y'a un bug la dedans  !!!!!!
    
      //  for ($p = 0; $p < 10; ++$p) {

     //    echo("(date : ) n = ".$evtCalendar[$p]["date"].$evtCalendar[$p-1]["subject"].'avt  crt'.$evtCalendar[$p]["subject"].'  ap'.$evtCalendar[$p+1]["subject"]."<br />");
   // }
    
    
    for ($p = 0; $p < $n; ++$p) {
        if (stristr($jours, $evtCalendar[$p]["date"]) != false) {
            $nbEvtTot = $nbEvtTot + 1;
            //limitation du nombre d'evt affiche
        }
    }
//echo("( nb total evt apres limitation) n = ".$nbEvtTot."<br />");
    for ($p = 0; $p < $n; ++$p) {
        if (stristr($jours, $evtCalendar[$p]["date"]) != false) {
            $nbEvt = $nbEvt + 1;
            //limitation du nombre d'evt affiche
            if ($nbEvt <= 7) {
                // Mise en forme des dates
                $annee = substr($evtCalendar[$p]["date"], 0, 4);
                $mois = substr($evtCalendar[$p]["date"], 4, 2);
                $jour = substr($evtCalendar[$p]["date"], 6, 2);
                $heure = '';
                if ($evtCalendar[$p]["heure"] != '') {
                    $heure = substr($evtCalendar[$p]["heure"], 0, 2) . 'h' . substr($evtCalendar[$p]["heure"], 2, 2);
                }
                $date = $jour . '/' . $mois . '/' . $annee;
                // Non répétition de la date si identique à l'événement n-1
                if ($date == $date0) {
                    $date = '';
                } else {
                    $date0 = $date;
                }
                if ($nbEvt == $nbEvtTot || $nbEvt == 7) {

                    $html .= '<tr class="ical ical_event">
			<td class="ical icalfin ical_date">' . $date . '</td>
			<td class="ical icalfin ical_heure">' . $heure . '</td>
			<td class="ical icalfin ical_detail">' . $evtCalendar[$p]["subject"] . '</td>
			</tr>';
                } else {
                    $html .= '<tr class="ical ical_event">
			<td class="ical ical_date">' . $date . '</td>
			<td class="ical ical_heure">' . $heure . '</td>
			<td class="ical ical_detail">' . $evtCalendar[$p]["subject"] . '</td>
			</tr>';
                }
            }
        }
    }

    $html .= '</table>';
   // $html = '<div><iframe src="https://calendar.google.com/calendar/b/1/embed?showTitle=0&amp;showNav=0&amp;showDate=0&amp;showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;showTz=0&amp;mode=AGENDA&amp;height=450&amp;wkst=1&amp;bgcolor=%23ff0000&amp;src=famillevdod%40gmail.com&amp;color=%231B887A&amp;src=fr.french%23holiday%40group.v.calendar.google.com&amp;color=%23125A12&amp;src=marjoriedurand85%40gmail.com&amp;color=%23BE6D00&amp;src=mathis.durand79%40gmail.com&amp;color=%230F4B38&amp;src=richardvdo%40gmail.com&amp;color=%238D6F47&amp;ctz=Europe%2FParis" style="border-width:0" width="540" height="310" frameborder="0" scrolling="no"></iframe></div>';
    return $html;
}

/////////////////////////////////////////////////
//  PING
/////////////////////////////////////////////////

function ping() {
    $hosts = array();
    $hosts_ip = array(
        'NAS' => array('192.168.1.199', '22', true),
        'Bis' => array('192.168.1.56', '135', true),
        'Spin' => array('192.168.1.51', '135', true),
        'Fixe' => array('192.168.1.50', '135', false),
	'Freebox' => array('192.168.2.254', '80', true),
        'Routeur-G' => array('192.168.1.230', '22', true),
        'Routeur-B' => array('192.168.1.200', '443', false),
        'Switch' => array('192.168.1.231', '443', true),
        'Imprimante' => array('192.168.1.53', '443', false)
    );
    foreach ($hosts_ip as $hostname => $host_data) {
        $host_ip = $host_data[0];
        $host_port = $host_data[1];
        $host_serveur = $host_data[2];
        $socket = 0;
        $socket = @fsockopen($host_ip, $host_port, $errno, $errstr, 3);
        if ($socket && !$errno) {
            $hosts[$hostname] = 'up';
        } else {
            if ($host_serveur == 1) {
                $hosts[$hostname] = 'down';
            } else {
                $hosts[$hostname] = 'sleep';
            }
        }
    }
    $html = '';
    $html .= '<table cellspacing="10px">';
    $c = 0;
    foreach ($hosts as $hostname => $host_status) {
        if ($c == 0) {
            $html .= '<tr>';
        }
        $html .= '<td class="ping ping_' . $host_status . '">' . $hostname . '</td>';
        $c++;
        if ($c == 1) {
            $c = 0;
            $html .= '</tr>';
        }
    }
    if ($c != 0) {
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

/////////////////////////////////////////////////
//  gmail
/////////////////////////////////////////////////

function email() {
    /* connect to gmail */
    $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
    $username = 'famillevdod@gmail.com';
    $password = 'ma1ri2ma3';
    /* try to connect */
    $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
    /* grab emails */
    $emails = imap_search($inbox, 'ALL');
    /* if emails are returned, cycle through each... */
    if ($emails) {

        /* begin output var */
        $output = '';
        $output .= '<table class="email">';
        /* put the newest emails on top */
        rsort($emails);
        $p = 0;
        $jour0 = '';
        /* for every email... */
        foreach ($emails as $email_number) {
            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox, $email_number, 0);
            /* output the email header information */
            if ($p <= 1 && $overview[0]->seen == 'unread') {
                $jour = substr($overview[0]->date, 5, 6);
                $heure = substr($overview[0]->date, 17, 5);

                if ($jour == $jour0) {
                    $jour = '';
                } else {
                    $jour0 = $jour;
                }
                // Decodage du sujet
                $elements = imap_mime_header_decode($overview[0]->subject);
                for ($i = 0; $i < count($elements); $i++) {
                    if ($p == 0) {
                        $output .= '<tr class="email email_list_top">';
                        $output .= '<td class="email email_jour_top">' . $jour . '</td>';
                        $output .= '<td class="email email_heure_top">' . $heure . '</td>';
                        $output .= '<td class="email email_top">' . $elements[$i]->text . '</td> ';
                        $output .= '</tr>';
                    } else {
                        $output .= '<tr class="email email_list">';
                        $output .= '<td class="email email_jour_bottom">' . $jour . '</td>';
                        $output .= '<td class="email email_heure_bottom">' . $heure . '</td>';
                        $output .= '<td class="email email_bottom">' . $elements[$i]->text . '</td> ';
                        $output .= '</tr>';
                    }
                    $p = $p + 1;
                }
            }
        }
        $output .= '</table>';
    }
    /* close the connection */
    imap_close($inbox);
    return $output;
}

/////////////////////////////////////////////////
//  VPN PPTPD
/////////////////////////////////////////////////

function vpn() {

    /*     $datas = vpn_parseData ("/home/pi/DashScreen/PiHomeDashScreenvpn/vpn_oberon.log");

      $html  = '';

      if(sizeof($datas) > 0){
      $html .= '<table cellspacing="0px">';
      foreach($datas as $data){
      $html .= '<tr>';
      $html .= '<td valign="middle"><img class="vpn" src="pict/vpn.png"></td><td class="vpn">'.$data[0].'</td>';
      $html .= '</tr>';
      }
      $html .= '</table>';
      } */
    $html = 'bloc vpn';
    return $html;
}

function vpn_parseData($stat_file) {
    $datas = array();
    if (filemtime($stat_file) < time() - 10) {
        return $datas;
    }
    $stats = fopen($stat_file, 'r');
    while (($line = fgets($stats)) !== false) {
        $explode_line = str_word_count($line, 1, "0123456789.");
        $datas[] = $explode_line;
    }
    fclose($stats);
    return $datas;
}

/////////////////////////////////////////////////
//  IFSTAT
/////////////////////////////////////////////////


function reduceToJson($precDatas, $Datas) {

//{\"date\":\"28-09-2017\",\"time\":\"11:10:46\",\"dl\":\"12.48\",\"up\":\"1.17\"}
    $line = "{\"date\":\"" . $Datas[1] . "\",\"time\":\"" . $Datas[3] . ":" . $Datas[4] . ":" . $Datas[5] . "\",\"dl\":\"" . $Datas[7] . "\",\"up\":\"" . $Datas[9] . "\"}";
    return $line;
}

function readStatDatas($up_down) {
    // $stat_file = "/home/pi/DashScreen/PiHomeDashScreen/speedtest/stat_debit";
    // $datas = array();
    // // if(filemtime($stat_file) < time()-10){return $datas;}
    // $stats = fopen($stat_file, 'r');
    // while (($line = fgets($stats)) !== false) {
    // $explode_line = str_word_count($line, 1, "0123456789.");
    // $datas[]  = $explode_line;
    // }
    // fclose($stats);	
    // /////////////////groupe permettant de mettre l'array de array dans un array simple////////////////////
    // // for ($i=0; $i<count($datas); $i++){
    // // if($up_down="up"){
    // // $line = "{\"date\":\"".$datas[$i][1]."\",\"time\":\"".$datas[$i][3].":".$datas[$i][4].":".$datas[$i][5]."\",\"dl\":\"".$datas[$i][7]."\",\"up\":\"".$datas[$i][9]."\"}";
    // // // $line = "[Date.UTC(".substr($datas[$i][1],6,4).",".substr($datas[$i][1],3,2).",".substr($datas[$i][1],0,2).",".$datas[$i][3].",".$datas[$i][4].",".$datas[$i][5]."),".$datas[$i][7]."]";
    // // // echo($line);
    // // $datas[$i] = $line;
    // // }
    // // else{
    // // $line = "[Date.UTC(".substr($datas[$i][1],6,4).",".substr($datas[$i][1],2,2).",".substr($datas[$i][1],0,2).",".$datas[$i][3].",".$datas[$i][4].",".$datas[$i][5]."),".$datas[$i][9]."]";
    // // $datas[$i] = $line;
    // // }
    // //}
    // ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // /////////////////groupe permettant de ne garder que les données utile dns l'array de array /////////////
    // for ($i=0; $i<count($datas); $i++){
    // if($up_down="up"){
    // //$line = "{\"date\":\"".$datas[$i][1]."\",\"time\":\"".$datas[$i][3].":".$datas[$i][4].":".$datas[$i][5]."\",\"dl\":\"".$datas[$i][7]."\",\"up\":\"".$datas[$i][9]."\"}";
    // //$line0 = "Date.UTC(".substr($datas[$i][1],6,4).",".substr($datas[$i][1],3,2).",".substr($datas[$i][1],0,2).",".$datas[$i][3].",".$datas[$i][4].",".$datas[$i][5].")";
    // $datetoto = "31-12-2015 00:00:00";
    // $datetimeUTC = (strtotime($datetoto) * 1000);
    // $line0 = $datetimeUTC;
    // $line1 = $datas[$i][7];
    // // echo($line);
    // unset($datas[$i]);
    // $datas[$i][0] = $line0;
    // $datas[$i][1] = $line1;
    // }
    // else{
    // $line = "[Date.UTC(".substr($datas[$i][1],6,4).",".substr($datas[$i][1],2,2).",".substr($datas[$i][1],0,2).",".$datas[$i][3].",".$datas[$i][4].",".$datas[$i][5]."),".$datas[$i][9]."]";
    // $datas[$i] = $line;
    // }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    // $nbRecord = 50;
    // $datas = array_slice($datas,count($datas) - $nbRecord);
//echo($datas[0]);
// echo(count($datas));
    // return $datas;
}

function puissance() {

    return true;
}

?>
