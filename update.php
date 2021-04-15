<?php

include("config.inc.php");

require_once("includes/classes/transip/DomainService.php");
require_once("includes/classes/pushover/Pushover.php");

$changed = 0;

if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if($_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER["REMOTE_ADDR"]) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
                $ip = $_SERVER["REMOTE_ADDR"];
        }
} else {
	$ip = $_SERVER["REMOTE_ADDR"];
}	


if(isset($_GET['token'])) {
	foreach(DNS_ARRAY as $token => $subdomain) {
		if($_GET['token'] === $token) {
			// Retrieve the current entries
			$dnsEntries = array();

			$dnsEntries = Transip_DomainService::getInfo(DNS_DOMAIN)->dnsEntries;
			// Filter the "dynamic" record

			foreach ( $dnsEntries as $result ) {
				foreach($result as $key => $value ) {
					if ( $key  == "name" ) {
						if ( $value == $subdomain) {
							if($result->content == $ip) {
								if(DEBUG == true) { echo "Record is unchanged"; } 
								syslog(LOG_INFO, ucfirst(DNS_DOMAIN) . " DynDNS - " . $subdomain . " Record Unchanged at ". $ip);
							} else {
								unset($result->content);
								$result->content = $ip;
								$changed = 1;
								if(DEBUG == true) { echo "Record will be changed"; }
								syslog(LOG_INFO, ucfirst(DNS_DOMAIN) . " DynDNS - " . $subdomain . " Record changed to: " . $ip);
							}
						}
					}
				}
			}

			if($changed == 1) {
				try {
					// Save the dns entries in the transip system
	
					Transip_DomainService::setDnsEntries(DNS_DOMAIN, $dnsEntries);
					syslog(LOG_INFO, "DNS: " . $subdomain . "." . DNS_DOMAIN . "  Address set to : " . $ip);

          if(PUSHOVER_ENABLED) {  
					  $push = new Pushover();
        		  $push->setToken(PUSHOVER_TOKEN);
	            $push->setUser(PUSHOVER_DESTINATION);

					  $push->setTitle($subdomain . "." . DNS_DOMAIN . " IP changed");
					  $push->setPriority(2);
					  $push->setRetry(500);
					  $push->setExpire(3600);
					  $push->setMessage('The new ip address is : ' . $ip . "\nDNS Records are updated");

            $go = $push->send();
          }
				}
	
				catch(SoapFault $f) {
					// It is possible that an error occurs when connecting to the TransIP Soap API,
					// those errors will be thrown as a SoapFault exception.
		
					if(DEBUG == true) { echo 'An error occurred: ' . $f->getMessage(), PHP_EOL; }
				}
			}
		}
	}
} else {
	$external_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	echo '
<html>
	<head>
		<title>' . ucfirst(DNS_DOMAIN) . ' Dynamic DNS Helper</title>
	</head>
	<body>
		<H1>
			This page should not be called without proper client side configuration.	
		</H1>
		<br />
		Please configure your cronscript as follows;
		<pre>
crontab -e or vim /etc/cron.d/dyndnsupdater

*/5 * * * * curl -4 '. $external_url . '?token=TOKEN
		</pre>
		An token can be requested via ' . htmlSpecialChars(DNS_CONTACT) . '
		<br />
		<br />
		This cronscript will update your Dynamic DNS record every 5 minutes. It is advised to leave this configured at 5 minute interval to avoid stressing the DNS API.
	</body>
</html>';
}
?>
