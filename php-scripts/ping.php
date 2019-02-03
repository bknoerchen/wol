<?php

class PingToHost
{
  public static function ping($host) {
    $pingresult = exec("/bin/ping -c 1 $host", $outcome, $status);
    if ($status == 0) {
      return true;
    }
    else {
      return false;
    }
  }
}

$host = $_GET["host"];
if (isset($host)) {
  $retVal = PingToHost::ping($host);
  if ($retVal) {
    echo "$host";
  }
  else {
    echo "error";
  }
}
?>
