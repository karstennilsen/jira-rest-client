<?php
function friendlyTime($seconds) {
    $minutes = 0;
    $hours = 0;
    $days = 0;

    $minutes = round($seconds / 60);
    $hours = floor($minutes / 60);
    $minutes = $minutes - ($hours * 60);
    $days = floor($hours / 24);
    $hours = $hours - ($days * 24);

    $datestring = "";
    if ($days != 0) {
        $datestring .= $days . 'd';
    }
    if ($hours != 0) {
        $datestring .= $hours . 'h';
    }
    if ($minutes != 0) {
        $datestring .= $minutes . 'm';
    }

    return $datestring;
}


use JiraRestApi\Configuration\ArrayConfiguration;

error_reporting(E_ALL);
ini_set('display_errors', 1);
require('../vendor/autoload.php');

//header('Content-Type: application/json; charset=utf-8');
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
$queueID = 16;

$startDate = \Carbon\Carbon::now()->addDays(-7);
$endDate = \Carbon\Carbon::now();

$serviceDesk = new \JiraRestApi\ServiceDesk\ServiceDeskService($config);
$tickets = [];
$x = 0;
do {
    $ticketsValues = $serviceDesk->getIssuesInQueue($projectID, $queueID, $x * 50);
    foreach ($ticketsValues->values as $ticket) {
        if (empty($ticket->fields->customfield_10107)) {
            $tickets['none'][] = $ticket;
        } else {
            $tickets[$ticket->fields->customfield_10107[0]][] = $ticket;
        }

    }
    $x++;
    //if ($size)
} while ($ticketsValues->size == 50);

$reportValues = [
    'ict' => [
        'total' =>
            [
                'logged' => 0,
                'resolved' => 0,
                'open' => 0,
                'solvetime' => 0
            ]
    ],
    'app' => [
        'total' =>
            [
                'logged' => 0,
                'resolved' => 0,
                'open' => 0,
                'solvetime' => 0
            ]
    ]
];

foreach ($tickets[$customer] as $ticket) {
    $typeKeys = [];
    if ($ticket->fields->customfield_10300 != null && !empty($ticket->fields->customfield_10300)) {
        foreach ($ticket->fields->customfield_10300 as $typeKey) {
            $typeKeys[$typeKey->id] = $typeKey->value;
        }
    } else {
        $typeKeys = [10201 => "-"];
    }

    $categoryDone = [];
    foreach ($typeKeys as $typeId => $typeKey) {
        if (in_array($typeId, [10200, 10201])) {
            $categoryKey = 'ict';
        } else {
            $categoryKey = 'app';
        }

        if (in_array($categoryKey, $categoryDone)) {
            $isDuplicate = true;
        } else {
            $isDuplicate = false;
            $categoryDone[] = $categoryKey;
        }

        if (!isset($reportValues[$categoryKey][$typeKey])) {
            $reportValues[$categoryKey][$typeKey] = ['logged' => 0, 'resolved' => 0, 'open' => 0, 'solvetime' => 0];
        }

        if ($startDate->lte(\Carbon\Carbon::parse($ticket->fields->created))) {
            $reportValues[$categoryKey][$typeKey]['logged']++;
            if (!$isDuplicate) {
                $reportValues[$categoryKey]['total']['logged']++;
            }
        }
        if ($ticket->fields->status->statusCategory->id == 3) {
            $solvetime = end($ticket->fields->customfield_10130->completedCycles)->elapsedTime->millis;
            $reportValues[$categoryKey][$typeKey]['resolved']++;
            $reportValues[$categoryKey][$typeKey]['solvetime'] += $solvetime;
            if (!$isDuplicate) {
                $reportValues[$categoryKey]['total']['resolved']++;
                $reportValues[$categoryKey]['total']['solvetime'] += $solvetime;
            }
        } else {
            $reportValues[$categoryKey][$typeKey]['open']++;
            if (!$isDuplicate) {
                $reportValues[$categoryKey]['total']['open']++;
            }
        }

    }
}

$css = "
<style>
th {
    text-align: left;
}
.logo {
    text-align: center;
}

.logo img {
   margin-bottom: 30px;
}

table.reportTable {
  border: none;
  border-collapse: collapse;
  margin-top:30px;
  margin-bottom:30px;
}
table.reportTable tr td{
    padding-top:5px;
}
table.reportTable td, table.reportTable th {
    padding-left: 5px;
    padding-right:5px;
}
table.reportTable tr.header {
    color:white;
    background-color:#008FD5;
    height:30px;
}

table.reportTable tr.header td {
    border: none !important;
}

table.reportTable tr {
    border-bottom: 1px dotted grey !important;:
}

table.ticketTable {
    margin-top: 50px;
    margin-bottom:50px;
      border: none;
  border-collapse: collapse;
}

table.ticketTable tr {

}

table.ticketTable td {
    min-width: 150px !important;
    padding-top:5px;
}

table.ticketTable td.label {
    background-color:#008FD5;
    color:white;
    padding-left: 5px;
    padding-right:5px;
    font-weight: bold;
        border-bottom: 1px dotted white;
}

