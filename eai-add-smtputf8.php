#!/usr/bin/php -q
<?php
/** 
 * PHP Version 5
 * @Rey Padilla <padilla.reyj@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
 
$from = $argv[2];
$to   = $argv[4];
 											
//Read mail
$mailContent = '';
$sock = fopen ("php://stdin", 'r');
while (!feof($sock)) 
	$mailContent .= fread($sock, 1024);
fclose($sock);
 
//Open socket @port 10025 
$stream = @fsockopen('127.0.0.1', '10025');
if (!$stream) exit (41);	
show_resp($stream);

 fputs($stream, "EHLO localhost\r\n");
show_resp($stream);

$cmd = "MAIL FROM:". $from ." SMTPUTF8\r\n";

fputs($stream, $cmd);
show_resp($stream);

$cmd = "RCPT TO:". $to ."\r\n";
fputs($stream, $cmd);
show_resp($stream);

$cmd = "data\r\n";
fputs($stream, $cmd);
show_resp($stream);

//send data and [CR].[CR]
fputs($stream, $mailContent. "\r\n.\r\n");
show_resp($stream);

$cmd = "quit\r\n";
//echo $cmd;
fputs($stream, $cmd);
show_resp($stream);

/***************************************************************************/ 
function show_resp($stream){
	$resp = ''; 
	while (substr($resp, 3, 1) != ' ' ){
		 $resp = fgets($stream, 256);
		 echo $resp;
	}	
}
 
