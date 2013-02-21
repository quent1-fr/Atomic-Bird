<?php
    include 'include/fonctions.inc';
    include 'include/config.inc';
    date_default_timezone_set('UTC');
    $now = mktime(date('H'), 0, date('s'), date('n'), date('j'), date('Y'));
    $months_array = array(
        'Jan' => '01',
        'Feb' => '02',
        'Mar' => '03',
        'Apr' => '04',
        'May' => '05',
        'Jun' => '06',
        'Jul' => '07',
        'Aug' => '08',
        'Sep' => '09',
        'Oct' => '10',
        'Nov' => '11',
        'Dec' => '12'
        );
    
    if(!isset($_GET['username'])) die('<h1>Erreur</h1><p>Aucun nom d\'utilisateur n\'a &eacute;t&eacute; sp&eacute;cifi&eacute;!</p>');
    if($protection !== false && !isset($_GET['protection']) or $protection !== false && $_GET['protection'] != $protection) die('<h1>Erreur</h1><p>Mauvaise cl&eacute; secr&egrave;te!</p>');
    if($cache_time < 10) die('<h1>Erreur</h1><p>Le temps de cache doit &ecirc;tre d\'au moins 10 minutes!</p>');      
    
    $username = strtolower($_GET['username']);
    
    if(cache_age($username) > $cache_time * 60):
        ini_set('user_agent', $user_agent);
        
        $mobile_timeline = file_get_contents('https://mobile.twitter.com/' . $username) or die('<h1>Erreur</h1><p>Le twittos n\'existe pas!</p>');

        # On y va a grand coup de regex pour récupérer tout qui va bien.
        # Le nom déjà
        $regex_fullname = '#<div class="fullname">(.*)#';
        preg_match($regex_fullname, $mobile_timeline, $fullname);
        $info['fullname'] = $fullname[1];
        $info['username'] = $_GET['username'];

        # La bio aussi
        $regex_bio = '#<div class="bio"><div class="dir-ltr" dir="ltr">(.*)</div></div>#';
        preg_match($regex_bio, $mobile_timeline, $bio);
        $info['bio'] = $bio[1];

        # Passons aux tweets !
        // TODO : différencier les RT des tweets.
        $regex_tweets = '#<div class="tweet-text" data-id="[0-9]*"><div class="dir-ltr" dir="ltr">(.*)</div></div>#iU';
        preg_match_all($regex_tweets, $mobile_timeline, $tweets);
        $tweets = $tweets[1];

        # URLs des tweets
        $regex_tweets_url = '#<table class="tweet" href="(.*)">#iU';
        preg_match_all($regex_tweets_url, $mobile_timeline, $tweets_url);
        $tweets_url = $tweets_url[1];

        # La date !
        $regex_tweets_date = '#<a name="tweet_[0-9]*" href=".*">(.*)</a>#iU';
        preg_match_all($regex_tweets_date, $mobile_timeline, $tweets_date);
        $tweets_date = $tweets_date[1];

        
        # Generation du RSS :
        $atom = 
        '<?xml version="1.0" encoding="utf-8"?>
        <feed xml:lang="fr-fr" xmlns="http://www.w3.org/2005/Atom"> 
            <title>Twitter de '.$info['username'].' / '.$info['fullname'].'</title>
            <subtitle>'.$info['bio'].'</subtitle>
            <link href="https://twitter.com/'.$info['username'].'"/>
            <updated>'.date(DATE_ATOM, $now).'</updated>
            <id>https://twitter.com/</id>
            <author>
                <name>'.$info['fullname'].'</name>
            </author>
        ';

        $i = 0;
        foreach ($tweets as $key => $value):
            # Déjà, on va convertir la date dans le format qui faut bien. Type possible : 4h, 4d, 13 Feb
            $tweets_date[$key];

            # Si c'est des heures :
            if(preg_match('#([0-9])*h#', $tweets_date[$key], $hours)):
                $tweet_date = date(DATE_ATOM, $now - $hours[1] * 60);
            elseif(preg_match('#([0-9])*d#', $tweets_date[$key], $days)):
                $tweet_date = date(DATE_ATOM, $now - $days[1] * 3600 * 24);
            elseif(preg_match('#([0-9]*) ([A-Za-z]*)#', $tweets_date[$key], $month)):
                $tweet_date = date(DATE_ATOM, mktime('12', 0, 0, $months_array[$month[2]], $month[1], date('Y')) - $i*60*15);
            else:
                $tweet_date = '';
            endif;

            $atom .=
            '<entry>
                <id>https://twitter.com'.$tweets_url[$key].'</id>
                <title>'.rtrim(substr(strip_tags($tweets[$key]), 0, 39)).'...</title>
                <updated>'.$tweet_date.'</updated>
                <link href="https://twitter.com'.$tweets_url[$key].'"/>
                <link href="'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'" rel="self"/>
                <content type="html"><![CDATA['.utf8_encode(html_entity_decode(htmlentities($tweets[$key], ENT_COMPAT,'utf-8'))).']]></content>
            </entry>
            ';

            $i++;
        endforeach;

        $atom .= '</feed>';
        write_cache($info['username'], $atom);

    else: 
        $atom = read_cache($username);
    endif;
    
    //header('Content-type: application/xml; charset=UTF-8');
    echo htmlspecialchars($atom);
?>