<?php

namespace JiraRestApi\ServiceDesk;

use JiraRestApi\JiraException;

class ServiceDeskService extends \JiraRestApi\JiraClient
{
    private $uri = '/servicedeskapi/servicedesk';

    /**
     * get get.
     *
     */
    public function get()
    {
        $paramArray = [
            'start' => 0,
            'limit' => 50
        ];
        $queryParam = '?'.http_build_query($paramArray);
        echo $this->uri.$queryParam;
        $ret = $this->exec($this->uri.$queryParam);
        $this->log->addDebug("getServiceDesk res=$ret\n");
        return json_decode($ret);
    }

    /**
     * get getOrganization.
     *
     */
    public function getOrganization($serviceDeskId)
    {
        $paramArray = [
            'start' => 0,
            'limit' => 50
        ];
        $queryParam = '?'.http_build_query($paramArray);
        echo $this->uri."/$serviceDeskId/organization".$queryParam;
        $ret = $this->exec($this->uri."/$serviceDeskId/organization".$queryParam);
        $this->log->addDebug("getOrganization res=$ret\n");
        return json_decode($ret);
    }

    /**
     * get Organization by Id.
     *
     * @param int   $organziationId
     *
     * @return mixed
     */
    public function getById($organziationId)
    {
        $ret = $this->exec($this->uri."/$organziationId");
        $this->log->addDebug("getOrganizationById res=$ret\n");
        return json_decode($ret);

    }

    /**
     * add Customer
     *
     * @param string serviceDeskId
     * @param array userNames
     *
     * @return mixed result
     */
    public function addCustomer($serviceDeskId, $usernames){
        $this->log->addInfo("addServiceDeskOrganization=\n");

        $postObject = (object) array('usernames' => null);
        $postObject->usernames = $usernames;
        $data = json_encode( $postObject );
        $url = $this->uri."/$serviceDeskId/customer";
        $type = 'POST';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    /**
     * add Organization
     *
     */
    public function addOrganization($serviceDeskId, $organizationId){
        $this->log->addInfo("addServiceDeskOrganization=\n");

        $postObject = (object) array('organizationId' => $organizationId);
        $data = json_encode( $postObject );
        $url = $this->uri."/$serviceDeskId/organization";
        $type = 'POST';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    public function getIssuesInQueue($serviceDeskId, $queueID, $start){
        $url = $this->uri."/$serviceDeskId/queue/$queueID/issue?expand=renderedFields&start=$start";
        $type = 'GET';

        $ret = $this->exec($url, null, $type);

        return json_decode($ret);

    }

    public function getIssue($issueID, $expand=null){
        $url = "/issue/$issueID/?expand=renderedFields";
        if($expand){
            $url .= ','.$expand;
        }

        $type = 'GET';

        $ret = $this->exec($url, null, $type);

        return json_decode($ret);
    }

    public function updateSecurityIssues($issueID, $securityID){
        $url = "/issue/$issueID";
        //echo $url;
        $data = json_encode( ['fields' => ['security' => ['id' => $securityID]]] );
        $type = 'PUT';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    public function updateBillable($issueID, $billableId){
        $url = "/issue/$issueID";
        //echo $url;
        $data = json_encode( ['fields' => ['customfield_10200' => ['id' => $billableId]]] );
        $type = 'PUT';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    public function updateTopdesk($issueID, $topdesk){
        $url = "/issue/$issueID";
        //echo $url;
        $data = json_encode( ['fields' => ['customfield_10500' => $topdesk]] );
        $type = 'PUT';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    public function addOrganizationToIssue($issueID, $organizationId){
        $url = "/issue/$issueID";
        //echo $url;
        $data = json_encode( ['fields' => ['customfield_10700' => (int) $organizationId]] );
        $type = 'PUT';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    public function updateBilled($issueID, $billed){
        $url = "/issue/$issueID";
        //echo $url;
        $data = json_encode( ['fields' => ['customfield_10401' => ['value' => $billed]]] );
        $type = 'PUT';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }
}
