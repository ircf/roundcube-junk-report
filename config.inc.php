<?php

/*
*
*	junk_report configuration file
*
*/

//Path to the domain file
$config['dir'] = "/var/vmail";

//Default value for the frequency of sending the junk report
$config['default_frequency'] = "never";

//Default value for the number of junk mails in the junk report
$config['default_maxlength'] = 20;

//Day of the weekly report
//Specified in letter
$config['day_of_weekly_report'] = "Sunday";

//Day of the monthly report
//Specified with two-digit number
$config['day_of_monthly_report'] = 01;

//Alternative text if the mail does not contain a subject
$config['no_subject'] = "Aucun objet";

//Header settings
$header[] = "From : technique@ircf.fr";
$header[] = 'Content-type: text/html; charset=UTF-8';

//Subject of the junk report
$config['subject'] = "Rapport de spam";
