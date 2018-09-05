<?php
/***
 * Classes that return HTML Code from BUV Classes like SuhvClub or SuhvTeam
 * 
 * @author Thomas Hardegger 
 * @version  05.09.2018
 * @todo Auf neue API umschreiben / die Funktionen bebehalten
 * STATUS: Change
 * 06.07.2018	kein Loop wenn keine in dieser Saison noch keine Runden definiert.
 * 05.09.2018 undef count() groups
 */


class Buendnerunihockey_Api_Public {

private static function cacheTime() {
        
  $cbase = 5*60; // 5 Min.
  $tag = date("w");
  $time = time();
  
    $cacheValue = array(2*$cbase,2*$cbase,3*$cbase,6*$cbase,6*$cbase,6*$cbase,2*$cbase);

  return($cacheValue[$tag]);
}

private static function nearWeekend() {
               // So Mo Di (Mi=4) Do Fr Sa //
  $dayline = array(0,-1,-2,4,3,2,1);
  $tag = date("w");
  //echo $tag." tag id <br />";
  $today = strtotime("today");
  //echo date("d.m.Y",$today )." heute <br />";
  $daytoSunday = $dayline[$tag];
  //echo $daytoSunday." day to sunday<br />";
  $sunday = strtotime($daytoSunday." day",$today);
  $saturday = strtotime("-1 day",$sunday);
  $friday = strtotime("-2 day",$sunday);
  //echo date("d.m.Y",$friday )." Freitag <br />";
  //echo date("d.m.Y",$saturday)." Samstag  <br />";
  //echo date("d.m.Y",$sunday )." Sonntag  <br />";
  $weekendDays= array("Freitag"=>$friday,"Samstag"=>$saturday,"Sonntag"=>$sunday);

  return($weekendDays);
}

private static function getTeams($season, $league, $round) {

    echo "Season ".$season." League ".$league." Round ". $round;
    $api = new Buendnerunihockey_Public(); 
    $details = $api->leagueTeams($season, $league, $round,'teams'); 
    echo "Elements: ".$details->elements."<br>";
    $teams = $details->groups[0]->teams;
    $teams_count = count($teams);
    echo "teamcount: ".$teams_count."<br>";
    $final_team_array = array();
    foreach ($teams as $team) {
      $final_team_array[] = $team->teamid;
      echo "team:".$team->teamid."<br>";
    }
    return($final_team_array);
}

private static function isMemberOfTeams($season, $league, $round, $team) {

    $isMember = TRUE;
    if (isset($team)) {
      $isMember = FALSE;
      echo "team IS:".$team."<br>";
      $api = new Buendnerunihockey_Public(); 
      $details = $api->leagueTeams($season, $league, $round,'teams'); 
      // echo "Elements: ".$details->elements."<br>";
      foreach ($details->groups as $group) {
        $teams = $group->teams;
        foreach ($teams as $teaminfo) {
          if ($teaminfo->teamid = $team) {
            $isMember = TRUE;
            // echo "team-Match:".$team->teamid."<br>";
          }
        }
      }
    }
    return($isMember);
}


/* ---------------------------------------------------------------------------------------------------- */
  public static function api_buv_getGames($season, $league, $round, $team, $mode = 'normal' , $cache) {
    // Modes: 
    // normal = filtern nach Teams
    // full = keine Filterung, alle Teams
    // next = Nächste Spiele
    // results = Spiel-Resultate
    $debug = FALSE;
    $trans_Factor = 1;
    $transient = "buv-api-".$league."buv_getGames".$season.$round.$team.$mode;
    $value = get_transient( $transient );
    $title_head = "";
    $next_game_weekend = "";
    $found = FALSE;
    $gamefound = FALSE;
    $groupfound = FALSE;

    if (!$cache) { $value = False; }

    if (($value == False)) {

      $start_date = strtotime("today");
      $end_date = strtotime("today");

      if (!isset($round)) $round = '1';

      $filter = TRUE;
      $full = FALSE;
      $results = FALSE;
      $next = FALSE;
      $weekend = FALSE;

      switch ($mode) {
      case 'full':
        $filter = FALSE;
        $full = TRUE; //echo "full - switch <br />";
        break;
      case 'results':
        $filter = FALSE;
        $results = TRUE; //echo "results - switch <br />";
        break;
      case 'next':
        $filter = FALSE;
        $next = TRUE; //echo "next - switch <br />";
        break;
      case 'weekend':
        $filter = FALSE;
        $weekend = TRUE; //echo "weekend - switch <br />";
        $weekendDays = Buendnerunihockey_Api_Public::nearWeekend();
        $start_date = $weekendDays["Freitag"];
        $end_date = $weekendDays["Sonntag"];
        //echo date("d.m.Y - H:i",$start_date)." Startweekend  <br />";
        //echo date("d.m.Y - H:i",$end_date)." Endweekend  <br />";
        break;
      } 

      $go =  time();
      $plugin_options = get_option( 'BUV_WP_plugin_options' );
      if ($league == "D") {
        $teamIDs = explode(';',$plugin_options['BUV_D_team_ids']);
        $league_str = 'junioren_d';
        //$title_head = 'Junioren D - ';
      }
      else {
         $teamIDs = explode(';',$plugin_options['BUV_E_team_ids']);
         $league_str = 'e_kids';
         //$round = '1';
      }
      if (isset($team)) { $teamIDs = array($team);}

      if (($next or $results) and $debug) {
        echo "Team ".$team."<br>";
        echo print_r(array('function' => 'buv_club_getGames', 'season' => $season, 'teamIDs' =>  $teamIDs, 'league' => $league_str, 'round' => $round, 'mode' => $mode));
        echo "<br>";
      }


      /* TEST 

      $team_test = Buendnerunihockey_Api_Public::isMemberOfTeams($season, $league_str, $round, $team);
      Buendnerunihockey_Api_Public::log_me(array('Team-Test' => $team_test));

      /* */

      Buendnerunihockey_Api_Public::log_me(array('function' => 'buv_club_getGames', 'season' => $season, 'teamIDs' =>  $teamIDs, 'league' => $league_str, 'round' => $round));

      $skip = "<br />";
      $html = "";

      $tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
      $tag = date("w");
      $wochentag = $tage[$tag];

      $api = new Buendnerunihockey_Public(); 
      $details = $api->leagueGames($season, $league_str, $round,'tournaments'); 
      //Buendnerunihockey_Api_Public::log_me( $details);

      $tournament = $details->tournamenttype;
      $roundtitle = $details->roundtitle ;
      $elements = $details->elements;

      $items = 0;
      $today = strtotime("now");
      $cTime = (Buendnerunihockey_Api_Public::cacheTime() / 60)*$trans_Factor;

      if (!$cache) {
         $view_cache = "<br> cache = off"; 
        } else {$view_cache ="";
      }
      
      //$html .=  "<h2>".$tournament." - ".$roundtitle."</h2>";

      $entries = count($details->groups);

      //echo "groups ".$entries."<br />";
      
      $header_Home = "Heimteam";
      $header_Guest = "Gastteam";
      $header_Result = "Resultat";

      if (($entries > 1) and (!$weekend) and (count($teamIDs)>1)){
        for ($cnt = 1; $cnt < $entries ; $cnt++) {
            $groupname = str_replace(" ","_",$details->groups[$cnt]->groupname);
            $html .= "<p align=\"right\"><a href=\"#".$groupname."\">"."Zur ".$details->groups[$cnt]->groupname."</a></p>";
        } 
      }

      $i = 0;
      if (isset($groupdata->rounds)) {
        do {
          $groupdata = $details->groups[$i]; 
          $round = $groupdata->rounds;
   
          $groupname = str_replace(" ","_",$groupdata->groupname);
          $html .=  "<a name=\"".$groupname."\"></a>"."<h2 class=\"suhv-title\">".$title_head.$groupdata->groupname."</h2>";
          $groupfound = FALSE;

          $r = 0;
          $rounds = count($round);

          do {
              $html_title = "";
              $html_head = "";
              $html_body = "";
              $found = FALSE;
              $roundname = $round[$r]->roundname;
              $rounddate = $round[$r]->rounddate;
              $roundplace = $round[$r]->roundplace;
              $roundinfo = $round[$r]->roundinfo;
              $roundcity = $round[$r]->roundcity;
              $mapY = $round[$r]->mapY;
              $mapX = $round[$r]->mapX;

              $date_parts = explode(".", $rounddate); // dd.mm.yyyy in german
              $date_of_game = strtotime($date_parts[2]."-".$date_parts[1]."-".$date_parts[0]);
              if (($next or $results) and $debug) { echo $rounddate," date ".$date_of_game." start ".$start_date." end ".$end_date."<br>";}
              $locationlink = "<a href=\"https://maps.google.ch/maps?q=".$mapY .",".$mapX."\""." target=\"_blank\" title= \"".$roundcity."\">";

              //$html .= "<h3>".$roundname." - ".$rounddate." - ".$locationlink.$roundplace."</a></h3>";
              $html_title .= "<h3>".$roundname." - ".$rounddate." - ".$locationlink.$roundplace."</a></h3>";

              $game_date = $rounddate;
              $game = $round[$r]->games;
              $g = 0;
              $games = count($game);
              $alt = 0;


              $dateClass = "suhv-date";
              $homeClass = "suhv-place";

              $html_head = "<table class=\"suhv-table suhv-planned-games-full\">\n";
              $html_head .= "<thead><tr><th class=\"suhv-date\">"."Zeit".
                "</th><th class=\"suhv-opponent\">".$header_Home.
                "</th><th class=\"suhv-opponent\">".$header_Guest."</th>";
              $html_head .="<th class=\"suhv-result\">".$header_Result."</th></thead><tbody>";

              do {
                $game_time = $game[$g]->gamestart;
                $game_homeclub = $game[$g]->home;
                $game_homeid = $game[$g]->homeid;
                $game_guestclub = $game[$g]->guest;
                $game_guestid = $game[$g]->guestid;
                $game_result = $game[$g]->result;
                $game_result_add = "";

                $game_homeDisplay = $game_homeclub;
                $game_guestDisplay = $game_guestclub;

                $game_home_result = substr($game_result,0,stripos($game_result,":"));
                $game_guest_result = substr($game_result,stripos($game_result,":")+1,strlen($game_result));
                if (stripos($game_result,"/")>0){$game_home_result = 0; $game_guest_result = 0;}

                $site_url = get_site_url();
                $site_display = substr($site_url,stripos($site_url,"://")+3);

                $resultClass = 'suhv-result';
                
                if (in_array($game_homeid,$teamIDs)) { 
                  $game_homeDisplay = $game_homeDisplay; 
                  $resultHomeClass = 'suhv-home';
                  if ($game_home_result > $game_guest_result) { $resultClass = 'suhv-win';} else {$resultClass = 'suhv-lose';}
                }
                else $resultHomeClass = 'suhv-guest';
                if (in_array($game_guestid,$teamIDs)) {
                  $game_guestDisplay =   $game_guestDisplay;
                 $resultGuestClass = 'suhv-home';
                 if ($game_guest_result > $game_home_result) { $resultClass = 'suhv-win';} else {$resultClass = 'suhv-lose';}
                }
                else $resultGuestClass = 'suhv-guest';
                if ($game_result == "")  { 
                  $resultClass = 'suhv-result';
                  $items++;
                }
                if ($game_home_result == $game_guest_result) { $resultClass = 'suhv-draw';} 
                
                if (((in_array($game_homeid,$teamIDs) or in_array( $game_guestid,$teamIDs)) and $filter) or
                    ((in_array($game_homeid,$teamIDs) or in_array( $game_guestid,$teamIDs)) and ($date_of_game >= $start_date) and $next) or
                    ((in_array($game_homeid,$teamIDs) or in_array( $game_guestid,$teamIDs)) and ($date_of_game <= $end_date) and $results) or
                    (((in_array($game_homeid,$teamIDs) or in_array( $game_guestid,$teamIDs)) and ($date_of_game >= $start_date) and $weekend) and
                     ((in_array($game_homeid,$teamIDs) or in_array( $game_guestid,$teamIDs)) and ($date_of_game <= $end_date) and $weekend)) or
                   ($full)) {

                  $html_body .= "<tr". ($alt % 2 == 1 ? ' class="alt"' : '') . "><td class=\"".$dateClass."\">".$game_time.
                  "</td><td class=\"".$resultHomeClass."\">".$game_homeDisplay.
                  "</td><td class=\"".$resultGuestClass."\">".$game_guestDisplay; 
                  $html_body .= "</td><td class=\"".$resultClass."\">".$game_result."<br>".$game_result_add."</td>";
                  $html_body .= "</tr>";
                  $alt++;
                  $found = TRUE;  $groupfound = TRUE;
                }
                else {
                  //if (($league_str == 'e_kids') and ($date_of_game > $start_date)) echo "not found ".$html_title,"<br>";
                  if (($next_game_weekend == "") and (($date_of_game > $start_date) and $weekend)) {
                    $next_game_weekend = "<p>Nächste Spiele am ".$rounddate."</p>";
                  }
                }
                $g++; 
              } while (($g < $games));

              $html_body .= "</tbody></table><br>";
              if ($found) {
              //$html .= "<br />found<br />";
                $html .= $html_title;
                $html .= $html_head;
                $html .= $html_body;
              }
              $r++; 
          } while (($r < $rounds));
          
          if (!($groupfound)) 
            if ($weekend) {
             $html .= "<p>keine Spiele für die Teams des Clubs am Wochenende ".date("d.m.Y",$start_date)." - ".date("d.m.Y",$end_date)."&nbsp;->&nbsp;"; 
             if  ($league == "D") {
              $html .= "<a href=\"https://www.buv.ch/junioren-d/\">Link BUV D-Junioren</a></p>".$next_game_weekend; }
             else {
              $html .= "<a href=\"https://www.buv.ch/e-kids-turniere/\">Link BUV E-Kids</a></p>"; }
            } 
            else { $html .= "<p>keine Spiele vorhanden.</p>";}
          
          $i++;
        } while (($i < $entries));
      }
      else { $html .= "<p>Liga: ".$league." noch keine Spiele zur aktuellen Saison bestimmt.</p>";}
      $stop =  time();
      $secs = ($stop- $go);
      Buendnerunihockey_Api_Public::log_me("buv_getGames eval-time: ".$secs." secs");
     
      set_transient( $transient, $html, Buendnerunihockey_Api_Public::cacheTime()*$trans_Factor );
     
    } //end If
    else { 
      $html = $value; // Abfrage war OK
    }
    return $html;
	}


/* ---------------------------------------------------------------------------------------------------- */

