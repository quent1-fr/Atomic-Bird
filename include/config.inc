<?php
    $cache_time = 15; // Le temps pendant lequel la timeline d'un utilisateur est mis en cache (en minutes). Ce temps doit être - d'au moins - 10 minutes!
    $show_mentions = true; // Permet de choisir si l'on souhaite ajouter les mentions au flux
    $user_agent = 'Opera/9.80 (S60; SymbOS; Opera Mobi/275; U; en) Presto/2.4.13 Version/10.00'; // L'user-agent à envoyer (ici, on se fait passer pour Opera Mobile)
    $protection = false; // Protection par clé secrète: false pour désactiver, '[VOTRE CLÉ]' pour activer. Ajoutez &protection=maclé à l'URL pour vous identifier.
?>