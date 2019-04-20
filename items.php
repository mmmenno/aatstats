<!DOCTYPE html>
<?php

$sparqlquery = "PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX skosxl: <http://www.w3.org/2008/05/skos-xl#>
SELECT ?narrower ?narrowerterm ?term ?broader ?broaderterm WHERE {
  <" . $_GET['term'] . "> skosxl:prefLabel ?pref .
  ?pref <http://vocab.getty.edu/ontology#term> ?term .
  <" . $_GET['term'] . "> <http://vocab.getty.edu/ontology#broaderPreferred> ?broader .
  ?broader skosxl:prefLabel ?broaderpref .
  ?broaderpref <http://vocab.getty.edu/ontology#term> ?broaderterm .
  OPTIONAL{
    ?narrower <http://vocab.getty.edu/ontology#broaderPreferred> <" . $_GET['term'] . "> .
    ?narrower skosxl:prefLabel ?narrowerpref .
    ?narrowerpref <http://vocab.getty.edu/ontology#term> ?narrowerterm .
    FILTER EXISTS {?cho dc:type ?narrower . }
  }
} 
LIMIT 200
";


$url = "https://api.druid.datalegend.net/datasets/adamnet/all/services/endpoint/sparql?query=" . urlencode($sparqlquery) . "";

$querylink = "https://druid.datalegend.net/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlquery) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";


// Druid does not like url parameters, send accept header instead
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: application/sparql-results+json\r\n"
    ]
];

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$json = file_get_contents($url, false, $context);

$data = json_decode($json,true);

$terms = $data['results']['bindings'];

$broader = array();
$narrower = array();

foreach ($terms as $k => $v) {
	$term = $v['term']['value'];
	$broader[$v['broader']['value']] = $v['broaderterm']['value'];
	$narrower[$v['narrower']['value']] = $v['narrowerterm']['value'];
}



// NOW, GET SOME IMAGES

$sparqlquery = "PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX void: <http://rdfs.org/ns/void#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT * WHERE {
  ?cho dc:type <" . $_GET['term'] . "> .
  #?cho dct:spatial ?building . 
  #?building dc:type <" . $_GET['term'] . "> .
  ?cho foaf:depiction ?img .
  ?cho void:inDataset ?collection .
  ?cho sem:hasBeginTimeStamp ?begin .
  ?cho dc:title ?title .
} 
LIMIT 60
";


$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";

$querylink = "https://data.adamlink.nl/AdamNet/all/services/endpoint#query=" . urlencode($sparqlquery) . "&contentTypeConstruct=text%2Fturtle&contentTypeSelect=application%2Fsparql-results%2Bjson&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2Fmenno%2Falles%2Fservices%2Falles%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&outputFormat=table";

$json = file_get_contents($url);

$data = json_decode($json,true);

$col1 = array();
$col2 = array();
$col3 = array();

foreach ($data['results']['bindings'] as $row) {
	$i++;
	if($i%3==0){
		$col3[] = array(
					"label" => $row['title']['value'],
					"img" => $row['img']['value'],
					"link" => $row['cho']['value'],
					"begin" => $row['begin']['value']
					);
	}elseif($i%2==0){
		$col2[] = array(
					"label" => $row['title']['value'],
					"img" => $row['img']['value'],
					"link" => $row['cho']['value'],
					"begin" => $row['begin']['value']
					);
	}else{
		$col1[] = array(
					"label" => $row['title']['value'],
					"img" => $row['img']['value'],
					"link" => $row['cho']['value'],
					"begin" => $row['begin']['value']
					);
	}
}

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
			<a href="index.php">home</a><br /><br /><br />
		</div>
		<div class="col-md-10">
		</div>
	</div>


	<div class="row">
		<div class="col-md-4">
			<h3>broader</h3>
			<?php foreach ($broader as $k => $v) { ?>
				<a href="items.php?term=<?= $k ?>"><?= $v ?></a><br />
			<?php } ?>
		</div>
		<div class="col-md-4">
			<h2><?= $term ?></h2>
		</div>
		<div class="col-md-4">
			<h3>narrower</h3>
			<?php foreach ($narrower as $k => $v) { ?>
				<a href="items.php?term=<?= $k ?>"><?= $v ?></a><br />
			<?php } ?>
		</div>
	</div>


	


	<div class="row">
		<div class="col-md-12 querylink">
			<a target="_blank" href="<?= $querylink ?>">SPARQL it yourself &gt;</a>
		</div>
	</div>




	
</div>


<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">
			<?php foreach ($col1 as $cho) { ?>
				<a target="_blank" href="<?= $cho['link'] ?>"><img src="<?= $cho['img'] ?>" /></a>
				<h3><?= $cho['label'] ?> <?= $cho['begin'] ?></h3>
			<?php } ?>
		</div>
		<div class="col-md-4">
			<?php foreach ($col2 as $cho) { ?>
				<a target="_blank" href="<?= $cho['link'] ?>"><img src="<?= $cho['img'] ?>" /></a>
				<h3><?= $cho['label'] ?> <?= $cho['begin'] ?></h3>
			<?php } ?>
		</div>
		<div class="col-md-4">
			<?php foreach ($col3 as $cho) { ?>
				<a target="_blank" href="<?= $cho['link'] ?>"><img src="<?= $cho['img'] ?>" /></a>
				<h3><?= $cho['label'] ?> <?= $cho['begin'] ?></h3>
			<?php } ?>
		</div>
	</div>
</div>


	








</body>
</html>
