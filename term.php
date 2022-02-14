<!DOCTYPE html>
<?php

$sparqlquery = "PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX skosxl: <http://www.w3.org/2008/05/skos-xl#>
PREFIX void: <http://rdfs.org/ns/void#>
SELECT DISTINCT ?set (SAMPLE(?label) AS ?label) (COUNT(?cho) AS ?count) WHERE {
  ?cho dc:type <" . $_GET['term'] . "> .
  ?cho void:inDataset ?set .
  <" . $_GET['term'] . "> skosxl:prefLabel ?labelid .
  ?labelid <http://www.w3.org/2008/05/skos-xl#literalForm> ?label .
} 
GROUP BY ?set
ORDER BY DESC(?count)
";



$url = "https://api.druid.datalegend.net/datasets/adamnet/all/services/endpoint/sparql?query=" . urlencode($sparqlquery) . "";

$querylink = "https://druid.datalegend.net/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlquery) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch,CURLOPT_USERAGENT,'adamlink');
$headers = [
  'Accept: application/sparql-results+json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$json = curl_exec ($ch);
curl_close ($ch);

$data = json_decode($json,true);



$all = $data['results']['bindings'];




$sparqlquery = "PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX skosxl: <http://www.w3.org/2008/05/skos-xl#>
PREFIX void: <http://rdfs.org/ns/void#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
SELECT DISTINCT ?year (COUNT(?cho) AS ?count) WHERE {
  ?cho dc:type <" . $_GET['term'] . "> .
  ?cho sem:hasBeginTimeStamp ?date .
  BIND (year(xsd:dateTime(?date)) AS ?year)
  FILTER (COALESCE(xsd:datetime(str(?date)), '!') != '!')
} 
GROUP BY ?year
ORDER BY ASC(?year)
";

$url = "https://api.druid.datalegend.net/datasets/adamnet/all/services/endpoint/sparql?query=" . urlencode($sparqlquery) . "";

$querylink = "https://druid.datalegend.net/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlquery) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch,CURLOPT_USERAGENT,'adamlink');
$headers = [
  'Accept: application/sparql-results+json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$json = curl_exec ($ch);
curl_close ($ch);

$data = json_decode($json,true);

// /print_r($sparqlquery);

$years = array();
$results = $data['results']['bindings'];

$firstyear = $results[0]['year']['value'];
if($firstyear < 1500){
	$firstyear = 1500;
}
$reversed = array_reverse($results);
$lastyear = $reversed[0]['year']['value'];
if($lastyear > 2017){
	$lastyear = 2017;
}


foreach($results as $v){
	$years[$v['year']['value']] = $v['count']['value'];
}

//print_r($years);
?>
<html>
<head>
	
	<title>AAT Amsterdam</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

	<link rel="stylesheet" href="styles.css" />

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

   

	
</head>
<body>






<div class="container-fluid">
	<div class="row">
		<div class="col-md-2 collections">
			<a href="index.php">home</a>
		</div>
		<div class="col-md-10 intro">
			<h1><?= $all[0]['label']['value'] ?>, per set</h1>

			<p>
				
			</p>
		</div>
	</div>


	<?php foreach ($all as $set) { ?>
	<div class="row">
		<div class="col-md-4">
				<a href="index.php?set=<?= $set['set']['value'] ?>"><?= $set['set']['value'] ?></a>
		</div>
		<div class="col-md-7 barcol">
			
			<?php 
				preg_match("/data.adamlink.nl\/([a-z]+)\//", $set['set']['value'],$found);
				$classname = $found[1];

				$cnt = $set['count']['value'];
				$lines = floor($cnt/1000);
				$rest = $cnt - ($lines*1000);
				if($lines>0){
					for($i = 0; $i < $lines; $i++) {
						echo '<div class="bar ' . $classname . '" style="width:100%"></div>';
					}
				}
				$perc = ceil($rest/10);
				echo '<div class="bar ' . $classname . '" style="width:' . $perc . '%"></div>';
			?>
		</div>
		<div class="col-md-1">
			<?= $set['count']['value'] ?>
		</div>
	</div>
	<?php } ?>


	<div class="row">
		<div class="col-md-12 querylink">
			<a target="_blank" href="<?= $querylink ?>">SPARQL it yourself &gt;</a>
		</div>
	</div>







	<div class="row">
		<div class="col-md-12">
		<h1><?= $all[0]['label']['value'] ?>, per jaar</h1>
		</div>
	</div>


	
</div>


<?php 

$width = ($lastyear - $firstyear)*2;
//print_r($years);
?>


<div class="container-fluid">
<div class="yeardiv" style="width: <?= $width ?>px;">

	<div style="float: right;"><h2><?= $lastyear ?></h2></div>
	<h2><?= $firstyear ?></h2>

	<?php
	for($i=$firstyear; $i<$lastyear; $i++) { 
		if(isset($years[$i])){ 
			echo '<div class="year" style="height:' . $years[$i] . 'px"></div>';
		}else{
			echo '<div class="empty"></div>';
		}
	} 
	?>
</div>
</div>



	








</body>
</html>
