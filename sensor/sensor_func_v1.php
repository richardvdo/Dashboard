<?php


  function getSensor () {

    $handle = fopen ('/sys/bus/w1/devices/28-0417c13d9dff', "r"); // ouverture du flux

    if (!$handle) die ("'/sys/bus/w1/devices/28-0417c13d9dff' not found");

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

   echo  ($trame); // on supprime les caracteres de debut et fin de trame
  }
?>
