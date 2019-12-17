<?php
    $GLOBALS['server'] = getenv('SERVER');
    $defaultIV = getenv('IV');
    $userHash = getenv('KEY');
    $cookie = getenv('COOKIE');
    $GLOBALS['tidx'] = getenv('TIDX');
    $GLOBALS['host'] = getenv('HOST');
    $GLOBALS['d'] = getenv('D');

    if($GLOBALS['server']  == false){
        $GLOBALS['server'] = 1;
    }
    
    if ($GLOBALS['server'] == 1){
        if ($defaultIV == false){
            $defaultIV = 'yCNBH$$rCNGvC+#f';
        } 

        if ($userHash == false){
            $userHash = '061dd115161aff9d956bba80768c9332';
        } 

        if ($cookie == false){
            $cookie = 'e1f6a65336c7b896bcbfc0bc06b39099%3A1';
        } 

        if ($GLOBALS['tidx'] == false){
            $GLOBALS['tidx'] = '163';
        } 

        if ($GLOBALS['host'] == false){
            $GLOBALS['host'] = 'http://appprd.dragonproject.gogame.net/ajax/';
        }

        if ($GLOBALS['d'] == false){
            $GLOBALS['d'] = 'bb6542934b7e8b9ca8f6e067a0b2b79b6eaa470bba2c66c33f7ef47303172a02a20218166a4b8fce62c6b3a2b30046ed';
        }

        $equipArray = array(
            "1080534");
    }

    if ($GLOBALS['server'] == 2){
        if ($defaultIV == false){
            $defaultIV = 'yCNBH$$rCNGvC+#f';
        } 

        if ($userHash == false){
            $userHash = '1833b33428bd3119bbfe7d1fef0d2114';
        } 

        if ($cookie == false){
            $cookie = '6290dba6f45b2f7dfebb4fc5d9e5674a109fc6ea%3A1';
        } 

        if ($GLOBALS['tidx'] == false){
            $GLOBALS['tidx'] = '18001';
        } 

        if ($GLOBALS['host'] == false){
            $GLOBALS['host'] = 'http://appprd-01.dragonproject.gogame.net/ajax/';
        }

        if ($GLOBALS['d'] == false){
            $GLOBALS['d'] = 'fb630a79aea07fba5bdff5a5ac5b56c55f070d5a5fdc011cc559d57c5b4267bd69bdb197a340de2f86f3408058dff066';
        }

        $equipArray = array(
        );
    }

    

    /* -----============== Basic tools ==============----- */
    function println ($string_message) {
        if(!isset($_SERVER['SERVER_PROTOCOL'])){
            print "$string_message\n";
        } else {
            $_SERVER['SERVER_PROTOCOL'] ? print "$string_message<br />" : print "$string_message\n";
        }
    }

    function toBase64($string){
        return base64_encode($string);
    }

    function fromBase64($string){
        return base64_decode($string);
    }

    //Removes whitespace from JSON
    function jsonMinify($json_string){
        return json_encode(json_decode($json_string));
    }

    //Formats JSON string for human reading
    function jsonPrettify($json_string){
        return json_encode(json_decode($json_string), JSON_PRETTY_PRINT);
    }

    //Probably unneeded
    function strToHex($x)
    {
        $s='';
        foreach (str_split($x) as $c) $s.=sprintf("%02X",ord($c));
        return($s);
    }

    //Takes plain String, returns Base64 encrypted
    function aes256CBCEncrypt($string, $key, $iv)
    {
        $method = 'aes-256-cbc';
        $encrypted = openssl_encrypt($string, $method, $key, false, $iv);

        return $encrypted;
    }

    //Takes encrypted Base64, returns plain string (garbage if compressed, needs inflation)
    function aes256CBCDecrypt($string, $key, $iv)
    {
        $method = 'aes-256-cbc';
        $decrypted = openssl_decrypt($string, $method, $key, false, $iv);

        return $decrypted;
    }

    //Takes plain string and returns raw compressed
    function compress($string){
        return zlib_encode($string);
    }

    //Takes raw compressed and returns plain string (Max 64 MB, probably not an issue?)
    function inflate($string){
        return  zlib_decode($string, 1024 * 1024 * 64);
    }

    //RcToken is an MD5 of the current datetime to the millisecond, used to separate connection retries from new requests to save server CPU time.
    //Needs to be different from all others within a period of time from my account.
    function genRcToken(){
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

        return md5($d->format("YmdHis.u"));
    }

    //cUrl template to be used as needed, since all relevant requests are the same anyway.
    //Differences are few but treated within.
    //Returns raw server response. Server errors return null and need to be addressed as they come.
    function requestTemplate($data = null, $endpoint, $cookie, $curl = null, $rctoken = null, $needInfo = false){
        if (is_null($curl)){
            $curl = curl_init();
        }
        
        if (is_null($rctoken)){
            $rcToken1 = genRcToken();
        } else {
            $rcToken1 = $rctoken;
        }

        $GLOBALS['lastRcToken'] = $rcToken1;

        $body = "app=rob&rcToken=$rcToken1";
        $host = $GLOBALS['host'].$endpoint;

        if (!is_null($data)){
            $body = "data=".urlencode($data).'&'.$body;
        }

        curl_setopt_array($curl, array(
        CURLOPT_URL => $host,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
                "Cookie: robpt=$cookie",
                "User-Agent: Dalvik/2.1.0 (Linux; U; Android 9; SM-N9600 Build/PPR1.180610.011)",
                "X-Unity-Version: 2018.4.3f1",
                "aidx: 106005",
                "amv: 1",
                "apv: 1.8.1",
                "cdv: -1",
                "dm: samsung SM-N9600",
                "tidx: ".$GLOBALS['tidx'],
                "tmv: 1",
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "Accept-Encoding: gzip, deflate",
                "Expect: ",
                "Connection: keep-alive"
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE );

        if ($response == 'error' || $httpCode != 200){
            return null;
        }

        if ($needInfo == false){
            return $response;
        } else {
            return array($response, $curl);
        }
    }

    /* -----============== Basic routines ==============----- */
    //Takes returned server hash, decrypts, inflates, prettifies and returns a prettified JSON String
    function serverToUserDecrypt($hash, $defaultIV, $userHash){
        $decryptRaw = aes256CBCDecrypt($hash, $userHash, $defaultIV);
        $stringInflated = inflate($decryptRaw);
        return $stringInflated;
    }

    //Takes a JSON String, minifies, encrypts and returns a base64 hash
    function userToServerEncrypt($string, $defaultIV, $userHash){
        $string = jsonMinify($string);
        $encryptedHash = aes256CBCEncrypt($string, $userHash, $defaultIV);
        return $encryptedHash;
    }

    /* -----============== Complex Tasks ==============----- */
    function redeemGiftBoxItems($page, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"page":'.$page.'}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'present/list', $cookie, $curl);

        if (is_null($response)) {
            println("ERRO SERVER");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
            $presents = $jsonResponse["result"]["presents"];
            $redeemed = false;
            $uniqIdArray = array();
            foreach ($presents as $entry){
                $ignored = false;
                //Is Ticket
                if ($entry["itemId"] == 1200000){
                    //$redeemed = true;
                    //$uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }
                //Is tablet
                if (preg_match("/(\sTablet\sx\s1)/", $entry["name"]) && $entry["type"] == 3 ){
                    continue;
                }
                //Is Magi
                if (preg_match("/(Lv1\sx\s1)/", $entry["name"]) && $entry["type"] == 5 ){
                    continue;
                }

                if (preg_match("/(Gems)/", $entry["name"]) && $entry["type"] == 1 ){
                    continue;
                }

                if (preg_match("/(Crystal)/", $entry["name"]) && $entry["type"] == 1 ){
                    continue;
                }

                if (preg_match("/(Obtained\sin\sSummon)/", $entry["comment"]) && $entry["type"] == 6 ){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }

                if (preg_match("/(Gold)/", $entry["name"]) && $entry["type"] == 2){
                    continue;
                }

                if (preg_match("/(Potion)/", $entry["name"])){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }

                if (preg_match("/(Elixir)/", $entry["name"])){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }
                //Is vault item
                //if (!$ignored /*&& preg_match("/(Obtained\sfrom\sDragon)/", $entry["comment"])*/)
                /*{
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"];
                }*/
            }

            if ($redeemed) {
                $requestArray = array(
                    "uids" => $uniqIdArray,
                    "page" => $page
                );

                $encryptedRequestHash = userToServerEncrypt(json_encode($requestArray), $defaultIV, $userHash);
                $response = requestTemplate($encryptedRequestHash, 'present/receive', $cookie, $curl);

                if (is_null($response)) {
                    println("ERRO SERVER");
                    return null; //Server Error
                }

                $response = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

                if ($response["error"] == 0){

                    return true; //Redeemed at least one item successfully
                } else {
                    println("ERRO: ".$response["error"]);
                    println("array: ".jsonPrettify(json_encode($requestArray)));
                    return null;
                }
            } else {
                return false; //No item to be redeemed in this page
            }
        } else {
            print "ERRO: ".$jsonResponse["error"];
            return null;
        }
    }

    function listRelevantItems($page, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"page":'.$page.'}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'present/list', $cookie, $curl);

        if (is_null($response)) {
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
            $presents = $jsonResponse["result"]["presents"];
            $uniqIdArray = array();
            foreach ($presents as $entry){
                //Is tablet
                if (preg_match("/(\sTablet\sx\s1)/", $entry["name"]) && $entry["type"] == 3 ){
                    $uniqIdArray[$entry["name"]] = $entry["uniqId"];
                }
                //Is Magi
                if (preg_match("/(Lv1\sx\s1)/", $entry["name"]) && $entry["type"] == 5 ){
                    $uniqIdArray[$entry["name"]] = $entry["uniqId"];
                }
            }

            if(!empty($uniqIdArray)){
                return $uniqIdArray;
            } else {
                return false; //No item to be redeemed in this page
            }
        } else {
            println("ERRO: ".$jsonResponse["error"]);
            return null;
        }
    }

    function rerollPerfectAbility($plainRequest, $aid, $maxap, $defaultIV, $userHash, $cookie, $curl, $euid){
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $rerollResult = requestTemplate($encryptedRequestHash, 'smith/changeability', $cookie, $curl);

        if (is_null($rerollResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($rerollResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
            $abilityResult = $jsonResponse["diff"][0]["equipItem"][0]["update"][0]["ability"];

            $isPerfect = true;

            foreach ($abilityResult as $row){ //Triggers the flag to false if the roll isnt perfect, and prints the selected ability results for viewing
                if($row["id"] == $aid && $row["pt"] == $maxap){
                    print '+'.$row["pt"];
                } else {
                    if($row["id"] == $aid){
                        print '+'.$row["pt"];
                    } else {
                        print '-'.$row["pt"];
                    }
                    $isPerfect = false;
                }
                print '/';
            }

            print " -> $euid\n";

            return $isPerfect;
        } else {
            print $jsonResponse["error"];
            return $jsonResponse["error"];
        }
    }

    function chugPotion($chugId, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"uid":"'.$chugId.'"}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $rerollResult = requestTemplate($encryptedRequestHash, 'inventory/useitem', $cookie, $curl);

        if (is_null($rerollResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($rerollResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
           return true;
        } elseif ($jsonResponse["error"] == 15005) {
            println($jsonResponse["error"].' Nro '. $chugId.' nao existe.');
            return null;
        } else {
            print $jsonResponse["error"];
            return null;
        }
    }

    function dupePresents($presents, $defaultIV, $userHash, $cookie, $curl){
        foreach ($presents as $present){
            /*if ($present["itemId"] == 1200000){
                print("-");

                continue;
            }*/

            if (preg_match("/(Obtained\sin\sSummon)/", $present["comment"]) && $present["type"] == 6 ){
                print("-");

                continue;
            }

            if (preg_match("/(Gems)/", $present["name"]) && $present["type"] == 1 ){
                print("-");

                continue;
            }

            if (preg_match("/(Potion)/", $present["name"]) || preg_match("/(Elixir)/", $present["name"])){
                print("-");

                continue;
            }

            /*if (preg_match("/(Lv1\sx\s1)/", $present["name"]) && $present["type"] == 5 ){
                continue;
            }*/

            $presentId = $present["uniqId"];

            $plainRequest = '{"uids":["'.$presentId.',1", "'.$presentId.',2", "'.$presentId.',3", "'.$presentId.',4", "'.$presentId.',5", "'.$presentId.',6", "'.$presentId.',7", "'.$presentId.',8", "'.$presentId.',9", "'.$presentId.',10"],"page":0}';
            println($plainRequest);
            
            $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
            $dupeResult = requestTemplate($encryptedRequestHash, 'present/receive', $cookie, $curl);

            println("\nYeeted 10x ".$present["name"]);
        }

        if(isset($dupeResult)){
            $jsonResponse = json_decode(serverToUserDecrypt($dupeResult, $defaultIV, $userHash), true);
        }
        
        //println($jsonResponse);
        if (isset($jsonResponse["result"]["list"]["presents"])){
            return $jsonResponse["result"]["list"]["presents"];
        } else {
            return null;
        }

    }

    function ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl, $isShadow = false){
        $questNr = 0;
        $error = 0;

        while ($questNr < ($qNum-5)){
            $qToken = genRcToken();

            if ($questNr < $qNum && $isShadow == false){
                for($j = 0; $j<5; $j++){
                    $isComplete = guildQuestComplete($j, $defaultIV, $userHash, $cookie, $curl);

                    if ($isComplete == true && ($questNr < ($qNum-5))){
                        if (guildQuestStart($qId, $j, $defaultIV, $userHash, $cookie, $curl) == true){
                            print("-");
                            $questNr++;
                            if($questNr > $qNum){
                                break;
                            }
                        }
                    }
                }

                if($questNr > $qNum){
                    break;
                }
            }

            /*println("Qid *".$qId."*");
            println("qtoken *".$qToken."*");*/

            $plainRequestStart = array (
                'qid' => $qId,
                'qt' => $qToken,
                'setNo' => 8,
                'crystalCL' => 0,
                'free' => 1,
                'dId' => 0,
                'd' => $GLOBALS['d'],
                'actioncount' => 
                array (
                    'revival' => 0,
                    'guard' => 0,
                    'counter' => 0,
                    'lance' => 0,
                    'combo' => 0,
                    'chargesword' => 0,
                    'chargebow' => 0,
                    'usemagi' => 0,
                    'weak' => 0,
                    'weaponweak' => 0,
                    'death' => 0,
                    'heatTwoHandSword' => 0,
                    'heatPairSwords' => 0,
                    'revengeBurst' => 0,
                    'justGuard' => 0,
                    'shadowSealing' => 0,
                    'jump' => 0,
                    'soulOneHandSword' => 0,
                    'soulTwoHandSword' => 0,
                    'soulSpear' => 0,
                    'soulPairSwords' => 0,
                    'soulArrow' => 0,
                    'burstOneHandSword' => 0,
                    'thsFullBurst' => 0,
                    'burstPairSwords' => 0,
                    'burstSpear' => 0,
                    'burstArrow' => 0,
                    'concussion' => 0,
                    'oracleOneHandSword' => 0,
                    'oracleSpear' => 0,
                    'oraclePairSwords' => 0,
                ),
            );

            $encryptedRequestHash1 = userToServerEncrypt(json_encode($plainRequestStart), $defaultIV, $userHash);
            $qStartReturn = requestTemplate($encryptedRequestHash1, 'quest/start', $cookie, $curl);

            /*println("\nsend hash 1 *".$encryptedRequestHash1."*");
            println("\nreturn hash 1 *".$qStartReturn."*");*/

            if (is_null($qStartReturn)) {
                println("Server Error qstart");
                if ($error == 0){
                    $questNr++;
                    $error = 1;
                    continue;
                } else {
                    return null; //Server Error
                }
            }

            $qStartJsonResponse = json_decode(serverToUserDecrypt($qStartReturn, $defaultIV, $userHash), true);

            if ($qStartJsonResponse["error"] != 0){
                println("API error qstart");
                println($qStartJsonResponse["error"]);
                println($encryptedRequestHash1);
                if ($error == 0 && $isShadow == false){
                    $questNr++;
                    $error = 1;
                    continue;
                } else {
                    return null; //Server Error
                }
            }

            $partList = [];
            if (isset($qStartJsonResponse["result"]["enemy"][0]["reward"])){
                foreach($qStartJsonResponse["result"]["enemy"][0]["reward"] as $part){
                    $partList[] = $part["regionId"];
                }
            } else {
                $partList[] = 0;
            }
            
            $plainRequestComplete = array (
                'qt' => $qToken,
                'breakIds0' => $partList,
                'breakIds1' => array (),
                'breakIds2' => array (),
                'breakIds3' => array (),
                'breakIds4' => array (),
                'memids' => array (),
                'mClear' => array (),
                'hpRate' => 0,
                'givenDamageList' => array (),
                'fieldId' => '47548172',
                'logs' => array (),
                'actioncount' => array (
                    'revival' => 0,
                    'guard' => 0,
                    'counter' => 0,
                    'lance' => 0,
                    'combo' => 0,
                    'chargesword' => 0,
                    'chargebow' => 0,
                    'usemagi' => 0,
                    'weak' => 0,
                    'weaponweak' => 0,
                    'death' => 0,
                    'heatTwoHandSword' => 0,
                    'heatPairSwords' => 0,
                    'revengeBurst' => 0,
                    'justGuard' => 0,
                    'shadowSealing' => 0,
                    'jump' => 0,
                    'soulOneHandSword' => 0,
                    'soulTwoHandSword' => 0,
                    'soulSpear' => 0,
                    'soulPairSwords' => 0,
                    'soulArrow' => 0,
                    'burstOneHandSword' => 0,
                    'thsFullBurst' => 0,
                    'burstPairSwords' => 0,
                    'burstSpear' => 0,
                    'burstArrow' => 0,
                    'concussion' => 0,
                    'oracleOneHandSword' => 0,
                    'oracleSpear' => 0,
                    'oraclePairSwords' => 0,
                ),
                'deliveryBattleInfo' => array (
                    'maxDamageSelf' => 0,
                    'totalAttackCount' => 0,
                    'attackCount' => 0,
                    'totalSkillCountList' => array (),
                    'mySkillCountList' => array (),
                    'damageByWeaponList' => array (),
                    'currentDamageByWeaponList' => array (),
                    'playerActionInfoList' => array (),
                ),
                'enemyHp' => 0,
                'remainSec' => 278.5302734375,
                'elapseSec' => 21.4697265625,
                'dc' => 0,
                'dbc' => 0,
                'pdbc' => 0,
                'rHp' => 0,
                'rSec' => 0,
                'wmwave' => 0,
            );

            $encryptedRequestHash2 = userToServerEncrypt(json_encode($plainRequestComplete), $defaultIV, $userHash);

            /* Claims stuff, uses the time spent to calculate the sleep needed so you can use the dead time for something... */
            /*$time1 = time();

            if ($questNr > ($qNum-7)){
            } else {
                for($j = 0; $j<5; $j++){
                    $isComplete = guildQuestComplete($j, $defaultIV, $userHash, $cookie, $curl);

                    if ($isComplete == true){
                        $isStart = guildQuestStart($qId, $j, $defaultIV, $userHash, $cookie, $curl);
                        if ($isStart == true){
                            println("-");
                            $questNr++;

                            if ($questNr > ($qNum-7)){
                                println("max behe");
                                break;
                            }
                        }
                    }
                }

                if($questNr > ($qNum-7)){
                    break;
                }
            }

            $time2 = time();
            $timeLeft = 16-($time2 - $time1);*/

            /* End of claim area... needs work i guess, can be made into a process? */

            //if($isShadow == true){
                sleep(15);
            //}

            $qCompleteReturn = requestTemplate($encryptedRequestHash2, 'quest/complete', $cookie, $curl);

            /*file_put_contents('./log_'.date("j.n.Y").'.txt', json_encode($plainRequestComplete)."\n", FILE_APPEND);
            file_put_contents('./log_'.date("j.n.Y").'.txt', $encryptedRequestHash2."\n", FILE_APPEND);*/

            /*println("\nsend hash 2 *".$encryptedRequestHash2."*");
            println("\nreturn hash 2 *".$qCompleteReturn."*");*/

            if (is_null($qCompleteReturn)) {
                println("\nServer Error qcomplete");
                if ($error == 0 && $isShadow == false){
                    $questNr++;
                    $error = 1;
                    continue;
                } else {
                    return null; //Server Error
                }
            }

            $qcompleteJsonResponse = json_decode(serverToUserDecrypt($qCompleteReturn, $defaultIV, $userHash), true);

            if ($qcompleteJsonResponse["error"] != 0){
                println("API error qcomplete");
                println($qcompleteJsonResponse["error"]);
                if ($error == 0 && $isShadow == false){
                    $questNr++;
                    $error = 1;
                    continue;
                } else {
                    return null; //Server Error
                }
            }

            $questNr++;
            print(".");
        }
        return true;
    }

    function pullBanner($bId, $tickets, $gems, $defaultIV, $userHash, $cookie){
        $plainRequest = '{"id":'.$bId.',"crystalCL":'.$gems.',"ticketCL":'.$tickets.',"productId":"","guaranteeCampaignType":0,"guaranteeCampaignId":0,"guaranteeRemainCount":0,"guaranteeUserCount":0,"useStepUpTicket":0,"seriesId":-1}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $pullResult = requestTemplate($encryptedRequestHash, 'gacha/gacha', $cookie);

        if (is_null($pullResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($pullResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
           return $jsonResponse["diff"];
        } else {
            println("Erro API: ".$jsonResponse["error"]);

            println($encryptedRequestHash);
            return null;
        }
    }

    function claimAll($defaultIV, $userHash, $cookie, $curl){
        $claimResult = requestTemplate(null, 'guild-request/complete-all', $cookie, $curl);

        if (is_null($claimResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
            return $jsonResponse;
        } else {
            if ($jsonResponse["error"] == 5052 || $jsonResponse["error"] == 5022){
                return false;
            } else {
                println("\nError ".$jsonResponse["error"]." On claim all.");
                return null;
            }
        }
    }

    function guildQuestStart($qid, $slotNr, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"slotNo":'.$slotNr.',"questId":'.$qid.',"num":1,"isQuestItem":1}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $claimResult = requestTemplate($encryptedRequestHash, 'guild-request/start', $cookie, $curl);

        if (is_null($claimResult)) {
            println("SERVER ERROR");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
            return true;
        } else {
            if ($jsonResponse["error"] == 5053){
                return false;
            } else {
                println("\nError Starting ".$jsonResponse["error"]." On slot Nr. ".$slotNr);
                return null;
            }
        }
    }

    function guildQuestComplete($slotNr, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"slotNo":'.$slotNr.'}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $claimResult = requestTemplate($encryptedRequestHash, 'guild-request/complete', $cookie, $curl);

        if (is_null($claimResult)) {
            println("SERVER ERROR");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);
        
        if ($jsonResponse["error"] == 0 || $jsonResponse["error"] == 5004){
            return true;
        } else {
            if ($jsonResponse["error"] == 5022 || $jsonResponse["error"] == 5052){
                return false;
            } else {
                println("\nError Completing ".$jsonResponse["error"]." On slot Nr. ".$slotNr);
                return null;
            }
        }
    }

    function checkAlive($defaultIV, $userHash, $cookie){
        $aliveResult = requestTemplate(null, 'status/alive', $cookie);

        if (is_null($aliveResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($aliveResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
           return true;
        } else {
            println("Erro API status alive: ".$jsonResponse["error"]);
            return null;
        }
    }

    function craft($cookie, $curl){
        $claimResult = requestTemplate("V2j2a5viWDRpIwX2xkm1b7SOj1s5SdXFgpXkQdkD6Dc=", 'smith/create', $cookie, $curl);
        //$claimResult = requestTemplate("t3yrsSTUcxk2WrcB/8kotwr1+Hg8Yq1J95431dfaucE=", 'smith/create', $cookie, $curl);
      
        return true;
    }



    function QuestComplete($qNr, $defaultIV, $userHash, $curl){
        $rcToken1 = genRcToken();
        $body = "data=".urlencode(aes256CBCEncrypt('{"uId":"'.$qNr.'"}', $userHash, $defaultIV))."&app=rob&rcToken=$rcToken1";

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://appprd-01.dragonproject.gogame.net/ajax/delivery/complete',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_IPRESOLVE,
        CURL_IPRESOLVE_V4,
        CURLOPT_HTTPHEADER => array(
                "Cookie: robpt=6290dba6f45b2f7dfebb4fc5d9e5674a109fc6ea%3A1",
                "User-Agent: Dalvik/2.1.0 (Linux; U; Android 9; SM-N9600 Build/PPR1.180610.011)",
                "X-Unity-Version: 2018.4.3f1",
                "aidx: 106005",
                "amv: 1",
                "apv: 1.8.1",
                "cdv: -1",
                "dm: samsung SM-N9600",
                "tidx: 18001",
                "tmv: 1",
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "Accept-Encoding: gzip, deflate",
                "Expect: ",
                "Connection: keep-alive"
            ),
        ));

        curl_exec($curl);
        
        return true;
    }
    /* -----============== Process Starters ==============----- */
    function rerollPerfectProcessStart($euid, $aNbr = 0, $defaultIV, $userHash, $cookie){
        $curl = curl_init();
        $plainRequest = '{"euid":"'.$euid.'"}';

        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'smith/getabilitylist', $cookie, $curl);

        if (is_null($response)) {
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0) {
            //if (is_null($aid)){
                if (isset($jsonResponse["result"][$aNbr]["aid"])){
                    //Main ability route
                    $aid = $jsonResponse["result"][$aNbr]["aid"];
                    $maxap = $jsonResponse["result"][$aNbr]["maxap"];
                    $isPerfect = false;

                    while ($isPerfect == false){
                        $isPerfect = rerollPerfectAbility($plainRequest, $aid, $maxap, $defaultIV, $userHash, $cookie, $curl, $euid);

                        if ($isPerfect == true){
                            file_put_contents('./rollList_'.$GLOBALS['server'].'.txt', "Perfected ->".$euid."<- Perfected ; Sv -> ".$GLOBALS['server']."\n", FILE_APPEND);
                        }
                    }
                } else {
                    file_put_contents('./rollList_'.$GLOBALS['server'].'.txt', "Error ->".$euid." ; Sv -> ".$GLOBALS['server']."\n", FILE_APPEND);
                    println(jsonPrettify(json_encode($jsonResponse)));
                    println("$euid - HAS NO REROLLABLE ABILITIES.");
                }
            //} else {
                //Custom ability route
            //}
        } else {
            println('ERROR: '.$jsonResponse["error"]);
        }
    }

    function listRelevantItemsProcess($startingPage, $defaultIV, $userHash, $cookie){
        $curl = curl_init();
        for($i = $startingPage; $i <= ($startingPage+6000); $i){
            $status = listRelevantItems($i, $defaultIV, $userHash, $cookie, $curl);

            if (is_null($status)){
                $log = "\nERRO PAGINA ".$i." ABORTANDO";
                println($log);
                file_put_contents('./log_'.date("j.n.Y").'.txt', $log."\n", FILE_APPEND);
                break;
            }

            if($status != false){
                print "\n";
                foreach ($status as $key=>$value)
                {
                    $log = "\n(".$key.") ID: ".$value.";";
                    println($log);
                    file_put_contents('./log_'.date("j.n.Y").'.txt', $log."\n", FILE_APPEND);
                }
            }

            $log = $i."<-";
            print($log);
            file_put_contents('./log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
            $i++; //Acabou os items coletaveis, prox pagina

        }

        $log = "\nUltima página processada: ".$i;
        println($log);
        file_put_contents('./log_'.date("j.n.Y").'.txt', $log."\n", FILE_APPEND);
    }

    function redeemProcessStart($startingPage, $defaultIV, $userHash, $cookie){
        $curl = curl_init();
        for($i = $startingPage; $i <= ($startingPage+6000); $i){
            $status = redeemGiftBoxItems($i, $defaultIV, $userHash, $cookie, $curl);
            if (is_null($status)){
                println('');
                println("ERRO PAGINA ".$i." ABORTANDO");
                $i++;
                continue;
                //break;
            }

            print ".";

            if ($status){ //Ainda tem item para coletar, não mudar de pagina.
                continue;
            }

            $i++; //Acabou os items coletaveis, prox pagina
            println('');
            println($i."<- Pg Processada");
        }

        println('');
        println("Ultima página processada: ".$i);
    }

    function chugProcessStart($defaultIV, $userHash, $cookie, $id = null){
        $curl = curl_init();
        //1909124 old  sv hunter elixir
        //3308121 new  sv hunter elixir
        if ($id == 1){
            $potionId = 1909124;
            println('Old server elixir');
        }

        if ($id == 2){
            $potionId = 3308121;
            println('New server elixir');
        }

        if (is_null($id)){
            $potionId = 1909124;
            println('Default old elixir');
        }

        if (isset($id) && $id != 1 && $id != 2){
            $potionId = $id;
            println('Custom potion');
        }

        for($i = 0; $i <= 7500; $i){
            $status = chugPotion($potionId, $defaultIV, $userHash, $cookie, $curl);

            if (is_null($status)){
                println('');
                println("ERRO POSICAO ".$i." ABORTANDO");
                break;
            }

            $i++; //Acabou os items coletaveis, prox pagina
            println("GLUG");
        }

        println('');
        println("Ultima pocao chugada: ".$i);
    }


    function dupeProcessStart($defaultIV, $userHash, $cookie, $pgStart = 0, $pgEnd = 900){
        $collectedAtLeastOnce = false;
        for($i = $pgStart; $i <= $pgEnd; $i++){
            $curl = curl_init();
            $plainRequest = '{"page":"'.$i.'"}';

            $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
            $response = requestTemplate($encryptedRequestHash, 'present/list', $cookie, $curl);

            if (is_null($response)) {
                return null; //Server Error
            }

            $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

            if ($jsonResponse["error"] == 0) {
                $presents = $jsonResponse["result"]["presents"];

                while (!empty($presents)){
                    $presents = dupePresents($presents, $defaultIV, $userHash, $cookie, $curl);

                    if (!empty($presents)){
                        $collectedAtLeastOnce = true;
                    }
                }

                println("\nPage $i empty");
            } else {
                println('ERROR: '.$jsonResponse["error"]);
            }
            
            if ($i == $pgEnd && $collectedAtLeastOnce == true){
                $i = 0;
                $collectedAtLeastOnce = false;
            }
        }
    }

    function massacreProcessStart($defaultIV, $userHash, $cookie){
        while(true){
            $curl = curl_init();

            $encryptedRequestHash = userToServerEncrypt(null, $defaultIV, $userHash);
            $response = requestTemplate($encryptedRequestHash, 'quest/list', $cookie, $curl);

            if (is_null($response)) {
                return null; //Server Error
            }

            $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

            if ($jsonResponse["error"] == 0) {
                $questList = $jsonResponse["result"]["order"];

                println("Starting... First Victim:");
                
                foreach($questList as $quest){
                    $qId = $quest["questId"];
                    $qNum = $quest["order"]["num"];

                    /*if ($qNum > 25){
                        continue;
                    }*/

                    if(is_null(ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl))){
                        break;
                    }

                    println("\nNext victim... Target aquired.");
                }

                println("\nHolocausted.");
            } else {
                println('ERROR: '.$jsonResponse["error"]);
            }
        }
    }

    function killProcessStart($defaultIV, $userHash, $cookie, $number, $mId){
        $curl = curl_init();

        println("Starting:");
        if ($number == 0){
            $qidArray = array(
                
            );
            foreach ($qidArray as $quest){
                println("Try: ".$quest);
                ripNTear($quest, 5, $defaultIV, $userHash, $cookie, $curl, true);
            }

        } else {        
            ripNTear($mId, $number, $defaultIV, $userHash, $cookie, $curl, true);
        }

        println("\nDone.");
    }

    function pullProcessStart($defaultIV, $userHash, $cookie, $bId, $tickets, $gems, $type = null){
        $curl = curl_init();

        if (is_null($type)){
            for($i = $tickets; $i >= 50; $i-= 0){
                $status = pullBanner($bId, $i, $gems, $defaultIV, $userHash, $cookie);

                if (is_null($status)){
                    println("Erro... t.".$i);
                } else {
                    $i = (int)$status[0]["item"][0]["update"][0]["num"];
                    println("Tickets remaining. $i");
                }
            }

            println('');
            println("Out of tickets, remaining: ".$i);
        } else {
            for($i = $gems; $i >= 250; $i-= 0){
                $status = pullBanner($bId, $tickets, $i, $defaultIV, $userHash, $cookie);

                if (is_null($status)){
                    println("Erro... t.".$i);
                } else {
                    $i -= 250;
                    println("Gems remaining. $i");
                }
            }

            println('');
            println("Out of gems, remaining: ".$i);
        }
    }

    /*function pirateProcessStart($defaultIV, $userHash, $cookie){
        $curl = curl_init();
        $encryptedRequestHash = userToServerEncrypt('', $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'gold/black-market-item-list', $cookie, $curl);

        if (is_null($response)) {
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0) {
            $wares = $jsonResponse["result"]["items"];
            if (pirateYoink($wares, $defaultIV, $userHash, $cookie, $curl)){
                println('Yoiked');
            } else{
                println('Nothin to yoink at');
            }
        } else {
            println('ERROR: '.$jsonResponse["error"]);
        }
    }*/

    /* -----============== Console Controller ==============----- */
    switch ($argv[1]){
        case "redeem":
            if (isset($argv[2])){
                redeemProcessStart($argv[2], $defaultIV, $userHash, $cookie);
            } else  {
                redeemProcessStart(0, $defaultIV, $userHash, $cookie);
            }

        break;
        case "list":
            if (isset($argv[2])){
                listRelevantItemsProcess($argv[2], $defaultIV, $userHash, $cookie);
            } else  {
                listRelevantItemsProcess(0, $defaultIV, $userHash, $cookie);
            }
        break;
        case "reroll":
            if (isset($argv[2]) && isset($argv[3])){
                rerollPerfectProcessStart($argv[2], $argv[3], $defaultIV, $userHash, $cookie);
            } elseif (isset($argv[2])){
                rerollPerfectProcessStart($argv[2], 0, $defaultIV, $userHash, $cookie);
            }
        break;
        case "chug":
            if (isset($argv[2])){
                chugProcessStart($defaultIV, $userHash, $cookie, $argv[2]);
            } else  {
                chugProcessStart($defaultIV, $userHash, $cookie, null);
            }
        break;
        case "dupe":
            if (isset($argv[2]) && isset($argv[3])){
                dupeProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3]);
            } else {
                dupeProcessStart($defaultIV, $userHash, $cookie);
            }
        break;
        /*case "pirate":
            pirateProcessStart($defaultIV, $userHash, $cookie);*/
        case "massacre":
            if (isset($argv[2]) && isset($argv[3])){
                killProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3]);
            } else  {
                massacreProcessStart($defaultIV, $userHash, $cookie);
            }
        break;
        case "pull":
            if (!isset($argv[5])){
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4]);
            } else {
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4], $argv[5]);
            }
        break;
        case "bigReroll":
            $i = 0;
            $offset = 0;

            if (isset($argv[2])){
                $offset = $argv[2];
            }

            foreach($equipArray as $equip){
                if ($i >= $offset){
                    println($equip);
                    file_put_contents('./rollList_'.$GLOBALS['server'].'.txt', "Started ->".$equip."\n", FILE_APPEND);
                    rerollPerfectProcessStart($equip, 0,$defaultIV, $userHash, $cookie);
                    file_put_contents('./rollList_'.$GLOBALS['server'].'.txt', "Finished ->".$equip."\n", FILE_APPEND);
                }
                $i++;
            }
        break;
        case "questSpam":
            $time1 = microtime(true);
            $i = 0;

            if (isset($argv[2])){
                $i = $argv[2];
            }

            $curl = curl_init();

            for($i; $i <= 5000000; $i++){
                println($i);
                QuestComplete($i, $defaultIV, $userHash, $curl);
            }
        break;
        case "ree":
            $max=5000000;

            $mh = curl_multi_init();
            for($j = $argv[2]; $j <= $max; $j+=10){
                for($i = $j; $i <= $j+10; $i++){
                    $ch[$i] = curl_init();

                    $rcToken1 = genRcToken();
                    $body = "data=".urlencode(aes256CBCEncrypt('{"uId":"'.$i.'"}', $userHash, $defaultIV))."&app=rob&rcToken=$rcToken1";

                    curl_setopt_array($ch[$i], array(
                    CURLOPT_URL => 'http://appprd-01.dragonproject.gogame.net/ajax/delivery/complete',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_IPRESOLVE,
                    CURL_IPRESOLVE_V4,
                    CURLOPT_HTTPHEADER => array(
                            "Cookie: robpt=6290dba6f45b2f7dfebb4fc5d9e5674a109fc6ea%3A1",
                            "User-Agent: Dalvik/2.1.0 (Linux; U; Android 9; SM-N9600 Build/PPR1.180610.011)",
                            "X-Unity-Version: 2018.4.3f1",
                            "aidx: 106005",
                            "amv: 1",
                            "apv: 1.8.1",
                            "cdv: -1",
                            "dm: samsung SM-N9600",
                            "tidx: 18001",
                            "tmv: 1",
                            "Cache-Control: no-cache",
                            "Content-Type: application/x-www-form-urlencoded",
                            "Accept-Encoding: gzip, deflate",
                            "Expect: ",
                            "Connection: keep-alive"
                        ),
                    ));

                    curl_multi_add_handle($mh,$ch[$i]);
                }

                // Executando consulta
                do {
                    curl_multi_exec($mh, $running);
                    curl_multi_select($mh);
                } while ($running > 0);

                // Obtendo dados de todas as consultas e retirando da fila
                foreach(array_keys($ch) as $key){
                    println($key);                    
                    curl_multi_remove_handle($mh, $ch[$key]);
                }
            }

            // Finalizando
            curl_multi_close($mh);
        break;
    }
