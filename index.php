<?php
    include 'include/fonctions.inc';
    include 'include/config.inc';
    date_default_timezone_set('UTC');
    $now = mktime(date('H'), 0, date('s'), date('n'), date('j'), date('Y'));
    
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
            <subtitle>'.strip_tags($info['bio']).'</subtitle>
            <link href="https://twitter.com/'.$info['username'].'"/>
            <link href="http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?username='.$info['username'].'" rel="self"/>
            <updated>'.date(DATE_ATOM, $now).'</updated>
            <id>https://twitter.com/</id>
            <author>
                <name>'.$info['fullname'].'</name>
            </author>
        ';

        $i = 0;
        foreach ($tweets as $key => $value):
            # On converti la date relative en un timestamp absolu. On la convertira en date valide plus tard, grâce à la fonction date et au paramètre « c »
            $tweets_date[$key] = strtotimestamp($tweets_date[$key]);

            $atom .=
            '<entry>
                <id>https://twitter.com' . $tweets_url[$key] . '</id>
                <title><![CDATA[' . title_formated($tweets[$key]). ']]></title>
                <updated>' . date('c', $tweets_date[$key]) . '</updated>
                <link href="https://twitter.com' . $tweets_url[$key] . '"/>
                <content type="html"><![CDATA[' . str_replace(' href="/', ' href="https://twitter.com/', $tweets[$key]) . ']]></content>
            </entry>
            ';

            $i++;
        endforeach;

        $atom .= '</feed>';
        write_cache($username, $atom);

    else: 
        $atom = read_cache($username);
    endif;
    
    header('Content-type: application/xml; charset=UTF-8');
    echo $atom;
?>