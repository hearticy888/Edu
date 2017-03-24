<?php

namespace App\Http\Controllers\Auth;

use App\Config\SiteConstants;
use App\Services\UserRolesServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class AboutMeController extends Controller
{
    public function index()
    {
        $displayName=$this->GetDisplayName();
        $role=$this->GetUserRole();
        $favoriteColor = $this->GetFavoriteColor();
        $showSaveMessage=false;
        if(isset($_GET['showSaveMessage'])){
            $showSaveMessage=true;
        }

        $arrData = array(
            'displayName'=>$displayName,
            'role'=>$role,
            'favoriteColor'=>$favoriteColor,
            'showSaveMessage'=>$showSaveMessage
        );
        return view('auth.aboutme',$arrData);

    }

    public function SaveFavoriteColor()
    {
        if(!Auth::user())
            return redirect('/login');
        $color =Input::get('FavoriteColor');
        $user=Auth::user();
        $user->favorite_color=$color;
        $user->save();
        return redirect('/auth/aboutme?showSaveMessage=true');
    }

    private function GetDisplayName(){
        $displayName='';
        if(Auth::user()){
            $displayName = Auth::user()->email;
            if(Auth::user()->firstName !='')
                $displayName =Auth::user()->firstName .' '. Auth::user()->lastName;
        }
        else{
            if(isset($_SESSION[SiteConstants::Session_O365_User_First_name] ) && isset( $_SESSION[SiteConstants::Session_O365_User_Last_name]))
                $displayName =  $_SESSION[SiteConstants::Session_O365_User_First_name] .' '. $_SESSION[SiteConstants::Session_O365_User_Last_name] ;
        }
        return $displayName;
    }

    private function GetUserRole(){
        $role='';
        $o365userId=null;
        if(Auth::user())
            $o365userId=Auth::user()->o365UserId;

        if(isset($_SESSION[SiteConstants::Session_O365_User_ID])){
            $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
        }

        if($o365userId)
            $role = (new UserRolesServices)->GetUserRole($o365userId);
        return $role;
    }

    private function GetFavoriteColor()
    {
        $color='';
        if(Auth::user()){
            $color = Auth::user()->favorite_color;
        }
        return $color;
    }
}