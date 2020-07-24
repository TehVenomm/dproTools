<?php
	//These are the variables used to connect to the server, and to my account
	//By default it takes the enviroment variable set within heroku, to make it easier to change on the fly in case i am not using my computer.
    $GLOBALS['server'] = getenv('SERVER');
    $defaultIV = getenv('IV');
    $userHash = getenv('KEY');
    $cookie = getenv('COOKIE');
    $GLOBALS['tidx'] = getenv('TIDX');
    $GLOBALS['host'] = getenv('HOST');
    $GLOBALS['d'] = getenv('D');
	
	//If it does not find an enviromental variable, then it sets up defaults, 
	//and assuming i am on my personal computer, i choose here whether i connect to linton or pamela servers. (1 old server, 2 new server)
    if($GLOBALS['server']  == false){
        $GLOBALS['server'] = 1;
    }
    
	//Here it sets up my account credentials to be used within the server connections further on.
	//Old server creds
    if ($GLOBALS['server'] == 1){
        if ($defaultIV == false){
			//This value is basically the one of the passwords used to decrypt the communications betwen the server/client.
            $defaultIV = 'yCNBH$$rCNGvC+#f';
        } 

        if ($userHash == false){
			//This is the value used to authenticate your account to the server. Changes whenever you login onto a fresh install to stop people from using multiple phones at once.
			//Not like it is hard to do that anyways... lol. Phone + multiple nox instances + this script using the same account isn't impossible, just annoying to set up.
			//In specific it is the key to encrypt/decrypt the contents passed between server/game. Changes for every account, so you can't read other player's network traffic without this.
			//You can only obtain this value through: 
			//1- Memory reading (it is saved in plaintext somewhere in the memory, just use gameguardian and search for userhash. Needs root.); 
			//2- Sniff network traffic for the first login onto a fresh install and then use the default userhash to grab the new hash being exchanged. (beware, this invalidades all other active userhashes.)
			//3- Grab an encrypted file containing the account values within the game's folders, paste it onto a rooted device's game folder and then read it's memory (Doesn't need root, can be pasted onto a nox virtual device, useful on pre-set accounts on unrooted devices)
            $userHash = '061dd115161aff9d956bba80768c9332-0';
        } 

        if ($cookie == false){
			//Another value used in authentication
            $cookie = 'e1f6a65336c7b896bcbfc0bc06b39099%4A1';
        } 

        if ($GLOBALS['tidx'] == false){
			//Value used to check the version of the content inside the app, bit complicated but the short is that this goes up every time new content is put in the server, then the app downloads it if the numbers don't match
            $GLOBALS['tidx'] = '163';
        } 

        if ($GLOBALS['host'] == false){
			//Old server address, used to talk to the game.
            $GLOBALS['host'] = 'http://appprd.dragonproject.gogame.net/ajax/';
        }

        if ($GLOBALS['d'] == false){
			//Uh, i forgot where this is from... Another bit of authentication i suppose, not really important. Copying the value used in the app is good enough.
            $GLOBALS['d'] = 'bb6542934b7e8b9ca8f6e067a0b2b79b6eaa470bba2c66c33f7ef47303172a02a20218166a4b8fce62c6b3a2b30046ed-0';
        }
		
		//This is a list of item IDs i selected,
		//A function that automatically perfectly rerolls abilities in items uses this as an item list to go through.
		//I manually list the items here since having the program automatically fetch them was annoying just thinking about it...
		//Every time you open the game it gives you a giant list of crap in your account to get your app from zero to having everything in your account, (anything you can think of, items, magis, potions, money, options, etc, etc)
		//Older accounts have this list be literal >100k lines long. And given to you all at once. Fucking ridiculous just thinking about it.
        $equipArray = array(
            "1080534",
			"1080535",
			"1080536",
			"1080537",
			"1080545",);
    }
	
	//New server creds. Everything is the same as old server creds, just a different account.
    if ($GLOBALS['server'] == 2){
        if ($defaultIV == false){
            $defaultIV = 'yCNBH$$rCNGvC+#f';
        } 

        if ($userHash == false){
            $userHash = '1833b33428bd3119bbfe7d1fef0d2114-1';
        } 

        if ($cookie == false){
            $cookie = '6290dba6f45b2f7dfebb4fc5d9e5674a109fc6ea%5A1';
        } 

        if ($GLOBALS['tidx'] == false){
            $GLOBALS['tidx'] = '18001';
        } 

        if ($GLOBALS['host'] == false){
            $GLOBALS['host'] = 'http://appprd-01.dragonproject.gogame.net/ajax/';
        }

        if ($GLOBALS['d'] == false){
            $GLOBALS['d'] = 'fb630a79aea07fba5bdff5a5ac5b56c55f070d5a5fdc011cc559d57c5b4267bd69bdb197a340de2f86f3408058dff066-1';
        }

        $equipArray = array(
        );
    }
	
	//Doesn't really matter if my credentials go out there, they change every time you login your account onto a fresh install of the game (even a cleared cache resets it)
	//So it is essential to capture the user hash & cookie values from whatever account you're trying to spoof.
	//One account is banned and i don't use the other anymore. Whatever...
    

    /* -----============== Basic tools ==============----- */
	//I use this as a generic print function since i need to both print onto heroku's HTML console and my PC's command prompt.
    function println ($string_message) {
        if(!isset($_SERVER['SERVER_PROTOCOL'])){
            print "$string_message\n";
        } else {
            $_SERVER['SERVER_PROTOCOL'] ? print "$string_message<br />" : print "$string_message\n";
        }
    }
	
	//Converts string to base64 format.
    function toBase64($string){
        return base64_encode($string);
    }
	
	//Converts base64 string to plain text
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

    //Probably unneeded, but converts string to hex, and concatenates onto a single string for returning
    function strToHex($x)
    {
        $s='';
        foreach (str_split($x) as $c) $s.=sprintf("%02X",ord($c));
        return($s);
    }

    //Takes plain String, returns Base64 encrypted (App -> server communication)
    function aes256CBCEncrypt($string, $key, $iv)
    {
        $method = 'aes-256-cbc';
        $encrypted = openssl_encrypt($string, $method, $key, false, $iv);

        return $encrypted;
    }

    //Takes encrypted Base64, returns plain string (garbage if compressed, needs inflation) (Server -> app communication)
    function aes256CBCDecrypt($string, $key, $iv)
    {
        $method = 'aes-256-cbc';
        $decrypted = openssl_decrypt($string, $method, $key, false, $iv);

        return $decrypted;
    }

    //Takes plain string and returns raw compressed
	//This is one of the steps needed to impersonate the server on the phone's app... not used since this is not the objective of this program. 
	//But is is useful to know hijacking responses is possible, but i forgot in which bits of the game this is useful... Stole the server response to a roll and set it all as SSS behemoths just to sate curiosity, but it's only visual anyways.
    function compress($string){
        return zlib_encode($string);
    }

    //Takes raw compressed and returns plain string (Max 64 MB, probably not an issue?)
    function inflate($string){
        return  zlib_decode($string, 1024 * 1024 * 64);
    }

    //RcToken is an MD5 hash of the current datetime to the millisecond, used to separate connection retries from new requests to save server CPU time.
    //Needs to be different from all others within a period of time from my account, otherwise the server just thinks it is just a retry of a previous request, and gives me the same results, doesn't even matter if a different address is used, bti annoying.
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
	//These methods are the very base of communications between the game and the server.
	//Game -> Server; The game needs to pass a json string onto an aes256 encryption before passing it onto the server.
	//Server -> Game; The game needs to use the aes256 decryption, inflate the compressed string using zlib, then either convert it from base64 onto JSON or use as is, depends on what you are doing, to understand what the server said.
	
	
    //Takes returned server hash, decrypts, inflates, prettifies and returns a prettified JSON String
	//Used to convert a server response into readable text.
	//The encryption routine was just so annoying to figure out... ugh
    function serverToUserDecrypt($hash, $defaultIV, $userHash){
        $decryptRaw = aes256CBCDecrypt($hash, $userHash, $defaultIV);
        $stringInflated = inflate($decryptRaw);
        return $stringInflated;
    }

    //Takes a JSON String, minifies, encrypts and returns a base64 hash
	//Used to convert a string onto an encrypted hash the server can use.
    function userToServerEncrypt($string, $defaultIV, $userHash){
        $string = jsonMinify($string);
        $encryptedHash = aes256CBCEncrypt($string, $userHash, $defaultIV);
        return $encryptedHash;
    }

    /* -----============== Complex Tasks ==============----- */
	//I had too many things in my gift box for various reasons... (literally >20k pages at one point lol)
	//This function goes page by page of my giftbox and redeems everything that is not an exeption.
	//The exceptions are treated inside the function (gems,tickets,magis,etc...) and wether each exception is active changes on personal preference,
	//Sometimes i have too many tickets, sometimes i want to print screen all of my gift box gems all at once, sometimes i want to check what magis i've won after 10k rolls on the vault.
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
                    $uniqIdArray[] = $entry["uniqId"]; 
                    continue;
                }
                //Is tablet
                if (preg_match("/(\sTablet\sx\s1)/", $entry["name"]) && $entry["type"] == 3 ){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; 
                    continue;
                }
                //Is Magi
                if (preg_match("/(Lv1\sx\s1)/", $entry["name"]) && $entry["type"] == 5 ){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; 
                    continue;
                }

                if (preg_match("/(Gems)/", $entry["name"]) && $entry["type"] == 1 ){
                    /*$redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; */
                    $ignored = true;
                    continue;
                }

                if (preg_match("/(Crystal)/", $entry["name"]) && $entry["type"] == 1 ){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; 
                    continue;
                }

                if (preg_match("/(Obtained\sin\sSummon)/", $entry["comment"]) && $entry["type"] == 6 ){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; //comment these once you have enough gold
                    continue;
                }

                if (preg_match("/(Gold)/", $entry["name"]) && $entry["type"] == 2){
                    $redeemed = true;
                    $uniqIdArray[] = $entry["uniqId"]; 
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

	//Basically the same as the above, but instead of redeeming things, it just thrawls through and saves the item names i want inside a text file/prints onto the console.
	//Like i said, 20k pages and i wanna know if any good magi or tablet is inside for me to brag about.
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
	
	//Takes an item ID and rerolls the selected ability (each item has many abilities, each ability has an ID number) onto a perfect roll.
	//Always rolls for max +/+ values for selected ability, works if it is one, 2 or even 3+ rollable ability items at once (Some older items have 3 rollable abilities, spooky, this has no real limit). 
	//One of my neatest creations if i do say so myself. This rolls literally as fast as the server lets me, so it uses money real quick. Good way to get rid of the vault gold that piles up so fast.
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

	//Takes one specific potion ID and just drinks it all.
	//The login setup is stupid and the server hands an incredibly long list of options and item numbers for the account each time you open the game (mine had over 100k lines at one point), older accounts suffer the risk of being unable to login due to the list being literally too big to be handled by the server, so instead of the list the server just hands you the error (hahaha, hilarious)
	//Anything with an expiration date is treated as an individual item and uses a large amount of space in the list (instead of Item ID: 123 - Has 37 of these; they go item 1 - expires date XXX, item 2 - expires date XXY; Even though item 1 & 2 are the same type of item.)
	//Hunter potions (And tickets) are one of those expensive (login wise) items, spamming the vault basically gave me >10k per week of every potion so i had to just dump them down the drain or risk being locked out of the game and needing to delete items blindly through my program to regain access.
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

	//This thrawls through my gift box and dupes the selected items.
	//If you redeemed the same item in the gift box multiple times at once, the server would give you the item as many times as you could fit in the request. It never checked wheter the second or third item you were redeeming had already been claimed if it happened before it finished redeeming everything (hahaha)
	//After a bit they caught on and limited it to 10 items at once (instead of duping one "5 gems" gift box entry 500 times, i could only do it 10 times). How sad.
	//A few weeks before i got banned they caught on and simply discarted any repeat ID number inside each request. I tried to get around it using a few PHP json string -> number trickery but they checked it at a latter part of the process so it wasn't feasible.
	//So this doesn't work anymore.
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

	//Basically... Well, it kills behemohts in a few different ways.
	//Put in a quest ID number here (each behemoth battle has its own quest ID, be it a summon, a shadow, a behemoth between map regions, an expedition behemoth, etc. Just gotta find the number of whatever you need killing.)
	//Put in the amount of times you want to have it be killed, then it'll go though the slaying process.
	//There is a list of things that must be done for each battle, and this process can only go start->finish one at a time per account, that's why you cannot play the game on multiple devices
	//Each time you start a battle it cancels the old one, so you can only kill one behemoth at a time, unfortunate.
	//Shadow behemoths can be killed an infinite number of times if you go through this and ignore the greyed out button in the game, the server doesnt check wether you're done with them or not.
	//Quest behemoths (those between areas) can also be killed an infinite number of times, you just can't access them. It was useful in farming EXP & Gold through selling behemoth items.
	//Also useful in farming HC tower points & crafting materials to be sold, just select some behemoth and leave your hunter to kill it all night long.
	//Snugly inside of this is also incorporated another function, before every kill it checks the guild hunter slots, claims any complete behemoth and then refills it with your summoned behemoths. Absolutely excellent way of burning through those annoying A & B rank fodder
	//Also proud of this one.
    function ripNTear($qId, $qNum, $defaultIV, $userHash, $cookie, $curl, $isShadow = false){
        $questNr = 0;
        $error = 0;

        while ($questNr < ($qNum)){
            $qToken = genRcToken();

            if ($questNr < ($qNum-5) && $isShadow == false){
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

	//Put in how many gams and tickets you have (needs to be exact otherwise the game gives you an error) and this will automatically pull onto a selected banner (you need to sniff the network traffic to figure out the banner ID you want to pull) until you run out of gems or tickets.
	//I had too many tickets to i used this to just dump them onto banners. This unfortunately led me to have too many behemoths in my gift box, double edged sword.
	//I basically pulled all of my banners using this, the manual pull process gets annoying once you have an infinite number of gems/tickets... lol
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

	//Tells the server to claim all of the complete behemoths inside of the hunter slots. Simple.
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
	
	//Uses the supplied behemoth ID (needs to be a summoned behemoth... actually i never tested wether this works with other behemoths, huh, i'll never know) and tells the server to start it onto the selected hunter slot
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
	
	//Claims the rewards of one hunter slot in specific if it has been completed.
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
	
	//Basically just asks the server if it is still online. Had to use it on a test but this isn't useful for anything anymore.
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

	//Not very dynamic, basically crafts items, but since i only really had to spam craft a single item ever i just manually encrypted the item ID, tablet, etc to be sent to the server.
	//Why? This is spicy, basically there were a few items that when transformed back into their tablets (Tablet reforge worked if you talked directly to the server, they just never implemented the button inside of the game hahaha) instead of giving up the item, returned a development item.
	//This item was basically an "anything tablet", i could craft literally any SS or SSS equipment in the game with it. I basically spent ungodly amounts of gems/tickets onto... SSS valdemere if i recall correctly? crafted the helmed and then reforged it back into it's tablet form to receive the dev item.
	//I made a few fun weapons in the new server, like the lightning and water oracle dual blades set when people only had access to normal type weapons in linton server, haha hilarious.
    function craft($cookie, $curl){
        $claimResult = requestTemplate("V2j2a5viWDRpIwX2xkm1b7SOj1s5SdXFgpXkQdkD6Dc=", 'smith/create', $cookie, $curl);
        //$claimResult = requestTemplate("t3yrsSTUcxk2WrcB/8kotwr1+Hg8Yq1J95431dfaucE=", 'smith/create', $cookie, $curl);
      
        return true;
    }

	//This never really worked, i thought about running though all of the quest ids (they're all unique, even the daily quests) and then trying to claim each and every one of them incase i had unclaimed rewards from the past. Never really went anywhere.
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

	//This is a spicy one...
	//This basically recreates any one arena run i've captured through a network sniffer and spams it as fast as is can.
	//Very useful to farm gold/exp/shop points when you're fighting the SSS arena level behemoths. You're basically killing a lv 900 behemoth per second, chug some 5x exp & gold potions and that's some good loot.
    function arenaComplete($defaultIV, $userHash, $cookie, $curl){
        $token = genRcToken();
        $plainRequest = '{"aid": 23006,"qid": 910000000,"qt": "'.$token.'","setNo": 0}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        /*$claimResult = */requestTemplate($encryptedRequestHash, 'arena/start', $cookie, $curl);

        /*if (is_null($claimResult)) {
            println("SERVER ERROR START");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError start ".$jsonResponse["error"]);
            return null;
        }*/

        print("-");

        $plainRequest = '{"wave": 1,"qt": "'.$token.'","remainMilliSec": 707591,"elapseMilliSec": 12408,"breakIds": [0,3,6,7,8],"enemyHp": 1971599,"logs": [{"leaveCnt": 0,"userId": 1831197,"name": "â.¤Ayanamié.¬â.¢","baseId": 0,"objId": 99999,"isNpc": false,"hostUserId": 1831197,"startRemaindTime": 717.808288574219,"atkInfos": [{"name": "PLC02_attack_36_1","count": 4,"damage": 235812,"skillId": 0},{"name": "PLC02_attack_36_2","count": 5,"damage": 345871,"skillId": 0},{"name": "PLC02_attack_37_1","count": 4,"damage": 156354,"skillId": 0},{"name": "PLC02_attack_37_2","count": 4,"damage": 156354,"skillId": 0},{"name": "PLC02_attack_37_3","count": 4,"damage": 156354,"skillId": 0},{"name": "PLC02_attack_37_4","count": 4,"damage": 156354,"skillId": 0},{"name": "PLC02_attack_38","count": 4,"damage": 581848,"skillId": 0},{"name": "PLC02_attack_35","count": 4,"damage": 234536,"skillId": 0}]}],"actioncount": {"revival": 0,"guard": 0,"counter": 0,"lance": 0,"combo": 0,"chargesword": 0,"chargebow": 0,"usemagi": 0,"weak": 0,"weaponweak": 0,"death": 0,"heatTwoHandSword": 0,"heatPairSwords": 0,"revengeBurst": 0,"justGuard": 0,"shadowSealing": 0,"jump": 0,"soulOneHandSword": 0,"soulTwoHandSword": 0,"soulSpear": 0,"soulPairSwords": 0,"soulArrow": 0,"burstOneHandSword": 0,"thsFullBurst": 0,"burstPairSwords": 0,"burstSpear": 0,"burstArrow": 0,"concussion": 0,"oracleOneHandSword": 0,"oracleSpear": 1,"oraclePairSwords": 0},"deliveryBattleInfo": {"maxDamageSelf": 208622,"totalAttackCount": 37,"attackCount": 37,"totalSkillCountList": [{"skillId": 203700000,"totalCount": 1}],"mySkillCountList": [{"skillId": 203700000,"totalCount": 1}],"damageByWeaponList": [{"equipmentType": 2,"spAttackType": 4,"damage": 2225859},{"equipmentType": 2,"spAttackType": 4,"damage": 0},{"equipmentType": 2,"spAttackType": 4,"damage": 0}],"currentDamageByWeaponList": [{"equipmentType": 2,"spAttackType": 4,"damage": 2225859},{"equipmentType": 2,"spAttackType": 4,"damage": 0},{"equipmentType": 2,"spAttackType": 4,"damage": 0}],"playerActionInfoList": [{"actionType": 28,"totalDamage": 0,"totalCount": 1}]}}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        /*$claimResult = */requestTemplate($encryptedRequestHash, 'arena/progress', $cookie, $curl);

        /*if (is_null($claimResult)) {
            println("SERVER ERROR PROGRESS 1");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError progress 1".$jsonResponse["error"]);
            return null;
        }*/

        print("-");
        
        $plainRequest = '{"wave": 2,"qt": "'.$token.'","remainMilliSec": 725841,"elapseMilliSec": 31749,"breakIds": [0,1,6],"enemyHp": 1971599,"logs": [{"leaveCnt": 0,"userId": 1831197,"name": "â.¤Ayanamié.¬â.¢","baseId": 0,"objId": 99999,"isNpc": false,"hostUserId": 1831197,"startRemaindTime": 717.808288574219,"atkInfos": [{"name": "PLC02_attack_36_1","count": 16,"damage": 486622,"skillId": 0},{"name": "PLC02_attack_36_2","count": 16,"damage": 616685,"skillId": 0},{"name": "PLC02_attack_37_1","count": 15,"damage": 311165,"skillId": 0},{"name": "PLC02_attack_37_2","count": 15,"damage": 304973,"skillId": 0},{"name": "PLC02_attack_37_3","count": 14,"damage": 283902,"skillId": 0},{"name": "PLC02_attack_37_4","count": 13,"damage": 298779,"skillId": 0},{"name": "PLC02_attack_38","count": 12,"damage": 1170343,"skillId": 0},{"name": "PLC02_attack_35","count": 17,"damage": 522523,"skillId": 0},{"name": "PLC02_attack_40_2_1","count": 1,"damage": 10085,"skillId": 0}]},{"leaveCnt": 0,"userId": 1831197,"name": "Beguiling Ayame","baseId": 900018402,"objId": 500001,"isNpc": false,"hostUserId": 1831197,"startRemaindTime": 753.407958984375,"atkInfos": [{"name": "atk7","count": 1,"damage": 0,"skillId": 0},{"name": "atk9","count": 1,"damage": 0,"skillId": 0},{"name": "atk_back","count": 4,"damage": 196,"skillId": 0},{"name": "atk5","count": 1,"damage": 492,"skillId": 0},{"name": "atk6","count": 1,"damage": 73,"skillId": 0}]}],"actioncount": {"revival": 0,"guard": 0,"counter": 0,"lance": 0,"combo": 0,"chargesword": 0,"chargebow": 0,"usemagi": 1,"weak": 1,"weaponweak": 0,"death": 0,"heatTwoHandSword": 0,"heatPairSwords": 0,"revengeBurst": 0,"justGuard": 0,"shadowSealing": 0,"jump": 0,"soulOneHandSword": 0,"soulTwoHandSword": 0,"soulSpear": 0,"soulPairSwords": 0,"soulArrow": 0,"burstOneHandSword": 0,"thsFullBurst": 0,"burstPairSwords": 0,"burstSpear": 0,"burstArrow": 0,"concussion": 0,"oracleOneHandSword": 0,"oracleSpear": 0,"oraclePairSwords": 0},"deliveryBattleInfo": {"maxDamageSelf": 154852,"totalAttackCount": 93,"attackCount": 93,"totalSkillCountList": [{"skillId": 203700000,"totalCount": 1}],"mySkillCountList": [{"skillId": 203700000,"totalCount": 1}],"damageByWeaponList": [{"equipmentType": 2,"spAttackType": 4,"damage": 1984088},{"equipmentType": 2,"spAttackType": 4,"damage": 0},{"equipmentType": 2,"spAttackType": 4,"damage": 0}],"currentDamageByWeaponList": [{"equipmentType": 2,"spAttackType": 4,"damage": 1984088},{"equipmentType": 2,"spAttackType": 4,"damage": 0},{"equipmentType": 2,"spAttackType": 4,"damage": 0}],"playerActionInfoList": [{"actionType": 1,"totalDamage": 27877,"totalCount": 1}]}}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        /*$claimResult = */requestTemplate($encryptedRequestHash, 'arena/progress', $cookie, $curl);

        /*if (is_null($claimResult)) {
            println("SERVER ERROR PROGRESS 2");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError progress 2".$jsonResponse["error"]);
            return null;
        }*/

        print("-");
        
        $plainRequest = '{"wave":3,"qt":"'.$token.'","remainMilliSec":696422,"elapseMilliSec":59419,"breakIds":[0,2,7],"enemyHp":1478699,"logs":[{"leaveCnt":0,"userId":1831197,"name":"â.¤Ayanamié.¬â.¢","baseId":0,"objId":99999,"isNpc":false,"hostUserId":1831197,"startRemaindTime":717.808288574219,"atkInfos":[{"name":"PLC02_attack_36_1","count":23,"damage":702460,"skillId":0},{"name":"PLC02_attack_36_2","count":25,"damage":940271,"skillId":0},{"name":"PLC02_attack_37_1","count":25,"damage":518926,"skillId":0},{"name":"PLC02_attack_37_2","count":22,"damage":459143,"skillId":0},{"name":"PLC02_attack_37_3","count":20,"damage":407238,"skillId":0},{"name":"PLC02_attack_37_4","count":19,"damage":422115,"skillId":0},{"name":"PLC02_attack_38","count":18,"damage":1787281,"skillId":0},{"name":"PLC02_attack_35","count":24,"damage":738361,"skillId":0},{"name":"PLC02_attack_40_2_1","count":7,"damage":110699,"skillId":0},{"name":"sk_light_mulchsword_1st_hit","count":7,"damage":934475,"skillId":100200501}]},{"leaveCnt":0,"userId":1831197,"name":"Beguiling Ayame","baseId":900018402,"objId":500001,"isNpc":false,"hostUserId":1831197,"startRemaindTime":753.407958984375,"atkInfos":[{"name":"atk7","count":1,"damage":0,"skillId":0},{"name":"atk9","count":1,"damage":0,"skillId":0},{"name":"atk_back","count":4,"damage":196,"skillId":0},{"name":"atk5","count":1,"damage":492,"skillId":0},{"name":"atk6","count":1,"damage":73,"skillId":0},{"name":"atk01_S","count":1,"damage":0,"skillId":0},{"name":"atk35","count":1,"damage":0,"skillId":0},{"name":"atk02_2","count":1,"damage":96,"skillId":0},{"name":"atk03_S","count":1,"damage":1379,"skillId":0},{"name":"atk06_S","count":1,"damage":965,"skillId":0},{"name":"atk09_2","count":4,"damage":492,"skillId":0},{"name":"atk08","count":1,"damage":0,"skillId":0},{"name":"atk09_3","count":2,"damage":98,"skillId":0},{"name":"atk09","count":1,"damage":0,"skillId":0}]}],"actioncount":{"revival":0,"guard":0,"counter":0,"lance":0,"combo":0,"chargesword":0,"chargebow":0,"usemagi":6,"weak":2,"weaponweak":0,"death":0,"heatTwoHandSword":0,"heatPairSwords":0,"revengeBurst":0,"justGuard":0,"shadowSealing":0,"jump":0,"soulOneHandSword":0,"soulTwoHandSword":0,"soulSpear":0,"soulPairSwords":0,"soulArrow":0,"burstOneHandSword":0,"thsFullBurst":0,"burstPairSwords":0,"burstSpear":0,"burstArrow":0,"concussion":0,"oracleOneHandSword":0,"oracleSpear":0,"oraclePairSwords":0},"deliveryBattleInfo":{"maxDamageSelf":219881,"totalAttackCount":76,"attackCount":76,"totalSkillCountList":[{"skillId":203700000,"totalCount":2},{"skillId":306620000,"totalCount":3},{"skillId":100200501,"totalCount":1}],"mySkillCountList":[{"skillId":203700000,"totalCount":2},{"skillId":306620000,"totalCount":3},{"skillId":100200501,"totalCount":1}],"damageByWeaponList":[{"equipmentType":2,"spAttackType":4,"damage":2107546},{"equipmentType":2,"spAttackType":4,"damage":0},{"equipmentType":2,"spAttackType":4,"damage":0}],"currentDamageByWeaponList":[{"equipmentType":2,"spAttackType":4,"damage":2107546},{"equipmentType":2,"spAttackType":4,"damage":0},{"equipmentType":2,"spAttackType":4,"damage":0}],"playerActionInfoList":[{"actionType":1,"totalDamage":55827,"totalCount":2}]}}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        /*$claimResult = */requestTemplate($encryptedRequestHash, 'arena/progress', $cookie, $curl);

        /*if (is_null($claimResult)) {
            println("SERVER ERROR PROGRESS 3");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError progress 3".$jsonResponse["error"]);
            return null;
        }*/

        print(".");
        /*sleep(5);
        
        $plainRequest = '{"wave":4,"qt":"'.$token.'","remainMilliSec":628990,"elapseMilliSec":97431,"breakIds":[0,1],"enemyHp":2957399,"logs":[{"leaveCnt":0,"userId":1831197,"name":"â.¤Ayanamié.¬â.¢","baseId":0,"objId":99999,"isNpc":false,"hostUserId":1831197,"startRemaindTime":717.808288574219,"atkInfos":[{"name":"PLC02_attack_36_1","count":41,"damage":994306,"skillId":0},{"name":"PLC02_attack_36_2","count":43,"damage":1280985,"skillId":0},{"name":"PLC02_attack_37_1","count":42,"damage":713756,"skillId":0},{"name":"PLC02_attack_37_2","count":39,"damage":664233,"skillId":0},{"name":"PLC02_attack_37_3","count":37,"damage":653348,"skillId":0},{"name":"PLC02_attack_37_4","count":34,"damage":637477,"skillId":0},{"name":"PLC02_attack_38","count":35,"damage":2674471,"skillId":0},{"name":"PLC02_attack_35","count":45,"damage":1322277,"skillId":0},{"name":"PLC02_attack_40_2_1","count":11,"damage":110723,"skillId":0},{"name":"sk_light_mulchsword_1st_hit","count":7,"damage":934475,"skillId":100200501},{"name":"PLC02_attack_40_1","count":17,"damage":327,"skillId":0},{"name":"PLC02_attack_40_2_2","count":1,"damage":21,"skillId":0}]},{"leaveCnt":0,"userId":1831197,"name":"Beguiling Ayame","baseId":900018402,"objId":500001,"isNpc":false,"hostUserId":1831197,"startRemaindTime":753.407958984375,"atkInfos":[{"name":"atk7","count":1,"damage":0,"skillId":0},{"name":"atk9","count":1,"damage":0,"skillId":0},{"name":"atk_back","count":4,"damage":196,"skillId":0},{"name":"atk5","count":1,"damage":492,"skillId":0},{"name":"atk6","count":1,"damage":73,"skillId":0},{"name":"atk01_S","count":1,"damage":0,"skillId":0},{"name":"atk35","count":1,"damage":0,"skillId":0},{"name":"atk02_2","count":1,"damage":96,"skillId":0},{"name":"atk03_S","count":1,"damage":1379,"skillId":0},{"name":"atk06_S","count":1,"damage":965,"skillId":0},{"name":"atk09_2","count":4,"damage":492,"skillId":0},{"name":"atk08","count":1,"damage":0,"skillId":0},{"name":"atk09_3","count":2,"damage":98,"skillId":0},{"name":"atk09","count":1,"damage":0,"skillId":0},{"name":"atk06","count":15,"damage":5955,"skillId":0},{"name":"atk_wind","count":6,"damage":0,"skillId":0},{"name":"atk01_2","count":3,"damage":0,"skillId":0},{"name":"atk_foot","count":6,"damage":0,"skillId":0},{"name":"atk_tornado","count":2,"damage":0,"skillId":0},{"name":"atk_stamp","count":1,"damage":0,"skillId":0},{"name":"atk07","count":1,"damage":0,"skillId":0},{"name":"atk07_2","count":1,"damage":0,"skillId":0}]}],"actioncount":{"revival":0,"guard":0,"counter":0,"lance":0,"combo":0,"chargesword":0,"chargebow":0,"usemagi":8,"weak":4,"weaponweak":0,"death":0,"heatTwoHandSword":0,"heatPairSwords":0,"revengeBurst":0,"justGuard":0,"shadowSealing":0,"jump":0,"soulOneHandSword":0,"soulTwoHandSword":0,"soulSpear":0,"soulPairSwords":0,"soulArrow":0,"burstOneHandSword":0,"thsFullBurst":0,"burstPairSwords":0,"burstSpear":0,"burstArrow":0,"concussion":0,"oracleOneHandSword":0,"oracleSpear":0,"oraclePairSwords":0},"deliveryBattleInfo":{"maxDamageSelf":230621,"totalAttackCount":160,"attackCount":160,"totalSkillCountList":[{"skillId":306620000,"totalCount":5},{"skillId":203700000,"totalCount":3}],"mySkillCountList":[{"skillId":306620000,"totalCount":5},{"skillId":203700000,"totalCount":3}],"damageByWeaponList":[{"equipmentType":2,"spAttackType":4,"damage":3129326},{"equipmentType":2,"spAttackType":4,"damage":0},{"equipmentType":2,"spAttackType":4,"damage":0}],"currentDamageByWeaponList":[{"equipmentType":2,"spAttackType":4,"damage":3129326},{"equipmentType":2,"spAttackType":4,"damage":0},{"equipmentType":2,"spAttackType":4,"damage":0}],"playerActionInfoList":[{"actionType":1,"totalDamage":230645,"totalCount":4}]}}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $claimResult = requestTemplate($encryptedRequestHash, 'arena/progress', $cookie, $curl);

        if (is_null($claimResult)) {
            println("SERVER ERROR PROGRESS 4");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError progress 4".$jsonResponse["error"]);
            return null;
        }

        print("-");
        
        $plainRequest = '{"remainMilliSec": 628990,"totalElapseMilliSec": 97431}';
        $encryptedRequestHash = userToServerEncrypt($plainRequest, $defaultIV, $userHash);
        $claimResult = requestTemplate($encryptedRequestHash, 'arena/complete', $cookie, $curl);

        if (is_null($claimResult)) {
            println("SERVER ERROR COMPLETE");
            return null; //Server Error
        }

        $jsonResponse = json_decode(serverToUserDecrypt($claimResult, $defaultIV, $userHash), true);

        if ($jsonResponse["error"] != 0){
            println("\nError complete".$jsonResponse["error"]);
            return null;
        }*/



        /*print(".");*/
        return true;
    }
    
	/* -----============== Process Starters ==============----- */
	//This area is where it sets up the process needed for each individual routine (like grabbing IDs, iterating through lists, etc)
	
	//Grabs the original set ability for the selected equipment, the max value for it and rerolls until it is perfect.
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
	
	//Sets up the starting page for the gift box item listing function, also saves the relevant items onto a TXT once found.
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
	
	//Sets up the starting page for the gift box item redeeming function. Assists the process by deciding wether the selected page still has redeemable items or should be skipped (items get backfilled onto the page once you redeem something)
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

	//Contains a few potion IDS to be selected for ease of use (so i dont have to fucking find the numbers every time) but also has an option for a custom ID just in case, then goes and starts the potion drinking.
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

	//Sets up the first page to kickstart the dupe process (either first page or whatver is chosen) then controlls wether it proceeds to the next page or keeps duping the present one.
	//Defunct, this dupe was patched.
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
	
	//Starts up a process to kill all of the summonned behemoths you own. Goes until you stop it, or it kills everything. Edgy when you think of it.
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

	//Similar to the above one, but instead of your summonned behemoth, it kills a selected behemoth (quest, HC tower, summon, etc) X amount of times. kills until you tell it to stop if you input 0 times.
    function killProcessStart($defaultIV, $userHash, $cookie, $number, $mId){
        $curl = curl_init();

        println("Starting:");
        if ($number == 0){
            /*$qidArray = array(
                
            );
            foreach ($qidArray as $quest){
                println("Try: ".$quest);
                ripNTear($quest, 5, $defaultIV, $userHash, $cookie, $curl, true);
            }*/

            while(true){      
                ripNTear($mId, 100, $defaultIV, $userHash, $cookie, $curl, true);
            }

        } else {  
            ripNTear($mId, $number, $defaultIV, $userHash, $cookie, $curl, true);
        }

        println("\nDone.");
    }
	
	//This basically controlls the remaining gems/tickets when pulling behemoths, goes until you run out or tell it to stop.
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

	//Unfinished business. The idea was to check ange's pirate shop for useful items, and buy it before anyone else.
	//Never went anywhere because it was annoying to set up something to automagically check every time the shop reset.
	//And i was drowning in tickets anyways, so who cares.
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
	//This is the bit that receives the command and starts up the relevant process, with the chosen options.
	
    switch ($argv[1]){
		
		//Redeems all chosen item types, starts on selected page.
        case "redeem":
            if (isset($argv[2])){
                redeemProcessStart($argv[2], $defaultIV, $userHash, $cookie);
            } else  {
                redeemProcessStart(0, $defaultIV, $userHash, $cookie);
            }

        break;
		
		//Lists all chosen item types, starts on selected page.
        case "list":
            if (isset($argv[2])){
                listRelevantItemsProcess($argv[2], $defaultIV, $userHash, $cookie);
            } else  {
                listRelevantItemsProcess(0, $defaultIV, $userHash, $cookie);
            }
        break;
		
		//Rerolls an item untill it has perfect abilities. Can either choose specific ability ID or leave it to choose the first one (most items have the original ability be the first, but some have better abilities on the other options, like magic charge on gryllen ~ez +5/+5 perfect set hehe~ or the second original ability on revamped mercurius)
		//This can be done in parallel... grab a bunch of item IDs at once and start up multiple processes to reroll a bunch of crap at once, much faster than one at a time.
        case "reroll":
            if (isset($argv[2]) && isset($argv[3])){
                rerollPerfectProcessStart($argv[2], $argv[3], $defaultIV, $userHash, $cookie);
            } elseif (isset($argv[2])){
                rerollPerfectProcessStart($argv[2], 0, $defaultIV, $userHash, $cookie);
            }
        break;
		
		//Drinks selected potion untill it is empty. Can choose which potion to drink.
        case "chug":
            if (isset($argv[2])){
                chugProcessStart($defaultIV, $userHash, $cookie, $argv[2]);
            } else  {
                chugProcessStart($defaultIV, $userHash, $cookie, null);
            }
        break;
		
		//Dupes all chosen item types, starts on selected page. Doesn't work anymore.
        case "dupe":
            if (isset($argv[2]) && isset($argv[3])){
                dupeProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3]);
            } else {
                dupeProcessStart($defaultIV, $userHash, $cookie);
            }
        break;
		
		//The pirate process... never finished cause it was moot.
        /*case "pirate":
            pirateProcessStart($defaultIV, $userHash, $cookie);
		break;*/
		
		//Kills a specific behemoth X times if you tell it to or just starts the summon behemoth slaugher if you don't choose anything.
        case "massacre":
            if (isset($argv[2]) && isset($argv[3])){
                killProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3]);
            } else  {
                massacreProcessStart($defaultIV, $userHash, $cookie);
            }
        break;
		
		//Pulls onto a banner using either gems by default, or tickets if you tell it to. Needs to have accurate starting gem/tix numbers to work.
        case "pull":
            if (!isset($argv[5])){
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4]);
            } else {
                pullProcessStart($defaultIV, $userHash, $cookie, $argv[2], $argv[3], $argv[4], $argv[5]);
            }
        break;
		
		//Uses the equipment list set up in the start of this program and sets up an automatic process to do a perfect reroll on each and every one of them.
		//If you grab all items its gonna roll all of the A/B/S rank crap and it'll use a lot of gold. It is neat having a fully perfect rolled account, shows true wealth when you collect even S rank sets and they're all perfect hehe.
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
		
		//Basically runs through all existing quests and tries to claim them in case the game lets me grab old, unclaimed rewards from past quests. Not sure if it works, never bothered with it since i thought it was pointless halfway, lol.
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
		
		//This... uh, tries to use multithreaded requests to spam claim the same quest as many times as i can at once...
		//Ya know, just in case i can claim the same quest multiple times if i ask fast enough. Valiant effort but it never worked. Good study onto how multithreaded processing works though.
        case "ree":
            $max=5000000;
            $step = 5;
            $mh = curl_multi_init();
            for($j = $argv[2]; $j <= $max; $j+=$step){
                for($i = $j; $i <= $j+$step; $i++){
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
		
		//Runs a pre-recorded arena run multiple times for the loot.
		//Needs to be pre-recorded for it to be beliveable, since arena runs are recorded and analyzed then i couldn't leave it to be auto-filled like behemoth runs and have the damage numbers/movements/skills used/etc not match up the remaining run time.
		//Funny how i got banned for an actual legit arena run, after doing all of this insane crap and getting away with it, hahaha.
        case "svOneArena":
            $total = 100;

            if (isset($argv[2])){
                $total = $argv[2];
            }

            $curl = curl_init();

            for($i = 0; $i <= $total; $i++){
                arenaComplete($defaultIV, $userHash, $cookie, $curl);
            }
        break;
    }
