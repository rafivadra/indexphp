<?php

ini_set('memory_limit', '-1');
set_time_limit(0);
date_default_timezone_set('Asia/Jakarta');

require __DIR__.'/vendor/autoload.php';
$ig = new \InstagramAPI\Instagram();

output_clean("");
output_clean("Bot Auto Komen New Post Target Instagram");
output_clean("by RafiVadra");
output_clean("");

run($ig);

/**
 * Let's start the show
 */
function run($ig) {
	global $username;

    try {

    	/*
        output('Please provide login data of your Instagram Account.');

        $login = getVarFromUser("Login");
        if (empty($login)) {
            do { 
                $login = getVarFromUser("Login"); 
            } while (empty($login));
        }

        $password = getVarFromUser("Password");
        if (empty($password)) {
            do { 
                $password = getVarFromUser("Password");
            } while (empty($password));
        }
        */

        $getdata = file_get_contents("http://filmkita.org/gakpenting/getuser.php");
		$dataUsername = explode("#", $getdata);

		for ($i=0; $i < count($dataUsername)-1; $i++) { 
		    $a = $i+1;
		    output($a.". ".$dataUsername[$i]);
		}
		output_clean("");
		output("Pilih Akun yang mau dipakai:");

		$inputUsername = getVarFromUser("Nomer");
		$hasilUsername = $inputUsername-1;

		if (empty($inputUsername)) {
		    do {
		        output("Pilih nomer akun yang mau dipakai");
		        $inputUsername = getVarFromUser("Nomer");
		        $hasilUsername = $inputUsername-1;
		    } while (empty($inputUsername));
		}

		if (!isset($dataUsername[$hasilUsername])) {
		    $dataUsername[$hasilUsername] = null;
		    output("Tidak ada pilihan ini! Ulangi dari awal");
		    run($ig);
		} elseif ($dataUsername[$hasilUsername] == "") {
		    output("Tidak ada pilihan ini! Ulangi dari awal");
		    run($ig);
		} elseif (is_string($hasilUsername)) {
		    output("Tidak ada pilihan ini! Ulangi dari awal");
		    run($ig);
		} 
		else {
		    $username = $dataUsername[$hasilUsername];
		    $login = $dataUsername[$hasilUsername];
		}

		$getpass = file_get_contents("http://filmkita.org/gakpenting/getpass.php?username=".$username);

		$password = $getpass;

        $first_loop = true;
        do {

            $proxy = 3;

            if (empty($proxy)) {
                do { 
                    $proxy = getVarFromUser("Proxy");
                } while (empty($proxy));
            }

            if ($proxy == '3') {
                // Skip proxy setup
                break;
            }
        } while (!isValidProxy($proxy));

        if ($proxy == "3") {
           // Skip proxy setup
        } else {
            output("Proxy - [OK]");
            $ig->setProxy($proxy);
        }

        // $speed $delay

        $is_connected = false;
        $is_connected_count = 0;
        $fail_message = "There is a problem with your Ethernet connection or Instagram is down at the moment. We couldn't establish connection with Instagram 10 times. Please try again later.";

        do {
            if ($is_connected_count == 10) {
                if ($e->getResponse()) {
                    output($e->getMessage());
                }
                throw new Exception($fail_message);
            }

            try {
                if ($is_connected_count == 0) {
                    output("Emulation of an Instagram app initiated...");
                }
                $login_resp = $ig->login($login, $password);
    
                if ($login_resp !== null && $login_resp->isTwoFactorRequired()) {
                    // Default verification method is phone
                    $twofa_method = '1';
    
                    // Detect is Authentification app verification is available 
                    $is_totp = json_decode(json_encode($login_resp), true);
                    if ($is_totp['two_factor_info']['totp_two_factor_on'] == '1'){
                        output("Two-factor authentication required, please enter the code from you Authentication app");
                        $twofa_id = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();
                        $twofa_method = '3';
                    } else {
                        output("Two-factor authentication required, please enter the code sent to your number ending in %s", 
                            $login_resp->getTwoFactorInfo()->getObfuscatedPhoneNumber());
                        $twofa_id = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();
                    }
    
                    $twofa_code = getVarFromUser("Two-factor code");
    
                    if (empty($twofa_code)) {
                        do { 
                            $twofa_code = getVarFromUser("Two-factor code");
                        } while (empty($twofa_code));
                    }
    
                    $is_connected = false;
                    $is_connected_count = 0;
                    do {
                        if ($is_connected_count == 10) {
                            if ($e->getResponse()) {
                                output($e->getMessage());
                            }
                            throw new Exception($fail_message);
                        }

                        if ($is_connected_count == 0) {
                            output("Two-factor authentication in progress...");
                        }

                        try {
                            $twofa_resp = $ig->finishTwoFactorLogin($login, $password, $twofa_id, $twofa_code, $twofa_method);
                            $is_connected = true;
                            testkomen();
                        } catch (\InstagramAPI\Exception\NetworkException $e) {
                            sleep(7);
                        } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                            sleep(7);
                        } catch (\InstagramAPI\Exception\InvalidSmsCodeException $e) {
                            $is_code_correct = false;
                            $is_connected= true;
                            do {
                                output("Code is incorrect. Please check the syntax and try again.");
                                $twofa_code = getVarFromUser("Two-factor code");
            
                                if (empty($twofa_code)) {
                                    do { 
                                        $twofa_code = getVarFromUser("Security code");
                                    } while (empty($twofa_code));
                                }
            
                                $is_connected = false;
                                $is_connected_count = 0;
                                do {
                                    try {
                                        if ($is_connected_count == 10) {
                                            if ($e->getResponse()) {
                                                output($e->getMessage());
                                            }
                                            throw new Exception($fail_message);
                                        }

                                        if ($is_connected_count == 0) {
                                            output("Verification in progress...");
                                        }
                                        $twofa_resp = $ig->finishTwoFactorLogin($login, $password, $twofa_id, $twofa_code, $twofa_method);
                                        $is_code_correct = true;
                                        $is_connected = true;
                                        testkomen();
                                    } catch (\InstagramAPI\Exception\NetworkException $e) { 
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\InvalidSmsCodeException $e) {
                                        $is_code_correct = false;
                                        $is_connected = true;
                                    } catch (\Exception $e) {
                                        throw $e;
                                    }
                                    $is_connected_count += 1;
                                } while (!$is_connected);
                            } while (!$is_code_correct);
                        } catch (\Exception $e) {
                            throw $e;
                        }

                        $is_connected_count += 1;
                    } while (!$is_connected);
                }

                $is_connected = true;
            } catch (\InstagramAPI\Exception\NetworkException $e) {
                sleep(7);
            } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                sleep(7);
            } catch (\InstagramAPI\Exception\CheckpointRequiredException $e) {
                throw new Exception("Please go to Instagram website or mobile app and pass checkpoint!");
            } catch (\InstagramAPI\Exception\ChallengeRequiredException $e) {

                if (!($ig instanceof InstagramAPI\Instagram)) {
                    throw new Exception("Oops! Something went wrong. Please try again later! (invalid_instagram_client)");
                }
        
                if (!($e instanceof InstagramAPI\Exception\ChallengeRequiredException)) {
                    throw new Exception("Oops! Something went wrong. Please try again later! (unexpected_exception)");
                }

                if (!$e->hasResponse() || !$e->getResponse()->isChallenge()) {
                    throw new Exception("Oops! Something went wrong. Please try again later! (unexpected_exception_response)");
                }
        
                $challenge = $e->getResponse()->getChallenge();

                if (is_array($challenge)) {
                    $api_path = $challenge["api_path"];
                } else {
                    $api_path = $challenge->getApiPath();
                }

                output("Instagram want to send you a security code to verify your identity.");
                output("How do you want receive this code?");
                output("1 - [Email]");
                output("2 - [SMS]");
                output("3 - [Exit]");

                $choice = getVarFromUser("Choice");

                if (empty($choice)) {
                    do { 
                        $choice = getVarFromUser("Choice");
                    } while (empty($choice));
                }

                if ($choice == '1' || $choice == '2' || $choice == '3') {
                    // All fine
                } else {
                    $is_choice_ok = false;
                    do {
                        output("Choice is incorrect. Type 1, 2 or 3.");
                        $choice = getVarFromUser("Choice");

                        if (empty($choice)) {
                            do { 
                                $choice = getVarFromUser("Choice");
                            } while (empty($choice));
                        }

                        if ($confirm == '1' || $confirm == '2' || $confirm == '3') { 
                            $is_choice_ok = true;
                        }
                    } while (!$is_choice_ok);
                }

                $challange_choice = 0;
                if ($choice == '3') {
                    run($ig);
                } elseif ($choice == '1') {
                    // Email
                    $challange_choice = 1;
                } else {
                    // SMS
                    $challange_choice = 0;
                }

                $is_connected = false;
                $is_connected_count = 0;
                do {
                    if ($is_connected_count == 10) {
                        if ($e->getResponse()) {
                            output($e->getMessage());
                        }
                        throw new Exception($fail_message);
                    }

                    try {
                        $challenge_resp = $ig->sendChallangeCode($api_path, $challange_choice);

                        // Failed to send challenge code via email. Try with SMS.
                        if ($challenge_resp->status != "ok") {
                            $challange_choice = 0;
                            sleep(7);
                            $challenge_resp = $ig->sendChallangeCode($api_path, $challange_choice);
                        }

                        $is_connected = true;
                    } catch (\InstagramAPI\Exception\NetworkException $e) {
                        sleep(7);
                    } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                        sleep(7);
                    } catch (\Exception $e) {
                        throw $e;
                    }

                    $is_connected_count += 1;
                } while (!$is_connected);
                
                if ($challenge_resp->status != "ok") {
                    if (isset($challenge_resp->message)) {
                        if ($challenge_resp->message == "This field is required.") {
                            output("We received the response 'This field is required.'. This can happen in 2 reasons:");
                            output("1. Instagram already sent to you verification code to your email or mobile phone number. Please enter this code.");
                            output("2. Instagram forced you to phone verification challenge. Try login to Instagram app or website and take a look at what happened.");
                        }
                    } else {
                        output("Instagram Response: " . json_encode($challenge_resp));
                        output("Couldn't send a verification code for the login challenge. Please try again later.");
                        output("- Is this account has attached mobile phone number in settings?");
                        output("- If no, this can be a reason of this problem. You should add mobile phone number in account settings.");
                        throw new Exception("- Sometimes Instagram can force you to phone verification challenge process.");
                    }
                }

                if (isset($challenge_resp->step_data->contact_point)){
                    $contact_point = $challenge_resp->step_data->contact_point;
                    if ($choice == 2) {
                        output("Enter the code sent to your number ending in " . $contact_point . ".");
                    } else {
                        output("Enter the 6-digit code sent to the email address " . $contact_point . ".");
                    }
                }

                $security_code = getVarFromUser("Security code");

                if (empty($security_code)) {
                    do { 
                        $security_code = getVarFromUser("Security code");
                    } while (empty($security_code));
                }

                if ($security_code == "3") {
                    throw new Exception("Reset in progress...");
                }

                // Verification challenge
                $ig = challange($ig, $login, $password, $api_path, $security_code, $proxy);

            } catch (\InstagramAPI\Exception\AccountDisabledException $e) {
                throw new Exception("Your account has been disabled for violating Instagram terms. Go Instagram website or mobile app to learn how you may be able to restore your account.");
            } catch (\InstagramAPI\Exception\ConsentRequiredException $e) {
                throw new Exception("Instagram updated Terms and Data Policy. Please go to Instagram website or mobile app to review these changes and accept them.");
            } catch (\InstagramAPI\Exception\SentryBlockException $e) {
                throw new Exception("Access to Instagram API restricted for spam behavior or otherwise abusing. You can try to use Session Catcher script (available by https://nextpost.tech/session-catcher) to get valid Instagram session from location, where your account created from.");
            } catch (\InstagramAPI\Exception\IncorrectPasswordException $e) {
                throw new Exception("The password you entered is incorrect. Please try again.");
            } catch (\InstagramAPI\Exception\InvalidUserException $e) {
                throw new Exception("The username you entered doesn't appear to belong to an account. Please check your username and try again.");
            } catch (\Exception $e) {
                throw $e;
            }

            $is_connected_count += 1;
        } while (!$is_connected);

        output("Logged as @" . $login . " successfully.");

        $varKomen = getVarFromUser("Mau test komen? 1 = ya");
        if (empty($varKomen)) {
        	do {
        		$varKomen = getVarFromUser("Mau test komen? 1 = ya");
        	} while (empty($varKomen));
        }

        if ($varKomen == 1) {
        	testkomen();
        } else {
        	refreshmedia();
        }

        testkomen();

    } catch (\Exception $e){
        output($e->getMessage());
        output("Please run script command again.");
        exit;
    }
}

