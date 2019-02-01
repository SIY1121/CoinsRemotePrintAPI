<?php

namespace siy\coins_print;

ini_set('display_errors', "On");
require_once '../../vendor/autoload.php';
require_once '../../helper/helper.php';

use phpseclib\Net\SSH2;

/**
 * 利用可能なプリンタ一覧を取得
 * @param $user string ログインに使用するユーザー名
 * @param $pass string パスワード
 * @param $res array 取得されたプリンタ一覧
 * @return bool 成功したらtrue
 */
function getPrinterInfo($user, $pass, &$res)
{
    $ssh = new SSH2('crocus01');

    if (!$ssh->login($_POST['user'], $_POST['pass'])) {
        return false;
    }

    $data = $ssh->exec('lpstat -s');
    preg_match_all("/^(.*?)のデバイス: (.*?)$/m", $data, $matches);
    $res = $matches;
    $ssh->disconnect();

    return true;
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

if (!isset($_POST['user'], $_POST['pass'])) {
    http_response_code(400);
    echo json_encode(array('msg'=>'userパラメータ、passパラメータが不足しています'));
    exit();
}

$res = getPrinterInfo($_POST['user'],$_POST['pass'],$printers);

if(!$res){
    http_response_code(401);
    echo json_encode(array('msg'=>'ログインに失敗しました'));
    exit();
}

echo json_encode(array('msg'=>'取得に成功しました','data'=>$printers[1]));