<?php
$output_row=file_get_contents("http://express.heartrails.com/api/json?method=getStations&x=".$_POST['x']."&y=".$_POST['y']);
$output=json_decode($output_row);
$line=$output->response->station['0']->line;

$stations_row=file_get_contents("http://express.heartrails.com/api/json?method=getStations&line=".$line);
$stations=json_decode($stations_row);

$point_max['x']=$_POST['x'];
$point_max['y']=$_POST['y'];
$point_min['x']=$_POST['x'];
$point_min['y']=$_POST['y'];
$strings=array();
$line_num=0;
$strings[$line_num]=array();
foreach ($stations->response->station as $x) {
	array_push($strings[$line_num],"new google.maps.LatLng(".$x->y.", ".$x->x.")");
	if ($x->next===NULL) {
		$station_points_string[$line_num]=implode(",\n",$strings[$line_num]);
		$line_num++;
		$strings[$line_num]=array();
	}	
	$point_max['x']=max($point_max['x'],$x->x);
	$point_max['y']=max($point_max['y'],$x->y);
	$point_min['x']=min($point_min['x'],$x->x);
	$point_min['y']=min($point_min['y'],$x->y);
}

$station_points_string[$line_num]=implode(",\n",$strings[$line_num]);

?>

<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset=utf-8>
<title>位置情報から路線を取得するテスト。</title>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDCa1AVsDR-NB-vi4xTJkQjU-AxSnG76Lw&sensor=false">
</script>

<script type="text/javascript">
function initialize(){
	var latlng = new google.maps.LatLng(<?=$_POST['y'].",".$_POST['x']?>);
	var center=new google.maps.LatLng(<?=(($point_min['y']+$point_max['y'])/2).",".(($point_min['x']+$point_max['x'])/2) ?>);
	var opts={
		zoom:2,
		center:latlng,
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};
	var map=new google.maps.Map(document.getElementById("map_canvas"),opts);
	var ll_sw = new google.maps.LatLng(<?=$point_min['y'].",".$point_min['x']?>);
	var ll_ne = new google.maps.LatLng(<?=$point_max['y'].",".$point_max['x']?>);
	var latLngBounds = new google.maps.LatLngBounds(ll_sw, ll_ne);
	map.fitBounds(latLngBounds);

	var marker=new google.maps.Marker({
		position:latlng,
		map:map
	});
<?php
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

echo "あなたは今、<b>".$line."</b>に乗っている可能性が高いです！".PHP_EOL;

?>
<div align="center"><input type="button" onclick="location.href='index.php'" value="更新" ></div>
<div id="map_canvas" style="width:100%; height:100%"></div>

<span style="position:absolute;bottom:0%;width:100%;text-align:right;"><a target="_blank" href="http://express.heartrails.com/">HeartRails Expressi</a></span>

</body>
</html>