/**
 * Get varable from user
 */
function getVarFromUser($text) {
    echo $text . ": ";
    $var = trim(fgets(STDIN));
    return $var;
}

/**
 * Output message with data to console
 */
function output($message) {
    echo "[", date("H:i:s"), "] ", $message, PHP_EOL;
}

/**
 * Output clean message to console
 */
function output_clean($message) {
    echo $message, PHP_EOL;
}

/**
 * Validates proxy address
 */
function isValidProxy($proxy) {
    output("Connecting to Instagram...");

    try {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'http://www.instagram.com', 
                                [
                                    "timeout" => 60,
                                    "proxy" => $proxy
                                ]);
        $code = $res->getStatusCode();
        $is_connected = true;
    } catch (\Exception $e) {
        output($e->getMessage());
        return false;
    }

    return $code == 200;
}

/**
 * Validates proxy address
 */
function finishLogin($ig, $login, $password, $proxy) {
    $is_connected = false;
    $is_connected_count = 0;

    try {
        do {
            if ($is_connected_count == 10) {
                if ($e->getResponse()) {
                    output($e->getMessage());
                }
                $fail_message = "There is a problem with your Ethernet connection or Instagram is down at the moment. We couldn't establish connection with Instagram 10 times. Please try again later.";
                output($fail_message);
                run($ig);
            }

            if ($proxy == "3") {
                // Skip proxy setup
            } else {
                $ig->setProxy($proxy);
            }

            try {
                $login_resp = $ig->login($login, $password);
        
                if ($login_resp !== null && $login_resp->isTwoFactorRequired()) {
                    // Default verification method is phone
                    $twofa_method = '1';

                    // Detect is Authentification app verification is available 
                    $is_totp = json_decode(json_encode($login_resp), true);
                    if ($is_totp['two_factor_info']['totp_two_factor_on'] == '1'){
                        output("Two-factor authentication required, please enter the code from you Authentication app");
                        $twofa_id = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();
                        $twofa_method = '3';
                    } else {
                        output("Two-factor authentication required, please enter the code sent to your number ending in %s", 
                            $login_resp->getTwoFactorInfo()->getObfuscatedPhoneNumber());
                        $twofa_id = $login_resp->getTwoFactorInfo()->getTwoFactorIdentifier();
                    }

                    $twofa_code = getVarFromUser("Two-factor code");

                    if (empty($twofa_code)) {
                        do { 
                            $twofa_code = getVarFromUser("Two-factor code");
                        } while (empty($twofa_code));
                    }

                    $is_connected = false;
                    $is_connected_count = 0;
                    do {
                        if ($is_connected_count == 10) {
                            if ($e->getResponse()) {
                                output($e->getMessage());
                            }
                            output($fail_message);
                            run($ig);
                        }

                        if ($is_connected_count == 0) {
                            output("Two-factor authentication in progress...");
                        }

                        try {
                            $twofa_resp = $ig->finishTwoFactorLogin($login, $password, $twofa_id, $twofa_code, $twofa_method);
                            $is_connected = true;
                        } catch (\InstagramAPI\Exception\NetworkException $e) {
                            sleep(7);
                        } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                            sleep(7);
                        } catch (\InstagramAPI\Exception\InvalidSmsCodeException $e) {
                            $is_code_correct = false;
                            $is_connected= true;
                            do {
                                output("Code is incorrect. Please check the syntax and try again.");
                                $twofa_code = getVarFromUser("Two-factor code");
            
                                if (empty($twofa_code)) {
                                    do { 
                                        $twofa_code = getVarFromUser("Security code");
                                    } while (empty($twofa_code));
                                }
            
                                $is_connected = false;
                                $is_connected_count = 0;
                                do {
                                    try {
                                        if ($is_connected_count == 10) {
                                            if ($e->getResponse()) {
                                                output($e->getMessage());
                                            }
                                            output($fail_message);
                                            run($ig);
                                        }

                                        if ($is_connected_count == 0) {
                                            output("Verification in progress...");
                                        }
                                        $twofa_resp = $ig->finishTwoFactorLogin($login, $password, $twofa_id, $twofa_code, $twofa_method);
                                        $is_code_correct = true;
                                        $is_connected = true;
                                    } catch (\InstagramAPI\Exception\NetworkException $e) { 
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\InvalidSmsCodeException $e) {
                                        $is_code_correct = false;
                                        $is_connected = true;
                                    } catch (\Exception $e) {
                                        throw new $e;
                                    }
                                    $is_connected_count += 1;
                                } while (!$is_connected);
                            } while (!$is_code_correct);
                        } catch (\Exception $e) {
                            throw $e;
                        }

                        $is_connected_count += 1;
                    } while (!$is_connected);
                }

                $is_connected = true;
            } catch (\InstagramAPI\Exception\NetworkException $e) { 
                sleep(7);
            } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
                sleep(7);
            } catch (\InstagramAPI\Exception\CheckpointRequiredException $e) {
                throw new Exception("Please go to Instagram website or mobile app and pass checkpoint!");
            } catch (\InstagramAPI\Exception\ChallengeRequiredException $e) {
                output("Instagram Response: " . json_encode($e->gerResponse()));
                output("Couldn't complete the verification challenge. Please try again later.");
                throw new Exception("Developer code: Challenge loop.");
            } catch (\Exception $e) {
                throw $e;
            }

            $is_connected_count += 1;
        } while (!$is_connected);
    } catch (\Exception $e){
        output($e->getMessage());
        run($ig);
    }

    return $ig;
}

