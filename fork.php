<?php


function ping_fork() {

  $hosts_ip = array(
                    'NAS'    => array('192.168.1.199', '22'),
                    'PC-Bis'    => array('192.168.1.51', '2049')
                  );

  $pids  = array();

  $db = new SQLite3('ifstat/hosts.sqlite');
  $db->exec('CREATE TABLE IF NOT EXISTS hosts_status (host_name VARCHAR(10), host_status VARCHAR(5));');
  $db->exec("DELETE FROM hosts_status;");

  foreach($hosts_ip as $host_name => $host){
    $pids[$host_name] = pcntl_fork();
    if(!$pids[$host_name]) {
      $socket = @fsockopen($host[0], $host[1], $errno, $errstr, 3);
      if($socket && !$errno){$status = 'up';}else{$status = 'down';}
      if($db->busyTimeout(5000)){
        $db->exec("INSERT INTO hosts_status VALUES ('$host_name', '$status');");
      }
      exit();
    }
  }

  foreach($pids as $host_name => $pid){
    pcntl_waitpid($pid, $status, WUNTRACED);
  }

  $results = $db->query('select * from hosts_status;');

  $html  = '';
  $html .= '<table cellspacing="10px">';
  $c=0;
  while($host = $results->fetchArray(SQLITE3_ASSOC)){
    if($c == 0){$html .= '<tr>';}
    $html .= '<td class="ping ping_'.$host['host_status'].'">'.$host['host_name'].'</td>';
    $c++;
    if($c == 2){$c = 0; $html .= '</tr>';}
  }
  if($c != 0){$html .= '</tr>';}
  $html .= '</table>';

  $db->exec("DELETE FROM hosts_status;");
  
  return $html;
}

echo ping_fork();

?>
