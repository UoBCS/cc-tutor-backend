<?php

namespace App\Infrastructure\Auth;

use App\Api\Users\Repositories\UserRepository;
use App\Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

/**
 * Class that deals with communication with the authorization server
 */
class LoginProxy
{
    /**
     * The refresh token name
     */
    const REFRESH_TOKEN = 'refreshToken';

    /**
     * The repository for dealing with the users table
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Create a new LoginProxy
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * Attempt to create an access token using user credentials
     *
     * @param string $email
     * @param string $password
     */
    public function attemptLogin($email, $password)
    {
        var_dump('smdmsmdmsd');

        $user = $this->userRepository->getWhere('email', $email)->first();

        var_dump('helooooosldlsd');

        if (!is_null($user)) {
            return $this->proxy('password', [
                'username' => $email,
                'password' => $password
            ]);
        }

        throw new InvalidCredentialsException("There is no user with email $email");
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh()
    {
        $refreshToken = request()->cookie(self::REFRESH_TOKEN);

        return $this->proxy('refresh_token', [
            'refresh_token' => $refreshToken
        ]);
    }

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     */
    public function proxy($grantType, array $data = [])
    {
        $data = array_merge($data, [
            'client_id'     => config('cc_tutor.oauth2.pgc_id'),
            'client_secret' => config('cc_tutor.oauth2.pgc_secret'),
            'grant_type'    => $grantType,
            'scope'         => '*'
        ]);

        try {
            $client = new Client();
            $guzzleResponse = $client->post(sprintf('%s/oauth/token', config('app.url')), [
                'form_params' => $data
            ]);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $message = $this->parseBadResponseException($e->getMessage());
            throw new InvalidCredentialsException($message['message']);
        }

        $data = json_decode($guzzleResponse->getBody());

        // Create a refresh token cookie
        cookie()->queue(
            self::REFRESH_TOKEN,
            $data->refresh_token,
            30 * 86400, // 30 days
            null,
            null,
            false,
            true // HttpOnly
        );

        return [
            'access_token' => $data->access_token,
            'expires_in' => $data->expires_in
        ];
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logout()
    {
        $accessToken = auth()->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
                        ->where('access_token_id', $accessToken->id)
                        ->update([
                            'revoked' => true
                        ]);

        $accessToken->revoke();

        cookie()->queue(cookie()->forget(self::REFRESH_TOKEN));
    }

    /**
     * Parse a \GuzzleHttp\Exception\BadResponseException exception message
     *
     * @param  string $message
     * @return array
     */
    private function parseBadResponseException($message)
    {
        $getStringBetween = function ($string, $start, $end){
            $string = ' ' . $string;
            $ini = strpos($string, $start);
            if ($ini == 0) return '';
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            return substr($string, $ini, $len);
        };

        $parsedMessage = '{' . call_user_func($getStringBetween, $message, '{', '}') . '}';
        return json_decode($parsedMessage, true);
    }
}
