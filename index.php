<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>WOL</title>
  </head>
  <script type="text/javascript">
    function wakeOnLan(macAddress, port) {
      var xmlHttp = new XMLHttpRequest();

      xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == XMLHttpRequest.DONE) {
          if (xmlHttp.status == 200) {
            document.getElementById("returnValue").innerHTML = xmlHttp.responseText;
          }
          else {
            alert("Error (ReturnCode:" + xmlHttp.status + ")");
          }
        }
      };
      xmlHttp.open("GET", "/wol.php?" + "macaddress=" + macAddress + "&port=" + port, true);
      xmlHttp.send();
    }
  </script>
  <body style="padding: 5px;">
    <div style="text-align: center; font-size: 35px; padding: 5px">WOL</div>
    <?php
      if (file_exists('clients.xml')) {
        $clients = simplexml_load_file("clients.xml") or die("Error: Cannot create object");
        foreach ($clients as $client) {
          echo "<button type='button' class='btn btn-primary btn-block' onclick='wakeOnLan(\"$client->macaddress\",$client->port)'>$client->name</button>";
        }
      }
      else {
        exit("Could not open clients.xml");
      }
    ?>
    <div id="returnValue">
  </body>
</html>