/**
 * Verification challenge
 */
function challange($ig, $login, $password, $api_path, $security_code, $proxy) {
    $is_connected = false;
    $is_connected_count = 0;
    $fail_message = "There is a problem with your Ethernet connection or Instagram is down at the moment. We couldn't establish connection with Instagram 10 times. Please try again later.";

    do {
        if ($is_connected_count == 10) {
            if ($e->getResponse()) {
                output($e->getMessage());
            }
            throw new Exception($fail_message);
        }

        if ($is_connected_count == 0) {
            output("Verification in progress...");
        }

        try {
            $challenge_resp = $ig->finishChallengeLogin($login, $password, $api_path, $security_code);
            $is_connected = true;
        } catch (\InstagramAPI\Exception\NetworkException $e) {
            sleep(7);
        } catch (\InstagramAPI\Exception\EmptyResponseException $e) {
            sleep(7);
        } catch (\InstagramAPI\Exception\InstagramException $e) {

            if ($e->hasResponse()) {
                $msg = $e->getResponse()->getMessage();
                output($msg);
            } else {
                $msg = explode(":", $e->getMessage(), 2);
                $msg = end($msg);
                output($msg);
            }

            output("Type 3 - to exit.");

            $security_code = getVarFromUser("Security code");

            if (empty($security_code)) {
                do { 
                    $security_code = getVarFromUser("Security code");
                } while (empty($security_code));
            }

            if ($security_code == "3") {
                throw new Exception("Reset in progress...");
            }

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($msg == 'Invalid Login Response at finishChallengeLogin().') {
                sleep(7);
                $ig = finishLogin($ig, $login, $password, $proxy);
                $is_connected = true;
            } else {
                throw $e;
            }
        }

        $is_connected_count += 1;
    } while (!$is_connected);

    return $ig;
}

