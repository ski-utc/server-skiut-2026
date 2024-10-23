<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Page Title')</title>
    @vite('resources/css/app.css')
</head>
<body>    
    <!--@vite('resources/js/app.js')-->
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
