<?php
/**
 * Admin Page for BUV API
 * @author Thonmas Hardegger /
 * @version 17.03.2018
 * STATUS: Reviewed
 */

if ( !class_exists( 'Buv_WP_Options' ) ) {
 class BUV_WP_Options {
 	
 	public $options;
  
 	
  public function __construct()
 	{
 	 $this->register_settings_and_fields();
 		$this->options = get_option( 'BUV_WP_plugin_options' );
 		if (($this->options['BUV_E_team_ids'] == "" ) or ($this->options['BUV_D_team_ids'] == "" ))
 		 add_action('admin_notices', array( $this, 'admin_notice_api_key' ));
 	}
 	
  public function admin_notice_api_key()
  {
   echo '<div class="error fade">
          <p><strong>Die BUV API muss konfiguriert werden. Gehe zu den <a href="' . admin_url( 'options-general.php?page=' . BUV_API_WP_PLUGIN_DIRNAME  . '/admin/buv_api_wp_admin.php' ) . '">Einstellungen</a> um die BUV E-Team und D-Team IDs zu setzen.</strong></p>
         </div>';
  }

 	
 	public static function add_menu_page()
 	{
 	  add_options_page( 'BUV API', 'BUV API', 'administrator', __FILE__, array( 'BUV_WP_Options', 'display_options_page') );	
 	}
  
 	public function display_options_page	()
 	{
 	 ?>   
   <div class='wrap'>
    <?php screen_icon(); ?>
    <h2>Einstellungen &#8250; BUV API</h2>
    <form method='post' action='options.php'>
     <?php 
 				 settings_fields( 'BUV_WP_plugin_options' ); // WP Security hidden Fields
 					do_settings_sections( __FILE__ );
 				?>
    <h3>Schnelleinstieg</h3>
    <p>Für eine ausführliche Beschreibung des Plugins besuche die <a href='https://www.buv.ch/api_download/' title='BUV API 1.0 für WordPress'>Plugin-Website</a> von <a href='mailto:webmaster@buv.ch' title='webmaster'>Thomas Hardegger</a></p>
     <?php 
 					do_settings_sections( __FILE__ . "_description" );
 				?>
     <p class='submit'><input name='submit' type='submit' value='Änderungen übernehmen' class='button-primary' /></p>
    </form>
   </div>
    <?php
 	}
 	
 	public function register_settings_and_fields()
 	{
 		register_setting( 'BUV_WP_plugin_options', 'BUV_WP_plugin_options' ); //3rd param = optional callback
 		add_settings_section( 'BUV_main_section', '', array( $this, 'BUV_main_section_cb' ), __FILE__ ); //id, title of section, cb, page
 		add_settings_field( 'BUV_E_team_ids', 'BUV E-Team IDs', array( $this, 'BUV_E_team_ids_setting' ), __FILE__,  'BUV_main_section'); // name, title, function to input, ?, section
 		add_settings_field( 'BUV_D_team_ids', 'BUV D-Team IDs', array( $this, 'BUV_D_team_ids_setting' ), __FILE__,  'BUV_main_section');
    add_settings_field( 'BUV_E_team_rounds', 'BUV aktive E Runde', array( $this, 'BUV_E_team_rounds_setting' ), __FILE__,  'BUV_main_section');
    add_settings_field( 'BUV_D_team_rounds', 'BUV aktive D Runde', array( $this, 'BUV_D_team_rounds_setting' ), __FILE__,  'BUV_main_section');

    // add_settings_field( 'BUV_club_name', 'BUV Club Name', array( $this, 'BUV_club_name_setting' ), __FILE__,  'BUV_main_section');
    add_settings_field( 'BUV_css_file', 'BUV CSS File laden', array( $this, 'BUV_css_file_setting' ), __FILE__,  'BUV_main_section');
    add_settings_field( 'BUV_cache', 'BUV Cache aktivieren', array( $this, 'BUV_cache_setting' ), __FILE__,  'BUV_main_section');
    // add_settings_field( 'BUV_club_games_limit', 'BUV Games Limit', array( $this, 'BUV_club_games_limits_setting' ), __FILE__,  'BUV_main_section');
  	add_settings_section( 'BUV_description_section', '', array( $this, 'BUV_description_section_cb' ), __FILE__ . "_description" ); //id, title of section, cb, page
 		add_settings_field( 'BUV_shortcode_league', 'BUV Games', array( $this, 'BUV_shortcodes_league' ), __FILE__ . "_description",  'BUV_description_section');
  	add_settings_field( 'BUV_shortcode_team', 'BUV Tables', array( $this, 'BUV_shortcodes_team' ), __FILE__ . "_description",  'BUV_description_section');
 	  add_settings_field( 'BUV_JSON_Info', 'BUV Tabellen mit Team-IDs', array( $this, 'BUV_JSON_Info' ), __FILE__ . "_description",  'BUV_description_section');
 		add_settings_field( 'BUV_shortcode_widget', 'Shortcodes in Widgets', array( $this, 'BUV_shortcodes_widget' ), __FILE__ . "_description",  'BUV_description_section');
 	}
 	
 	public function BUV_main_section_cb()
 	{
 	 //optional	
 	}
 	public function BUV_description_section_cb()
 	{
 	 echo "<p>Für den schnellen Einstieg findest du nachfolgend alle verfügbaren Shortcodes. Diese können direkt im Texteditor aufgerufen werden.</p>";
 	}
 	
 	
 	// E-Teams IDs
 	public function BUV_E_team_ids_setting()
 	{
 		echo "<input name='BUV_WP_plugin_options[BUV_E_team_ids]' type='text' value='" . $this->options['BUV_E_team_ids'] . "' />";
      echo " E-Teams IDs: <a href=\"http://www.buv.ch/wp-content/plugins/buv-api-for-wp/includes/buv/php/getting_started_e_teams_table.php\" target=\"_blank\">E-Team-Liste</a><br>";
 		echo "<span class='description'>Filter-Liste der E-Teams. Nur Spiele und Spielrunden dieser/dieses Teams werden angezeigt.</span>";
 	}

 	
 	// D-Teams IDs
 	public function BUV_D_team_ids_setting()
 	{
 		echo "<input name='BUV_WP_plugin_options[BUV_D_team_ids]' type='text' value='" . $this->options['BUV_D_team_ids'] . "' />";
      echo " D-Teams IDs: <a href=\"http://www.buv.ch/wp-content/plugins/buv-api-for-wp/includes/buv/php/getting_started_d_teams_table.php\" target=\"_blank\">D-Team-Liste</a><br>";
 		echo "<span class='description'>Filter-Liste der D-Teams. Nur Spiele und Spielrunden dieser/dieses Teams werden angezeigt.</span>";
 	}


  // Default Club Shortname
  public function BUV_club_name_setting()
  {
    echo "<input name='BUV_WP_plugin_options[BUV_club_name]' type='text' value='" . $this->options['BUV_club_name'] . "' /><br>";
    echo "<span class='description'>Clubname. Beipiel 'Chur Unihockey' oder 'piranha chur'.</span>";
  }


  // Anzeige der Anzahl Spiele 
  public function BUV_club_games_limits_setting()
  {
    echo "<input name='BUV_WP_plugin_options[BUV_club_games_limit]' style='width:50px' type='text' value='" . $this->options['BUV_club_games_limit'] . "' />";
    echo "<span class='description'> Anzahl n Games in der Club Games Anzeige. (Beispiel: 10)</span>";
 
  }

    public function BUV_E_team_rounds_setting()
  {    
      $roundtypes = array('Auto','Vorrunde','Finalrunde');
      echo "<select name='BUV_WP_plugin_options[BUV_E_team_rounds]' value='" . $this->options['BUV_E_team_rounds'] . "' />";
      //echo "<select>";
      foreach ($roundtypes as $option) {
        echo '<option value="' . $option . '" id="' . $option . '"', $this->options['BUV_E_team_rounds'] == $option ? ' selected="selected"' : '', '>', $option , '</option>';
      }
      echo "</select>";
      echo "<span class='description'> Auto wählen für aktuelle Runden (Vorrunde plus Finalrunde).</span>";

  }

  public function BUV_D_team_rounds_setting()
  {    
      $roundtypes = array('Auto','Vorrunde','Finalrunde','Challengerunde');
      echo "<select name='BUV_WP_plugin_options[BUV_D_team_rounds]' value='" . $this->options['BUV_D_team_rounds'] . "' />";
      //echo "<select>";
      foreach ($roundtypes as $option) {
        echo '<option value="' . $option . '" id="' . $option . '"', $this->options['BUV_D_team_rounds'] == $option ? ' selected="selected"' : '', '>', $option , '</option>';
      }
      echo "</select>";
      echo "<span class='description'> Auto wählen für aktuelle Runden (Vorrunde plus Finalrunde). (Bei gleichzeitiger Mitgliedschaft in Final & Challagerunde über Parameter round=\"3\")</span>";

  }
  
 	// Default CSS loading?
 	//public function BUV_css_setting()
 	//{echo "<label><input name='BUV_WP_plugin_options[BUV_css]' type='checkbox' value='1' " . checked( 1, $this->options['BUV_css'], false ) . "/> Ja, das Standard-CSS bitte laden.</label><br>";}

  // Default CSS File ?
  public function BUV_css_file_setting()
  {    
      $filesearch = BUV_API_WP_PLUGIN_PATH."includes/buv/styles/*.css";
      $fpath = BUV_API_WP_PLUGIN_PATH."includes/buv/styles/";
      $len = strlen($fpath);
      $css_files[]="- nein -";
      $files=glob($filesearch);
      foreach ($files as $file) {
        $css_files[]= substr($file,$len);
      }
      echo "<select name='BUV_WP_plugin_options[BUV_css_file]' value='" . $this->options['BUV_css_file'] . "' />";
      //echo "<select>";
      foreach ($css_files as $option) {
        echo '<option value="' . $option . '" id="' . $option . '"', $this->options['BUV_css_file'] == $option ? ' selected="selected"' : '', '>', $option , '</option>';
      }
      echo "</select>";
      echo "<span class='description'> -nein- wählen falls bereits SUHV API 2.0 Plugin aktiv.</span>";
  }

  // Default Cache setting?
  public function BUV_cache_setting()
  {
    echo "<label><input name='BUV_WP_plugin_options[BUV_cache]' type='checkbox' value='1' " . checked( 1, $this->options['BUV_cache'], false ) . "/> Ja, Cache einschalten. (Bitte nur zu Testzwecken ausschalten)</label><br>";
  }

 	
 	// Default Logfiles?
 	public function BUV_log_setting()
 	{
 		echo "<label><input name='BUV_WP_plugin_options[BUV_log]' type='checkbox' value='1' " . checked( 1, $this->options['BUV_log'], false ) . "/> Ja, die Log-Daten des BUV-Frameworks am Seitenende anzeigen (nur für WordPress-Administratoren sichtbar).</label><br>";
 	}
 	
 	public function BUV_shortcodes_league()
 	{
    // echo "[buv-api-buv-games_E] <span class='description'>Meisterschaftsrunden der Liga E-Kids</span><br>"; 
    // echo "[buv-api-buv-games_D] <span class='description'>Meisterschaftsrunden der Liga D-Junioren</span><br>"; 
 		echo "[buv-api-buv-games] <span class='description'>Hauptrunden der Liga (Default D-Junioren) / für E-Kids: [buv-api-buv-games league=\"E\"] </span><br>"; 
    echo "[buv-api-buv-lastgames] <span class='description'>Letzte Spiele der Liga (Default D-Junioren)</span><br>"; 
    echo "[buv-api-buv-nextgames] <span class='description'>Nächste Spiele der Liga (Default D-Junioren)</span><br>"; 
    echo "[buv-api-buv-weekendgames] <span class='description'>Wochenend-Spiele der Liga (Default D-Junioren)</span><br>"; 
    echo "Beispiele mit Parameter:<br>";
    echo "[buv-api-buv-games league=\"D\" round=\"1\"] <span class='description'>Hauptrunden der Liga</span><br>"; 
    echo "[buv-api-buv-weekendgames league=\"E\"] <span class='description'>Wochenendspiele E-Kids</span><br>"; 
    echo "[buv-api-buv-weekendgames round=\"3\"] <span class='description'>Wochenendspiele Challengerunde D-Junioren</span><br>"; 
    echo "<br>mögliche Parameter: league=\"D|E\" round=\"1|2|3\" mode=\"filter|full|next|result|weekend\" team=\"26\"<br>";


 	}
 	
 	public function BUV_shortcodes_team()
 	{

    echo "[buv-api-buv-table]<span class='description'> Rangliste in Liga-Tabelle der Teams D-Junioren</span><br>"; 

 	}

   public function BUV_JSON_Info()
  {
    function getSeason()
    {
       $season = intval(date('Y'));
      if (date('m') < 6) {
      $currentSeason = 'saison'.strval($season-1).'_'.strval($season);
        }
      else {
          $currentSeason = 'saison'.strval($season).'_'.strval($season+1);
      }
      return ($currentSeason);
    }

    echo "E-Teams IDs: <a href=\"https://www.buv.ch/wp-content/plugins/buv-api-for-wp/includes/buv/php/getting_started_e_teams_table.php\" target=\"_blank\">E-Team-Liste</a><br>";
    echo "D-Teams IDs: <a href=\"https://www.buv.ch/wp-content/plugins/buv-api-for-wp/includes/buv/php/getting_started_d_teams_table.php\" target=\"_blank\">D-Team-Liste</a><br>";
    $homeurl = home_url();
    if (stripos($homeurl,"www.buv.ch")>0) {
      $path = get_home_path();
      $len = strlen($path);
      $current = getSeason();
      $filesearch = $path."/jsondata/*.json";
      $files=glob($filesearch);
       $i=0;
       foreach ($files as $file) {
        $jsonfile = substr($file,$len); 
        // echo "json: ".$jsonfile."<br>" ;
        if ((stripos($jsonfile,$current)>0) or (stripos($jsonfile,"index")>0)) {
         $json_url = "<a href=\"".$homeurl.$jsonfile."\">".$homeurl.$jsonfile."</a>";
         $index_array['APIfiles'][$i] = $homeurl.$jsonfile;
         echo $json_url."<br>";
         $i++;
        }
      }
    } 
    
  }
 	
 	public function BUV_shortcodes_widget()
 	{
 		echo "Achtung, nicht alle WordPress-Templates unterstützen Shortcodes in Widgets. Füge folgendes in dein functions.php hinzu, falls du damit Probleme hast:	<span class='description'>add_filter('widget_text', 'do_shortcode');</span><br>";
 	}
 
 	
 } // END Class BUV_WP_Options
} // END if Class BUV_WP_Options Exists


add_action( 'admin_init', function(){
 new BUV_WP_Options(); 
});

add_action('admin_menu', function() {
  BUV_WP_Options::add_menu_page();
});


