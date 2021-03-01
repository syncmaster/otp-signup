@extends('layouts.index')
@section('page_content')
<div class="container-fluid">
    <div class="row">
        <div class="sidebar d-none d-md-block col-xs-12 col-sm-12 col-md-6"></div>
        <div class="sidebar col-xs-12 col-sm-12 col-md-6">
            <div class="content ">
                <div class="header">
                    <h1>Let's Get Started</h1>
                    <p>Sign up and claim free credits</p>
                </div>
                <div class="authentication">
                    <form action="{{route('auth.register')}}" method="POST" class="auth">
                        @csrf
                        <div class="form-group">
                          <input type="text" value="" name="email" class="form-control" id="email" placeholder="Enter email">
                          <small id="emailError" class="form-text text-danger"></small>
                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-control" name="phone" id="phoneNumber" placeholder="">
                            <input type="hidden" id="country" name="country" value=""/>
                            <small id="phoneError" class="form-text text-danger"></small>
                          </div>
                        <div class="form-group">
                          <input type="password" name="password"  class="form-control" id="passoword" placeholder="Password">
                          <small id="passwordError" class="form-text text-danger"></small>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </form>
                      <div class="alert alert-danger alert-dismissible error-message d-none" role="alert">
                        <p></p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="verifyNumber modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Verify Phone Number</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{route('auth.verify.number')}}" class="confirmPhone">
          @csrf
          <input type="hidden" name="user_id" id="userId">
          <div class="form-group">
            <input type="text" class="form-control" name="code" placeholder="Enter your Code"/>
            <small id="codeError" class="form-text text-danger"></small>
          </div>
          <button type="submit" class="btn btn-primary verify">Submit</button>
        </form>
        <div class="alert alert-danger alert-dismissible d-none alert-message" role="alert">
          <p></p>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="alert alert-success alert-dismissible d-none alert-message" role="alert">
          <p></p>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
      <div class="modal-footer">
        <button  class="resend-code btn btn-warning" data-route="{{route('auth.generate.code')}}">Resend Code</button>
      </div>
    </div>
  </div>
</div>
@endsection
