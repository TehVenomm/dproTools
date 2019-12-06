<?php
    $defaultIV = 'yCNBH$$rCNGvC+#f';
    $userHash = '061dd115161aff9d956bba80768c9332';
    $cookie = 'e1f6a65336c7b896bcbfc0bc06b39099%3A1';

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
    function requestTemplate($data = null, $endpoint, $cookie, $curl = null){
        if (is_null($curl)){
            $curl = curl_init();
        }

        $rcToken1 = genRcToken();
        $body = "app=rob&rcToken=$rcToken1";

        $host = "http://appprd.dragonproject.gogame.net/ajax/$endpoint";

        if (!is_null($data)){
            $body  .= "&data=".urlencode($data);
        }

        curl_setopt_array($curl, array(
        CURLOPT_URL => $host,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
                "Cookie: robpt=$cookie",
                "Host: appprd.dragonproject.gogame.net",
                "User-Agent: Dalvik/2.1.0 (Linux; U; Android 9; SM-N9600 Build/PPR1.180610.011)",
                "X-Unity-Version: 2018.4.3f1",
                "aidx: 106005",
                "amv: 1",
                "apv: 1.8.1",
                "cdv: -1",
                "dm: samsung SM-N9600",
                "tidx: 163",
                "tmv: 1"
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE );

        if ($response == 'error' || $httpCode != 200){
            return null;
        }

        return $response;
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
                    continue;
                }

                if (preg_match("/(Gold)/", $entry["name"]) && $entry["type"] == 2){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }
                //Is vault item
                if (!$ignored /*&& preg_match("/(Obtained\sfrom\sDragon)/", $entry["comment"])*/)
                {
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"];
                }
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
        return $jsonResponse["result"]["list"]["presents"];

    }

    function ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl){
        $questNr = 1;
        while ($questNr <= $qNum){
            $qToken = genRcToken();

            $plainRequestStart = '{"qid": '.$qId.',"qt": "'.$qToken.'","setNo": 27,"crystalCL": 0,"free": 1,"dId": 0,"d": "bb6542934b7e8b9ca8f6e067a0b2b79b6eaa470bba2c66c33f7ef47303172a02a20218166a4b8fce62c6b3a2b30046ef","actioncount": {"revival": 0,"guard": 0,"counter": 0,"lance": 0,"combo": 0,"chargesword": 0,"chargebow": 0,"usemagi": 0,"weak": 0,"weaponweak": 0,"death": 0,"heatTwoHandSword": 0,"heatPairSwords": 0,"revengeBurst": 0,"justGuard": 0,"shadowSealing": 0,"jump": 0,"soulOneHandSword": 0,"soulTwoHandSword": 0,"soulSpear": 0,"soulPairSwords": 0,"soulArrow": 0,"burstOneHandSword": 0,"thsFullBurst": 0,"burstPairSwords": 0,"burstSpear": 0,"burstArrow": 0,"concussion": 0,"oracleOneHandSword": 0,"oracleSpear": 0,"oraclePairSwords": 0}}';

            $encryptedRequestHash = userToServerEncrypt($plainRequestStart, $defaultIV, $userHash);
            $qStartReturn = requestTemplate($encryptedRequestHash, 'quest/start', $cookie, $curl);

            if (is_null($qStartReturn)) {
                println($qStartReturn." response");
                return true; //Server Error
            }

            $qStartJsonResponse = json_decode(serverToUserDecrypt($qStartReturn, $defaultIV, $userHash), true);

            if ($qStartJsonResponse["error"] == 0){
            return true;
            } else {
                print $qStartJsonResponse["error"];
                return null;
            }
            
            $partList = [];
            foreach($qStartJsonResponse["result"]["enemy"]["reward"] as $part){
                $partList[] = $part["regionId"];
            }

            $plainRequestComplete = '{
                "qt": "'.$qToken.'",
                "breakIds0": [
                    '.implode(',', $partList).'
                ],
                "breakIds1": [],
                "breakIds2": [],
                "breakIds3": [],
                "breakIds4": [],
                "memids": [],
                "mClear": [],
                "hpRate": 0,
                "givenDamageList": [],
                "fieldId": "47548172",
                "logs": [],
                "actioncount": {
                    "revival": 0,
                    "guard": 0,
                    "counter": 0,
                    "lance": 0,
                    "combo": 0,
                    "chargesword": 0,
                    "chargebow": 0,
                    "usemagi": 0,
                    "weak": 0,
                    "weaponweak": 0,
                    "death": 0,
                    "heatTwoHandSword": 0,
                    "heatPairSwords": 0,
                    "revengeBurst": 0,
                    "justGuard": 0,
                    "shadowSealing": 0,
                    "jump": 0,
                    "soulOneHandSword": 0,
                    "soulTwoHandSword": 0,
                    "soulSpear": 0,
                    "soulPairSwords": 0,
                    "soulArrow": 0,
                    "burstOneHandSword": 0,
                    "thsFullBurst": 0,
                    "burstPairSwords": 0,
                    "burstSpear": 0,
                    "burstArrow": 0,
                    "concussion": 0,
                    "oracleOneHandSword": 0,
                    "oracleSpear": 0,
                    "oraclePairSwords": 0
                },
                "deliveryBattleInfo": {
                    "maxDamageSelf": 10000,
                    "totalAttackCount": 50,
                    "attackCount": 50,
                    "totalSkillCountList": [],
                    "mySkillCountList": [],
                    "damageByWeaponList": [],
                    "currentDamageByWeaponList": [],
                    "playerActionInfoList": []
                },
                "enemyHp": 10000,
                "remainSec": 278.5302734375,
                "elapseSec": 21.4697265625,
                "dc": 0,
                "dbc": 0,
                "pdbc": 0,
                "rHp": 0,
                "rSec": 0,
                "wmwave": 0
            }';

            $encryptedRequestHash = userToServerEncrypt($plainRequestComplete, $defaultIV, $userHash);
            $qCompleteReturn = requestTemplate($encryptedRequestHash, 'quest/complete', $cookie, $curl);

            if (is_null($qCompleteReturn)) {
                println("Empty Complete");
                return true; //Server Error
            }

            $qStartJsonResponse = json_decode(serverToUserDecrypt($qCompleteReturn, $defaultIV, $userHash), true);

            if ($qStartJsonResponse["error"] == 0){
            return true;
            } else {
                print $qStartJsonResponse["error"];
                return null;
            }
            $questNr++;
            print(".");
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
        $curl = curl_init();
        $plainRequest = '{"page":"0"}';

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

            println('This bitch empty yeet');
        } else {
            println('ERROR: '.$jsonResponse["error"]);
        }
    }

    function massacreProcessStart($defaultIV, $userHash, $cookie){
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

                ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl);
                println("\nNext victim... Target aquired.");
            }

            println('Metal Af.');
        } else {
            println('ERROR: '.$jsonResponse["error"]);
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
    }
