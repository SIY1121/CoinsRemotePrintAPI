<?php

namespace siy\coins_print;

ini_set('display_errors', "On");
require_once '../vendor/autoload.php';
require_once '../helper/helper.php';

use phpseclib\Net\SCP;
use phpseclib\Net\SSH2;

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

if (!is_uploaded_file($_FILES['up_file']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(array('msg' => 'ファイルがアップロードされませんでした'));
    exit();
}

//chmod($_FILES['up_file']['tmp_name'],600);

if (!isset($_POST['user'], $_POST['pass'], $_POST['printer'])) {
    http_response_code(400);
    echo json_encode(array('msg' => '印刷の実行には少なくとも"user","pass","printerID"のパラメータが必要です'));
    exit();
}

//コネクション確率
$ssh = new SSH2('crocus01');
if (!$ssh->login($_POST['user'], $_POST['pass'])) {
    http_response_code(401);
    echo json_encode(array('msg' => 'ユーザー名、またはパスワードが違います', 'log' => $ssh->getErrors()));
    exit();
}
//ホームディレクトリ取得
$path = substr($ssh->exec('echo $HOME'), 0, -1);
$scp = new SCP($ssh);
//ファイルを転送
var_dump($scp->put($path . "/coins_print_service_tmp_source.pdf",$_FILES['up_file']['tmp_name'], SCP::SOURCE_LOCAL_FILE));

//一時ファイル削除
unlink($_FILES['up_file']['tmp_name']);

//ファイルを自分のみが読み書きできるように権限を変更
$ssh->exec('chmod 600 ~/coins_print_service_tmp_source.pdf');
//印刷実行
//$data = $ssh->exec('lpr -P ' . $_POST['printer'] . ' ~/coins_print_service_tmp_source.pdf');
$data = '';
$ssh->disconnect();

echo json_encode(array('msg'=>'印刷のリクエストに成功しました','log'=>$data));