<?php

namespace siy\coins_print;
require_once '../vendor/autoload.php';
require_once '../helper/helper.php';


/**
 * 印刷に必要なポイントの残高を取得する
 * @param $user string ログインの用いるユーザー名 ex. s1234567
 * @param $pass string パスワード
 * @return string
 */
function getPaperCutPoint($user, $pass)
{
    //トップページへ行きセッションを生成する
    $topPage = \Requests::get('https://violet-nwm.coins.tsukuba.ac.jp:9192/user', array(), array());

    //ログインに必要な情報
    $params = array(
        'service' => 'direct/1/Home/$Form$0',
        'sp' => 'S0',
        'Form0' => '$Hidden$0,$Hidden$1,inputUsername,inputPassword,$PropertySelection,$Submit$0',
        '$Hidden$0' => 'true',
        '$Hidden$1' => 'X',
        'inputUsername' => $user,
        'inputPassword' => $pass,
        '$PropertySelection' => 'en',
        '$Submit$0' => 'ログイン',
    );

    //ログイン情報とセッションを載せてユーザーページを取得
    $res = \Requests::post('https://violet-nwm.coins.tsukuba.ac.jp:9192/app', array('Cookie' => $topPage->headers['Set-Cookie']), $params);

    //ポイント部分を抽出
    preg_match_all("/\r\r\r(.*?) Pt\./", $res->body, $matches);
    return $matches[1][0];
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
$pt = getPaperCutPoint($_POST['user'],$_POST['pass']);

if(!isset($pt)){
    http_response_code(401);
    echo json_encode(array('msg'=>'ログインに失敗しました'));
    exit();
}

echo json_encode(array('msg'=>'取得に成功しました','data'=>$pt));