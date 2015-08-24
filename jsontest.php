<html>
<head>
<meta charset=utf-8>
<title>位置情報から路線を取得するテスト。</title>
</head>
<body>
<?php
/*
// http://qiita.com/khirose/items/870ffec6ce4562f54c9d
$POST_DATA=array(
	'method'=>'getStations',
	'x'=>$_GET['x'],
	'y'=>$_GET['y']
);

$curl=curl_init("http://express.heartrails.com/api/json");
curl_setopt($curl,CURLOPT_POST, TRUE);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($POST_DATA));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  // 
curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl,CURLOPT_COOKIEJAR,      'cookie');
curl_setopt($curl,CURLOPT_COOKIEFILE,     'tmp');
curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡

$output_row= curl_exec($curl);
$output=json_decode($output_row);
 */

$output_row=file_get_contents("http://express.heartrails.com/api/json?method=getStations&x=".$_POST['x']."&y=".$_POST['y']);
$output=json_decode($output_row);

//print_r($output);
//print_r($_GET);
echo "あなたは今、".$output->response->station['0']->line."に乗っている可能性が高いです！<br>".PHP_EOL;
?>
<span style="position:absolute;bottom:0%;width:100%;text-align:right;"><a target="_blank" href="http://express.heartrails.com/">HeartRails Expressi</a></span>
</body>
</html>
