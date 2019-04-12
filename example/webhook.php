<?php
use JiraRestApi\Configuration\ArrayConfiguration;

require_once '../vendor/autoload.php';
$config = new ArrayConfiguration(
    [
        'jiraHost' => 'https://cleverit.atlassian.net',
        'jiraUser' => 'testuser@cleverit.nl',
        'jiraPassword' => 'P@ssword1',
    ]
);


$data = json_decode(file_get_contents("php://input"));

$serviceDesk = new \JiraRestApi\ServiceDesk\ServiceDeskService($config);
$fields = $data->issue->fields;
if (in_array('Contoso', $fields->customfield_10107)) {
    if($fields->security->id != 10001) {
        $serviceDesk->updateSecurityIssues($data->issue->id, '10001');
    }
    //update security level

} else {
    if ($fields->security->id == 10001) {
        $serviceDesk->updateSecurityIssues($data->issue->id, '10000');
    }
}
