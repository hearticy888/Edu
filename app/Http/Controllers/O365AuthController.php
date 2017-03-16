<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Connect\Constants;
use Lcobucci\JWT\Parser;
use App\Config\SiteConstants;
use App\User;
use Illuminate\Support\Facades\Crypt;
use App\Model\TokenCache;


class O365AuthController extends Controller
{
    public function oauth()
    {

        //We store user name, id, and tokens in session variables
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => Constants::CLIENT_ID,
            'clientSecret'            => Constants::CLIENT_SECRET,
            'redirectUri'             => Constants::REDIRECT_URI,
            'urlAuthorize'            => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken'          => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => '',
            'scopes'                  => Constants::SCOPES
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['code'])) {
            $authorizationUrl = $provider->getAuthorizationUrl();

            // The OAuth library automatically generates a state value that we can
            // validate later. We just save it for now.
            $_SESSION['state'] = $provider->getState();

            header('Location: ' . $authorizationUrl);
            exit();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
            // Validate the OAuth state parameter
            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['state'])) {
                unset($_SESSION['state']);
                exit('State value does not match the one initially sent');

            }


            // With the authorization code, we can retrieve access tokens and other data.
            try {
                // Get an access token using the authorization code grant
                $microsoftToken = $provider->getAccessToken('authorization_code', [
                    'code'     => $_GET['code']
                ]);


                $aadGraphToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token'     => $microsoftToken->getRefreshToken(),
                    'resource' =>Constants::AADGraph
                ]);


                $ts = $aadGraphToken->getExpires();
                $date = new \DateTime("@$ts");
                $aadTokenExpires = $date->format('Y-m-d H:i:s');
                $ts = $microsoftToken->getExpires();
                $date = new \DateTime("@$ts");
                $microsoftTokenExpires =  $date->format('Y-m-d H:i:s');
                $format = '{"https://graph.windows.net":{"expiresOn":"%s","value":"%s"},"https://graph.microsoft.com":{"expiresOn":"%s","value":"%s"}}';
                $tokensArray = sprintf($format, $aadTokenExpires,$aadGraphToken->getToken(), $microsoftTokenExpires,$microsoftToken->getToken());
                $_SESSION[SiteConstants::Session_Tokens_Array] = $tokensArray;

                $refreshToken = $aadGraphToken->getRefreshToken();
                $_SESSION[SiteConstants::Session_Refresh_Token] = $refreshToken;

                $idToken = $microsoftToken->getValues()['id_token'];
                $token = (new Parser())->parse((string) $idToken); // Parses from a string

                $o365UserId = $token->getClaim('oid');

                $user  = User::where('o365UserId',$o365UserId)->first();

                //If user exists on db, check if this user is linked. If linked, go to schools/index page, otherwise go to link page.
                //If user doesn't exists on db, add user information like o365 user id, first name, last name to session and then go to link page.
                if($user){
                     $o365UserIdInDB= $user->o365UserId;
                    $o365UserEmailInDB=$user->o365Email;
                    if($o365UserEmailInDB==='' || $o365UserIdInDB===''){
                        return redirect('/link');
                    }else{
                        Auth::loginUsingId($user->id);
                        if (Auth::check()) {

                            (new TokenCache)->UpdateOrInsertCache($o365UserIdInDB,$refreshToken,$tokensArray);

                            return redirect("/schools");
                        }
                    }
                }
                else{
                    $_SESSION[SiteConstants::Session_O365_User_ID] = $o365UserId;
                    $_SESSION[SiteConstants::Session_O365_User_Email] = $token->getClaim('unique_name');
                    $_SESSION[SiteConstants::Session_O365_User_First_name] = $token->getClaim('given_name');
                    $_SESSION[SiteConstants::Session_O365_User_Last_name] = $token->getClaim('family_name');
                    return redirect('/link');
                }



            } catch (Exception $e) {
                echo 'Something went wrong, couldn\'t get tokens: ' . $e->getMessage();
            }
        }
    }


}
