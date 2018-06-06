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



$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";

$querylink = "https://data.adamlink.nl/AdamNet/all/services/endpoint#query=" . urlencode($sparqlquery) . "&contentTypeConstruct=text%2Fturtle&contentTypeSelect=application%2Fsparql-results%2Bjson&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2Fmenno%2Falles%2Fservices%2Falles%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&outputFormat=table";

$json = file_get_contents($url);

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

$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";

$querylink = "https://data.adamlink.nl/AdamNet/all/services/endpoint#query=" . urlencode($sparqlquery) . "&contentTypeConstruct=text%2Fturtle&contentTypeSelect=application%2Fsparql-results%2Bjson&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2Fmenno%2Falles%2Fservices%2Falles%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&outputFormat=table";

$json = file_get_contents($url);

$data = json_decode($json,true);

$years = array();
$results = $data['results']['bindings'];

$firstyear = $results[0]['year']['value'];
$reversed = array_reverse($results);
$lastyear = $reversed[0]['year']['value'];

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
				<a target="_blank" href="index.php?set=<?= $set['set']['value'] ?>"><?= $set['set']['value'] ?></a>
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
