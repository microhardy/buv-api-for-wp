<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Ex Ranking - Getting Started</title>

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>


  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/styles/default.min.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/highlight.min.js"></script>  <script>hljs.initHighlightingOnLoad();</script>
</head>
<body>

<div class="container">
  <div class="row">
    <h1>E-Kids League Teams</h1>

    <?php

      set_include_path(preg_replace("/php/", "lib", __DIR__));
      //echo "get inc:".get_include_path();

      require('BusinessServer/bootstrap.php');
      $api = new Buendnerunihockey_Public();
      $version_info = $api->version();
      echo "API-Version: " .$version_info. "<br>";  

      ////saison2016_2017_e_kids_turniere_1_teams.json
 
      $details = $api->leagueTeams('saison2016_2017','e_kids','1','teams'); 
      //echo print_r($details)."<br>";
    
      $entriesCount = count($details);

      echo "entries: " . $entriesCount. "<br>";  
      
      $Team_Name = $details->teamstype;
      $Season =  $details->season;
      $entriesCount =  count($details->groups);
      echo 'Teamtype: '.$Team_Name."<br>";
      echo 'Season: '.$Season."<br>";
      echo 'Groups: '.$entriesCount."<br>";

 
      $i = 0;
      // Report all errors except E_NOTICE
      //error_reporting(E_ALL & ~E_NOTICE);
      do {
          $GroupName = $details->groups[$i]->groupname;
          //$RoundName = $details->groups[$i]->roundname;
         // $RoundDate = $details->groups[$i]->rounddate;
          $teamCount = count($details->groups[$i]->teams);
          $up = $teamCount;
          echo "<h1>".$GroupName.' mit '.$teamCount." Teams</h1>";
          echo "<table class=".addslashes("table-bordered")." width=".addslashes("850px").">";
          echo "<thead><tr>"."<th>"."Team"."</th><th>"."Team-ID"."</th>"."</tr></thead>";
          echo "<tbody>";
          
          $t= 0;
          do {
             $Team_Name = $details->groups[$i]->teams[$t]->teamname;
             $Team_ID = $details->groups[$i]->teams[$t]->teamid;
             echo "<tr>"."<td>".$Team_Name."</td><td>".$Team_ID ."</td>"."</tr>"; 
             $t++;
            //} while ($t <= $up);
          } while ($t < $up);
          echo "</tbody>";
          echo "</table>";
          $i++; 
        } while ($i < $entriesCount);
        // Report all errors
        //error_reporting(E_ALL);
      

    ?>

  <h2> (RAW data) </h2>

  <pre>
  <code class="javascript">
    <?= json_encode($details, JSON_PRETTY_PRINT) ?>
  </code>
  </pre>
</div>

</body>
</html>