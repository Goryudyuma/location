<?php

//直接このページに来た時、座標が取得できないのでindex.phpにとばす。
//絶対パスしか指定できないので、URLをそのまま書く。
if ($_POST['x']==""||$_POST['y']=="") {
	header('Location: https://near-route.appspot.com');
	exit;
}

//近くの駅のデータを取ってくる。
$near_station=file_get_contents("http://express.heartrails.com/api/json?method=getStations&x=".$_POST['x']."&y=".$_POST['y']);
$near_station_data=json_decode($near_station);

//線名を取得。
$line_name=$near_station_data->response->station['0']->line;

//線名からその線に含まれる駅一覧を取得。
$stations_row=file_get_contents("http://express.heartrails.com/api/json?method=getStations&line=".$line_name);
$stations=json_decode($stations_row);

//googlemapで表示する範囲を出すため、変数を準備。
$map_range['max']['x']=$_POST['x'];
$map_range['max']['y']=$_POST['y'];
$map_range['min']['x']=$_POST['x'];
$map_range['min']['y']=$_POST['y'];

//google map apiに埋め込む文字列。
$strings=array();

//線が分割されている可能性があるので、何線にわたって分割されているかをいれる変数。(信越本線など)
$line_num=0;

$strings[$line_num]=array();

//線内すべての駅を線でつなぐ
foreach ($stations->response->station as $x) {
	array_push($strings[$line_num],"new google.maps.LatLng(".$x->y.", ".$x->x.")");
	if ($x->next===NULL) {
		$station_points_string[$line_num]=implode(",\n",$strings[$line_num]);
		$line_num++;
		$strings[$line_num]=array();
	}	
	$map_range['max']['x']=max($map_range['max']['x'],$x->x);
	$map_range['max']['y']=max($map_range['max']['y'],$x->y);
	$map_range['min']['x']=min($map_range['min']['x'],$x->x);
	$map_range['min']['y']=min($map_range['min']['y'],$x->y);
}

$station_points_string[$line_num]=implode(",\n",$strings[$line_num]);

?>

<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset=utf-8>
<title>位置情報から路線を取得するテスト。</title>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDCa1AVsDR-NB-vi4xTJkQjU-AxSnG76Lw&sensor=false&region=JP">
</script>

<script type="text/javascript">
function initialize(){
	var latlng = new google.maps.LatLng(<?=$_POST['y'].",".$_POST['x']?>);
	var center=new google.maps.LatLng(<?=(($map_range['min']['y']+$map_range['max']['y'])/2).",".(($map_range['min']['x']+$map_range['max']['x'])/2) ?>);
	var opts={
		zoom:2,
		center:latlng,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};
	var map=new google.maps.Map(document.getElementById("map_canvas"),opts);
	var ll_sw = new google.maps.LatLng(<?=$map_range['min']['y'].",".$map_range['min']['x']?>);
	var ll_ne = new google.maps.LatLng(<?=$map_range['max']['y'].",".$map_range['max']['x']?>);
	var latLngBounds = new google.maps.LatLngBounds(ll_sw, ll_ne);
	map.fitBounds(latLngBounds);

	var marker=new google.maps.Marker({
		position:latlng,
		map:map
	});
<?php
//線を引く。分割されている場合でも対応。
for ($i=0; $i <= $line_num; $i++) {

	echo "
		var points=[
		".$station_points_string[$i]."
	];

	var polylineOpts={
		map:map,
		path:points,
		strokeColor: '#0000FF',
		strokeWeight: 5
	};
	var polyline = new google.maps.Polyline(polylineOpts);
";
}
?>
}
</script>

</head>
<body onload="initialize()">
<?php

echo "あなたは今、<b>".$line_name."</b>に乗っている可能性が高いです！".PHP_EOL;

?>

<div align="right">
<a href="https://twitter.com/share" class="twitter-share-button" data-text="私は今、<?=$line_name?>に乗っています！" data-url="https://near-route.appspot.com/" data-via="Goryudyuma">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
</div>

<div align="center"><input type="button" onclick="location.href='index.php'" value="更新" ></div>
<div id="map_canvas" style="width:100%; height:100%"></div>

<span style="position:absolute;bottom:0%;width:100%;text-align:right;"><a target="_blank" href="http://express.heartrails.com/">HeartRails Expressi</a></span>

</body>
</html>