table.ticketTable td.value {
    padding-left: 10px;
        border-bottom: 1px dotted grey;
}
</style>";
if ($showProgressTable) {
    $htmlTable = "
<table width='100%' class='reportTable'>
    <tr class='header'>
        <th>Category</th>
        <th>Logged</th>
        <th>Resolved</th>
        <th>Open</th>
        <th>Time until solved</th>
    </tr>
    <tr>
        <td><strong>ICT issues</strong></td>
        <td>" . $reportValues['ict']['total']['logged'] . "</td>
        <td>" . $reportValues['ict']['total']['resolved'] . "</td>
        <td>" . $reportValues['ict']['total']['open'] . "</td>
        <td>" . ($reportValues['ict']['total']['solvetime'] != 0 ? friendlyTime($reportValues['ict']['total']['solvetime'] / $reportValues['ict']['total']['resolved'] / 1000) : '0m') . "</td>
    </tr>
";

    foreach ($reportValues['ict'] as $key => $ictValues) {
        if ($key == "total" || strlen(trim($key)) <= 2) {
            continue;
        }
        $htmlTable .= '
    <tr>
        <td>-- ' . $key . '</td>
        <td>' . $ictValues['logged'] . '</td>
        <td>' . $ictValues['resolved'] . '</td>
        <td>' . $ictValues['open'] . '</td>
        <td>' . ($ictValues['solvetime'] != 0 ? friendlyTime($ictValues['solvetime'] / $ictValues['resolved'] / 1000) : '0m') . '</td>
    </tr>
    ';
    }

    $htmlTable .= "
    <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><tr>
    <tr>
        <td><strong>Application issues</strong></td>
        <td>" . $reportValues['app']['total']['logged'] . "</td>
        <td>" . $reportValues['app']['total']['resolved'] . "</td>
        <td>" . $reportValues['app']['total']['open'] . "</td>
        <td>" . ($reportValues['app']['total']['solvetime'] != 0 ? friendlyTime($reportValues['app']['total']['solvetime'] / $reportValues['app']['total']['resolved'] / 1000) : '0m') . "</td>
    </tr>
    ";

    foreach ($reportValues['app'] as $key => $ictValues) {
        if ($key == "total" || strlen(trim($key)) <= 2) {
            continue;
        }
        $htmlTable .= '
    <tr>
        <td>-- ' . $key . '</td>
        <td>' . $ictValues['logged'] . '</td>
        <td>' . $ictValues['resolved'] . '</td>
        <td>' . $ictValues['open'] . '</td>
        <td>' . ($ictValues['solvetime'] != 0 ? friendlyTime($ictValues['solvetime'] / $ictValues['resolved'] / 1000) : '0m') . '</td>
    </tr>
    ';
    }

    $htmlTable .= '</table>';
}

?>
<?php $fullTable = $css . '
<table width="600">
    <tr>
        <td class="logo"><img src="http://bram.debian.testuser.nl/cleverit.png"></td>
    </tr>
    <tr>
        <td>
            <p>
                Hello mister X,<br><br>

                Here you have a report
            </p>
        </td>
    </tr>
    <tr>
        <td>' . $htmlTable . '</td>
    </tr>
    <tr>
        <td>';
if ($showTaskList) {
    foreach ($tickets[$customer] as $ticket) {
        $assignee = "";
        if ($ticket->fields->assignee != null) {
            $assignee = $ticket->fields->assignee->displayName;
        }

        echo $ticket->key;
        $issue = $serviceDesk->getIssue($ticket->key);
        $fullTable .= '
                <table class="ticketTable">
                    <tr>
                        <td class="label">Service request nr</td>
                        <td class="value">' . $ticket->key . '</td>
                    </tr>
                    <tr>
                        <td class="label">Datum aanvraag</td>
                        <td class="value">' . (new \Carbon\Carbon($ticket->fields->created))->toFormattedDateString() . '</td>
                    </tr>
                    <tr>
                        <td class="label">Sluitdatum</td>
                        <td class="value">' . (new \Carbon\Carbon($ticket->fields->resolutiondate))->toFormattedDateString() . '</td>
                    </tr>
                    <tr>
                        <td class="label">Onderwerp</td>
                        <td class="value">' . $ticket->fields->summary . '</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td class="value">' . $ticket->fields->status->name . '</td>
                    </tr>
                    <tr>
                        <td class="label">Assignee</td>
                        <td class="value">' . $assignee . '</td>
                    </tr>
                    <tr>
                        <td valign="top" class="label">Extra informatie</td>
                        <td class="value">' . $issue->renderedFields->description . '</td>
                    </tr>

                </table>';
    }
}

$fullTable .= '
        </td>
    </tr>
    <tr>
        <td>
            <p>
                Best regards,<br><br>

                CleverIT team
            </p>
        </td>
    </tr>

</table>';

echo $fullTable;

$to = 'bram@cleverit.nl';

$subject = 'WorkAnyWhere - report';

$headers = "From: bram@bramvaneijk.nl\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";


//mail($to, $subject, $fullTable, $headers);

