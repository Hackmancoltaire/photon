<?php

// - PHOTON Session Handler. PHOTON uses a combination of PHP session management and calls to your database
// to authenticate users and to ensure that session cookie files are properly expired. This is an example of
// how to use a User object to determine proper authentication.

function authenticateUser($account = NULL,$password = NULL) {
	global $logout,$siteActive,$targetDatabase;
	
	session_start();
	$sessionID = session_id();
	
	if ($sessionID != "") {
		if ($logout != "") { destroySession(); }
		else {
			if ($GLOBALS["allowAuthentication"]) {
				$loginSuccess = FALSE;
				$user = new User;
				
				if (!$account || !$password) {
					$user->initWithID($_SESSION['currentUserID']);
					
					if ($user->valid) {
						if ($user->password == $_SESSION['currentUserPassword']) { $loginSuccess = TRUE; }
					}
				}
				else {
					$user->initWithEmail($account);
					
					if ($user->valid) {
						if ($user->password == mysqlPassword($password)) { $loginSuccess = TRUE; }
					}
				}

				if ($loginSuccess) {
					if ($account && $password) {
						$expire = 3600 * 3; // Hour * 3
						$_SESSION['currentUserEmail'] = $user->email;
						$_SESSION['currentUserPassword'] = $user->password;
						$_SESSION['currentFullName'] = $user->userData[givenName] . " " . $user->userData[sn];
						$_SESSION['currentUserPhone'] = $user->userData[telephoneNumber];
						$_SESSION['currentUserID'] = $user->id;
						$_SESSION['link'] = NULL;
						$_SESSION['connectionCount'] = 0;
						setcookie(session_name(), session_id(), time()+$expire, "/");
					}
					else {
						$expire = 3600 * 3; // Hour * 3
						setcookie(session_name(), session_id(), time()+$expire, "/");
					}
				}
				//else { destroySession("signin.php","loginError"); }
			}
			else { destroySession("signin.php","loginError"); }
		}
	}
}

function destroySession($whereToError = "index.php",$error = NULL) {
	session_unset();
	session_destroy();
	setcookie("PHPSESSID","",0,"/");
	header("Location: http://".  $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $whereToError . (($error != NULL) ? "?error=$error":""));
}

?>