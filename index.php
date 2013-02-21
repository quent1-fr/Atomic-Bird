<?php
    include 'include/fonctions.inc';
    include 'include/config.inc';
    date_default_timezone_set('UTC');
    
    if(!isset($_GET['username'])) die('<h1>Erreur</h1><p>Aucun nom d\'utilisateur n\'a &eacute;t&eacute; sp&eacute;cifi&eacute;!</p>');
    if($protection !== false && !isset($_GET['protection']) or $protection !== false && $_GET['protection'] != $protection) die('<h1>Erreur</h1><p>Mauvaise cl&eacute; secr&egrave;te!</p>');
    if($cache_time < 10) die('<h1>Erreur</h1><p>Le temps de cache doit &ecirc;tre d\'au moins 10 minutes!</p>');      
    
    $username = strtolower($_GET['username']);
    
    if(1==1){
        ini_set('user_agent', $user_agent);
        
        // $mobile_timeline = @file_get_contents('https://mobile.twitter.com/' . $username);
        // write_cache('debetux', $mobile_timeline);
        $mobile_timeline = read_cache('debetux');
    
        if(!$mobile_timeline) die('<h1>Erreur</h1><p>Le twittos n\'existe pas!</p>');

        # On y va a grand coup de regex pour récupérer tout qui va bien.
        # Le nom déjà
        // $regex_fullname = '#<div class="fullname#';
        // $regex_fullname = '/<div class="(.*)/iU';
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
        echo "<pre>";
        // print_r($tweets[1]);
        $tweets = $tweets[1];

        # URLs des tweets
        $regex_tweets_url = '#<table class="tweet" href="(.*)">#iU';
        preg_match_all($regex_tweets_url, $mobile_timeline, $tweets_url);
        // print_r($tweets_url[1]);
        $tweets_url = $tweets_url[1];

        # La date !
        $regex_tweets_date = '#<a name="tweet_[0-9]*" href=".*">(.*)</a>#iU';
        preg_match_all($regex_tweets_date, $mobile_timeline, $tweets_date);
        // print_r($tweets_date[1]);
        $tweets_date = $tweets_date[1];

        // foreach ($tweets_url[1] as $key => $value) {
        //     echo '<a href="https://twitter.com'.$value.'">'.$key.'</a><br/>';
        // }

        // print_r($info);

        # 
        // foreach ($r as $v) {
        //     echo htmlspecialchars($v);
        // }
        //echo preg_match($regex_fullname, $mobile_timeline);

        // echo $mobile_timeline;
        $rss = 
        '<feed xml:lang="fr-fr" xmlns="http://www.w3.org/2005/Atom"> 
            <title>Twitter de '.$info['username'].' / '.$info['fullname'].'</title>
            <subtitle>'.$info['bio'].'</subtitle>
            <link href="https://twitter.com/'.$info['username'].'"/>
            <updated></updates>
            <author>
                <name>'.$info['fullname'].'</name>
            </author>
        ';
    } 

    else $rss = read_cache($username);
    
    //header('Content-type: application/xml; charset=UTF-8');
    //echo $rss;
?>