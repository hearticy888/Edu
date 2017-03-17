<?php

namespace App\Http\Controllers;

use App\Config\SiteConstants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Model\TokenCache;


class LinkController extends Controller
{
    public  function index()
    {

        $isLocalUserExists = false;
        $areAccountsLinked = false;
        $showLinkToExistingO365Account = false;
        $localUserEmail = '';
        //If session exists for O365 user, it's the first time that an O365 user login.

        if(isset($_SESSION[SiteConstants::Session_O365_User_ID])){
            $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
            if($o365userId){
                $user  = User::where('email', $_SESSION[SiteConstants::Session_O365_User_Email])->first();
                if($user){
                    $isLocalUserExists = true;
                    $localUserEmail = $_SESSION[SiteConstants::Session_O365_User_Email];
                }
            }
        }


        //check if a user is login.
        if (Auth::check()) {
            $user = Auth::user();
            $o365UserIdInDB= $user->o365UserId;
            $o365UserEmailInDB=$user->o365Email;
            if( !$o365UserEmailInDB || ! $o365UserEmailInDB || $o365UserEmailInDB==='' || $o365UserIdInDB==='') {
                //Local user login but not linked. Should show link to existing o365 account link and then login to o365.
                $showLinkToExistingO365Account = true;
            }else{
                //Accounts are linked. Will show user basic information and  allow user to update favorite color.
                $areAccountsLinked = true;
            }
        }
        $arrData = array(
            'isLocalUserExists'=>$isLocalUserExists,
            'areAccountsLinked'=>$areAccountsLinked,
            'localUserEmail' =>$localUserEmail,
            'showLinkToExistingO365Account' =>$showLinkToExistingO365Account
        );
        return view("link.index",$arrData);
    }

    //Create a new local account and link with O365 account.
    public function createLocalAccount()
    {

        if($input =Input::all()){
            $favoriteColor = $input['FavoriteColor'];
            $o365UserId = $_SESSION[SiteConstants::Session_O365_User_ID];
            $o365Email = $_SESSION[SiteConstants::Session_O365_User_Email];
            $firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
            $lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
          $user =  User::create([
                'firstName' => $firstName,
                'lastName' => $lastName,
               // 'password' => bcrypt('secret'),
                'o365UserId' =>$o365UserId,
                'o365Email'=>$o365Email,
                'email' =>$o365Email,
                'favorite_color'=>$favoriteColor
            ]);
            Auth::loginUsingId($user->id);
            (new TokenCache)->UpdateOrInsertCache($o365UserId,$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
            return redirect('/schools');
        }else{
            return view("link.createlocalaccount");
        }
        }


    public function loginLocal()
    {
        $o365email = $_SESSION[SiteConstants::Session_O365_User_Email];
        if($input =Input::all()){
            //Post from page. Link o365 user to an existing local account.
           $email = $input['email'];
           $password = $input['password'];
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];
            if (Auth::attempt($credentials)) {
               $user = Auth::user();
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->password = '';
                $user->save();
                Auth::loginUsingId($user->id);

                (new TokenCache)->UpdateOrInsertCache($_SESSION[SiteConstants::Session_O365_User_ID],$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }else{
                return back()->with('msg','Invalid login attempt.');
            }

        }
        else{
            //If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
            $user  = User::where('email', $o365email)->first();

            if($user){
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->password = '';
                $user->save();
                Auth::loginUsingId($user->id);
                (new TokenCache)->UpdateOrInsertCache($_SESSION[SiteConstants::Session_O365_User_ID],$_SESSION[SiteConstants::Session_Refresh_Token],$_SESSION[SiteConstants::Session_Tokens_Array]);
                if (Auth::check()) {
                    return redirect("/schools");
                }

            }
            return view('link.loginlocal');
        }


    }
}
