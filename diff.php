<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

  <title>2018 Advent Calendar TEST</title>
  <meta name="description" content="2018 Advent Calendar">
  <meta name="author" content="BigRedRadish">

  <link rel="stylesheet" href="css/styles.css?v=1.0">
    <style>
.table {
	width: 100%;
	background-color: mediumseagreen;
	padding: 50px;
}
.deck-wrapper {
	text-align: center;
	margin: 0 auto;
}
.deck {
	list-style-type: none;
	padding: 0;
	margin-bottom: 0;
}
.card-sm {
    display: inline-block;
    min-width: 42px;
    padding: 18px 4px;
    text-align: center;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 3px;
    margin-right: 4px;
}
.hearts, .diams {
	color: red;
}
.spades, .clubs {
	color: black;
}
</style>
</head>
<body>
<?php
$date1 = new DateTime("now");
//$date2 = new DateTime("tomorrow");
//$date2 = new DateTime("next monday");
$date2 = new DateTime("now");

$interval = date_diff($date1, $date2);
echo 'Interval: '.$interval->format('%R%a days');
echo '<br />';
echo ' :D1 == D2 '.var_dump($date1 == $date2);
echo '<br />';
echo ' :D1 < D2'.var_dump($date1 < $date2);
echo '<br />';
echo ' :D1 > D2'.var_dump($date1 > $date2);
echo '<br />';
echo '<pre>';
	print_r($interval);
echo '</pre>';


$today = new DateTime("now");
$the_now_day = DateTime::createFromFormat('Y-m-d', $today->format('Y-m-d'));
$the_open_day = DateTime::createFromFormat('Y-m-d', '2018-11-29');


echo $the_now_day->format("Y-m-d H:i:s");
echo '<br />';
echo $the_open_day->format("Y-m-d H:i:s");
echo '<br />';

$interval2 = $the_now_day->diff($the_open_day);
echo 'Interval: '.$interval2->format('%R%a days');

echo '<br />';
echo ' :D1 == D2 '.var_dump($the_now_day == $the_open_day);
echo '<br />';
echo ' :D1 < D2'.var_dump($the_now_day < $the_open_day);
echo '<br />';
echo ' :D1 > D2'.var_dump($the_now_day > $the_open_day);
echo '<br />';

echo '<pre>';
	print_r($interval2);
echo '</pre>';

echo '<br />';

/**
 * yesterday 	= -1 d
 * today 		= +0 d
 * tomorrow 	= +1 d
 */

if($the_now_day == $the_open_day)
	echo 'Today';//openable and make modal

if($the_now_day < $the_open_day)
	echo 'The Futar';// not yet

if($the_now_day > $the_open_day)
	echo 'The Past';//done unless is_null($row['open'] - openable + modal

echo '<br />';

//Cards

//suits and faces
$suits = array (
    "Spades", "Hearts", "Clubs", "Diamonds"
);
 
$faces = array (
    "Two", "Three", "Four", "Five", "Six", "Seven", "Eight",
    "Nine", "Ten", "Jack", "Queen", "King", "Ace"
);

//build a deck
$deck = array();
 	//all suits + all faces
	foreach ($suits as $suit) {
	    foreach ($faces as $face) {
	        $deck[] = array ("face"=>$face, "suit"=>$suit);
	    }
	}

//echo '<pre>';
//	print_r($deck);
//echo '</pre>';

//shuffle the deck
shuffle($deck);

//echo '<pre>';
//	print_r($deck);
//echo '</pre>';
//return (and remove) the first array value (card) of the shuffled deck
$card = array_shift($deck);

//show card
//echo $card['face'] . ' of ' . $card['suit'];

echo '<br />';



//would need to add db column of e.g card_board to hold 5? cards in format ah,1d,5s etc. then render to screen from there
//inputs would need to be the same - 2 cards rendered onto a button

//if(!is_null($row['card_board'])){
	// will be a poker question 
	// or could have  type '3' - but would function like type '2' 
//}

//put in helper
function suitsYou($suit_short)
{
	switch ($suit_short) {
	    case 'h': 
	        $suit = 'hearts';
	        break;
	    case 's':
	        $suit = 'spades';
	        break;
	    case 'c':
	        $suit = 'clubs';
	        break;
	    case 'd':
	        $suit = 'diams';
	        break;
	}
return $suit;
}

//$card_one = '4h';
//$arrCardOne = str_split($card_one);
//echo '<div class="'.suitsYou($arrCardOne[1]).'">'.$arrCardOne[0].'&'.suitsYou($arrCardOne[1]).';</div>';

$hand_in = 'As,Ad,Js,Qs,10h';
$arrHand = explode(',',$hand_in);
if(!empty($arrHand)){
//$hand_out = '<button>';
	$hand_out = '<ul class="deck">';
		foreach ($arrHand as $key => $card) {
			if(strlen($card) >= 3){
				$arrCard = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$card);
			} else {
				$arrCard = str_split($card);
			}
	$hand_out .= '<li class="card-sm shadow '.suitsYou($arrCard[1]).'">'.$arrCard[0].'&'.suitsYou($arrCard[1]).';</li>';
	}
	$hand_out .= '</ul>';
	//$hand_out .= '</button>';
} //else { 'something went wrong'}
echo '<div class="table"><div class="deck-wrapper">';
echo $hand_out;//********************
echo '</div></div>';
echo '<br />';

//$arr_answers = array();

$a1 = '6h,10d';

$a2 = 'Ac,Ah';

$a3 = '3h,4h';

$a4 = '6s,8d';

//array_push($arr_answers, $a1,$a2,$a3,$a4);

//try to get like this from db
$arr_answers = array(
	'a1' => $a1,
	'a2' => $a2,
	'a3' => $a3,
	'a4' => $a4
);

//var_dump($arr_answers);

$dealt_out = '';
foreach ($arr_answers as $ans => $cards) {
	$arrDealt = explode(',',$cards);
	if(!empty($arrDealt)){
		//$dealt_out .= '<button>';

		$dealt_out .= '<button type="button" class="answer btn btn-default btn-block animated slideInLeft" data-ans_id="'.$ans.'" data-day="now">';//now will be the day id
			$dealt_out .= '<ul class="deck">';
				foreach ($arrDealt as $key => $card) {
					if(strlen($card) >= 3){
						$arrCard = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$card);
					} else {
						$arrCard = str_split($card);
					}
				$dealt_out .= '<li class="card-sm '.suitsYou($arrCard[1]).'">'.$arrCard[0].'&'.suitsYou($arrCard[1]).';</li>';
				}
			$dealt_out .= '</ul>';
		$dealt_out .= '</button>';
	}
}
echo $dealt_out;//********************
?>
 <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>