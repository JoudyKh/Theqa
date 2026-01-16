<!DOCTYPE html>
<html lang="{{\Illuminate\Support\Facades\App::getLocale()}}">
<head>
    <title>{{env('APP_NAME')}}</title>
</head>
<body>
<h1>{{ $details['title'] }}</h1>
<p>{{ $details['body'] }}</p>

<p>Your code is valid only for 15 minutes from now . Thank you</p>
</body>
</html>
