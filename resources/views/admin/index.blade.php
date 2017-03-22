@extends('layouts.app')
@section('content')
    @if(session('msg') || $msg)
        <div class="message-container bg-danger"> <p>{{session('msg') }} <?php echo $msg; ?></p>  </div>
    @endif
    <h2>Admin</h2>
    @if (!$IsAdminConsented)
    <div>
        <h3>Admin Consent</h3>
        <hr />

        <p>To use this application in this tenancy you must first provide Admin Consent. </p>
        <p>Please click the button below to proceed.</p>

        <p class="form-group">
            <form method="post" action="{{url('/admin/adminconsent')}}">
            {{csrf_field()}}
            <input type="submit" value="Consent" class="btn btn-primary" />
        </form>
        </p>

    </div>
    @else
       <p>Admin Consent has been applied.</p>
    @endif
@endsection