  public static function api_buv_getTable($season, $league, $round, $cache) {
    
    //echo $season."<br>";
    //$season = "saison2015_2016";
    //echo $season."<br>";

    $transient = "buv-api-".$league.$round."getTeamTable".$season;
    $value = get_transient( $transient );
    $trans_Factor = 5;

    if (!$cache) $value = False;
    if ($league == 'E') {
      $html = "Keine E-Kids Tabellen verfügbar. Es zählt Spiel & Spass!";
    }

    if (($value == False) and ($league == 'D')) {

      $go =  time();
      $plugin_options = get_option( 'BUV_WP_plugin_options' );
      if ($league == "D") {
         $teamIDs = explode(';',$plugin_options['BUV_D_team_ids']);
         $league_str = 'junioren_d';
         if (!isset($round)) $round = '1';
      }
      else {
         $league_str = 'e_kids';
         $teamIDs = explode(';',$plugin_options['BUV_E_team_ids']);
         $round = '1';
      }
      

      Buendnerunihockey_Api_Public::log_me(array('function' => 'api_buv_getTabl', 'season' => $season, 'league' => $league_str, 'round' => $round));

     
      $skip = "<br />";
      $html = "";

      $tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
      $tag = date("w");
      $wochentag = $tage[$tag];

      $api = new Buendnerunihockey_Public(); 
      $details = $api->leagueTable($season, $league_str, $round,'rankingtable'); 
      //Buendnerunihockey_Api_Public::log_me( $details);

      $tabletype = $details->tabletype;
      $roundtitle = $details->roundtitle ;
      $elements = $details->elements;


      $items = 0;
      $today = strtotime("now");
      $cTime = (Buendnerunihockey_Api_Public::cacheTime() / 60)*$trans_Factor;

      if (!$cache) {
         $view_cache = "<br> cache = off"; 
        } else {$view_cache ="";
      }

      $header_Rank = 'Rang';
      $header_Team = 'Team';
      $header_Sp = 'Sp';
      $header_S = 'S';
      $header_U = 'U';
      $header_N = 'V';
      $header_TP = '+';
      $header_TM = '-';
      $header_TD = '+/-';
      $header_P = "P";

      if (!$cache) {
             $view_cache = "cache = off / Runde: ".$roundtitle; 
            } else {$view_cache ="";
      }

      $groupcount = 0;
      if (isset($details->groups)) $groupcount = count($details->groups);

      $html .=  "<a name=\"".$tabletype."\"></a>"."<h2  class=\"suhv-title\">".$tabletype." - ".$roundtitle."</h2>";

      if ($groupcount > 0) {

        $i = 0;

        do {

          $groupdata = $details->groups[$i]; 
          $teams = $groupdata->teams;
      
          $groupname = str_replace(" ","_",$groupdata->groupname);
          //$html .=  "<a name=\"".$groupname."\"></a>"."<h3>".$groupdata->groupname."</h3>";

          $html .= "<table class=\"suhv-table\">";
          $html .= "<caption class=\"suhv-table-caption\">"."<a name=\"".$groupname."\"></a>".$groupdata->groupname."</caption>";
          $html .= "<thead>".       
            "<tr><th class=\"suhv-rank\"><abbr title=\"Rang\">".$header_Rank."</abbr>".
            "</th><th class=\"suhv-team\"><abbr title=\"Team\">".$header_Team."</abbr>".
            "</th><th class=\"suhv-games\"><abbr title=\"Spiele\">".$header_Sp."</abbr>".
            "</th><th class=\"suhv-wins\"><abbr title=\"Siege\">".$header_S."</abbr>";
          $html .= "</th><th class=\"suhv-even\"><abbr title=\"Spiele unentschieden\">".$header_U."</abbr>".
            "</th><th class=\"suhv-lost\"><abbr title=\"Niederlagen\">".$header_N."</abbr>".
            "</th><th class=\"suhv-scored\"><abbr title=\"Tore erzielt\">".$header_TP."</abbr>".
            "</th><th class=\"suhv-ties\"><abbr title=\"Tore erhalten\">".$header_TM."</abbr>".
            "</th><th class=\"suhv-diff\"><abbr title=\"Tordifferenz\">".$header_TD."</abbr>".
            "</th><th class=\"suhv-points\"><abbr title=\"Punkte\">".$header_P."</abbr>";
          $html .= "</th></tr></thead>";
          $html .= "<tbody>";

          $t = 0;
          $teamcount = count($teams);
          
          do {

            
            $ranking_Rank = $t+1;
            $ranking_TeamID = $teams[$t]->teamid;
            $ranking_Team = $teams[$t]->team;           
            $ranking_Sp = $teams[$t]->games;
            $ranking_S = $teams[$t]->gameswon;
            $ranking_U = $teams[$t]->gamesdraw;
            $ranking_N = $teams[$t]->gameslost;;
            $ranking_TP = $teams[$t]->goalsshot;
            $ranking_TM = $teams[$t]->goalsgot;
            $ranking_TD = $teams[$t]->goalbalance;
            $ranking_P = $teams[$t]->points;

            if (in_array($ranking_TeamID,$teamIDs)) { $tr_class = 'suhv-my-team';} else {$tr_class = '';}

            $html .= "<tr class=\"".$tr_class.($t % 2 == 1 ? ' alt' : '')."\">".
            "<td class=\"suhv-rank\">".$ranking_Rank.
            "</td><td class=\"suhv-team\">".$ranking_Team. //". (".$ranking_TeamID.")".
            "</td><td class=\"suhv-games\">".$ranking_Sp.
            "</td><td class=\"suhv-wins\">".$ranking_S;
            $html .= "</td><td class=\"suhv-even\">".$ranking_U.
            "</td><td class=\"suhv-lost\">".$ranking_N.
            "</td><td class=\"suhv-scored\">".$ranking_TP.
            "</td><td class=\"suhv-ties\">".$ranking_TM.
            "</td><td class=\"suhv-diff\">".$ranking_TD.
            "</td><td class=\"suhv-points\">".$ranking_P;
            $html .= "</td></tr>"; 
            
            $t++; 
          } while ($t < $teamcount);

          $html .= "</tbody>";
          $html .= "</table>";

          $i++;
        } while ($i < $groupcount);
      

      }
      else $html .= "<p>bisher keine Spiele ausgetragen.</p>";

      set_transient( $transient, $html,  Buendnerunihockey_Api_Public::cacheTime()*$trans_Factor );
    }
    else { $html = $value; }
    return $html;
   
    }

/* ---------------------------------------------------------------------------------------------------- */

  public static function api_show_json($season) {
      
     $html =  "<strong>BUV JSON-Files</strong><br>";

     $api = new Buendnerunihockey_Public(); 
     $details = $api->JSON_List(); 

     $activ_season = $details->season;
     $html.= "Aktive Saison: ".$activ_season."<br>";
     $json_files = $details->APIfiles;
     
     foreach ($json_files as $file){
       $html.= "<a href=\"".$file."\">".$file."</a><br>";
     }
      
    return $html;
   
  }
 
/* ---------------------------------------------------------------------------------------------------- */

  // Funktion: Log-Daten in WP-Debug schreiben
  public static function log_me($message) {
      if ( WP_DEBUG === true ) {
          if ( is_array($message) || is_object($message) ) {
              error_log( print_r($message, true) );
          } else {
                error_log( $message );
          }
      }
  }
/* ---------------------------------------------------------------------------------------------------- */
  public static function api_show_params($season, $club_ID, $team_ID, $mode) {

      echo "<br>Season: ".$season." - Club: ".$club_ID." - Team: ".$team_ID;
  }

}




