à<?php
//load rc config file

include('/opt/roundcubemail/config/config.inc.php');

//read all users prefs

preg_match('|([a-z]+)://([^:]*)(:(.*))?@([A-Za-z0-9\.-]*)(/([0-9a-zA-Z_/\.]*))|',
     $config['db_dsnw'],$matches);
$dsnArray=array(
    $matches[1].':host='.$matches[5].';dbname='.$matches[7],
    $matches[2],
    $matches[4]
);

$dsn = $dsnArray[0];
$username = $dsnArray[1];
$password = $dsnArray[2];

try {
  $conn = new PDO($dsn, $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

$requete = $conn->query("SELECT * FROM users;");

foreach($requete->fetchAll() as $personne){
  $preferences = unserialize($personne['preferences']);
  if (isset($preferences['frequency']) && isset($preferences['maxlength'])){
    $tabPrefs[$personne['username']] = array("frequency" => $preferences['frequency'], "maxlength" => $preferences['maxlength']);
  }else{
    $tabPrefs[$personne['username']] = array("frequency" => "never", "maxlength" => 100);
  }
}

$monthDay = date('d');
$weekDay = date('l');

if ($weekDay == "Sunday")
  $weekly = true;
else
  $weekly = false;

if ($monthDay == 01)
  $monthly = true;
else
  $monthly = false;

//parse junkdirs and send report

$dir = "/var/vmail";
$listeMail = array();
$domaineDirectory = scandir($dir);
foreach ($domaineDirectory as $domaine){
  if ($domaine != '.' && $domaine != '..' && is_dir("$dir/$domaine")){
    $userDirectory = scandir("$dir/$domaine");
    foreach ($userDirectory as $user){
      if ($user != '.' && $user != '..' && is_dir("$dir/$domaine/$user")){
	$adresseMail = "$user@$domaine";
        $preferencesUser = $tabPrefs["$adresseMail"];
	$frequency = $preferencesUser["frequency"];
	if ($frequency!="never" && file_exists("$dir/$domaine/$user/Maildir/.Junk/cur")){
	if (($frequency == "weekly" && $weekly)||($frequency=="monthly" && $monthly)||($frequency=="daily")){
          $junkDirectory = scandir("$dir/$domaine/$user/Maildir/.Junk/cur");
	  array_splice($listeMail,0);
	echo "\n";
	  foreach ($junkDirectory as $junk){
            if ($junk != '.' && $junk != '..'){

	      $file = file("$dir/$domaine/$user/Maildir/.Junk/cur/$junk");

              $sender = explode(":",preg_grep("/^From:/",$file)[array_keys(preg_grep("/^From:/",$file))[0]])[1];
	      //if (empty($sender)) echo "$dir/$domaine/$user/Maildir/.Junk/cur/$junk";

              if (explode(",",explode(":",preg_grep("/^Date:/",$file)[array_keys(preg_grep("/^Date:/",$file))[0]])[1])){
                $date = substr(explode(",",explode(":",preg_grep("/^Date:/",$file)[array_keys(preg_grep("/^Date:/",$file))[0]])[1])[0],0,-3);
              }else{
	        $date = substr(explode(":",preg_grep("/^Date:/",$file)[array_keys(preg_grep("/^Date:/",$file))[0]])[1],0,12);
              }

	      if (preg_grep("/^Subject:/",$file))
	        $subject = explode(":",preg_grep("/^Subject:/",$file)[array_keys(preg_grep("/^Subject: /",$file))[0]])[1];
	      else
	        $subject = "Pas d'objet";
	      //if (empty($subject)) echo "$dir/$domaine/$user/Maildir/.Junk/cur/$junk";

	      $mail["sender"]=$sender;
              $mail["date"]=$date;
              $mail["subject"]=$subject;
              array_push($listeMail,$mail);
            }
          }
	  }
	  //if (!empty($listeMail))
	    //print_r($listeMail);
	}
      }
    }
  }
}
