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


$hostname = "mail.vclass.in.th";
$from = $argv[2];
$to   = $argv[4];
$myDomains  = array("localhost", "", " วีคลาส.ไทย", "vclass.in.th");
$body_end = '<br>This EMAIL is an EAI';
//Known servers that supports EAI
$eaiEnabledDomains = array("gmail.com");
$eaiEnabledMX 	   = array("aspmx.l.google.com","alt1.aspmx.l.google.com","alt2.aspmx.l.google.com","alt3.aspmx.l.google.com",
							"alt4.aspmx.l.google.com","gmail-smtp-in.l.google.com");
							
$notEAIenabledDomains  = array("yahoo.com");
							
/***************************************************************************/
							
//Read e-mail
$mailContent = '';
$sock = fopen ("php://stdin", 'r');
while (!feof($sock)) 
	$mailContent .= fread($sock, 1024);
fclose($sock);
$mailContent = "Received: by EAI Filter ".date("D, j M Y H:i:s")." \r\n". $mailContent;
#$mailContent = "Return-Path:  padilla.reyj@gmail.com\r\n". $mailContent;
#$mailContent = "X-Original-To: <padilla.reyj@gmail.com>\r\n". $mailContent;
#$mailContent = "Delivered-To: EAI Filter Check\r\n". $mailContent;

//Check EAI 
$isFromAscii  = mb_detect_encoding($from, 'ASCII', true);
$isToAscii    = mb_detect_encoding($to, 'ASCII', true);
$toDomain     = strtolower(array_pop(explode('@', $to)));
$fromDomain   = strtolower(array_pop(explode('@', $from)));
$isLocalMail  = in_array($toDomain, $myDomains);
$isEAIenabled = in_array($toDomain, $eaiEnabledDomains);
$isNotEAIenabled = in_array($toDomain, $notEAIenabledDomains);


if ($fp = fopen('/var/log/eai_log.txt', 'a+')){
fwrite($fp, $mailContent . 'FROM='. $from . 'TO='. $to . " toDomain =". $toDomain );
fclose($fp);
#exit(44);
}else{
echo 'Current script owner: ' . get_current_user();
#exit(88);
}
#$mailContent = "Date:".date("D, j M Y H:i:s +0008 (UTC)")." \r\n". $mailContent;
#$mailContent = "Delivered-for: eai@localhost \r\n". $mailContent;


//receiving mail server
$isSMTPUTF8 = true;

//server is known to be not EAI enabled
if ($isNotEAIenabled) $isSMTPUTF8 = false;


//outgoing mail AND  not known to be {EAI enabled/not enabled} AND  ASCII
if (!$isLocalMail  &&  !$isEAIenabled && !$isNotEAIenabled && $isToAscii ) {	
	if(dns_get_mx($toDomain, $mxhosts, $weights)) {

		foreach($mxhosts as $key => $host) {
			$host = strtolower($host);
			//@TODO sort by lesser weight
			//should check all MX server
			//currently just break upon the first check
			//echo "Hostname: $host (Weight: {$weights[$key]}<BR/>\n";
				
			
			if (!in_array($host, $eaiEnabledMX)) break;
			
			$stream = @fsockopen($host, '25');
			if (!$stream)   exit (1); //"Can't open SMTP stream."
			$tmp = fgets($stream, 1024);
			fputs($stream, "EHLO $hostname\r\n");
			$resp = '';

			
			while (substr($resp, 3, 1) != ' ' ){
				 $resp = fgets($stream, 256);
				 $isSMTPUTF8 = strpos($resp, 'SMTPUTF8');
			}
			
			if ( $isSMTPUTF8 ){								
				//@TODO 
				//add in DB for enabled MX server 
					 
			}else {
				//@TODO 
				//add in DB for not enabled MX server 
				 $isSMTPUTF8 = false;
			}
			
			break;			 
		}
	}else {
		exit(41); //Cannot get MX Record
	}
}



