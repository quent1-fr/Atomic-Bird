<?php    
    function strtotimestamp($date){
        $correspondance = array( 's' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400 );
        
        preg_match('/[0-9]+/', $date, $coefficient);
        preg_match('/[a-z]+/i', $date, $mor);
        
        if(array_key_exists($mor[0], $correspondance)) return time() - ($coefficient[0] * $correspondance[$mor[0]]);
        elseif(strtotime($date) !== false) return strtotime($date);
        else return false;        
    }
    function write_cache($username, $feed){
        $cache = fopen('cache/' . md5($username) . '.cache', 'w');
        fwrite($cache, gzdeflate($feed, 9));
        return true;
    }
    function cache_age($username){
        if(!file_exists('cache/' . md5($username) . '.cache')) return time() + 3600;
        else return time() - filemtime('cache/' . md5($username) . '.cache');
    }
    function read_cache($username){
        $cached = gzinflate(file_get_contents('cache/' . md5($username) . '.cache'));
        return $cached;
    }
    function title_formated($tweet){
        $tweet = strip_tags(html_entity_decode($tweet));
	if(strlen($tweet) <= 40)
	    return $tweet;
	$sub = substr($tweet, 0, 39);
	return rtrim(substr($sub, 0, strrpos($sub,' '))) . '...';
    }
?>