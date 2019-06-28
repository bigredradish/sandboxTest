<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function file_get_contents_curl($url)
{
    $curl = curl_init();
	$return = array();

    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    $response = curl_exec($curl);
	
	$curl_errno = curl_errno($curl);
	$curl_error = curl_error($curl);
			
	if ($curl_errno > 0) {
		die ('cURL Error ('.$curl_errno.'): '.$curl_error);
	}
	$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	//got what we need - kill curl
	curl_close($curl);
			

	$return['responseCode'] = $responseCode;
	$return['response'] = $response;	

	return 	$return;
}

//need some checks
$html = array();
$input_url = '';
$company = '';
$page_type = '';
$locale = '';
$schema_company = '';
$twitter_username = '';
$schema_type = '';
$schema_locale = '';
$twitter_card = true;

if(!empty($_POST)){
	$input_url = $_POST['input_url'];
	$company = $_POST['company'];
	$page_type = $_POST['page-type'];

	switch ($company) {
		case 0:
			$schema_company = 'Armagard Ltd.';
			$twitter_username = 'armagard';
			break;
		case 1:
			$schema_company = 'Galleon Systems';
			$twitter_username = 'galleonsystems';
			break;
		default:
			$schema_company = '';
			$twitter_username = '';
			break;
	}

	switch ($page_type) {
		case 0:
			$schema_type = 'WebPage';
			break;
		case 1:
			$schema_type = 'Article';
			break;
		default:
			$schema_type = 'WebPage';
			break;
	}
	$parsed_host = parse_url($input_url, PHP_URL_HOST);

	$host_parts = explode('.', $parsed_host);

	if(is_array($host_parts)){
		//rough and ready locale for now
		$locale = end($host_parts);

		$schema_locale =  $locale.'_'.strtoupper($locale);

		if($locale == 'com'){
			if(count($host_parts == 3) && $host_parts[0] == 'www') {
				$schema_locale = 'en-US';
			} else {
				$schema_locale = $host_parts[0].'_'.strtoupper($host_parts[0]);
			}
		}

		if($locale == 'uk'){
			if(count($host_parts == 4) && $host_parts[0] == 'www') {
				$schema_locale = 'en-GB';
			} else {
				$schema_locale = $host_parts[0].'_'.strtoupper($host_parts[0]);
			}
		}
	}
}

