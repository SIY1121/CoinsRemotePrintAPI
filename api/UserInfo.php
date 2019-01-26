<?php
namespace siy\coins_print;

ini_set('display_errors', "On");
require_once '../vendor/autoload.php';
require_once '../helper/helper.php';

use \phpseclib\Net\SSH2;

/**
 * ログインできるかを確認すると共に、ユーザーの基本情報を返す
 * @param $user string ユーザー名
 * @param $pass string パスワード
 * @return array|bool
 */
function getUserData($user,$pass){
    $ssh = new SSH2('abelia01');
    if(!$ssh->login($user,$pass))
        return false;

    $id = substr($ssh->exec('id -un'),0,-1);
    $name = substr($ssh->exec('id -F'),0,-1);

    $ssh->disconnect();
    return array('id'=>$id,'name'=>$name);
}

if (__FILE__ != realpath($_SERVER['SCRIPT_FILENAME']))
    return; //include時は以下を実行しない

header('content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if($_SERVER['REQUEST_METHOD']!='POST'){
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(array('msg'=>'POSTリクエストのみ受け付けています。'));
    exit();
}

if(!isset($_POST['user'],$_POST['pass'])){
    http_response_code(400);
    echo json_encode(array('msg'=>'userパラメータ、またはpassパラメータが不足しています'));
    exit();
}

$data = getUserData($_POST['user'], $_POST['pass']);

if(!$data){
    http_response_code(401);
    echo json_encode(array('msg'=>'ログインに失敗しました'));
    exit();
}

echo json_encode(array('msg'=>'ログインに成功しました','data'=>$data));