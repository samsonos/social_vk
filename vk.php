<?php
/**
* Created by Svyatoslav Svitlychnyi <svitlychnyi@samsonos.com>
* on 11.02.14 at 11:35
*/

namespace samson\social;

/**
 *
 * @author Svyatoslav Svitlychnyi <svitlychnyi@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version
 */
class VK extends \samson\social\Network
{
    public $id = 'vk';

    public $dbIdField = 'vk_id';

    public $socialURL = 'https://oauth.vk.com/authorize';

    public $tokenURL = 'https://oauth.vk.com/access_token';

    public $userURL = 'https://api.vk.com/method/users.get';
	
	public $requirements = array('socialnetwork');

    public function __HANDLER()
    {
        // Send http get request to retrieve VK code
        $this->redirect($this->socialURL, array(
            'client_id'     => $this->appCode,
            'redirect_uri'  => $this->returnURL(),
            'response_type' => 'code'
        ));
    }

    public function __token()
    {
        $code = & $_GET['code'];
        if (isset($code)) {

            // Send http get request to retrieve VK code
            $token = $this->post($this->tokenURL, array(
                'client_id' => $this->appCode,
                'client_secret' => $this->appSecret,
                'code' => $code,
                'redirect_uri' => $this->returnURL(),
            ));

            // take user's information using access token
            if (isset($token['access_token'])) {

                // Perform API request to get user data
                $request = $this->get($this->userURL, array(
                    'uids' => $token['user_id'],
                    'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo',
                    'access_token' => $token['access_token']
                ));

                // If we have successfully received user data
                if(isset($request['response'][0])) {
                    $this->setUser($request);
                }
            }
        }

        // Call standart behaviour
        parent::__token();
    }

    protected function setUser(array $user)
    {
        $this->user = new User();
        $this->user->birthday = $user['response'][0]['bdate'];
        $this->user->gender = $user['response'][0]['sex'];
        $this->user->name = $user['response'][0]['first_name'];
        $this->user->surname = $user['response'][0]['last_name'];
        $this->user->socialID = $user['response'][0]['uid'];
        $this->user->photo = $user['response'][0]['photo'];

        parent::setUser($user);
    }
}
