<?php
    $defaultIV = '';
    $userHash = '';
    $cookie = '';

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
    function requestTemplate($data = null, $endpoint, $cookie){
        $curl = curl_init();
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
                "X-Unity-Version: 5.6.7f1",
                "aidx: 105005",
                "amv: 1",
                "apv: 1.6.7",
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
    //Unit testing.
    function redeemGiftBoxItems($page, $defaultIV, $userHash, $cookie){
        $plainRequest = '{"page":'.$page.'}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $response = requestTemplate($encryptedRequestHash, 'present/list', $cookie);

        if (is_null($response)) {
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
                //Is vault item
                if (!$ignored && preg_match("/(Obtained\sfrom\sDragon)/", $entry["comment"]))
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
                $response = requestTemplate($encryptedRequestHash, 'present/receive', $cookie);

                if (is_null($response)) {
                    return null; //Server Error
                }

                $response = json_decode(serverToUserDecrypt($response, $defaultIV, $userHash), true);

                if ($response["error"] == 0){
                    return true; //Redeemed at least one item successfully
                }
            } else {
                return false; //No item to be redeemed in this page
            }
        } else {
            return null;
        }
    }

    function redeemProcessStart($startingPage, $defaultIV, $userHash, $cookie){
        for($i = $startingPage; $i <= ($startingPage+100); $i){
            $status = redeemGiftBoxItems($i, $defaultIV, $userHash, $cookie);
            if (is_null($status)){
                println('');
                println("ERRO PAGINA ".$i." ABORTANDO");
                break;
            }

            println('');
            print ".";

            if ($status){ //Ainda tem item para coletar, não mudar de pagina.
                continue;
            }

            $i++; //Acabou os items coletaveis, prox pagina
            println($i."<- Pg Processada");
        }

        println('');
        println("Ultima página processada: ".$i);
    }

    if (isset($argv[1])){
        redeemProcessStart($argv[1], $defaultIV, $userHash, $cookie);
    } else  {
        redeemProcessStart(0, $defaultIV, $userHash, $cookie);
    }
