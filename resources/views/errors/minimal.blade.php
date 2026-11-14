<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 32px 20px;
                background: #f8fafc;
                color: #1e293b;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                text-align: center;
            }
            main { width: 100%; max-width: 600px; }
            .code { margin: 0 0 12px; color: #475569; font-size: 20px; font-weight: 600; }
            h1 { margin: 0 0 16px; font-size: clamp(28px, 5vw, 44px); line-height: 1.2; }
            .description { margin: 0 auto 28px; max-width: 480px; color: #475569; line-height: 1.7; }
            .home-button {
                display: inline-block;
                max-width: 100%;
                padding: 14px 24px;
                border-radius: 6px;
                background: #1d4ed8;
                color: #fff;
                font-weight: 600;
                line-height: 1.5;
                text-decoration: none;
            }
            .home-button:hover { background: #1e40af; }
            .home-button:focus-visible { outline: 3px solid #0f172a; outline-offset: 4px; }
        </style>
    </head>
    <body>
        <main aria-labelledby="error-title">
            <p class="code">@yield('code')</p>
            <h1 id="error-title">@yield('message')</h1>
            <p class="description">@yield('description', __('You can return to the home page using the button below.'))</p>
            <a class="home-button" href="{{ url('/') }}">{{ __('Go to home page') }}</a>
        </main>
    </body>
</html>
