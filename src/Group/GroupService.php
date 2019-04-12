<?php

namespace JiraRestApi\Group;

/**
 * Class to perform all user related queries.
 *
 * @author Anik
 */
class GroupService extends \JiraRestApi\JiraClient
{
    private $uri = '/group';

    /**
     * Function to get user.
     *
     * @param array $paramArray Possible values for $paramArray 'username', 'key'.
     *   "Either the 'username' or the 'key' query parameters need to be provided".
     *
     * @return User class
     */
    public function getMember($groupname)
    {
        $this->log->addInfo("getMember=\n");
        $paramArray = [
            'groupname' => $groupname
        ];
        $queryParam = '?'.http_build_query($paramArray);

        $ret = $this->exec($this->uri."/member".$queryParam, null);

        $this->log->addInfo("Result=\n".$ret);

        return json_decode($ret);

    }
    /**
     * add Organization
     *
     */
    public function removeUser($groupname, $username ){
        $this->log->addInfo("GroupRemoveUser=\n");

        $paramArray = [
            'groupname' => $groupname,
            'username' => $username
        ];

        $queryParam = '?'.http_build_query($paramArray);
        $url = $this->uri . "/user" . $queryParam;
        $type = 'DELETE';

        $ret = $this->exec($url, '', $type);

        return json_decode($ret);
    }
}
