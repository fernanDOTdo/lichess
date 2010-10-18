<?php

/**
 * The purpose of this flat script is to dramatically improve performances, and save my webserver.
 * It handles the synchronization requests (95% of the traffic)
 * If APC cache exists for the user, and no event has occured since previous synchronization (90% of the requests)
 * The application is not run and this script can deliver a response in less than 0.1 milliseconds.
 * If this script returns, the normal Symfony application is run.
 **/

// Configuration
$timeout = 20;

// Get url
$url = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];

// Handle number of connected players requests

if('/how-many-players-now' === $url) {
    $cleanup = 0 === rand(0, 50);
    $it = new \APCIterator('user', '/alive$/', $cleanup ? APC_ITER_MTIME | APC_ITER_KEY : APC_ITER_MTIME, 100, APC_LIST_ACTIVE);
    $nb = 0;
    $limit = time() - $timeout;
    foreach($it as $i) {
        if($cleanup) apc_fetch($i['key']);
        if($i['mtime'] >= $limit) ++$nb;
    }

    // Return minimalist JSON response telling the number of connected players
    header('HTTP/1.0 200 OK');
    header('content-type: text/plain');
    die((string)$nb);
}

// Handle game synchronization

if (0 === strpos($url, '/sync/') && preg_match('#^/sync/(?P<hash>[\w-]{6})/(?P<color>(white|black))/(?P<version>\d+)/(?P<playerFullHash>([\w-]{10}|))$#x', $url, $matches)) {
    $hash = $matches['hash'];
    $color = $matches['color'];
    $clientVersion = $matches['version'];
    $playerFullHash = $matches['playerFullHash'];
    $opponentColor = 'white' === $color ? 'black' : 'white';
}
else return;

// Get user cache from APC
$userVersion = apc_fetch($hash.'.'.$color.'.data');

// If the user has no cache, hit the application
if(!$userVersion) return;

// If the client and server version differ, update the client
if($userVersion != $clientVersion) return;

if($playerFullHash) {
    // Set the client as connected
    apc_store($hash.'.'.$color.'.alive', 1, $timeout);
}

// Check is opponent is connected
if($playerFullHash) {
    $isOpponentAlive = apc_fetch($hash.'.'.$opponentColor.'.alive') ? 1 : 0;
}
else {
    $isOpponentAlive = true;
}

// Return minimalist JSON response telling the state of the opponent
header('HTTP/1.0 200 OK');
header('content-type: application/json');
die('{"o": '.$isOpponentAlive.'}');
