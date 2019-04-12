<?php

namespace JiraRestApi\User;

/**
 * Class to perform all user related queries.
 *
 * @author Anik
 */
class UserService extends \JiraRestApi\JiraClient
{
    private $uri = '/user';

    /**
     * Function to get user.
     *
     * @param array $paramArray Possible values for $paramArray 'username', 'key'.
     *   "Either the 'username' or the 'key' query parameters need to be provided".
     *
     * @return User class
     */
    public function get($paramArray)
    {
        $queryParam = '?'.http_build_query($paramArray);

        $ret = $this->exec($this->uri.$queryParam, null);

        $this->log->addInfo("Result=\n".$ret);

        return $this->json_mapper->map(
                json_decode($ret), new User()
        );
    }

    public function search($paramArray)
    {
        $queryParam = '?'.http_build_query($paramArray);
echo $queryParam;
        $ret = $this->exec($this->uri.'/search'.$queryParam, null);

        $this->log->addInfo("Result=\n".$ret);

        $userData = json_decode($ret);
        $users = [];

        foreach($userData as $user) {
            $users[] = $this->json_mapper->map(
                $user, new User()
            );
        }
        return $users;
    }
    public function getProperties($userName)
    {
        $queryParam = '?'.http_build_query(['username' => $userName]);

        $ret = $this->exec($this->uri.'/properties'.$queryParam, null);

        $this->log->addInfo("Result=\n".$ret);

        return json_decode($ret);

    }

    /**
     * add Organization
     *
     */
    public function add($user, $pass, $email, $displayname ){
        $this->log->addInfo("addUser=\n");

        $postObject = (object) [
            'name' => $user,
            'password' => $pass,
            'emailAddress' => $email,
            'displayName' => $displayname,
            'applicationRoles' => []
        ];
        $data = json_encode( $postObject );
        $url = $this->uri;
        $type = 'POST';

        $ret = $this->exec($url, $data, $type);

        return json_decode($ret);
    }
}
