<?php
/**
 * Created by PhpStorm.
 * User: bram.vaneijk
 * Date: 25-11-2016
 * Time: 14:55
 */

use JiraRestApi\Configuration\ArrayConfiguration;

error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../vendor/autoload.php');

header('Content-Type: application/json; charset=utf-8');
$config = new ArrayConfiguration(
    [
        'jiraHost' => 'https://cleverit.atlassian.net',
        'jiraUser' => 'testuser@cleverit.nl',
        'jiraPassword' => 'P@ssword1',
    ]
);

//$customer = 'CleverIT';
set_time_limit(600);
$customer = 'Contoso';
$showTaskList = false;
$showProgressTable = true;
$projectID = 2;
$queueID = 18;

$startDate = \Carbon\Carbon::now()->addDays(-7);
$endDate = \Carbon\Carbon::now();

$serviceDesk = new \JiraRestApi\ServiceDesk\ServiceDeskService($config);
$tickets = [];
echo json_encode($serviceDesk->getIssuesInQueue($projectID, $queueID, 0));