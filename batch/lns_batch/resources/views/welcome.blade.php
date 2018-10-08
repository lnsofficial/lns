<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-image: url("{{ asset('img/otukaree.png') }}");
                background-size:contain;
                background-position: center;
                background-repeat: no-repeat;
                background-color: #fff;
/*                color: #636b6f; */
                color: #0012ff;
                font-family: 'Raleway', sans-serif;
/*                font-weight: 100; */
                font-weight: 900;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }


            /*
            text-shadow forked from: https://codepen.io/teetteet/details/dFICw#forkline
            */
            .superShadow {
              text-shadow: 0 1px 0 hsl(174,5%,80%),
                           0 2px 0 hsl(174,5%,75%),
                           0 3px 0 hsl(174,5%,70%),
                           0 4px 0 hsl(174,5%,66%),
                           0 5px 0 hsl(174,5%,64%),
                           0 6px 0 hsl(174,5%,62%),
                           0 7px 0 hsl(174,5%,61%),
                           0 8px 0 hsl(174,5%,60%),

                           0 0    5px  rgba(0,0,0,.05),
                           0 1px  3px  rgba(0,0,0,.2),
                           0 3px  5px  rgba(0,0,0,.2),
                           0 5px  10px rgba(0,0,0,.2),
                           0 10px 10px rgba(0,0,0,.2),
                           0 20px 20px rgba(0,0,0,.3);
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    {{ config('app.name', 'Laravel') }}
                </div>
{{--
                <div class="links">
                    <a href="https://laravel.com/docs">Documentation</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
--}}
            </div>
        </div>

        <script src="{{ asset('js/TweenMax.min.js') }}"></script>
        <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
        <script>
            $(".title").each(function(index, element){
              var animation = TweenMax.to(this, 0.2, {
                className: '+= superShadow',
                marginTop: '-10px',
                marginBottom: '10px',
                ease: Power1.easeIn,
                paused:true
              });
              element.animation = animation;
            })

            $('.title').hover(function(){
             this.animation.play()
            }, function(){
             this.animation.reverse();
            })
        </script>
    </body>
</html>
