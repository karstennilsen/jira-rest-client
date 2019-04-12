<?php

use JiraRestApi\Configuration\ArrayConfiguration;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once '../vendor/autoload.php';
$config = new ArrayConfiguration(
    [
        'jiraHost' => 'https://cleverit.atlassian.net',
        'jiraUser' => 'testuser@cleverit.nl',
        'jiraPassword' => 'P@ssword1',
    ]
);

$projectID = 2;
$queueID = 7;
$serviceDesk = new \JiraRestApi\ServiceDesk\ServiceDeskService($config);

$tickets = ['cleverit' => ['Unassigned' => ['total' => ['amount' => 0, 'totalTime' => 0], 'open' => ['amount' => 0, 'totalTime' => 0], 'waiting' => ['amount' => 0, 'totalTime' => 0], 'supplier' => ['amount' => 0, 'totalTime' => 0], 'internal' => ['amount' => 0, 'totalTime' => 0]]], 'contoso' => []];
$x = 0;
do {
    $ticketsValues = $serviceDesk->getIssuesInQueue($projectID, $queueID, $x * 50);
    foreach ($ticketsValues->values as $ticket) {
        $key = 'Unassigned';
        $company = 'cleverit';
        if ($ticket->fields->assignee != null) {
            $key = $ticket->fields->assignee->displayName;
            $email = $ticket->fields->assignee->emailAddress;
            $company = explode(".", explode("@", $email)[1])[0];

            if(!isset($tickets[$company][$key])){
                $tickets[$company][$key] = ['total' => ['amount' => 0, 'totalTime' => 0], 'open' => ['amount' => 0, 'totalTime' => 0], 'waiting' => ['amount' => 0, 'totalTime' => 0], 'supplier' => ['amount' => 0, 'totalTime' => 0], 'internal' => ['amount' => 0, 'totalTime' => 0]];
            }
        }

        $diffSeconds = (new \Carbon\Carbon($ticket->fields->created))->diffInSeconds(\Carbon\Carbon::now());


        $tickets[$company][$key]['total']['amount'] += 1;
        $tickets[$company][$key]['total']['totalTime'] += $diffSeconds;
        $organizations = $ticket->fields->customfield_10107;
        if(count($organizations) == 1 && $organizations[0] == 'CleverIT'){
            $tickets[$company][$key]['internal']['amount'] += 1;
            $tickets[$company][$key]['internal']['totalTime'] += $diffSeconds;
        } elseif($ticket->fields->status->id == 10001){
            $tickets[$company][$key]['open']['amount'] += 1;
            $tickets[$company][$key]['open']['totalTime'] += $diffSeconds;
        } elseif($ticket->fields->status->id == 10002){
            $tickets[$company][$key]['waiting']['amount'] += 1;
            $tickets[$company][$key]['waiting']['totalTime'] += $diffSeconds;
        }  elseif($ticket->fields->status->id == 10003){
            $tickets[$company][$key]['waiting']['amount'] += 1;
            $tickets[$company][$key]['waiting']['totalTime'] += $diffSeconds;
        } elseif($ticket->fields->status->id == 10100){
            $tickets[$company][$key]['supplier']['amount'] += 1;
            $tickets[$company][$key]['supplier']['totalTime'] += $diffSeconds;
        }


    }
    $x++;
    //if ($size)
} while ($ticketsValues->size == 50);

ksort($tickets['cleverit']);
ksort($tickets['contoso']);
echo json_encode($tickets);
