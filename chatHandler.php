<?php
/*
THIS SECTION USED THE TECHNIQUES GIVEN FROM THIS LINK/WEBSITE
https://phppot.com/php/simple-php-chat-using-websocket/
*/
$dbCon = getDB();
    class ChatHandler{
        //saves all the users in the chat
        private $names = array();
        //saves all of mii info
        private $miiInfo = array();
        //saves chat history
        private $chatHistory = array();
        //send message 
        function sendMessage($message){
            global $clientSockets;
            $messageLen = strlen($message);
            foreach($clientSockets as $c){
                @socket_write($c,$message,$messageLen);
            }
            return true;
        }
        //open message
        function openMessage($socketData){
            $len = ord($socketData[1]) & 127;
            if($len == 126){
                $mask = substr($socketData,4,4);
                $data = substr($socketData,8);
            }
            elseif($len == 127){
                $mask = substr($socketData,10,4);
                $data = substr($socketData,14);
            }
            else{
                $mask = substr($socketData,2,4);
                $data = substr($socketData,6);
            }
            $sData="";
            for($x=0;$x <strlen($data);++$x){
                $sData .= $data[$x] ^ $mask[$x%4];
            }
            return $sData;
        }
        //encloses message
        function encloseMessage($socketData){
            $b1 = 0x80 | (0x1 & 0x0f);
            $len = strlen($socketData);
            if($len <= 125){
                $header = pack('CC',$b1,$len);
            }
            elseif($len >125 && $ $len < 65536){
                $header = pack('CCn',$b1,126,$len);
            }
            elseif($len >= 65536){
                $header = pack('CCNN',$b1,127,$len);
            }
            return $header.$socketData;
        }
        //handshakes with another user
        function handshake($received_header,$client_socket_resource, $host_name, $port){
            $header = array();
            $lines = preg_split("/\r\n/",$received_header);
            foreach($lines as $l){
                $l = chop($l);
                if(preg_match('/\A(\S+): (.*)\z/',$l, $matches)){
                    $headers[$matches[1]] = $matches[2];
                }
            }
            $secureKey = $headers['Sec-WebSocket-Key'];
            $secureAccept = base64_encode(pack('H*', sha1($secureKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $buf = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host_name\r\n" .
            "WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
            "Sec-WebSocket-Accept:$secureAccept\r\n\r\n";
            socket_write($client_socket_resource,$buf,strlen($buf));
        }
        
        //creates creates an array of a string in this format
        //[a,b,c]
        function arrayMessage($array){
            $l = "[";
            $x = 0;
            while($x < count($array)){
                $l = $l.$array[$x];
                $x++;
                if($x != count($array)){
                    $l = $l.",";
                }
            }
            $l = $l."]";
            
            $messArray = array('message'=>$l,'mess_type'=>'user_array');
            $json = json_encode($messArray);
            return $json;
        }
        
        //creates a chat box to be displayed
        function createChatBoxMessage($chat_user,$chat_box_message,$code,$users,$miis) {
            //FIX MESSAGE FORMAT
            $messArray = null;
            if($code == 1){
                $mess = "<div>".$chat_box_message . "</div>";
                array_push($this->chatHistory,$chat_box_message);
                $messArray = array('message'=>$mess,'users'=>$users,'miiInfo'=>$miis,'message_type'=>'enter');
            }
            elseif($code == 0){
                $mess = "<div>".$chat_user . ":" . $chat_box_message . "</div>";
                array_push($this->chatHistory,$chat_user . ":" . $chat_box_message);
                $messArray = array('message'=>$mess,'users'=>$users,'message_type'=>'chat');
            }
            elseif($code == 2){
                $mess = "<div>".$chat_box_message . "</div>";
                array_push($this->chatHistory,$chat_box_message);
                $messArray = array('message'=>$mess,'users'=>$users,'message_type'=>'exit');
            }
            
            if(count($this->chatHistory) == 25){
                $this->chatHistory = array_shift($this->chatHistory);
            }
            $chatMessage = $this->encloseMessage(json_encode($messArray));
            return $chatMessage;
        }
        
        //adds or removes a user from the current user present list
        function updateUsers($newUser,$action){
            if($action == "add"){
                array_push($this->names,$newUser);
            }
            elseif($action == "remove"){
                $index = array_search($newUser,$this->names);
                array_splice($this->names,$index,1);
            }
        }
        
        //get all the usernames of all present users
        function getUsernames(){
            $arr = array();
            for($x=0;$x<count($this->names);$x++){
                array_push($arr,$this->names[$x]);
            }
            return $arr;
        }
        
        //gets all of the mii info for all present users
        function getMiiInfo(){
            $arr = array();
            for($x=0;$x<count($this->miiInfo);$x++){
                array_push($arr,$this->miiInfo[$x]);
            }
            return $arr;
        }
        
        //get index of user in the names array
        function getIndex($user){
            return array_search($user,$this->names);
        }
        
        //adds or removes a users mii info
        function updateMiiInfo($newInfo,$user,$action){
            if($action == "add"){
                array_push($this->miiInfo,$newInfo);
            }
            elseif($action == "remove"){
                $index = array_search($user,$this->names);
                array_splice($this->miiInfo,$index,1);
            }
            
        }
        
        //gets the mii info at a specific index
        function getMiiInfoIndex($index){
            return $this->miiInfo[$index];
        }
    }
?>
