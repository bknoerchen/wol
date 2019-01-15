<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>WOL</title>
  </head>
  <script type="text/javascript">
    var MethodEnum = {
      WOL:  1,
      PING: 2,
    };

    function XMLHttpGet(url, method, host) {
      var xmlHttp = new XMLHttpRequest();

      xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == XMLHttpRequest.DONE) {
          if (xmlHttp.status == 200) {
            switch(method) {
	    case MethodEnum.WOL:
                document.getElementById("retValDiv").innerHTML = xmlHttp.responseText;
                break;
              case MethodEnum.PING:
		document.querySelector("[ip='" + host + "']").disabled = xmlHttp.responseText == host;
                if (xmlHttp.responseText == host) {
                  document.getElementById("retValDiv").innerHTML = host + " is running";
                }
                break;
            }
          }
          else {
            document.getElementById("retValDiv").innerHTML = "Error (ReturnCode:" + xmlHttp.status + ")";
          }
        }
      };
      xmlHttp.open("GET", url, true);
      xmlHttp.send();
    }

    function wakeOnLan(macAddress, port) {
      XMLHttpGet("wol.php?" + "macaddress=" + macAddress + "&port=" + port, MethodEnum.WOL)
    }

    function pingHost(host) {
      XMLHttpGet("ping.php?" + "host=" + host, MethodEnum.PING, host)
    }

    setInterval(function() {
      <?php
        if (file_exists('clients.xml')) {
          $clients = simplexml_load_file("clients.xml") or die("Error: Cannot create object");
          foreach ($clients as $client) {
            echo "pingHost(\"$client->ip\");";
          }
        }
      ?>
    }, 10000);

  </script>
  <body style="padding: 5px;">
    <div style="text-align: center; font-size: 35px; padding: 5px">WOL</div>
    <?php
      if (file_exists('clients.xml')) {
        $clients = simplexml_load_file("clients.xml") or die("Error: Cannot create object");
        foreach ($clients as $client) {
          // echo "<button type='button' class='btn btn-primary btn-block' onclick='pingHost(\"$client->ip\")'>ping $client->name</button>";
          echo "<button type='button' ip='$client->ip' class='btn btn-primary btn-block' onclick='wakeOnLan(\"$client->macaddress\",$client->port)'>$client->name</button>";
        }
      }
      else {
        exit("Could not open clients.xml");
      }
    ?>
    <div id="retValDiv">
  </body>
</html>