/**
 * Refresh Media before bot started
 */

function refreshmedia()
{
    global $username;
    global $ig;

    $cekaktif = 0;

    output("");

    $get_batas = file_get_contents("http://filmkita.org/gakpenting/getbatas.php?username=".$username);
    $get_limit = file_get_contents("http://filmkita.org/gakpenting/getlimit.php?username=".$username);
    $get_target = file_get_contents("http://filmkita.org/gakpenting/target.php?target=".$username);

    if ($get_batas < $get_limit) {
        if ($get_target == "") {
            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=1");
            output("Tidak ada target yang Aktif, mencoba lagi dlm 1 menit...");
            sleep(60);
            refreshmedia();
        } else {
            $dataTarget = explode("#", $get_target);
            for ($i=0; $i < count($dataTarget)-1; $i++) { 

                try {
                    $usernameTarget = $dataTarget[$i];

                    $userId = $ig->people->getUserIdForName($usernameTarget);
                    $maxId = null;

                    $response = json_decode($ig->timeline->getUserFeed($userId, $maxId));

                    $media_id = $response->items[0]->id;

                    $listcomment = json_decode($ig->media->getComments($media_id));

                    $updatemedia = file_get_contents("http://filmkita.org/gakpenting/updatemedia.php?target=".$usernameTarget."&username=".$username."&media=".$media_id);

                    if ($updatemedia == "sukses") {
                       output("sedang refresh ".$usernameTarget);
                    } else {
                        output("GAGAL refresh ".$usernameTarget);
                    }
                } catch (\InstagramAPI\Exception\NetworkException $e){
                    output("Koneksi ke Instagram Error, mencoba lagi...");
                    sleep(7);
                } catch (\InstagramAPI\Exception\NotFoundException $e){
                    output("Di block oleh target ".$usernameTarget);
                    output("Menghapus target ".$usernameTarget);
                    $nontarget = file_get_contents("http://filmkita.org/gakpenting/nontarget.php?target=".$usernameTarget."&username=".$username);
                    if ($nontarget == "sukses") {
                        output("Sukses menghapus ".$usernameTarget);
                    } else {
                        output($e->getMessage());
                        die();
                    }
                } catch (\InstagramAPI\Exception\InvalidUserException $e){
                    output("Target gak jelas ".$usernameTarget);
                    output("Menghapus target ".$usernameTarget);
                    $nontarget = file_get_contents("http://filmkita.org/gakpenting/nontarget.php?target=".$usernameTarget."&username=".$username);
                    if ($nontarget == "sukses") {
                        output("Sukses menghapus ".$usernameTarget);
                    } else {
                        output($e->getMessage());
                        die();
                    }
                } catch (\InstagramAPI\Exception\EmptyResponseException $e){
                    if ($totalempty < 20) {
                        $totalempty = $totalempty+1;
                        output("Kena Empty Response, memulai lagi...");
                        sleep(25);
                    } else {
                        output("Terlalu banyak kena Empty Response, script dihentikan");
                        die();
                    }
                } catch (\InstagramAPI\Exception\EndpointException $e){
                    output('Endpoint Error: '.$e->getMessage());
                    sleep(7);
                } catch (\InstagramAPI\Exception\InternalException $e){
                    output('Internal: '.$e->getMessage());
                    sleep(7);
                } catch (\InstagramAPI\Exception\BadRequestException $e){
                    print 'BadRequest: '.$e->getMessage()."\n";
                    sleep(7);
                } catch (\InstagramAPI\Exception\InstagramException $e){
                    print 'InstagramException: '.$e->getMessage()."\n";
                    sleep(7);
                } catch (\InstagramAPI\Exception\RequestException $e){
                    print 'RequestException: '.$e->getMessage()."\n";
                    sleep(7);
                } catch (\Exception $e) {
                    $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
                    print 'Something went wrong refreshmedia: '.$e->getMessage()."\n";
                    print "Kena Error gak tau kenapa\n";
                    die();
                } 
            }
        }
    } else {
    	output("");
        output("sudah melebihi batas limit harian, akan dilanjutkan besok.");
        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=11");
        die();
    }
}

