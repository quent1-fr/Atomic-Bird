# Atomic Bird
## Présentation

Atomic Bird (atomic pour le format de syndication Atom, et Bird pour l'oiseau de Twitter) est un petit script (~60ko) écrit en PHP qui vous permettra de continuer à suivre des personnes via un flux Atom, et ce, malgré le probable abandon prochain de cette fonctionnalité. Pour cela, le script va se faire passer pour un navigateur mobile, aller chercher la timeline de l'utilisateur souhaité sur la [version mobile de Twitter](mobile.twitter.com) et parser la page HTML (grâce à [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/)) pour renvoyer un fichier au format Atom.

Ce script dispose d'un cache d'une durée minimale de 10 minutes. Merci de bien vouloir mettre une durée **décente** de façon à éviter que Twitter interdisse purement et simplement ce genre de script (qui est déjà à la limite de ce qui est autorité dans leur conditions générales d'utilisation).

## Installation

1. Décompressez le ficher téléchargé
2. Copiez le dossier obtenur sur votre serveur
3. Autorisez PHP à écrire à l'intérieur du dossier cache/
3. Configurez votre script à l'aide du fichier include/config.inc

## Utilisation

Pour utiliser ce script, passez simplement le nom d'utilisateur dont vous souhaitez obtenir la timeline grâce au paramètre username.

**Exemple**: *http://monserveur.fr/Atomic-Bird/index.php?username=google* vous renverra la timeline du compte officiel de Google sur Twitter.

## Licences
* Atomic Bird: distribué sous licence Licence publique générale GNU
* PHP Simple HTML DOM Parser: distribué sous licence MIT 

## Todo
* Afficher plus de tweets (jusqu'à 50 par exemple)