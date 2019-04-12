<?php

namespace JiraRestApi\ServiceDesk;

use JiraRestApi\JiraException;

class OrganizationService extends \JiraRestApi\JiraClient
{
    private $uri = '/servicedeskapi/organization';

     /**
     * get get.
     *
     */
    public function get($start = 0, $limit = 50)
    {
        $paramArray = [
            'start' => $start,
            'limit' => $limit
        ];
        $queryParam = '?'.http_build_query($paramArray);
        $ret = $this->exec($this->uri.$queryParam);
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

    public function getUsers($organziationId, $start = 0, $limit = 50)
    {
        $paramArray = [
            'start' => $start,
            'limit' => $limit
        ];
        $queryParam = '?'.http_build_query($paramArray);
        $ret = $this->exec($this->uri."/$organziationId/user".$queryParam);
        $this->log->addDebug("OrganizationgetUsers res=$ret\n");
        return json_decode($ret);

    }

    /**
     * add Organization
     *
     */
    public function add($organizationName){
        $this->log->addInfo("addOrganization=\n");

        $postObject = (object) array('name' => $organizationName);
        $data = json_encode( $postObject );
        $url = $this->uri;
        $type = 'POST';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }

    /**
     * add User to Organization
     *
     */
    public function addUser($organizationId, $usernames){
        $this->log->addInfo("OrganizationaddUser=\n");

        $postObject = (object) array('usernames' => null);
        $postObject->usernames = $usernames;
        $data = json_encode( $postObject );

        $url = $this->uri."/$organizationId/user";
        $type = 'POST';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }
}
