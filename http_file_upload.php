<?php

  /**
   * This is a function for doing HTTP File Uploads with PHP
   * i.e. not for handling uploads to your script, but for your script
   * to upload files to another location, essentially like filling out a 
   * form with a file-field. 
   *
   * No additional form-fields are required. Change to your need. 
   * 
   * Author: Gunnar A. Grimnes, http://gromgull.net
   * License:
   * "I really couldn't care less what anyone does with this, but 
   * claiming that they wrote it would be just sad and in asserting 
   * my right of intellectual property I assert my right to publicly 
   * ridicule anyone who does."
   * - http://www.hackcraft.net/rssvalid.xsl

   */

function http_upload_file($url, $fieldname, $mimetype, $filename) { 

  $parts=parse_url($url);
  $host=$parts["host"];
  $port=$parts["port"]; 
  $path=$parts["path"];

  $f=fopen($filename,"r"); 
  $filecontent='';
  while(!feof($f))
    $filecontent.=fread($f,1024); 

  fclose($f);

  $data="----AaB03x\r\n".
	"Content-Disposition: form-data; name=\"$fieldname\"; filename=\"".basename($filename)."\"\r\n".
	"Content-Type: $mimetype\r\n".
	"Content-Transfer-Encoding: Binary\r\n".
	"\r\n".$filecontent.
	"\r\n----AaB03x--\r\n\r\n";


  $fid = fsockopen($host, $port, &$errno, &$errstr, 30);
  $res='';
  if ($fid) {
	$eol = "\r\n";
	$errno = 0;
	$errstr = '';
	
	fputs($fid, "POST $path HTTP/1.1$eol");
	fputs($fid, "HOST: localhost$eol");
	fputs($fid, "Connection: close$eol");

	// Use 'Content-Length' NOT 'Length' !
	fputs($fid, "Content-Type: multipart/form-data; boundary=--AaB03x$eol");
	fputs($fid, 'Content-Length: ' . strlen($data) . $eol);

	fputs($fid, $eol);
	fputs($fid, $data);
	fputs($fid, $eol);

	while (!feof($fid)) 
	  $res.=fread($fid, 1024);
	fclose($fid);
  } else { 
	throw new Exception("It's fooked. Could not upload $filename ($mimetype) to $host:$port/$path : $errno - $errstr");
  }

  $res=preg_replace("/.*?\r\n\r\n/s", "", $res);

  return $res; 
}


?>