if ( !$isSMTPUTF8 ){								
 						
	//@TODO Query in DB for FROM Mapping for it's non ASCII
	
	if ($from == 'ลภาษ@วีคลาส.ไทย' ) $from = 'rey@vclass.in.th';
	else if ($from == strtolower('pensri@วีคลาส.ไทย') ) $from = 'pensri@vclass.in.th';
	else if ($from == 'ชั่วโมง@วีคลาส.ไทย' ) $from = 'jirasak@vclass.in.th';
	else $from = 'rey@vclass.in.th';
	
	$body_end = '<br>The email address is change to NON-ASCII ';
	
	//@TODO
	//Modify Headers to change FROM with the NEW FROM
	$pattern = '/From:([^}]*)<([^}]*)>/';	
	
	//WITH NAME
	$from_string  = '';
	if (preg_match($pattern, $mailContent, $matches) ) {
		//echo 'Match Found';
		if (count($matches) == 3 ){
			
			$from_string  =  str_replace($matches[2], $from, $matches[0]);
			$mailContent = str_replace($matches[0], $from_string, $mailContent);
		}
	}else {
		//echo 'Match NOT FOUND';
		//@Todo without the <>
		$pattern = '/From:([^}]*)/';
		
		exit(2); //Not implemented
	}

	
}	


//@Todo
//@DB Implementation
if ($toDomain == 'vclass.in.th' ) {
	
	$toAsciiForm = $to;
	
	if ($to == 'rey@vclass.in.th' ) $to = 'ลภาษ@วีคลาส.ไทย';
	else if ($to == strtolower('pensri@vclass.in.th') ) $to = 'pensri@วีคลาส.ไทย';
	else if ($to == 'jirasak@vclass.in.th' ) $to = 'ชั่วโมงjirasak@วีคลาส.ไทย';
	else $to = 'ลภาษ@วีคลาส.ไทย';
	
	$mailContent = str_replace($toAsciiForm, $to, $mailContent);
		
	//@TODO 
	//change to EAI	
}
if ($toDomain == 'xn--42c0eeo3bp.xn--o3cw4h' ) {
	
	$toAsciiForm = $to;
	
	if ($to == 'rey@xn--42c0eeo3bp.xn--o3cw4h' ) $to = 'ลภาษ@วีคลาส.ไทย';
	else if ($to == strtolower('pensri@xn--42c0eeo3bp.xn--o3cw4h') ) $to = 'pensri@วีคลาส.ไทย';
	else if ($to == 'jirasak@xn--42c0eeo3bp.xn--o3cw4h' ) $to = 'ชั่วโมงjirasak@วีคลาส.ไทย';
	else $to = 'ลภาษ@วีคลาส.ไทย';
	
	$mailContent = str_replace($toAsciiForm, $to, $mailContent);
		
	//@TODO 
	//change to EAI	
}

if ($toDomain == 'วีคลาส.ไทย' ) {
	
	$toAsciiForm = $to;	
	
	if ($to == 'rey@วีคลาส.ไทย' ) $to = 'ลภาษ@วีคลาส.ไทย';
	else if ($to == strtolower('pensri@วีคลาส.ไทย') ) $to = 'pensri@วีคลาส.ไทย';
	else if ($to == 'jirasak@วีคลาส.ไทย' ) $to = 'ชั่วโมง@วีคลาส.ไทย';
	else $to = 'ลภาษ@วีคลาส.ไทย';
	
	$mailContent = str_replace($toAsciiForm, $to, $mailContent);
		
	//@TODO 
	//change to EAI	
}


//Open socket @port 10025 
$stream = @fsockopen('127.0.0.1', '10025');
if (!$stream) 	exit (41);	
show_resp($stream);

//echo "EHLO localhost\r\n";

fputs($stream, "EHLO localhost\r\n");
show_resp($stream);

if (!$isSMTPUTF8) {
	$cmd = "MAIL FROM:". $from ."\r\n";
}else{
	$cmd = "MAIL FROM:". $from ." SMTPUTF8\r\n";
}

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
 

