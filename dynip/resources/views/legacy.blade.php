<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ config('app.name', 'Laravel') }}</title>
    </head>
    <body style="background-color:white">
        @if ($message)
        <h1>{{ $message }}</h1>
        @endif
        @if (!empty($errors))
        @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
        @endif
    </body>
</html>