/**
 * Bot Auto Komen
 */

$totalsukses = 0;
$totalgagal = 0;
$totalempty = 0;
$totalreply = 0;
$cekaktif = 0;
$cekfeedback = 0;

bot_autokomen();

function bot_autokomen($ig) {
	global $username;
    global $totalgagal;
    global $totalsukses;
    global $totalempty;
    global $totalreply;
    global $cekaktif;
    global $cekfeedback;

    $delayrandom = rand(5,20);

    try {
        $get_batas = file_get_contents("http://filmkita.org/gakpenting/getbatas.php?username=".$username);
        $get_limit = file_get_contents("http://filmkita.org/gakpenting/getlimit.php?username=".$username);
        $get_totalkomen = file_get_contents("http://filmkita.org/gakpenting/get_totalkomen.php?username=".$username);
        $getexp = file_get_contents("http://filmkita.org/gakpenting/getexp.php?username=".$username);
    } catch (\InstagramAPI\Exception\InternalException $e){
        print "Kena Error Internal php...\n";
        sleep(7);
        bot_autokomen();
    } catch (\Exception $e) {
        //$sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
        print 'GET CONTENT AKG: '.$e->getMessage()."\n";
        sleep(7);
        bot_autokomen();
    } 

    if ($getexp == "expired") {
        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=3");
        print "\nSudah expired...\n";
        die();
    } else {
        if ($get_totalkomen == 0) {
            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=2");
            print "\nBelum memiliki list komen, mencoba lagi dalam 1 menit...\n";
            sleep(60);
        } else {
            if ($get_batas < $get_limit) {
                try {
                    $get_target = file_get_contents("http://filmkita.org/gakpenting/target.php?target=".$username);
                } catch (\InstagramAPI\Exception\InternalException $e){
                    print "Kena Error Internal php...\n";
                    sleep(7);
                } catch (\Exception $e) {
                    //$sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
                    print 'GET CONTENT AKG: '.$e->getMessage()."\n";
                    sleep(7);
                } 

                if ($get_target == "") {
                    $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=1");
                    print "Tidak ada target yang Aktif, mencoba lagi dlm 1 menit...\n";
                    sleep(60);
                } else {
                    $dataTarget = explode("#", $get_target);

                    for ($i=0; $i < count($dataTarget)-1; $i++) {
                        try {
                            print "\n";

                            $usernameTarget = $dataTarget[$i];

                            $userId = $ig->people->getUserIdForName($usernameTarget);
                            $maxId = null;

                            $response = json_decode($ig->timeline->getUserFeed($userId, $maxId));

                            $media_id = $response->items[0]->id;

                            $urlpost = "https://www.instagram.com/p/".$response->items[0]->code."/";

                            $listcomment = json_decode($ig->media->getComments($media_id));

                            try {
                                $getmedia = file_get_contents("http://filmkita.org/gakpenting/media.php?target=".$usernameTarget."&username=".$username);
                                $getkomen = file_get_contents("http://filmkita.org/gakpenting/komen.php?username=".$username);
                            } catch (\InstagramAPI\Exception\InternalException $e){
                                print "Kena Error Internal php...\n";
                                sleep(7);
                            } catch (\Exception $e) {
                                //$sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
                                print 'GET CONTENT AKG: '.$e->getMessage()."\n";
                                sleep(7);
                            } 

                            print "Target: ".$usernameTarget."\n";
                            print "Last Post: ".$urlpost."\n";

                        } catch (\InstagramAPI\Exception\NotFoundException $e){
                            print "\nDi block oleh target ".$usernameTarget."\n";
                            print "Menghapus target ".$usernameTarget."...\n";
                            $nontarget = file_get_contents("http://filmkita.org/gakpenting/nontarget.php?target=".$usernameTarget."&username=".$username);
                            if ($nontarget == "sukses") {
                                print "Sukses menghapus ".$usernameTarget."\n\n";
                            } else {
                                print "Gagal hapus: ".$e->getMessage()."\n";
                                $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=7");
                                die();
                            }
                        } catch (\InstagramAPI\Exception\NetworkException $e){
                            print "Koneksi ke Instagram Error, mencoba lagi...\n";
                            sleep(7);
                        } catch (\InstagramAPI\Exception\InvalidUserException $e){
                            print "\nTarget gak jelas ".$usernameTarget."\n";
                            print "Menghapus target ".$usernameTarget."...\n";
                            $nontarget = file_get_contents("http://filmkita.org/gakpenting/nontarget.php?target=".$usernameTarget."&username=".$username);
                            if ($nontarget == "sukses") {
                                print "Sukses menghapus ".$usernameTarget."\n\n";
                            } else {
                                print $e->getMessage()."\n";
                                die();
                            }
                        } catch (\InstagramAPI\Exception\CheckpointRequiredException $e){
                            $totalgagal = $totalgagal+1;
                            print "Terkena Checkpoint Required\n";
                            print "Buka App Instagram untuk verifikasi";
                            sleep(7);
                        } catch (\InstagramAPI\Exception\SentryBlockException $e){
                            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=8");
                            $totalgagal = $totalgagal+1;
                            print "Kena Sentry Block, script dihentikan\n";
                            die();
                        } catch (\InstagramAPI\Exception\ChallengeRequiredException $e){
                            $totalgagal = $totalgagal+1;
                            print "Kena Feedback Required, memulai lagi dlm 5 mnt...\n";
                            sleep(300);
                            refreshmedia();
                        } catch (\InstagramAPI\Exception\EmptyResponseException $e){
                            if ($totalempty < 20) {
                                $totalempty = $totalempty+1;
                                print "Kena Empty Response, memulai lagi...\n";
                                sleep(25);
                            } else {
                                $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=9");
                                print "Terlalu banyak kena Empty Response, script dihentikan";
                                die();
                            }
                         } catch (\InstagramAPI\Exception\EndpointException $e){
                            print 'AKG Endpoint Error: '.$e->getMessage()."\n";
                            sleep(7);
                         } catch (\InstagramAPI\Exception\InternalException $e){
                            print 'AKG Internal: '.$e->getMessage()."\n";
                            sleep(7);
                         } catch (\InstagramAPI\Exception\BadRequestException $e){
                            print 'AKG BadRequest: '.$e->getMessage()."\n";
                            sleep(7);
                         } catch (\InstagramAPI\Exception\InstagramException $e){
                            print 'AKG InstagramException: '.$e->getMessage()."\n";
                            sleep(7);
                         } catch (\InstagramAPI\Exception\RequestException $e){
                            print 'AKG RequestException: '.$e->getMessage()."\n";
                            sleep(7);
                         } catch (\Exception $e) {
                            $totalgagal = $totalgagal+1;
                            print 'AKG Something went wrong: '.$e->getMessage()."\n";
                            print "Kena Error gak tau kenapa, mulai ulang dlm 5 mnt...";
                            sleep(300);
                            refreshmedia();
                        } 

                        if ($getmedia == $media_id) {
                            print "Status: Sudah pernah di komen\n";
                        } elseif (!isset($media_id)) {
                            print "Gagal get last post target, sedang mencoba lagi...";
                            sleep(1);
                            //autokomen();
                        } elseif (!isset($getmedia)) {
                            print "Gagal get last post dari server, sedang mencoba lagi...";
                            sleep(1);
                            //autokomen();
                        } elseif ($media_id == "") {
                            print "Gagal get last post target, sedang mencoba lagi...";
                            sleep(1);
                            //autokomen();
                        } elseif ($getmedia == "") {
                            print "Gagal get last post target, sedang mencoba lagi...";
                            sleep(1);
                            //autokomen();
                        }

                        else {
                            print "Komen: ".$getkomen."\n";
                            $updatemedia = file_get_contents("http://filmkita.org/gakpenting/updatemedia.php?target=".$usernameTarget."&username=".$username."&media=".$media_id);
                            if ($updatemedia != "sukses") {
                                $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=10");
                                print "Terjadi kesalahan pada saat update media_id, script di hentikan";
                                die();
                            } else {
                                if (isset($listcomment->comments[0])) {
                                    try {
                                        $user_reply = "@".$listcomment->comments[0]->user->username." ";
                                        $pk_id = $listcomment->comments[0]->pk;
                                        $commentreply = substr_replace($getkomen, $user_reply, 0, 0 ); 

                                        if ($get_totalkomen >= 10) {
                                            $sendcomment = json_decode($ig->media->comment($media_id, $commentreply, $pk_id, 'comments_v2', 0, 1, true));
                                        } else {
                                            $sendcomment = json_decode($ig->media->comment($media_id, $commentreply." #".rand(1,100), $pk_id, 'comments_v2', 0, 1, true));
                                        }

                                        //print_r($sendcomment);

                                        print "Status: Post baru, Reply first komen.\n";

                                        if ($sendcomment->status == "ok") {
                                            print "Status ngomen: Sukses komen\n";
                                            $totalreply = $totalreply+1;
                                            $add_riwayat = file_get_contents("http://filmkita.org/gakpenting/addriwayat.php?username=".$username."&media=".$urlpost."&target=".$usernameTarget);
                                            print "Mengirim riwayat ke server...\n";

                                            if ($add_riwayat == "sukses") {
                                                    print "Sukses mengirim riwayat ke server\n";
                                            }

                                            $add_batas = file_get_contents("http://filmkita.org/gakpenting/updatebatas.php?username=".$username);
                                            print "Mengirim batas +1 ke server...\n";

                                            if ($add_batas == "sukses") {
                                                    print "Sukses mengirim batas +1 ke server\n";
                                            }
                                        } else {
                                            print "Status ngomen: Tidak bisa komen alasan unknown\n";
                                            die();
                                        }
                                    } catch (\InstagramAPI\Exception\NetworkException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Koneksi Error, mencoba untuk menghubungkan kembali...\n";
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\CheckpointRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Terkena Checkpoint Required\n";
                                        print "Buka App Instagram untuk verifikasi";
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\SentryBlockException $e){
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=8");
                                        $totalgagal = $totalgagal+1;
                                        print "Kena Sentry Block, script dihentikan";
                                        die();
                                    } catch (\InstagramAPI\Exception\ChallengeRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Kena Challenge Required, memulai lagi dlm 5 mnt...\n";
                                        sleep(300);
                                        refreshmedia();
                                    } catch (\InstagramAPI\Exception\EmptyResponseException $e){
                                        if ($totalempty < 20) {
                                            $totalempty = $totalempty+1;
                                            print "Kena Empty Response, memulai lagi...\n";
                                            sleep(25);
                                        } else {
                                            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=9");
                                            print "Terlalu banyak kena Empty Response, script dihentikan\n";
                                            die();
                                        }
                                    } catch (\InstagramAPI\Exception\FeedbackRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        $cekfeedback = $cekfeedback+1;
                                        print "Kena Feedback Required, memulai lagi dlm 10 mnt...\n";
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=12");
                                        sleep(600);
                                        refreshmedia();
                                    } catch (\Exception $e) {
                                        $totalgagal = $totalgagal+1;
                                        print 'Something went wrong: '.$e->getMessage()."\n";
                                        print "Kena Error gak tau kenapa, mulai ulang dlm 5 mnt...\n";
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
                                        sleep(300);
                                        refreshmedia();
                                    } 

                                } else {
                                    try {
                                        if ($get_totalkomen >= 10) {
                                            $sendcomment = json_decode($ig->media->comment($media_id, $getkomen, null, 'comments_v2', 0, 1, true));
                                        } else {
                                            $sendcomment = json_decode($ig->media->comment($media_id, $getkomen." #".rand(1,100), null, 'comments_v2', 0, 1, true));
                                        }
                                        //print_r($sendcomment);
                                        print "Status: Post baru, proses melakukan komen.\n";
                                        print "Status ngomen: Sukses komen\n";
                                        
                                        if ($sendcomment->status == "ok") {
                                            print "Status ngomen: Sukses komen\n";
                                            $totalsukses = $totalsukses+1;
                                            $add_riwayat = file_get_contents("http://filmkita.org/gakpenting/addriwayat.php?username=".$username."&media=".$urlpost."&target=".$usernameTarget);
                                            print "Mengirim riwayat ke server...\n";

                                            if ($add_riwayat == "sukses") {
                                                    print "Sukses mengirim riwayat ke server\n";
                                            }

                                            $add_batas = file_get_contents("http://filmkita.org/gakpenting/updatebatas.php?username=".$username);
                                            print "Mengirim batas +1 ke server...\n";

                                            if ($add_batas == "sukses") {
                                                    print "Sukses mengirim batas +1 ke server\n";
                                            }
                                        } else {
                                            print "Status ngomen: Tidak bisa komen alasan unknown\n";
                                            die();
                                        }
                                    } catch (\InstagramAPI\Exception\NetworkException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Koneksi Error, mencoba untuk menghubungkan kembali...\n";
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\CheckpointRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Terkena Checkpoint Required\n";
                                        print "Buka App Instagram untuk verifikasi\n";
                                        sleep(7);
                                    } catch (\InstagramAPI\Exception\SentryBlockException $e){
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=8");
                                        $totalgagal = $totalgagal+1;
                                        print "Kena Sentry Block, script dihentikan\n";
                                        die();
                                    } catch (\InstagramAPI\Exception\ChallengeRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        print "Kena Challenge Required, memulai lagi dlm 5 mnt...\n";
                                        sleep(300);
                                        bot_autokomen();
                                    } catch (\InstagramAPI\Exception\EmptyResponseException $e){
                                        if ($totalempty < 20) {
                                            $totalempty = $totalempty+1;
                                            print "Kena Empty Response, memulai lagi...\n";
                                            sleep(25);
                                        } else {
                                            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=9");
                                            print "Terlalu banyak kena Empty Response, script dihentikan\n";
                                            die();
                                        }
                                    } catch (\InstagramAPI\Exception\FeedbackRequiredException $e){
                                        $totalgagal = $totalgagal+1;
                                        $cekfeedback = $cekfeedback+1;
                                        print "Kena Feedback Required, memulai lagi dlm 10 mnt...\n";
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=12");
                                        sleep(600);
                                        refreshmedia();
                                    } catch (\Exception $e) {
                                        $totalgagal = $totalgagal+1;
                                        print 'Something went wrong: '.$e->getMessage()."\n";
                                        print "Kena Error gak tau kenapa, mulai ulang dlm 5 mnt...\n";
                                        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=6");
                                        sleep(300);
                                        refreshmedia();
                                    } 
                                }
                                print "\n";
                            }
                        }
                    }
                    print "\n";
                    print "sleep ".$delayrandom." detik\n";
                    print date("Y-m-d H:i:s")."\n";
                    print "Username: ".$username."\n";
                    print "Total Komen: ".$totalsukses." - Total Reply: ".$totalreply." - Total Gagal: ".$totalgagal."\n";
                    sleep($delayrandom);
                }
            } else {
                print "\nsudah melebihi batas limit harian, akan dilanjutkan besok.\n";
                $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=11");
                die();
            }
        }
    }
    if ($cekaktif == 0) {
        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=Aktif");
        $cekaktif = $cekaktif+1;
    } else {

    }
    if ($cekfeedback < 5) {
        
    } else {
        $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=13");
        print "sudah kena Feedback 5x. OFF\n";
        die();
    }
    bot_autokomen();
}

