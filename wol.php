<?php

class WakeOnLAN
{
  public static function wakeUp($macAddressHexadecimal, $broadcastAddress, $port)
  {
    $macAddressHexadecimal = str_replace(':', '', $macAddressHexadecimal);
    // check if $macAddress is a valid mac address
    if (!ctype_xdigit($macAddressHexadecimal)) {
      throw new \Exception('Mac address invalid, only 0-9 and a-f are allowed');
      return false;
    }
    $macAddressBinary = pack('H12', $macAddressHexadecimal);
    $magicPacket = str_repeat(chr(0xff), 6).str_repeat($macAddressBinary, 16);
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
    $retVal = socket_sendto($sock, $magicPacket, strlen($magicPacket), MSG_DONTROUTE, $broadcastAddress, $port);
    socket_close($sock);
    return $retVal > -1;
  }
}

$macAddress = $_GET["macaddress"];
$port       = $_GET["port"];
if (isset($macAddress)) {
  if (WakeOnLAN::wakeUp($macAddress, '192.168.0.255', $port)) {
    echo "successful woke up $macAddress on port $port";
  }
}
?>