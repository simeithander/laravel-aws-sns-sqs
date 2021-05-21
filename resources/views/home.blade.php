@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Send Mail') }}</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @isset($notification)
                            Mensagem: {{ $notification }}
                            <hr />
                        @endisset
                        @isset(Session::get('message')->status)
                            <h4>Email sent!!!</h4>
                            Title: {{ Session::get('message')->title ?? '' }}<br />
                            Message: {{ Session::get('message')->message ?? '' }}
                            <hr />
                        @endisset
                        <form action={{ url('send-mail') }} method="post">
                            @csrf
                            <input type="text" name="title" placeholder="Title Message"><br />
                            <textarea name="message" cols="30" rows="10" placeholder="Message"></textarea>
                            <br />
                            <button type="submit">Submit Email</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
