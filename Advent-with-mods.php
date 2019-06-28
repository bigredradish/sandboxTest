<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
	
class Advent extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> model('advent_model');
		$this -> load -> helper('advent_helper');
	}
//maybe get rid of $year
public function index($year=NULL){
//set the date
	new DateTimeZone('Europe/London');
	$today = new DateTime('now');
	
	//$not_this_year = false;
	if(!is_null($year)){
		if($year !== $today->format('Y')){
			$year = $today->format('Y');
			//$not_this_year = true;
		}
		$data = array();

		$data['today'] = $today;
		$show_day = '';

		if($today->format('m')==='12' && $today->format('j') > '0' && $today->format('j') <= '25'){
			$show_day = $today->format('j');
		}

		//lat long for bournville for moon
		$lat = 52.424062;
		$long = -1.933692;
		$zenith=90+50/60;
		$offset = $today->getOffset() / 3600;    // difference between GMT and local time in hours
	
		$sunrise = date_sunrise($today->getTimestamp(), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $offset);
		$sunset = date_sunset($today->getTimestamp(), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $offset);
		$timeNow = DateTime::createFromFormat('H:i', $today->format('H:i'));
		$sunRiseTime = DateTime::createFromFormat('H:i', $sunrise);
		$sunSetTime = DateTime::createFromFormat('H:i', $sunset);
		$timeToSunset = $timeNow->diff($sunSetTime);
		$timeToSunrise = $timeNow->diff($sunRiseTime);
		if($timeToSunset->format('%R') === '+' && $timeToSunset->format('%H') === 0 &&($timeToSunset->format('%I') >= 0 && $timeToSunset->format('%I') <= 59)){
			echo 'Sunset';
		}
		if($timeToSunrise->format('%R') === '+' && $timeToSunrise->format('%H') === 0 &&($timeToSunrise->format('%I') >= 0 && $timeToSunrise->format('%I') <= 59)){
			echo 'Sunrise';
		}
		$rise_interval = $today->diff($sunRiseTime);
		$set_interval = $today->diff($sunSetTime);
		$day_interval = $sunRiseTime->diff($sunSetTime);
		$header_data = 'class="header';
		$header_class = ' header-night';
		if($rise_interval->format('%R') === '-' && $set_interval->format('%R') === '+'){
		    //echo 'day time' . $rise_interval->format('%R') . ' ' .$set_interval->format('%R');
		    $header_class = ' header-day';
		}
		if($timeToSunset->format('%R') === '+' && $timeToSunset->format('%H') === '00' &&($timeToSunset->format('%I') >= '00' && $timeToSunset->format('%I') <= '59')){
			$header_class = ' header-sunset';
		}
		if($timeToSunrise->format('%R') === '+' && $timeToSunrise->format('%H') === '00' &&($timeToSunrise->format('%I') >= '00' && $timeToSunrise->format('%I') <= '59')){
			$header_class = ' header-sunrise';
		}
		$data['header_data'] = 'class="header'. $header_class .'" data-sunrise="'.$sunrise.'" data-sunset="'.$sunset.'"';


		$advent_block = '';
		$modal_block = '';
		$extra_block = '';
		$is_done = false;
		$door_data = $this->advent_model->get_advent_data($year);

		if($door_data){
			foreach ($door_data as $row) {
				 $advent_block .= '<div class="col-lg-3 col-md-4 col-sm-6">';
                    $advent_block .= '<div class="card card-block card-flat text-xs-center">';
                        $advent_block .= '<h3 class="card-title"><strong>Day '.$row['id'].'</strong></h3>';
                        if(!is_null($row['open'])){
                        	$advent_block .= '<img class="img-fluid rounded-circle img-advent" src="'.base_url('assets/advent/2016/img/icons/open_').$row['go'].'.png" />';
                        }else{
                        	$advent_block .= '<img class="img-fluid rounded-circle img-advent" src="'.base_url('assets/advent/2016/img/icons/closed_').$row['id'].'.png" />';
                        }

							$the_now_day = DateTime::createFromFormat('Y-m-d', $today->format('Y-m-d'));
							$the_open_day = DateTime::createFromFormat('Y-m-d', $row['startDate']);

							$interval = $the_now_day->diff($the_open_day);

							$descr = ($interval->format('%d') === '1') ? ' Day':' Days';//this is a string				

								//today - could also be:  if($the_now_day == $the_open_day)
								if($interval->format('%d') === '0'){
									 if(!is_null($row['open'])){
										$advent_block .= '<span class="btn btn-dark btn-block"><i class="fa fa-map-marker"></i> <strong>'.$row['coords'].'</strong></span>';
									}else{
										$advent_block .= '<button class="btn btn-dark btn-block" data-id="'.$row['id'].'" data-toggle="modal" data-target="#QandA'.$row['id'].'">Open&hellip;</button>';
									}
								//the past - could also be:  if($the_now_day > $the_open_day)
								}elseif($interval->format('%R') === '-'){
									if(!is_null($row['open'])){
										$advent_block .= '<button id="show-again'.$row['id'].'" class="btn btn-dark btn-block btn-show" data-id="'.$row['id'].'">Show Me Again&hellip;</button>';
									}else{
										$advent_block .= '<button class="btn btn-dark btn-block" data-id="'.$row['id'].'" data-toggle="modal" data-target="#QandA'.$row['id'].'">Open&hellip;</button>';
									}
								//the future - could also be:  if($the_now_day < $the_open_day)
								}elseif($interval->format('%R')==='+'){
									$advent_block .= '<button class="btn btn-dark btn-block" data-id="'.$row['id'].'" disabled="disabled">'.$interval->days. ' ' . $descr.' to go!</button>';
								}
								//disabled="disabled">'.$interval->format('%d').$descr.' to go!</button>';
                        $advent_block .= '</div>';
                      $advent_block .= '</div>';

        /*
         * if today or in the past AND not open - make modal for each day
         * make modal data
         *
       	 */
		if(($interval->format('%d') === '0' || $interval->format('%R') === '-') && is_null($row['open'])){

		    $modal_block .= '<div id="QandA'.$row['id'].'" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">';
		      $modal_block .= '<div class="modal-dialog modal-lg">';
		        $modal_block .= '<div class="modal-content modal-content-flat">';
		    
		          $modal_block .= '<div class="modal-header">';
		            $modal_block .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
		              $modal_block .= '<span aria-hidden="true">×</span>';
		            $modal_block .= '</button>';
		            $modal_block .= '<h4 class="modal-title text-xs-center" id="myLargeModalLabel">'.$row['q'].'</h4>';
		          $modal_block .= '</div>';
		          $modal_block .= '<div class="modal-body">';
             $modal_block .= '<div class="row">';
		                  $modal_block .= '<div class="col-sm-12">';
		                        $modal_block .= '<div class="bd-example">';
		                        	$row['card_board'] = null;//test purposes only
		                        	if($row['type'] == '1'){
		                        		// multi-choice
		                        		$arr_answers = array(
															'a1' => $row['a1'],
															'a2' => $row['a2'],
															'a3' => $row['a3'],
															'a4' => $row['a4']
														);
			                        		if(!is_null($row['card_board'])){
			                        			// POKER STUFFS
			                        				// QUESTION
			                        				// DEALERS TOP AREA
			                        				// ANSWERS AREA
											} else {
												foreach($arr_answers as $key => $answer){
													$modal_block .= '<button type="button" class="answer btn btn-dark btn-lg btn-block animated slideInLeft" data-ans_id="'.$key.'" data-day="'.$row['id'].'">'.$answer.'</button>';
												}
			                        		}
		                        		}
		                        	}elseif($row['type'] == '2'){
		                        		// input box
		                        		$modal_block .= '<input id="input-answer" class="answer-input form-control" type="text" name="input-answer" value="" placeholder="your answer" autofocus="autofocus" />';
		                        		$modal_block .= '<button id="input-answer-btn" type="button" class="btn btn-dark btn-lg btn-block btn-answer animated zoomIn" data-day="'.$row['id'].'">Submit Answer</button>';
		                        		//Hmmm
		                        	}
		                            $modal_block .= '<div id="response-area-'.$row['id'].'" class="text-xs-center"></div>';
		                        $modal_block .= '</div>';
		                    $modal_block .= '</div>';
						$modal_block .= '</div>';
		          $modal_block .= '</div>';
		        $modal_block .= '</div>';
		      $modal_block .= '</div>';
		    $modal_block .= '</div>';

		}//else stays empty
			}
		}else{
			$advent_block .= 'Oooops - something went wrong!';
			//make this a countdown to next Christmas
		}
		
		//do the score
		$score_block = '';
			if(empty($show_day)){ 
				$score_block .= '<h2>Happy Advent!</h2>';
                $score_block .= '<p>Each day you will get a question and 4 possible answers&hellip; <strong>Warning</strong> the more attempts you take to answer the question, the less points you will get.  Like last year, when you answer the question corrrectly you will get the co-ordinates of where the \'advent present\' will be!</p>';
                $score_block .= '<p> There may be a <strong>bonus prize</strong> at the end depending on the number of points you have accumulated! <em>So, if in doubt, do some research!</em></p>';
             }else{
				$getScore = $this->advent_model->getScores($year);

				if($getScore){
					$the_array = array();
						foreach($getScore as &$row){
							$val = $row['go'];
								$val = 3-$val;
								array_push($the_array,$val);
							}
							$countGo = count($the_array);
							$currentScore = array_sum($the_array);
							$maxScore = $countGo*3;
							$percent = round(($currentScore/$maxScore)*100,1);

						$score_block .= '<div class="col-lg-2 col-md-3">';
						$score_block .= '<img class="img-fluid" src="'.base_url('assets/advent/2016/img').'/krim-beu-score.png" />';
						$score_block .= '</div>';
						$score_block .= '<div class="col-lg-10 col-md-9">';	
						$score_block .= '<span class="text-xs-center" id="score-caption"><strong>'.$countGo.'</strong>/25 questions answered.</span>';
						$score_block .= '<progress class="progress progress-striped progress-info" value="'.$countGo.'" max="25"></progress>';
						$score_block .= '<span class="text-xs-center">Accuracy: <strong>'.$percent.'%</strong></span>';
						$score_block .= '<progress class="progress progress-striped progress-danger" value="'.$currentScore.'" max="'.$maxScore.'"></progress>';
						$score_block .= '</div>';
						}
			}


		$data['xmas_block'] = daysUntilXmas($today->format('Y'), true);		
		$data['show_day'] = $show_day;
		$data['score_block'] = $score_block;
		$data['extra_block'] = $extra_block;
		$data['advent_block'] = $advent_block;
		$data['modal_block'] = $modal_block;
		$data['year'] = $year;
	
		//echo '<pre>';
			//print_r($output);
		//echo '</pre>';

		/*
		$this -> load -> helper('advent_helper');
		$data['year']=$year;
		
		$thisYear = date("Y");
		if($year === $thisYear){
			$ghost = 'present';
			$hohoho = $this->advent_model->get_advent_data($year);
				if($hohoho){
					$data['advent']=$hohoho;
				}//else error
		}elseif($year > $thisYear){
			$ghost = 'future';
		}elseif($year < $thisYear){
			$ghost = 'past';
		}

*/

		$this -> load -> view('advent/view_advent_header', $data);
		$this -> load -> view('advent/view_advent_main');
		$this -> load -> view('advent/view_advent_footer');
	}else{
		//sort this out...
		$year = $today->format('Y');
		//echo $year;
		redirect('/advent/'.$year, 'location', 301);
	}
}

