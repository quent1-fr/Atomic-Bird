<?php
    include 'include/simplehtmldom.inc';
    include 'include/fonctions.inc';
    include 'include/config.inc';
    date_default_timezone_set('UTC');
    
    if(!isset($_GET['username'])) die('<h1>Erreur</h1><p>Aucun nom d\'utilisateur n\'a &eacute;t&eacute; sp&eacute;cifi&eacute;!</p>');
    if($protection !== false && !isset($_GET['protection']) or $protection !== false && $_GET['protection'] != $protection) die('<h1>Erreur</h1><p>Mauvaise cl&eacute; secr&egrave;te!</p>');
    if($cache_time < 10) die('<h1>Erreur</h1><p>Le temps de cache doit &ecirc;tre d\'au moins 10 minutes!</p>');      
    
    $username = strtolower($_GET['username']);
    
    if(cache_age($username) > $cache_time * 60){
        ini_set('user_agent', $user_agent);
        
        $mobile_timeline = @file_get_contents('https://mobile.twitter.com/' . $username);
    
        if(!$mobile_timeline) die('<h1>Erreur</h1><p>Le twittos n\'existe pas!</p>');
        
        $timeline = new simple_html_dom();
        $timeline->load($mobile_timeline);
    
        $feed = array(
            'name'        => trim($timeline->find('div.fullname', 0)->innertext),
            'url'         => $timeline->find('div.url', 0)->innertext,
            'description' => $timeline->find('div.bio', 0)->innertext . '<br />' . $timeline->find('div.url', 0)->innertext,
            'date'        => $timeline->find('td.timestamp'),
            'timeline'    => $timeline->find('div.tweet-text')
        );
        
        $rss = '<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom">
        <title>Timeline de ' . $feed['name']. ' (@' . $username . ')</title><subtitle><![CDATA[' . htmlentities($feed['description']) . ']]</subtitle>
        <updated>' . date('c', strtotimestamp($feed['date'][0]->plaintext)) . '</updated><link href="https://twitter.com/' . $username . '" />
        <author><name>' . $feed['name'] . '</name><uri>' . $feed['url'] . '</uri></author>';

        $rss = 
        '<?xml version="1.0" encoding="UTF-8"?> <rss xmlns:atom="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss" version="2.0">
            <channel>
                <title>Twitter / '.$username.'</title>
                <link>http://twitter.com/'.$username.'</link>
                <description>Twitter updates from '.$username.'.</description>
                <language>en-us</language>
                <ttl>40</ttl>';

        $nbrdate = count($feed['date']) - 1;
        for($i = 0; $i < $nbrdate; $i++)
            if((!$show_mentions && $feed['timeline'][$i]->plaintext[0] != '@') or $show_mentions)
                $rss .= '
                <item>
                    <title>'.$username.': '. htmlspecialchars(utf8_encode(htmlentities(rtrim(substr($feed['timeline'][$i]->plaintext, 0, 39)), ENT_COMPAT,'utf-8'))).'...</title>
                    <description>'.$username.': '.htmlspecialchars(utf8_encode(htmlentities($feed['timeline'][$i]->plaintext, ENT_COMPAT,'utf-8'))).'</description>
                    <pubDate>'.date("D, d M Y H:i:s", strtotimestamp($feed['date'][$i]->plaintext)).' GMT</pubDate>
                    <guid>http://twitter.com'.$feed['date'][$i]->find('a', 0)->href.'</guid>
                    <link>http://twitter.com'.$feed['date'][$i]->find('a', 0)->href.'</link>

                    
                </item>';

        $rss .= '</channel></rss>';
    
        //write_cache($username, $rss);
    }

    else $rss = read_cache($username);
    
    header('Content-type: application/xml; charset=UTF-8');
    echo $rss;
?>