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
        
        $atom = '<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom"><title>Timeline de ' . $feed['name']. ' (@' . $username . ')</title><subtitle>' . $feed['description'] . '</subtitle><updated>' . date('c', strtotimestamp($feed['date'][0]->plaintext)) . '</updated><link href="https://twitter.com/' . $username . '" /><author><name>' . $feed['name'] . '</name><uri>' . $feed['url'] . '</uri></author>';
        $nbrdate = count($feed['date']) - 1;
        for($i = 0; $i < $nbrdate; $i++)
            if((!$show_mentions && $feed['timeline'][$i]->plaintext[0] != '@') or $show_mentions)
                $atom .= '<entry>
    <title>@' . $username . ': ' . rtrim(substr($feed['timeline'][$i]->plaintext, 0, 39)) . '...</title>
    <updated>' . date('c', strtotimestamp($feed['date'][$i]->plaintext)) . '</updated>
    <link href="https://twitter.com/' . $feed['date'][$i]->find('a', 0)->href . ' "/>
    <summary><![CDATA[' . preg_replace('/\s{2,}/', ' ', full_links($feed['timeline'][$i]->innertext)) . ']]></summary>
</entry>';
        $atom .= '</feed>';
    
        write_cache($username, $atom);
    }

    else $atom = read_cache($username);
    
    header('Content-type: application/atom+xml; charset=utf-8');
    echo $atom;
?>