public function checkAnswer(){
if ($this -> input -> is_ajax_request()) {
	$this->form_validation->set_rules('day', 'Day', 'required|max_length[2]');
	$this->form_validation->set_rules('answer', 'Answer', 'required|exact_length[2]');
	$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		if ($this->form_validation->run() == FALSE){
				$data['response'] = 'error';
				$data['message'] = validation_errors();
		}else{
			$year = date('Y');
			$day = $this->input->post('day');
			$answer = $this->input->post('answer');
			$q = $this->advent_model->check_answer($day,$year);//need new model
			  if($q){
			  	$go = $q['0']->go;
				if($q['0']->answer === $answer){
					$this->advent_model->set_opened($day, $year);//set opened to datestamp
						$data['response'] = 'done';
						$data['success_message'] = '<p><i class="fa fa-smile-o fa-fw" aria-hidden="true"></i> Yey, Great Job!</p>';
						$data['right'] = $answer;
				}else{
					$go++;
					$this->advent_model->update_go($day,$year,$go);//does this work
						$data['response'] = 'error';
						$data['fail_message'] = '<p><i class="fa fa-frown-o fa-fw" aria-hidden="true"></i> Nope, Try Again.</p>';
						$data['wrong'] = $answer;
				}	
			  }else{
				$data['response'] = 'error';
				$data['message'] = '<p><i class="fa fa-meh-o fa-fw" aria-hidden="true"></i> Ooops, Daddy messed up!</p>';
			  }
		}
			//generate output
			header('Content-Type: application/json');
			echo json_encode($data);
		}
}

