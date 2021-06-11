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
                        <p>Register emails</p>
                        <form action={{ url('add-emails') }} method="post">
                            @csrf
                            <input type="text" name="list_mail" placeholder="mail1@example.com, mail2@example.com"
                                style="width: 100%"><button type="submit">Submit Emails</button>
                            {{ Session::get('addEmailConfirm')->emailConfirm ?? '' }}
                            <br /><br />
                        </form>
                        @isset(Session::get('message')->status)
                            <h4>Email sent!!!</h4>
                            Title: {{ Session::get('message')->title ?? '' }}<br />
                            Message: {{ Session::get('message')->message ?? '' }}
                            <hr />
                        @endisset
                        <p>Write Email</p>
                        <form action={{ url('post-mail') }} method="POST" enctype="multipart/form-data">
                            @csrf
                            <input style="width: 50%" type="text" name="from" placeholder="From"><br />
                            <input style="width: 50%" type="text" name="to" placeholder="To"><br />
                            <input type="text" name="title" placeholder="Title"><br />
                            <textarea name="message" cols="30" rows="10" placeholder="Message"></textarea>
                            <input type="file" name="file" class="form-control">
                            <br />
                            <button type="submit">Submit Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
