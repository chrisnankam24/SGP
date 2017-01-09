<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
$config['useragent'] = 'CodeIgniter';
$config['protocol'] = 'smtp';
// $config['mailpath'] = '/usr/sbin/sendmail';
//$config['smtp_host'] = 'ssl://smtp.googlemail.com';
$config['smtp_host'] = '172.21.55.12';
//$config['smtp_user'] = 'chp.testbed@gmail.com';
$config['smtp_user'] = 'DTI_SIT@orange.com';
//$config['smtp_pass'] = 'chp_testbed_2016';
$config['smtp_pass'] = '';
//$config['smtp_port'] = 465;
$config['smtp_port'] = 25;
$config['smtp_timeout'] = 5;
$config['wordwrap'] = TRUE;
$config['wrapchars'] = 76;
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['validate'] = FALSE;
$config['priority'] = 3;
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";
$config['bcc_batch_mode'] = FALSE;
$config['bcc_batch_size'] = 200;