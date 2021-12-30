# TransIP-DynDNS-Helper
A TransIP Dynamic DNS helper

> This project has been replaced by [TransIP-DynDNS-Helper-REST](https://github.com/Qonnect-IT/TransIP-DynDNS-Helper-REST), in order to comply with TransIP's REST requirements (effective from early 2022)

This project can be used to automaticly update DNS Subdomain records via the TransIP API by utilizing a Cronjob on a remote device to "call in" the external IP address for that device.
Optional, Pushover can be used to send a message if there was a change in IP Address.

## Using classes pulled from the following contributors;

https://github.com/cschalenborgh/php-pushover

## Before use ##
Please copy the config.inc.php.template to config.inc.php and fill in the variables.
Also copy the classes/transip/ApiSettings.php.empty file to classes/transip/ApiSettings.php, and fill the file with the correct login and prvateKey (Which can be retrieved via TransIP.nl in your account)
