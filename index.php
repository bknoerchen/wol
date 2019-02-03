<!DOCTYPE html>
<html lang="de">
  <style>
    .flex-container {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .flex-container > div {
      margin: 5px;
    }

    .led-green {
      width: 16px;
      height: 16px;
      background-color: #ABFF00;
      border-radius: 50%;
      box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 4px 1px, inset #304701 0 -1px 5px, #89FF00 0 1px 8px;
    }

    .led-gray {
      width: 16px;
      height: 16px;
      background-color: #A9A9A9;
      border-radius: 50%;
      box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 4px 1px, inset #676767 0 -1px 5px, #D7D7D7 0 1px 8px;
    }
  </style>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>WOL</title>
  </head>
  <?php
    if (file_exists('clients.xml')) {
      $clients = simplexml_load_file("clients.xml") or die("Error: Cannot create object");
    }
    else {
      exit("Could not open clients.xml");
    }

    function pingAll($clients) {
      foreach ($clients as $client) {
        echo "pingHost(\"$client->ip\");\n";
      }
    }
  ?>
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
                var running = xmlHttp.responseText == host;
                var button = document.querySelector("[data-ip='" + host + "']");
                button.disabled = running;
                button.querySelector(".led").className = (running ? "led-green led" : "led-gray led");
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
      XMLHttpGet("php-scripts/wol.php?" + "macaddress=" + macAddress + "&port=" + port, MethodEnum.WOL)
    }

    function pingHost(host) {
      XMLHttpGet("php-scripts/ping.php?" + "host=" + host, MethodEnum.PING, host)
    }

    setInterval(function() {
      <?php
        pingAll($clients);
      ?>
    }, 10000);

    <?php
      pingAll($clients);
    ?>
  </script>
  <body style="padding: 5px;">
    <div style="text-align: center; font-size: 35px; padding: 5px">BASTI-WOL</div>
    <?php
      foreach ($clients as $client) {
        // echo "<button type='button' class='btn btn-primary btn-block' onclick='pingHost(\"$client->ip\")'>ping $client->name</button>";
        echo "<button type='button' data-ip='$client->ip' class='btn btn-primary btn-block' onclick='wakeOnLan(\"$client->macaddress\",$client->port)'>
              <div class='flex-container'><div>$client->name ($client->ip)</div><div class='led-gray led'</div></div></button>\n";
      }
    ?>
    <div id="retValDiv">
  </body>
</html>
