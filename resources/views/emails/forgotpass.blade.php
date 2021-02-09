@extends('beautymail::templates.ark')

@section('content')

    @include('beautymail::templates.ark.contentStart')
        <h3>Password Anda Telah Diubah, berikut adalah password anda yang baru!</h3>
        <h4 class="secondary"><strong>Password Baru : {{ $newPass }}</strong></h4>

    @include('beautymail::templates.ark.contentEnd')

@stop
