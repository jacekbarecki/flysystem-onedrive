<?php

namespace JacekBarecki\FlysystemOneDrive\OAuth2;


class OAuth2Provider
{

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     */
    private static function getProvider($clientId, $clientSecret, $redirectUri) {
        $provider = new \Stevenmaguire\OAuth2\Client\Provider\Microsoft([
                    'clientId'          => $clientId,
                    'clientSecret'      => $clientSecret,
                    'redirectUri'       => $redirectUri
                ]);
        return $provider;
    }


    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * 
     * Url returned looks like this:
     * https://login.live.com/oauth20_authorize.srf?state=OPTIONAL_CUSTOM_CONFIGURED_STATE&scope=wl.basic%2Cwl.signin%2Cwl.skydrive_update%2Cwl.offline_access&response_type=code&approval_prompt=auto&redirect_uri=https%3A%2F%2Fxxxxxxxx.com%2Fpath&client_id=9828281-18237-xxxxxxx
     */
    public static function getAuthorization($clientId, $clientSecret, $redirectUri) {
        $provider = OAuth2Provider::getProvider($clientId, $clientSecret, $redirectUri);

        //Set the scopes needed for OneDrive API
        $options = [
            'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
            'scope' => ['wl.basic', 'wl.signin', 'wl.skydrive_update', 'wl.offline_access'] // array or string
        ];

        return $provider->getAuthorizationUrl($options);
    }


    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $code
     *
     * To extract the access token:  $token->getToken();
     * To extract the refresh token:  $token->getRefreshToken();
     */
    public static function getTokenFromCode($clientId, $clientSecret, $redirectUri, $code) {
        $provider = OAuth2Provider::getProvider($clientId, $clientSecret, $redirectUri);

        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        return $token;
    }


    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $refreshToken
     *
     * To extract the access token:  $token->getToken();
     * To extract the refresh token:  $token->getRefreshToken();
     */
    public static function getTokenFromRefreshToken($clientId, $clientSecret, $redirectUri, $refreshToken) {
        $provider = OAuth2Provider::getProvider($clientId, $clientSecret, $redirectUri);

        //Get new access token
        $token = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken
        ]);

        return $token;
    }
}


?>