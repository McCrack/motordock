<?php


if (isset($_COOKIE['finger'])) {
  $session = $mySQL->getRow(
  " SELECT * FROM gb_sessions
    CROSS JOIN gb_community USING(CommunityID)
    CROSS JOIN gb_staff USING(CommunityID)
    WHERE Token LIKE {str}
    LIMIT 1",
    $_COOKIE['finger']
  );
}

$key = rand();
setcookie("key", $key, time()+3600, "/");

if (empty($session)) {
  $users = $mySQL->get(
  " SELECT * FROM gb_staff
    LEFT JOIN gb_community USING(CommunityID)
    LEFT JOIN gb_sessions USING(CommunityID)"
  );
  if (isset($_COOKIE['finger'])) {
    foreach ($users as &$user) {
      $finger = md5($user['Login'].$user['Passwd'].$_COOKIE['key']);
      if ($_COOKIE['finger'] == $finger) {
        $session = $user;
        $finfer = md5($user['Login'].$user['Passwd'].$key);
        //print_r($session);
        $mySQL->inquiry(
        " INSERT INTO gb_sessions
          SET CommunityID={int}, Token={str}
          ON DUPLICATE KEY UPDATE Token={str}",
          $session['CommunityID'], $finfer, $finfer
        );
        break;
      }
    }
  }
}

if (!empty($session)) {
  define("USER_ID",     $session['UserID']);
  define("USER_NAME",		$session['Name']);
  define("USER_LOGIN",	$session['Login']);
  define("USER_GROUP",	$session['Group']);
  define("USER_EMAIL",	$session['Email']);
  define("USER_PHONE",	$session['Phone']);
  define("COMMUNITY_ID",$session['CommunityID']);

  $uConfig = new Config($session['settings']);
}
 /*
 CREATE TABLE IF NOT EXISTS cb_staff(
 	UserID INT UNSIGNED NOT NULL AUTO_INCREMENT,
   Token CHAR(32) NOT NULL,
 	Login VARCHAR(24) NOT NULL,
 	Passwd CHAR(32) NOT NULL,
 	Group ENUM('admin', 'developer', 'editor', 'manager', 'author', 'designer', 'partner', 'performer') DEFAULT 'manager',
 	PRIMARY KEY(UserID),
 	UNIQUE(login)
 )ENGINE=InnoDB CHARACTER SET utf8;

 key:{
   "ip",
   "key",
   "time"
 }

 <?php

 if (isset($_COOKIE['token'])) {
   $user = $mySQL->getRow(
       "SELECT * FROM sb_staff WHERE Token LIKE {str} LIMIT 1",
       $_COOKIE['token']
   );
 }
 if (empty($user)) {
     if(isset($_POST['key'])) {
       // Login
       $user = $mySQL->getRow(
         "SELECT * FROM sb_staff WHERE MD5(CONCATE(Login, Passwd)) = {str} LIMIT 1",
         $_POST['key']
       );
       if (empty($user)) {
         // Permission Denied
       } else {
         $token = md5();
         setcookie("token", $token, time()+3600, "/");
         $mySQL->inquity(
           "UPDATE cd_staff SET Token={str} WHERE UserID={int} LIMIT 1",
           $token,
           $user['UserID']
         );
         // Open session
       }
     }
 } else {
   // Open session
 }
 */
