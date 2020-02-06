<?php
require "inc/config.php";
require "inc/header.php";

/*********************************************
 * ******       BEGIN      FUNCTIONS
 *********************************************/
function getHashes($url,$path_file,$type_file,$path_stamp,$type_stamp) {

    $file = curl_file_create($path_file,$type_file,'file_');
    $stamp = curl_file_create($path_stamp,$type_stamp,'stamp_');

    $postData = array(
    'cloudWalletId'=>CLOUDWALLETID,
    'cloudWalletPassword'=>CLOUDWALLETPASSPORT,
    'file' => $file,
    'stamp' =>$stamp
    );

    $headers = array(
    "ApplicationToken: ".APLICATION_TOKEN,
    "Authorization: ".AUTHORIZATION
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData); 
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $r = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($r);
    $stampHash = $response->stampHash;
    $stampID = $response->stampID;
    return '{"stampID":"'.$stampID.'","stampHash":"'.$stampHash.'"}';
}
/*********************************************
 * ******       END     FUNCTIONS
 *********************************************/

function setFile($data) {
    $path_tmp = "tmp/";
    $file_name = time().'.txt';    
	$file = fopen($path_tmp.$file_name,"a");
	$row = $data;
	fwrite($file,$row.PHP_EOL);
    fclose($file);
    return $path_tmp.$file_name;
}
function logLine($data) {
    $path_log = "logs/";
	$file = fopen($path_log."bc_".date('Y-m-d_H').".log", "a");
	$row = "[".date('Y-m-d H:i:s')."] ".$data;
	fwrite($file,$row.PHP_EOL);
	fclose($file);
}
function rmTmp() {
    //TODO
}

if($_SERVER['REQUEST_METHOD']=="POST") {
    $body = urldecode(file_get_contents('php://input'));
    if(isset($_POST['data']) AND !empty($_POST['data'])) $data = $_POST['data'];
    else if(!empty($body)) $data = $body;
    
    if(!empty($data)){
        logLine($data);
        if($result->error) $response = array("error" => $result->error, "success"=>false);
        else $response = array("id" => $result->id, "success"=>true);
        $path_file = setFile($data);
        $type_file = 'text/plain';
        $path_stamp = 'tmp/template.json';
        $type_stamp = 'application/json';;
        $response = getHashes(URL_API,$path_file,$type_file,$path_stamp,$type_stamp);
    }
    else {
        $error = array("error" => "es.dmn.api.rest.MissingField.datos", "success"=>false);
        $response= json_encode($error);
    }
   
    
    
}
else {
    $error = array("error" => "es.dmn.api.rest.MissingParam.cod", "success"=>false);
    $response= json_encode($error);
}
echo $response;
?>

