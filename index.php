<?php

    $defaultIV = getenv('IV');
    $userHash = getenv('KEY');
    $cookie = getenv('COOKIE');
    
    if ($defaultIV == false){
        $defaultIV = 'yCNBH$$rCNGvC+#f';
    } 

    if ($userHash == false){
        $userHash = '061dd115161aff9d956bba80768c9332';
    } 

    if ($cookie == false){
        $cookie = 'e1f6a65336c7b896bcbfc0bc06b39099%3A1';
    } 

    $equipArray = array(
    "728419",
    "742588",
    "759837",
    "759839",
    "759840",
    "759841",
    "788783",
    "796581",
    "797925",
    "803116",
    "832288",
    "832771",
    "832772",
    "832773",
    "844292",
    "844293",
    "844294",
    "844295",
    "844296",
    "844297",
    "844298",
    "847617",
    "847884",
    "847906",
    "847907",
    "847908",
    "850947",
    "850948",
    "850949",
    "850950",
    "850951",
    "850952",
    "850954",
    "857891",
    "857892",
    "857893",
    "857894",
    "857896",
    "857898",
    "857900",
    "858346",
    "864512",
    "866846",
    "866847",
    "866848",
    "866849",
    "866850",
    "866851",
    "866857",
    "866858",
    "866859",
    "866860",
    "866861",
    "866862",
    "866863",
    "866864",
    "869693",
    "874073",
    "874517",
    "874518",
    "874519",
    "874521",
    "874523",
    "874525",
    "874527",
    "881743",
    "881744",
    "881745",
    "881746",
    "881747",
    "881748",
    "881749",
    "882023",
    "882024",
    "882025",
    "882026",
    "882027",
    "882181",
    "885308",
    "885309",
    "885310",
    "885311",
    "886927",
    "892838",
    "892839",
    "892840",
    "892841",
    "892842",
    "892843",
    "892849",
    "892894",
    "892895",
    "892897",
    "892900",
    "892902",
    "892908",
    "892910",
    "920172",
    "920173",
    "920174",
    "920175",
    "920176",
    "920177",
    "920178",
    "920179",
    "920180",
    "920181",
    "920182",
    "920183",
    "920184",
    "920185",
    "921717",
    "921718",
    "921719",
    "921720",
    "921721",
    "925630",
    "936971",
    "936972",
    "936973",
    "936974",
    "936979",
    "936980",
    "936981",
    "936983",
    "936984",
    "936985",
    "936986",
    "936987",
    "936988",
    "936989",
    "939028",
    "940049",
    "943626",
    "943627",
    "943628",
    "943629",
    "944037",
    "944039",
    "944521",
    "944522",
    "944523",
    "944525",
    "944527",
    "944529",
    "944531",
    "950627",
    "950629",
    "950630",
    "950632",
    "950633",
    "950634",
    "950635",
    "953287",
    "953501",
    "953941",
    "953942",
    "953943",
    "953944",
    "953945",
    "953946",
    "953947",
    "965533",
    "965534",
    "965535",
    "965536",
    "965537",
    "966490",
    "966491",
    "966492",
    "966493",
    "966494",
    "966495",
    "966496",
    "966497",
    "966503",
    "966504",
    "966505",
    "966506",
    "966507",
    "966508",
    "967654",
    "973288",
    "973289",
    "973290",
    "973291",
    "973292",
    "974284",
    "974471",
    "974472",
    "974473",
    "974474",
    "974475",
    "974476",
    "974477",
    "982037",
    "982038",
    "982039",
    "982040",
    "982041",
    "982042",
    "982043",
    "982052",
    "982053",
    "982472",
    "982473",
    "982474",
    "982475",
    "982818",
    "982819",
    "1075449",
    "1075451",
    "1075452",
    "1075453",
    "1075454",
    "1075455",
    "1075456",
    "1075457",
    "1075458",
    "1075459",
    "1075460",
    "1075461",
    "1075462",
    "1075463");

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
        $host = "http://appprd.dragonproject.gogame.net/ajax/$endpoint";

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
                "tidx: 163",
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
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
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

    function rerollPerfectAbility($plainRequest, $aid, $maxap, $defaultIV, $userHash, $cookie, $curl){
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

            print "\n";

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
            return true;
        } else {
            print $jsonResponse["error"];
            return null;
        }
    }

    function dupePresents($presents, $defaultIV, $userHash, $cookie, $curl){
        foreach ($presents as $present){
            $presentId = $present["uniqId"];

            $plainRequest = '{"uids":["'.$presentId.',1", "'.$presentId.',2", "'.$presentId.',3", "'.$presentId.',4", "'.$presentId.',5", "'.$presentId.',6", "'.$presentId.',7", "'.$presentId.',8", "'.$presentId.',9", "'.$presentId.',10"],"page":0}';
            $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
            $dupeResult = requestTemplate($encryptedRequestHash, 'present/receive', $cookie, $curl);

            println("\nYeeted 10x ".$present["name"]);
        }

        $jsonResponse = json_decode(serverToUserDecrypt($dupeResult, $defaultIV, $userHash), true);
        //println($jsonResponse);
        if (isset($jsonResponse["result"]["list"]["presents"])){
            return $jsonResponse["result"]["list"]["presents"];
        } else {
            return null;
        }

    }

    function ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl){
        $questNr = 0;
        $error = 0;

        //while ($questNr < ($qNum-7)){
        while ($questNr < $qNum){
            $qToken = genRcToken();

            /*println("Qid *".$qId."*");
            println("qtoken *".$qToken."*");*/

            $plainRequestStart = array (
                'qid' => $qId,
                'qt' => $qToken,
                'setNo' => 12,
                'crystalCL' => 0,
                'free' => 1,
                'dId' => 0,
                'd' => 'bb6542934b7e8b9ca8f6e067a0b2b79b6eaa470bba2c66c33f7ef47303172a02a20218166a4b8fce62c6b3a2b30046ed',
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
                if ($error == 0){
                    $questNr++;
                    $error = 1;
                    continue;
                } else {
                    return null; //Server Error
                }
            }

            $partList = [];
            foreach($qStartJsonResponse["result"]["enemy"][0]["reward"] as $part){
                $partList[] = $part["regionId"];
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

            //if($timeLeft >= 0){
                sleep(15);
            //}

            $qCompleteReturn = requestTemplate($encryptedRequestHash2, 'quest/complete', $cookie, $curl);

            /*file_put_contents('./log_'.date("j.n.Y").'.txt', json_encode($plainRequestComplete)."\n", FILE_APPEND);
            file_put_contents('./log_'.date("j.n.Y").'.txt', $encryptedRequestHash2."\n", FILE_APPEND);*/

            /*println("\nsend hash 2 *".$encryptedRequestHash2."*");
            println("\nreturn hash 2 *".$qCompleteReturn."*");*/

            if (is_null($qCompleteReturn)) {
                println("\nServer Error qcomplete");
                if ($error == 0){
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
                if ($error == 0){
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

    function pullBanner($bId, $tickets, $gems, $defaultIV, $userHash, $cookie, $curl){
        $plainRequest = '{"id":'.$bId.',"crystalCL":'.$gems.',"ticketCL":'.$tickets.',"productId":"","guaranteeCampaignType":0,"guaranteeCampaignId":0,"guaranteeRemainCount":0,"guaranteeUserCount":0,"useStepUpTicket":0,"seriesId":-1}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $pullResult = requestTemplate($encryptedRequestHash, 'gacha/gacha', $cookie, $curl);

        if (is_null($pullResult)) {
            println("SERVER ERROR");
            return true; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($pullResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0){
           return $jsonResponse["diff"];
        } else {
            println("Erro API: ".$jsonResponse["error"]);
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
            println("\ntrue ".$slotNr);
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
    /* -----============== Process Starters ==============----- */
    function rerollPerfectProcessStart($euid, $aid = null, $defaultIV, $userHash, $cookie){
        $curl = curl_init();
        $plainRequest = '{"euid":"'.$euid.'"}';

        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'smith/getabilitylist', $cookie, $curl);

        if (is_null($response)) {
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] == 0) {
            if (is_null($aid)){
                //Main ability route
                $aid = $jsonResponse["result"][0]["aid"];
                $maxap = $jsonResponse["result"][0]["maxap"];
                $isPerfect = false;

                while ($isPerfect == false){
                    $isPerfect = rerollPerfectAbility($plainRequest, $aid, $maxap, $defaultIV, $userHash, $cookie, $curl);
                }
            } else {
                //Custom ability route
            }
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
                break;
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

    function chugProcessStart($defaultIV, $userHash, $cookie){
        $curl = curl_init();

        for($i = 0; $i <= 7500; $i){
            $status = chugPotion('337205', $defaultIV, $userHash, $cookie, $curl);

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


    function dupeProcessStart($defaultIV, $userHash, $cookie){
        for($i = 0; $i<=300; $i++){
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
                }

                println("\nPage $i empty");
            } else {
                println('ERROR: '.$jsonResponse["error"]);
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

                    /*if ($qNum <= 7){
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

    function pullProcessStart($defaultIV, $userHash, $cookie, $bId, $tickets, $gems, $type = null){
        $curl = curl_init();

        if (is_null($type)){
            for($i = $tickets; $i >= 50; $i-= 0){
                $status = pullBanner($bId, $tickets, $gems, $defaultIV, $userHash, $cookie, $curl);


                if (is_null($status)){
                    println('');
                    println("ERRO TICKET ".$i." ABORTANDO");
                    break;
                }

                $i = (int)$status[0]["item"][0]["update"][0]["num"];
                println($status[0]["item"][0]["update"][0]["num"]." Tickets remaining. $i");
            }

            println('');
            println("Out of tickets, remaining: ".$i);
        } else {
            for($i = $gems; $i >= 250; $i-= 250){
                $status = pullBanner($bId, $tickets, $gems, $defaultIV, $userHash, $cookie, $curl);

                if (is_null($status)){
                    println('');
                    println("ERRO GEMS ".$i." ABORTANDO");
                    break;
                }

                println($status[0]["status"][0]["crystal"][0]." Gems remaining.");
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
                rerollPerfectProcessStart($argv[2], null,$defaultIV, $userHash, $cookie);
            }
        break;
        case "chug":
            chugProcessStart($defaultIV, $userHash, $cookie);
        break;
        case "dupe":
            dupeProcessStart($defaultIV, $userHash, $cookie);
        break;
        /*case "pirate":
            pirateProcessStart($defaultIV, $userHash, $cookie);*/
        case "massacre":
            massacreProcessStart($defaultIV, $userHash, $cookie);
        break;
        case "pull":
            if (!isset($argv[5])){
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4]);
            } else {
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4], $argv[5]);
            }
        break;
        case "bigReroll":
            foreach($equipArray as $equip){
                println($equip);
                file_put_contents('./rollList_'.date("j.n.Y").'.txt', $equip."\n", FILE_APPEND);
                rerollPerfectProcessStart($equip, null,$defaultIV, $userHash, $cookie);
            }
        break;
    }
