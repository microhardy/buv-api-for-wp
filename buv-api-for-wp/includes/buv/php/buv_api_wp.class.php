<?php
/***
 * Buv_WP Klasse
 * 
 * @author Thonmas Hardegger
 * @version 20.01.2018
 * @todo Auf neue API umschreiben / die Funktionen bebehalten
 * STATUS: second Review
 */

class BuvException extends RuntimeException {}
 
if ( !class_exists( 'Buv_WP' ) ) {
	
class Buv_WP {
	private $options; // WordPress Options Array
	private $Season = 'saison2017_2018'; // Saison
	private $ClubName = 'Chur Unihockey'; // Club Name
    private $Team_IDs = '26;84;25;15'; // Chur Unihockey D Junioren Saison 2016-2017
	private $League = 'D'; // Leage-ID "Junioren D" 
	private $RoundName_D = 'Vorrunde';
	private $RoundName_E = 'Vorrunde';
	private $Round_D = '1'; // 1 = Vorrunde / 2 = Hauptrunde / 3 = Challangerunde
	private $Round_E = '1'; // 1 = Vorrunde / 2 = Hauptrunde 
	private $use_cache = True;
    private $css_path = NULL;

		
	public function __construct() {
		/* ------------------------------------------------------------------------------------ */
		// API 1.0
		add_shortcode ( 'buv-api-buv-games', array( &$this, 'api_buv_getGames' ) );  // Display Games of the League
		add_shortcode ( 'buv-api-buv-table', array( &$this, 'api_buv_getTable' ) );  // Display Table of the League

		add_shortcode ( 'buv-api-buv-games_E', array( &$this, 'api_buv_getGames_E' ) );  // Display Games of the E-Kids League
		add_shortcode ( 'buv-api-buv-games_D', array( &$this, 'api_buv_getGames_D' ) );  // Display Games of the D-Kids League

		add_shortcode ( 'buv-api-buv-results', array( &$this, 'api_buv_getLastResults' ) );  // Display Done Games (Results) of the  League
		add_shortcode ( 'buv-api-buv-nextgames', array( &$this, 'api_buv_getNextGames' ) );  // Display Next Games of the League
		add_shortcode ( 'buv-api-buv-weekendgames', array( &$this, 'api_buv_getWeekendGames' ) );  // Display Weekend-Games of the League
		
		add_shortcode ( 'buv-api-show-json', array( &$this, 'api_show_json' ) ); // show all current API JSON-Files

		// for testing & debug
		add_shortcode ( 'buv-api-show-params', array( &$this, 'api_show_params' ) );
		add_shortcode ( 'buv-api-show-vars', array( &$this, 'api_show_vars' ) );
		add_shortcode ( 'buv-api-show-processing', array( &$this, 'api_show_processing' ) );

        //************************************************************************************

		/* ------------------------------------------------------------------------------------ */
		// Action-Hooks
		add_action ( 'wp_head', array( &$this, 'check_post_meta' ) );
		add_action ( 'wp_footer', array( &$this, 'buv_check_update' ) );
 	
		$this->options = get_option( 'BUV_WP_plugin_options' );
		if ( isset( $this->options['BUV_club_name'] ) ) $this->club_id = $this->options['BUV_club_name'];
		if ( isset( $this->options['BUV_league_id'] ) ) $this->League = $this->options['BUV_league_id'];
		if ( $this->League == 'E')
		  if ( isset( $this->options['BUV_E_team_ids'] ) ) $this->TeamIDs = $this->options['BUV_E_team_ids'];
		if ( $this->League == 'D')
		  if ( isset( $this->options['BUV_D_team_ids'] ) ) $this->TeamIDs = $this->options['BUV_D_team_ids'];
		if ( isset( $this->options['BUV_D_team_rounds'] ) ) $this->RoundName_D = $this->options['BUV_D_team_rounds'];
		if ( isset( $this->options['BUV_E_team_rounds'] ) ) $this->RoundName_E = $this->options['BUV_E_team_rounds'];

		if (substr_count($this->RoundName_E,'Auto')>0) {
			$api = new Buendnerunihockey_Public(); 
            $details = $api->JSON_List(); 
            $this->RoundName_E = $details->active_E_round;
		}
		switch ($this->RoundName_E) {
          case 'Vorrunde':
           $this->Round_E = '1';
          break;
          case 'Finalrunde':
           $this->Round_E = '2';
          break;
        } 
		if (substr_count($this->RoundName_D,'Auto')>0) {
			$api = new Buendnerunihockey_Public(); 
            $details = $api->JSON_List(); 
            $this->RoundName_D = $details->active_D_round;
		}
		switch ($this->RoundName_D) {
          case 'Vorrunde':
           $this->Round_D = '1';
          break;
          case 'Finalrunde':
           $this->Round_D = '2';
          break;
          case 'Challengerunde':
           $this->Round_D = '3';
          break;
        } 
	    
		/* ------------------------------------------------------------------------------------ */
		// Stylesheet verlinken wenn selektiert in Admin
		if (isset( $this->options['BUV_css_file'] )) 
		  if (substr_count($this->options['BUV_css_file'],".css")>0) {
		    $this->css_path = BUV_API_WP_PLUGIN_URL . "includes/buv/styles/".$this->options['BUV_css_file'];
		    Buendnerunihockey_Api_Public::log_me($this->css_path);
 		    //add_action( 'wp_enqueue_scripts', 'my_scripts_buv' );
 		    add_action( 'wp_enqueue_scripts', array( $this, 'my_scripts_buv' ) );
 	      }
 		// aktuelle Saison ermitteln
        $season = intval(date('Y'));
	    if (date('m') < 6) {
		  $this->Season = 'saison'.strval($season-1).'_'.strval($season);
        }
        else {
          $this->Season = 'saison'.strval($season).'_'.strval($season+1);
        }
        Buendnerunihockey_Api_Public::log_me($this->options);
	    if(isset($this->options['BUV_cache']) == 1) {
        	 $this->use_cache = TRUE; }
        else { $this->use_cache = False;}
        	 
    }	

	/* ------------------------------------------------------------------------------------ */
    public function my_scripts_buv() {
    /*** Register global styles & scripts.*/
      wp_register_style('buv-api-style-css', $this->css_path);
      wp_enqueue_style('buv-api-style-css');
  
    }

	/* ------------------------------------------------------------------------------------ */
	// BUV-Club erstellen
	private function set_club( $club_name = NULL ){
		if ( $club_name != NULL )
		  $this->ClubName = $club_name;
		// echo "<p class='error buv'>Club ".$this->club_id."<br></p>";
		
	}
	/* ----------------------------------------------------------------------------------- */
	// Funktion: BUV Teams erstellen 
	private function set_teams( $teams = NULL ) {

         if ( $teams != NULL )
		   $this->TeamIDs = $teams;
		 // echo "<p class='error buv'>Team: ".$this->team_id."<br></p>";
		
	}
	/* ------------------------------------------------------------------------------------ */
	// Funktion: BUV League erstellen
	private function set_league($league = NULL){
	  if ( $league  != NULL )
 	    $this->League = $league;
	}
	// Funktion: BUV League Round erstellen
	private function set_round_E($round = NULL){
	  if ( $round  != NULL )
 	    $this->Round_E = $round;
	}

	private function set_round_D($round = NULL){
	  if ( $round  != NULL )
 	    $this->Round_D= $round;
	}

	
	/* ------------------------------------------------------------------------------------ */
	// Funktion: Überprüfung der Meta-Values eines Post 
	function check_post_meta(){
		// Ändert Club, wenn eine Club-ID im Post-Meta-Feld eingegeben wurde.
		if ( get_post_meta( get_the_ID(), 'BUV Club Name', true ) != "" ) {
			$this->set_club( get_post_meta( get_the_ID(), 'BUV Club Name', true ) );
		}
		// Ändert Team, wenn eine Team-ID im Post-Meta-Feld eingegeben wurde.
		if ( get_post_meta( get_the_ID(), 'BUV Team IDs', true ) != "" ) {
		 $this->set_team( get_post_meta( get_the_ID(), 'BUV Team IDs', true ) );
		}
		//Liga
		// Ändert Liega, wenn eine Liega-ID im Post-Meta-Feld eingegeben wurde.
		if ( get_post_meta( get_the_ID(), 'BUV League', true ) != "" ) {
		 $this->set_league( get_post_meta( get_the_ID(), 'BUV League', true ) );
		}
		// Ändert Liega-Runde, wenn eine Runden-ID im Post-Meta-Feld eingegeben wurde.
		if ( get_post_meta( get_the_ID(), 'BUV Round', true ) != "" ) {
		 $this->set_round( get_post_meta( get_the_ID(), 'BUV Round', true ) );
		}

	}
	

	/* ------------------------------------------------------------------------------------ */
	// ??
	// Funktion: Update das BUV-Team-Objekt in der Datenbank, sofern nötig
	function buv_check_update(){
		if ( isset ( $this->Team_D_IDs) ) {
			echo "<p class='error buv'>Keine Default TeamIDs vorhanden!</p>";
		}
	}	
	
	/* ------------------------------------------------------------------------------------ */
	// Funktion: Log-Daten ausgeben
	public function get_log(){
	if ( current_user_can('activate_plugins') ) // only for Administrators
     echo '<pre style="clear: both; width: 80%; padding: 2% 10%; margin: 0 auto; background-color: rgb(234,90,90)"><strong>Logdaten des BUV Frameworks</strong><br>----------------------------<br>', 
     print_r( BuvApiManager::getInstance()->getLog() ), '</pre>';
    }

 
	// *********************************************************************************************
	//
	// API 1.0
	//
	//
    function api_buv_getTable($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "round" => NULL,
         "team" => NULL,
        ), $atts));
        if ($league == 'D') {
         if (!isset($round)) {$round = $this->Round_D;}
        }
        else  if (!isset($round)) {$round = $this->Round_E;}
        
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getTable($season, $league, $round, $team, $cache);
	}

	function api_buv_getGames($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "round" => NULL,
         "mode" => 'filter',
         "team" => NULL,
        ), $atts));
        if ($league == 'D') {
          if (!isset($round)) {$round = $this->Round_D;}
        }
        else  if (!isset($round)) {$round = $this->Round_E;}
        
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}

	function api_buv_getGames_E($atts){
		extract(shortcode_atts(array(
	     "league" => 'E',
         "mode" => 'filter',
         "team" => NULL,
        ), $atts));
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}

	function api_buv_getGames_D($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "mode" => 'filter',
         "team" => NULL,
        ), $atts));
        if (!isset($round)) {$round = $this->Round_D;}
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}


    function api_buv_getLastResults($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "round" => NULL,
         "mode" => 'results',
         "team" => NULL,
        ), $atts));
        if ($league == 'D') {
          if (!isset($round)) {$round = $this->Round_D;}
        }
        else if (!isset($round)) {$round = $this->Round_E;}

		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}

	function api_buv_getNextGames($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "round" => NULL,
         "mode" => 'next',
         "team" => NULL,
        ), $atts));
        if ($league == 'D') {
          if (!isset($round)) {$round = $this->Round_D;}
        }
        else  if (!isset($round)) {$round = $this->Round_E;}
      
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}

	function api_buv_getWeekendGames($atts){
		extract(shortcode_atts(array(
	     "league" => 'D',
         "round" => NULL,
         "mode" => 'weekend',
         "team" => NULL,
        ), $atts));
        if ($league == 'D') {
          if (!isset($round)) {$round = $this->Round_D;}
        }
        else  if (!isset($round)) {$round = $this->Round_E;}
         
		$season = $this->Season;
 	    $cache = $this->use_cache;
 	    return Buendnerunihockey_Api_Public::api_buv_getGames($season, $league, $round, $team, $mode, $cache);
	}


    function api_show_json(){
        $season = $this->Season;
		return Buendnerunihockey_Api_Public::api_show_json($season);	
	}

	function api_show_params(){
 		if ( !isset ( $this->team ) ) $this->set_team();
 	    if ( !isset ( $this->club ) ) $this->set_club();
 	    $season = $this->season;
 	    $club_ID = $this->club_id;
 	    $team_ID = $this->team_id;
 	    $mode = "team";
		return Buendnerunihockey_Api_Public::api_show_params($season, $club_ID, $team_ID, $mode);	
	}

	function api_show_vars(){
		echo 'Club-ID: '.$this->club_id.'<br>';
		echo 'Team-ID '.$this->team_id.'<br>';
		echo 'Player-ID: '.$this->player_id.'<br>';
		echo 'Sponsor Name: '.$this->sponsor_name.'<br>';
	    echo 'Sponsor Sub: '.$this->sponsor_sub.'<br>';
	    echo 'Sponsor Logo: '.$this->sponsor_logo.'<br>';
	    echo 'Sponsor Link: '.$this->sponsor_link.'<br>';
	    echo 'Sponsor Link Title: '.$this->sponsor_link_title.'<br>';
	    echo 'Saison: '.$this->season.'<br>';
		echo 'Liga ID: '.$this->league_id.'<br>';
 	    echo 'Liga Class: '.$this->league_class.'<br>';
 	    echo 'Liga Gruppe: '.$this->league_group.'<br>';
 	    echo 'Liga Runde: '.$this->league_round.'<br>';
	}

    function api_show_processing(){
	  echo 'processing...';
      $moment = "<img src=\"http://www.churunihockey.ch/picture_library/cu/icons/processing.gif\" title=\"Moment bitte!\">";
      echo $moment;
      flush(); 
    }
	

} // End class buv_WP
	
function Buv_WP_init() {
	 global $Buv_WP;
	 date_default_timezone_set("Europe/Paris");
	 $Buv_WP = new Buv_WP(); 	
 }

 /*
 //Buendnerunihockey_Api_Public::log_me("BUV Init");
 //$plugin_options = get_option( 'BUV_WP_plugin_options' );
 //Buendnerunihockey_Api_Public::log_me($plugin_options);
 */

 add_action('plugins_loaded', 'Buv_WP_init');

} // End if ( !class_exists( 'Buv_WP' ) )
else echo "<p class='error buv'>Es besteht eine Kollision mit einer anderen Klasse welche ebenfalls Buv_WP heisst!</p>";