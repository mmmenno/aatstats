<!DOCTYPE html>
<?php

$color = "#ccc";

if(isset($_GET['set'])){
	if($_GET['set']=="https://data.adamlink.nl/saa/beeldbank/"){
		$color = "#000";
	}elseif($_GET['set']=="https://data.adamlink.nl/rma/beeldbank/"){
		$color = "#94BC6C";
	}elseif($_GET['set']=="https://data.adamlink.nl/iisg/beeldbank/"){
		$color = "#4A90E2";
	}elseif($_GET['set']=="https://data.adamlink.nl/nharchief/beeldbank/"){
		$color = "#B77C1B";
	}elseif($_GET['set']=="https://data.adamlink.nl/am/amcollect/"){
		$color = "#D0021B";
	}elseif($_GET['set']=="https://data.adamlink.nl/uva/maps/"){
		$color = "#9013FE";
	}
}


$sparqlquery = "PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX skosxl: <http://www.w3.org/2008/05/skos-xl#>
PREFIX void: <http://rdfs.org/ns/void#>
SELECT DISTINCT ?term SAMPLE(?label) AS ?label (COUNT(?cho) AS ?count) WHERE {
  ";
if(isset($_GET['set'])){
	$sparqlquery  .= "?cho void:inDataset <" . $_GET['set'] . "> .\n";
}
$sparqlquery  .= "?cho dc:type ?term .
  FILTER REGEX(?term,'vocab.getty') .
  ?term skosxl:prefLabel ?labelid .
  ?labelid <http://www.w3.org/2008/05/skos-xl#literalForm> ?label .
  } 
GROUP BY ?term
ORDER BY DESC(?count)
";


//echo $sparqlquery;

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

//print_r($all);

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

   

	<style>
		.bar{
			height: 1px;
			background-color: <?= $color ?>;
		}
	</style>

	
</head>
<body>






<div class="container-fluid">
	<div class="row">
		<div class="col-md-5 collections">
			<a href="index.php">alles</a> |
			<a style="border-color:#000;" href="index.php?set=https://data.adamlink.nl/saa/beeldbank/">saa</a> |
			<a style="border-color:#B77C1B;" href="index.php?set=https://data.adamlink.nl/nharchief/beeldbank/">nharchief</a> |
			<a style="border-color:#94BC6C;" href="index.php?set=https://data.adamlink.nl/rma/beeldbank/">rma</a> |
			<a style="border-color:#4A90E2;" href="index.php?set=https://data.adamlink.nl/iisg/beeldbank/">iisg</a> |
			<a style="border-color:#D0021B;" href="index.php?set=https://data.adamlink.nl/am/amcollect/">am</a> |
			<a style="border-color:#9013FE;" href="index.php?set=https://data.adamlink.nl/uva/maps">uva maps</a>
		</div>
		<div class="col-md-1">
			
			
		</div>
		<div class="col-md-6 intro">
			<h1>Amsterdam-AAT</h1>

			<p>
				Wat is er in onze collecties al aan de AAT gekoppeld? Doorklikken op termen geeft meer inzicht.
			</p>
		</div>
	</div>


	<?php foreach ($all as $term) { ?>
	<div class="row">
		<div class="col-md-3">
				<a href="term.php?term=<?= $term['term']['value'] ?>"><?= $term['label']['value'] ?></a>
		</div>
		<div class="col-md-8 barcol">
			
			<?php 
				$cnt = $term['count']['value'];
				$lines = floor($cnt/1000);
				$rest = $cnt - ($lines*1000);
				if($lines>0){
					for($i = 0; $i < $lines; $i++) {
						echo '<div class="bar" style="width:100%"></div>';
					}
				}
				$perc = ceil($rest/10);
				echo '<div class="bar" style="width:' . $perc . '%"></div>';
			?>
		</div>
		<div class="col-md-1">
			<?= $term['count']['value'] ?>
		</div>
	</div>
	<?php } ?>


	<div class="row">
		<div class="col-md-12 querylink">
			<a target="_blank" href="<?= $querylink ?>">SPARQL it yourself &gt;</a>
		</div>
	</div>
</div>








<script>


</script>



</body>
</html>
