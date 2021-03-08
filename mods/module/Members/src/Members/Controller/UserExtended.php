<?php

/**
 *
 * PHP Pro Bid
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */

/**
 * MOD:- GOOGLE PLUS LOGIN
 *
 * @version 2.0
 * MOD:- FACEBOOK LOGIN
 *
 * @version 2.1
 */

namespace Members\Controller;

use Cube\Controller\Front,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Service,
    Facebook\FacebookSession,
    Facebook\FacebookRedirectLoginHelper,
    Facebook\FacebookRequestException,
    Facebook\FacebookRequest,
    Facebook\GraphUser;

class UserExtended extends User
{

    public function GoogleOauthLogin()
    {
        if ($this->_settings['enable_google_plus_login']) {
            require_once APPLICATION_PATH . '/mods/library/Google/autoload.php';

            $bootstrap = Front::getInstance()->getBootstrap();

            /** @var \Cube\Session $session */
            $session = $bootstrap->getResource('session');
            /** @var \Cube\View $view */
            $view = $bootstrap->getResource('view');
            // create Client Request to access Google API
            $client = new \Google_Client();
            $client->setClientId($clientID);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);
            $client->addScope("email");
            $client->addScope("profile");

// authenticate code from Google OAuth Flow
//            if (isset($_GET['code'])) {
//                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
//                $client->setAccessToken($token['access_token']);
//
//                // get profile info
//                $google_oauth = new \Google_Service_Oauth2($client);
//                $google_account_info = $google_oauth->userinfo->get();
//                $email =  $google_account_info->email;
//                $name =  $google_account_info->name;
//
//                // now you can use this profile info to create account in your website and make user logged in.
//            } else {
//                echo "<a href='".$client->createAuthUrl()."'>Google Login</a>";
//            }
            $client = new \Google_Client();
            $client->setApplicationName('Google Oauth Log In - ' . $this->_settings['sitename']);
            $client->setClientId($this->_settings['google_oauth_client_id']);
            $client->setClientSecret($this->_settings['google_oauth_client_secret']);
            $client->setRedirectUri(
                $view->url(array('module' => 'members', 'controller' => 'user', 'action' => 'google-oauth-login'))
            );
            $client->addScope('email');
            $client->addScope('profile');
//
//            $client->setApprovalPrompt('auto');
//
//            $plus = new \Google_Service_Plus($client);
            $oauth2 = new \Google_Service_Oauth2($client);

            //If user wishes to log out, we just unset Session variable
            if (isset($_REQUEST['reset'])) {
                $session->unregister('googlePlusAccessToken');
                $client->revokeToken();
            }

            //If code is empty, redirect user to google authentication page for code.
            //Code is required to acquire Access Token from google
            //Once we have access token, assign token to session variable
            //and we can redirect user back to page and login.
            if (isset($_GET['code'])) {
                $client->authenticate($_GET['code']);
                $session->set('googleOauthAccessToken', $client->getAccessToken());

                $this->_helper->redirector()->redirect('index', 'summary', 'members');
            }

            $accessToken = $session->get('googleOauthAccessToken');
            if (isset($accessToken)) {
                $client->setAccessToken($accessToken);
            }


            if ($client->getAccessToken()) {
                //For logged in user, get details from google using access token
                $googleOauthUser = $oauth2->userinfo->get();
                $_SESSION['token'] = $client->getAccessToken();

                if (!empty($googleOauthUser['id'])) {
                    $isRegistered = false;

                    $userId = $googleOauthUser['id'];
                    $email = filter_var($googleOauthUser['email'], FILTER_SANITIZE_EMAIL);

                    $usersService = new Service\Users();
                    $user = $usersService->findBy('google_oauth_id', $userId);

                    if (count($user) > 0) {
                        $isRegistered = true;
                    }
                    else {
                        $user = $usersService->findBy('email', $email);

                        if (count($user) > 0) {
                            $user->save(array(
                                'google_oauth_id' => $userId,
                            ));

                            $isRegistered = true;
                        }
                        else {
                            $session->set('googleOauthUserProfile', array(
                                'google_oauth_id' => $userId,
                                'username'        => filter_var($googleOauthUser['name'], FILTER_SANITIZE_SPECIAL_CHARS),
                                'email'          => $email,
                            ));
                        }
                    }

                    if ($isRegistered) {
                        // log user in
                        Authentication::getInstance()->authenticate(
                            new Adapter(array(), $user->getData('id')));

                        $this->_helper->redirector()->redirect('index', 'summary', 'members');
                    }
                }

                $this->_helper->redirector()->redirect('register', 'user', 'members');
            }
            else {
                //For Guest user, get google login url
                $authUrl = $client->createAuthUrl();
                $this->_helper->redirector()->gotoUrl($authUrl);
            }
        }

        $this->_helper->redirector()->redirect('login', 'user', 'members');
    }

    public function FacebookLogin()
    {
        if ($this->_settings['enable_facebook_login']) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');
            FacebookSession::setDefaultApplication($this->_settings['facebook_app_id'], $this->_settings['facebook_app_secret']);

            $helper = new FacebookRedirectLoginHelper(
                $view->url(array('module' => 'members', 'controller' => 'user', 'action' => 'facebook-login'))
            );

            $session = null;

            try {
                $session = $helper->getSessionFromRedirect();
            } catch (FacebookRequestException $ex) {
                // When Facebook returns an error
            } catch (\Exception $ex) {
                // When validation fails or other local issues
            }

            if ($session) {
                // Logged in
                $userProfile = (new FacebookRequest($session, 'GET', '/me?fields=id,first_name,last_name,email'))->execute()->getGraphObject(GraphUser::className());

                if ($userId = $userProfile->getId()) {
                    $isRegistered = false;
                    $usersService = new Service\Users();
                    $user = $usersService->findBy('facebook_id', $userId);

                    if (count($user) > 0) {
                        $isRegistered = true;
                    }
                    else {
                        $email = $userProfile->getEmail();
                        $user = $usersService->findBy('email', $email);

                        if (count($user) > 0) {
                            $user->save(array(
                                'facebook_id' => $userId,
                            ));

                            $isRegistered = true;
                        }
                        else {
                            /** @var \Cube\Session $session */
                            $session = Front::getInstance()->getBootstrap()->getResource('session');
                            $session->set('facebookUserProfile', array(
                                'facebook_id' => $userId,
                                'email'       => $email,
                                'name'        => array(
                                    'first' => $userProfile->getFirstName(),
                                    'last'  => $userProfile->getLastName(),
                                ),
                            ));
                        }
                    }

                    if ($isRegistered) {
                        // log user in
                        Authentication::getInstance()->authenticate(
                            new Adapter(array(), $user->getData('id')));

                        $this->_helper->redirector()->redirect('index', 'summary', 'members');
                    }
                }

                $this->_helper->redirector()->redirect('register', 'user', 'members');

            }
            else {
                $loginUrl = $helper->getLoginUrl(array(
                    'scope' => 'public_profile', 'email'
                ));
                $this->_helper->redirector()->gotoUrl($loginUrl);
            }
        }

        $this->_helper->redirector()->redirect('login', 'user', 'members');
    }
    
}

