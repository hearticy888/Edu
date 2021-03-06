@extends('layouts.app')
@section('content')
    <h2>Link Office 365 & Local Account</h2>
    @if ($areAccountsLinked)
        accounts are linked
    @else
        <p>This page will enable you to link your Office 365 &amp; Local Account together to successfully use the demo application.</p>
        <hr>
        <div class="form-horizontal">
            <p>
                @if($isLocalUserExists)
                    <a class="btn btn-primary" disabled="disabled" href="javascript:void(0)">Continue with new Local Account</a>
                @else
                    <a class="btn btn-primary" href="{{url("/link/createlocalaccount")}}">Continue with new Local Account</a>
                @endif
                             &nbsp; &nbsp;

                <a class="btn btn-primary" href="{{url("/link/loginlocal")}}">Link with existing Local Account</a> &nbsp; &nbsp;
            </p>
        </div>
    @endif
@endsection