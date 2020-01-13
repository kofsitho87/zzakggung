<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vue SPA Demo</title>
</head>
<body>
    <div id="app"></div>
    <script src="{{ (env('APP_ENV') === 'local') ? mix('js/app.js') : asset('js/app.js') }}"></script>
    <!-- <script src="http://localhost:8080/js/app.js"></script> -->
</body>
</html>