if(!empty($input_url))
	$html = file_get_contents_curl($input_url);

	$output = '';

	if(!empty($html) && $html['responseCode'] == 200) {
		//parsing begins here:
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($html['response']);
		$nodes = $doc->getElementsByTagName('title');

		//get and display what you need:
		$title = $nodes->item(0)->nodeValue;

		$metas = $doc->getElementsByTagName('meta');

		for ($i = 0; $i < $metas->length; $i++)
		{
			$meta = $metas->item($i);
			//if($meta->getAttribute('name') == 'description' || $meta->getAttribute('name') == 'DESCRIPTION')
			if(strtolower($meta->getAttribute('name')) == 'description')
				$description = $meta->getAttribute('content');
			//if($meta->getAttribute('name') == 'keywords' || $meta->getAttribute('name') == 'KEYWORDS')
			if(strtolower($meta->getAttribute('name')) == 'keywords')
				$keywords = $meta->getAttribute('content');
		}
		
		//h1
		//$pageTitle = $doc->getElementsByTagName('h1');
		
		//image
		$imgs = $doc->getElementsByTagName('img');
		//foreach ($imgs as $img) {
		//	$img_sources[] = $img->getAttribute('src');
		//}
		//var_dump($img_sources);
		$img = $doc->getElementsByTagName('img');

		$xpath = new DomXpath($doc);
		$pageTitle = $xpath->query("//h1[position() = 1]");
		//$image = $xpath->query('//div[@id="main-product-image"]/following-sibling::img')->item(0);
		//$image = $xpath->query('//div[@id="main-product-image"]/img');	
		//echo 'Title: ' . $title . '<br />';
		//echo 'Description: ' . $description . '<br />';
		//echo 'Keywords: '. $keywords . '<br />';
		//echo 'Image src: ' . $img->item(1)->getAttribute('src') . '<br />';
		//echo 'Image alt: ' . $img->item(1)->getAttribute('alt') . '<br />';
		//echo 'Image title: ' . $img->item(1)->getAttribute('title') . '<br />';
		//echo 'Page title: ' . $pageTitle->item(0)->textContent . '<br />';//or nodeValue
		//echo 'test: ' .  $image->getAttribute('src') . '<br />';
		
		$output = '<meta property="og:title" content="'.$pageTitle->item(0)->nodeValue.'" />'.PHP_EOL;//
		$output .= '<meta property="og:type" content="'.$schema_type.'" />'.PHP_EOL;
		$output .= '<meta property="og:description" content="'.$description.'" />'.PHP_EOL;
		$output .= '<meta property="og:site_name" content="'.$schema_company.'" />'.PHP_EOL;
		$output .= '<meta property="og:locale" content="'.$schema_locale.'" />'.PHP_EOL;
		$output .= '<meta property="og:article:author" content="'.$schema_company.'" />'.PHP_EOL;
		$output .= '<meta property="og:url" content="'.$input_url.'" />'.PHP_EOL;
		$output .= '<meta property="og:image" content="'.$img->item(1)->getAttribute('src').'" />'.PHP_EOL;
			if($twitter_card){
				$output .= '<meta name="twitter:site" content="@'.$twitter_username.'">'.PHP_EOL;
				$output .= '<meta name="twitter:card" content="summary_large_image">'.PHP_EOL;
				$output .= '<meta name="twitter:image:alt" content="'.$img->item(1)->getAttribute('alt').'">'.PHP_EOL;
			}
		$output .= '	<script type="application/ld+json">'.PHP_EOL;
		$output .= '		{'.PHP_EOL;
		$output .= '			"@context": "http://schema.org",'.PHP_EOL;
		$output .= '			"@type": "'.$schema_type.'",'.PHP_EOL;
		$output .= '			"description": "'.$description.'",'.PHP_EOL;
		$output .= '			"url": "'.$input_url.'",'.PHP_EOL;
		$output .= '			"publisher": {'.PHP_EOL;
		$output .= '				"@type": "Organization",'.PHP_EOL;
		$output .= '				"name": "'.$schema_company.'",'.PHP_EOL;
		$output .= '				"logo": "'.$img->item(0)->getAttribute('src').'"'.PHP_EOL;
		$output .= '			},'.PHP_EOL;
		$output .= '			"headline": "'.$pageTitle->item(0)->nodeValue.'",'.PHP_EOL;
		$output .= '			"image": ["'.$img->item(1)->getAttribute('src').'"]'.PHP_EOL;
		$output .= '		}'.PHP_EOL;
		$output .= '	</script>'.PHP_EOL;
	}
?>
	<!doctype html>

	<html lang="en">
	<head>
	  <meta charset="utf-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	  <!-- Bootstrap CSS -->
	  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	  <title>Schemacalifragilisticexpialicoder</title>
	  <meta name="description" content="Schema Data created from static html | Schemacalifragilisticexpialicoder">
	  <meta name="author" content="Dave">
	</head>
	
	<body>
		<header>
			<nav>
			</nav>
		</header>
		<main role="main">
		<div class="p-3 p-md-5 text-white bg-dark mb-2">
			<div class="container">
				<h1>Schemacalifragilisticexpialicoder</h1>
			</div>
		</div>
		<div class="container">
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" method="post" accept-charset="utf-8">
				<div class="form-group">
					<label for="input_url">Input URL</label>
					<input type="url" class="form-control" id="input_url" name="input_url" aria-describedby="urlHelp" placeholder="Page to Schemafy" value="<?php echo $input_url; ?>">
					<small id="urlHelp" class="form-text text-muted">Enter full url including http or https.</small>
				</div>
				<p>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" id="product-family-a" name="company" value="0" <?php echo ($company=='0')?'checked':'' ?>>
						<label class="form-check-label" for="product-family">Armagard Ltd</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" id="product-family-g" name="company" value="1" <?php echo ($company=='1')?'checked':'' ?>>
						<label class="form-check-label" for="product-family">Galleon Systems</label>
					</div>
				</p>
				<p>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" id="page-type-page" name="page-type" value="0" <?php echo ($page_type=='0')?'checked':'' ?>>
						<label class="form-check-label" for="page-type">Page</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" id="page-type-article" name="page-type" value="1"<?php echo ($page_type=='1')?'checked':'' ?>>
						<label class="form-check-label" for="page-type">Article</label>
					</div>
				</p>
				<input type="submit" name="formSubmit" class="btn btn-primary mb-2" value="Punch it Chewie!" />
			</form>

			<div class="form-group">
				<label for="output">Output - put me in the &lt;head&gt;</label>
				<?php
				echo '<textarea id="output" name="output" class="form-control" rows="30">';
					echo $output; 
				echo '</textarea>';
				?>
			</div>

		</div>
		</main>
	<footer></footer>  
	 <!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	</body>
	</html>