public function checkInputAnswer(){
if ($this -> input -> is_ajax_request()) {
	$this->form_validation->set_rules('day', 'Day', 'required|max_length[2]');
	$this->form_validation->set_rules('answer', 'Answer', 'required|trim');
	$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		if ($this->form_validation->run() == FALSE){
				$data['response'] = 'error';
				$data['fail_message'] = validation_errors();
		}else{
			$year = date('Y');
			$day = $this->input->post('day');
			$answer = $this->input->post('answer');
			$q = $this->advent_model->check_input_answer($day,$year);//need new model
			  if($q){
			  	$go = $q['0']->go;
				if(trim(strtolower($q['0']->input)) === trim(strtolower($answer))){
					$this->advent_model->set_opened($day, $year);//set opened to datestamp
						$data['response'] = 'done';
						$data['success_message'] = '<p><i class="fa fa-smile-o fa-fw" aria-hidden="true"></i> Yey, Great Job!</p>';
				}else{
					//max value of go is 3
					while($go <= 2) {
						$go++;//i.e. 2++ === 3
					}
					$this->advent_model->update_go($day,$year,$go);//does this work
						$data['response'] = 'error';
						$data['fail_message'] = '<p><i class="fa fa-frown-o fa-fw" aria-hidden="true"></i> Nope, Try Again.</p>';
				}	
			  }else{
				$data['response'] = 'error';
				$data['fail_message'] = '<p><i class="fa fa-meh-o fa-fw" aria-hidden="true"></i> Ooops, Daddy messed up!</p>';
			  }
		}
			//generate output
			header('Content-Type: application/json');
			echo json_encode($data);
		}
}


public function showAgain(){
	if ($this -> input -> is_ajax_request()) {
		$this->form_validation->set_rules('day', 'Day', 'required|max_length[2]');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		if ($this->form_validation->run() == FALSE){
				$data['response'] = 'error';
				$data['message'] = validation_errors();
		}else{
			$year = date('Y');
			$day = $this->input->post('day');
			$q = $this->advent_model->get_coords($day,$year);//need new model chck to see if open not null
			  if($q){
				$data['response'] = 'done';
				$data['success_message'] = '<span class="btn btn-dark btn-block"><i class="fa fa-map-marker"></i> <strong>'.$q.'</strong></span>';
	
			  }else{
				$data['response'] = 'error';
				$data['fail_message'] = '<span class="btn btn-dark btn-block"><i class="fa fa-meh-o fa-fw" aria-hidden="true"></i> Ooops, Daddy messed up!</span>';
			  }
		}
			//generate output
			header('Content-Type: application/json');
			echo json_encode($data);
		}
}


/*
get scores
max is 3*25
foreach each day where done not null score =  3-($go) - if you get in on ego lose no points
% of number of days vs. score

*/

}
