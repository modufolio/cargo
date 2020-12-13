@extends('beautymail::templates.ark')

@section('content')

    @include('beautymail::templates.ark.contentStart')
        <h3>Selamat bergabung, untuk menggunakan fitur, silahkan verifikasi email anda!</h3>
        <h4 class="secondary"><strong>Halo {{ $user->name }}</strong></h4>
        <p>Klik tombol dibawah ini untuk verifikasi email enda</p>
        <a style="color: #ffffff;font-weight: 600;
            padding: 10px 15px;background-color: #9e1515;
            text-decoration: none;text-align: center;
            position: relative;margin-left: auto;
            border-radius: 20px;margin-right: auto;"
        href="{{url('user/verify', $user->verifyUser->token)}}">Verifikasi Email</a>

    @include('beautymail::templates.ark.contentEnd')

@stop
