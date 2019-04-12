<?php

require ('../vendor/autoload.php');
include ('emailConfig.php');

use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Project\ProjectService;
use JiraRestApi\ServiceDesk\OrganizationService;
use JiraRestApi\ServiceDesk\ServiceDeskService;
use JiraRestApi\User\UserService;
use JiraRestApi\Group\GroupService;
use JiraRestApi\JiraException;

header('Content-Type: text/html; charset=utf-8');

$config = new ArrayConfiguration(
    [
        'jiraHost' => 'https://cleverit.atlassian.net',
        'jiraUser' => 'testuser@cleverit.nl',
        'jiraPassword' => 'P@ssword1',
    ]
);


if(!isset($_GET['email'])) {
    die('Please supply email');
}


try {

    $email = $_GET['email'];
    $user = new UserService($config);
    $param = ['username' => $email];
    $userSearch = $user->search($param);
    $userExists = false;
    if (count($userSearch) > 0) {
        foreach ($userSearch as $userSearchEntry) {
            if ($userSearchEntry->emailAddress == $email) {
                $userExists = true;
            }
        }
    }
    if ($userExists) die ('User already exists :)');

    //$proj = new ProjectService($config);
    //$p = $proj->get('CLEVER');

    //echo $p->id;
    //$org = new OrganizationService($config);

    //$p = $org->get();

    //$factCust = explode("\r\n", $facturatieCustomers);
    //foreach($factCust as $factCustEntry) {
        //print_r($org->add($factCustEntry));
    //}

    //$servicedesk = new ServiceDeskService($config);
    //$y = $servicedesk->getOrganization(2);


    //$y = $org->add('Benefit Inkoopadviesgroep BV');

    //print_r($p);

    $username = $email;

    $user = new UserService($config);
    //$y = $user->get(['username' => $username]);
    $y = $user->add($username, 'Niet.Inloggen684!', $email, $email);

    echo '<pre>' . PHP_EOL;

    print_r($y);

    $group = new GroupService($config);
    //$z = $group->getMember('jira-servicedesk-users');
    //print_r($z);

    try {
        $x = $group->removeUser('jira-servicedesk-users', $username);
        print_r($x);
    } catch (Exception $e) {
        print("Error Occured! " . $e->getMessage());
    }

    $servicedesk = new ServiceDeskService($config);
    $z = $servicedesk->addCustomer(2, [$username]);
    print_r($z);

    $orgName = null;
    foreach ($emailConfig as $emailConfigName => $emailConfigValue) {
        if (strpos($email, $emailConfigName) !== false) {
            $orgName = $emailConfigValue;
        }
    }

    if ($orgName != null) {
        echo 'Organziation based on e-mail: ' . $orgName . PHP_EOL;

        $org = new OrganizationService($config);
        $x = 0;
        $allOrgs = [];
        do {
            $orgValue = $org->get($x * 50, 50);
            foreach ($orgValue->values as $orgEntry) {
                $allOrgs[$orgEntry->name] = $orgEntry->id;
            }
            $x++;
            //if ($size)
        } while ($orgValue->size == 50);

        //print_r($allOrgs);

        if (isset($allOrgs[$orgName])) {
            //print_r($org->get());
            print_r($org->addUser($allOrgs[$orgName], [$username]));
            echo 'Organization found' . PHP_EOL;
        }
    }



    for ($i = 1; $i <= 120; $i++) {
        //print_r($servicedesk->addOrganization(2, $i));
    }


} catch (JiraException $e) {
    print("Error Occured! " . $e->getMessage());
}