/**
 * Test Komen
*/

function testkomen()
{
    global $username;
    global $ig;
    $cekfeedback = 0;

    print "\n";

    try {
        $baba = "rafivadra";
        $userId = $ig->people->getUserIdForName($baba);
        $maxId = null;

        $response = json_decode($ig->timeline->getUserFeed($userId, $maxId));

        $media_id = $response->items[0]->id;

        $getkomen = file_get_contents("http://filmkita.org/gakpenting/komen.php?username=".$username);

        if (isset($getkomen)) {
            $komen_nya = "#".rand(1000,1000000);
        } else {
            $komen_nya = "#".rand(1000,1000000);
        }

        if (isset($media_id)) {
            $sendcomment = json_decode($ig->media->comment($media_id, $komen_nya, null, 'comments_v2', 0, 0, false));
            if ($sendcomment->status == "ok") {
                print "sukses mencoba komen...\n\n";
                refreshmedia();
                //print_r($sendcomment);
            } else {
                print "gagal mencoba komen...\n\n";
                print_r($sendcomment);
                die();
            }
        }

    } catch (\InstagramAPI\Exception\FeedbackRequiredException $e){
        if ($cekfeedback != 0) {
            $cekfeedback = $cekfeedback+1;
            print "Kena Feedback Required, memulai lagi dlm 1 menit...\n";
            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=12");
            sleep(60);
            testkomen();
        } else {
            $sendanjay = file_get_contents("http://filmkita.org/gakpenting/update_status.php?username=".$username."&status=13");
            print "TK. sudah kena Feedback 2x. OFF\n";
            die();
        }
    } catch (\Exception $e) {
        $totalgagal = $totalgagal+1;
        print 'Something went wrong: '.$e->getMessage()."\n";
        print $getkomen;
        print "anjay";
        die();
    } 

}

/**
 * Send request
 * @param $url
 * @return mixed
 */
function request($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $return = curl_exec($ch);

    curl_close($ch);

    return $return;
}
  
/**
 * Get IP details
 */
function ip_details() {
    try {
        $json = request("http://www.geoplugin.net/json.gp");
    } catch (Exception $e){
        $msg = $e->getMessage();
        output($msg);
        run($ig);
    }
    $details = json_decode($json);
    return $details;
}
/**
 * Validate license
 * @param $license_key
 * @return string
 */
function activate_license($license_key, $ig) {
    return 'valid';
}

?>
