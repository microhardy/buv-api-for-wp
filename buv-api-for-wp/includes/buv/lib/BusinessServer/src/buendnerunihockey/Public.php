<?php

/**
 * Interface class for the public interface of Swiss Unihockey API server. 
 * All methods that exist on the REST interface will eventually be methods 
 * here, so that calls can be transparent for the rest of the code. 
 * 
 */
class Buendnerunihockey_Public {
     public function __construct($base_url='http://www.buv.ch/jsondata', 
        $key=null, $secret=null)
    {
        $this->base_url = $base_url; 
        $this->key = $key; 
        $this->secret = $secret; 
        $this->authenticated = isset($key);
    }

    public function version() 
    {
        //$json = $this->get('');
        //return $json; 
        return 1;
    }


    /**
     * Returns a list of active teams for a given season and club id. 
     *
     * Parameters:
     *  * season: The season for which to get the clubs. 
     *  * League: League D or E.
     *
     */


    public function leagueTeams($season, $league, $group, $type)  //saison2016_2017_junioren_d_1_teams.json or saison2016_2017_e_kids_1_teams.json
    {
        $url = $season."_".$league."_".$group."_".$type.".json";
        //echo $url.'<br>';

        $json = $this->get('/', $url );
        return $json; 
    }
    
    public function leagueGames($season, $league, $group, $type)  //saison2016_2017_junioren_d_1_tournaments.json  or saison2016_2017_e_kids_1_tournaments.json
    {
        $url = $season."_".$league."_".$group."_".$type.".json";
        //echo $url.'<br>';

        $json = $this->get('/', $url );
        return $json; 
    }
    
    public function leagueTable($season, $league, $group, $type)  //saison2016_2017_junioren_d_1_rankingtable.json
    {
        $url = $season."_".$league."_".$group."_".$type.".json";
        //echo $url.'<br>';

        $json = $this->get('/', $url );
        return $json; 
    }


    public function JSON_List()  //index.json
    {
        $url = "index.json";

        $json = $this->get('/', $url );
        return $json; 
    }
    
    // get ..


    private function get($path, $file)
    {
        $uri = $this->base_url . $path.$file; 
        //echo $uri.'<br>';
        if ($this->authenticated) {
            $params = $this->HMACAuth('GET', $uri, $params); 
        }

        $request = \Httpful\Request::get($uri);

        $response = $request->send();

        $json = $response->body; 
        return $json; 
    }

    /**
     * Converts query parameters into a full URI. 
     */
    private function paramsToQuery($uri, $params) 
    {
        if (empty($params)) 
            return $uri;

        return $uri . "?" . $this->encodeQuery($params);
    }

    private function encodeQueryPart($part) 
    {
        return str_replace( '%7E', '~', rawurlencode( $part ) );
    }
    private function encodeQuery($params)
    {
        // Create the canonicalized query
        $query = array();
        foreach ( $params as $param => $value ) {
            $param = $this->encodeQueryPart($param);
            $value = $this->encodeQueryPart($value); 
            $query[] = $param . '=' . $value;
        }
        return implode( '&', $query );
    }

    /**
     * Authenticates the given request by adding HMAC signature. 
     * 
     * Much of the procedure for this is what Amazon does to authenticate their
     * services. Please refer to their documentation to understand all the 
     * details. (http://aws.amazon.com/articles/1928?_encoding=UTF8&jiveRedirect=1)
     */
    private function HMACAuth($method, $uri, $params) 
    {
        $params['key']   = $this->key; 
        $params['ts']    = time();

        // Sort the parameters
        ksort( $params );

        $canonicalized_query = $this->encodeQuery($params);
        $string_to_sign = $method . "\n" . $uri . "\n" . $canonicalized_query;

        // Calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode( 
            hash_hmac( 'sha256', $string_to_sign, $this->secret, TRUE ) );

        // Encode the signature for the request
        $signature = $this->encodeQueryPart($signature); 
        
        $params['sig'] = $signature; 
        return $params; 
    }
}
