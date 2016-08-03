<?php
/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */

/**
 * Hybrid_Providers_Kakao
 */
class Hybrid_Providers_Kakao extends Hybrid_Provider_Model_OAuth2 {

    public $scope = "";
    /**
     * Provider API wrapper
     * @var OAuth2Client
     */
    public $api = null;

    function initialize() {
        parent::initialize();
        $this->api->api_base_url  = "https://kapi.kakao.com/";
        $this->api->authorize_url = "https://kauth.kakao.com/oauth/authorize";
        $this->api->token_url     = "https://kauth.kakao.com/oauth/token";
    }

    function getUserProfile(){
	
        $data = $this->api->api("v1/user/me" );
		print_r( $data );

        $this->user->profile->identifier  = $data->id;
		print_r( $this->user->profile);
        #$this->user->profile->displayName = $data->data->full_name ? $data->data->full_name : $data->data->username;
        #$this->user->profile->description = $data->data->bio;
        #$this->user->profile->photoURL    = $data->data->profile_picture;

        #$this->user->profile->webSiteURL  = $data->data->website;

        #$this->user->profile->username    = $data->data->username;
		return $this->user->profile;
    }


    /**
     * {@inheritdoc}
     */
    function loginFinish() {
        $error = (array_key_exists('error', $_REQUEST)) ? $_REQUEST['error'] : "";
        // check for errors
        if ($error) {
            throw new Exception("Authentication failed! {$this->providerId} returned an error: $error", 5);
        }
        // try to authenticate user
        $code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : "";
        try {
            $this->api->authenticate($code);
        } catch (Exception $e) {
            throw new Exception("User profile request failed! {$this->providerId} returned an error: $e", 6);
        }
        // check if authenticated
        if (!$this->api->access_token) {
            throw new Exception("Authentication failed! {$this->providerId} returned an invalid access token.", 5);
        }
        // store tokens
        $this->token("access_token", $this->api->access_token);
        $this->token("refresh_token", $this->api->refresh_token);
        $this->token("expires_in", $this->api->access_token_expires_in);
        $this->token("expires_at", $this->api->access_token_expires_at);
        // set user connected locally
        $this->setUserConnected();
    }
    /**
     * {@inheritdoc}
     */
    function refreshToken() {
        // have an access token?
        if ($this->api->access_token) {
            // have to refresh?
            if ($this->api->refresh_token && $this->api->access_token_expires_at) {
                // expired?
                if ($this->api->access_token_expires_at <= time()) {
                    $response = $this->api->refreshToken(array("refresh_token" => $this->api->refresh_token));
                    if (!isset($response->access_token) || !$response->access_token) {
                        // set the user as disconnected at this point and throw an exception
                        $this->setUserUnconnected();
                        throw new Exception("The Authorization Service has return an invalid response while requesting a new access token. " . (string) $response->error);
                    }
                    // set new access_token
                    $this->api->access_token = $response->access_token;
                    if (isset($response->refresh_token))
                        $this->api->refresh_token = $response->refresh_token;
                    if (isset($response->expires_in)) {
                        $this->api->access_token_expires_in = $response->expires_in;
                        // even given by some idp, we should calculate this
                        $this->api->access_token_expires_at = time() + $response->expires_in;
                    }
                }
            }
            // re store tokens
            $this->token("access_token", $this->api->access_token);
            $this->token("refresh_token", $this->api->refresh_token);
            $this->token("expires_in", $this->api->access_token_expires_in);
            $this->token("expires_at", $this->api->access_token_expires_at);
        }
    }
}
