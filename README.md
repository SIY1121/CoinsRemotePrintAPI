# CoinsRemotePrintAPI
筑波大学情報学群情報科学類(coins)のプリンタを利用するための非公式WebAPIです。

# できること
- pdfファイルの印刷
- プリンタ一覧の取得
- 印刷残高の取得

# 本APIを利用したクライアント
- [CoinsRemotePrintWebClient](https://github.com/SIY1121/CoinsRemotePrintWebClient) Web上で印刷できるクライアント
- CoinsRemotePrintWindowsClient(準備中) Windows上でワンクリックでPDFを印刷できる
- CoinsRemotePrintMacClient(誰か作ってくれないかな)

# オープンソースにした理由
パスワードを悪用していないことを証明するため。

# API詳細
セッションは使用しないため、毎回ユーザー名とパスワードを送信する必要があります。

生パスワードを使用する都合上、セッションの使用は危険と判断しました(生パスワード保持のためにファイルに書き込んでしまうため)


以下のAPIはすべて`https://www.coins.tsukuba.ac.jp/~s1811317/printer/api/v1/`配下に存在します。
## UserInfo API
|項目|説明|
|---|---|
|Description|与えられたユーザ名とパスワードの組が有効かを検証し、<br>有効であればidと本名を返す
|URL|/UserInfo.php|
|Method|POST|
|BodyContentType|multipart/form-data or <br> application/x-www-form-urlencoded|
|DataParam|user:[string] ユーザー名<br>pass:[string] パスワード|
|SuccessResponse|Code: 200 <br>Content: {id: [string], name: [string], msg:[string]}|
|ErrorResponse|Code: 400 Bad Request <br>必要なパラメータが指定されていません<br><br>Code: 401 Unauthorized<br>指定されたユーザー名またはパスワードは無効です<br><br>Code: 405 Method Not Allowed<br>POST以外で呼び出すとこのコードが返ります<br><br>Content: {msg:[string]}|

## PrinterInfo API
|項目|説明|
|---|---|
|Description|利用可能なプリンタの一覧を返す
|URL|/PrinterInfo.php|
|Method|POST|
|BodyContentType|multipart/form-data or <br> application/x-www-form-urlencoded|
|DataParam|user:[string] ユーザー名<br>pass:[string] パスワード|
|SuccessResponse|Code: 200 <br>Content: {data: [array], msg:[string]}|
|ErrorResponse|Code: 400 Bad Request <br>必要なパラメータが指定されていません<br><br>Code: 401 Unauthorized<br>指定されたユーザー名またはパスワードは無効です<br><br>Code: 405 Method Not Allowed<br>POST以外で呼び出すとこのコードが返ります<br><br>Content: {msg:[string]}|

## PaperCut API
|項目|説明|
|---|---|
|Description|印刷に使用するポイントの残高を取得する
|URL|/PaperCut.php|
|Method|POST|
|BodyContentType|multipart/form-data or <br> application/x-www-form-urlencoded|
|DataParam|user:[string] ユーザー名<br>pass:[string] パスワード|
|SuccessResponse|Code: 200 <br>Content: {data: [string], msg:[string]}|
|ErrorResponse|Code: 400 Bad Request <br>必要なパラメータが指定されていません<br><br>Code: 401 Unauthorized<br>指定されたユーザー名またはパスワードは無効です<br><br>Code: 405 Method Not Allowed<br>POST以外で呼び出すとこのコードが返ります<br><br>Content: {msg:[string]}|

## Print API
|項目|説明|
|---|---|
|Description|印刷を行う
|URL|/Print.php|
|Method|POST|
|BodyContentType|**multipart/form-data のみ**|
|DataParam|user:[string] ユーザー名<br>pass:[string] パスワード<br>printer:[string] 印刷に使用する、PrinterInfo APIで取得したプリンターID<br>up_file:[binary] 印刷するPDFファイル|
|SuccessResponse|Code: 200 <br>Content: {data: [string], msg:[string]}|
|ErrorResponse|Code: 400 Bad Request <br>必要なパラメータが指定されていません<br><br>Code: 401 Unauthorized<br>指定されたユーザー名またはパスワードは無効です<br><br>Code: 405 Method Not Allowed<br>POST以外で呼び出すとこのコードが返ります<br><br>Content: {msg:[string]}|


# 仕様
## 言語
このAPIはPHP 5.3で記述されています。
coins内のプリンタにアクセスするには
www.coins.tsukuba.ac.jp上でプログラムを動かすのが
楽なので、上記環境で動作するPHP 5.3を選択しました。

正直PHP 7系の時代にこれは辛いです。
ライブラリ選定にも一苦労しました。

ライブラリ管理には[composer](https://getcomposer.org/)を使っています。

## ライブラリ
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) ssh接続をしたりscpでファイル転送をしたりするのに使う
- [rmccue/requests](https://github.com/rmccue/Requests) httpリクエストを楽に行える
## Core
APIにアクセスすると、内部で各種印刷コマンドが実行されます。
