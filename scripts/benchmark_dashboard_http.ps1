param(
    [string]$BaseUrl = 'http://localhost:8000',
    [int]$MeasuredRuns = 5,
    [int]$WarmupRuns = 1
)

$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Net.Http

$cookieContainer = [System.Net.CookieContainer]::new()
$handler = [System.Net.Http.HttpClientHandler]::new()
$handler.AllowAutoRedirect = $true
$handler.CookieContainer = $cookieContainer
$client = [System.Net.Http.HttpClient]::new($handler)
$client.Timeout = [TimeSpan]::FromMinutes(2)

try {
    $landingResponse = $client.GetAsync("$BaseUrl/").GetAwaiter().GetResult()
    $landingHtml = $landingResponse.Content.ReadAsStringAsync().GetAwaiter().GetResult()
    $csrfMatch = [regex]::Match($landingHtml, 'name="_token"\s+value="([^"]+)"')

    if (-not $csrfMatch.Success) {
        throw 'Could not locate the login CSRF token.'
    }

    $seederPath = Join-Path $PSScriptRoot '..\database\seeders\UsersTableSeeder.php'
    $seeder = Get-Content -Raw -LiteralPath $seederPath
    $credentialMatch = [regex]::Match(
        $seeder,
        "'username'\s*=>\s*'super-admin'.{0,500}?'password'\s*=>\s*bcrypt\('([^']+)'\)",
        [System.Text.RegularExpressions.RegexOptions]::Singleline
    )

    if (-not $credentialMatch.Success) {
        throw 'Could not locate the local seeded Municipality Super Admin credential.'
    }

    $loginFields = [System.Collections.Generic.Dictionary[string,string]]::new()
    $loginFields.Add('_token', $csrfMatch.Groups[1].Value)
    $loginFields.Add('username', 'super-admin')
    $loginFields.Add('password', $credentialMatch.Groups[1].Value)
    $loginContent = [System.Net.Http.FormUrlEncodedContent]::new($loginFields)

    $loginResponse = $client.PostAsync("$BaseUrl/login", $loginContent).GetAwaiter().GetResult()

    if (-not $loginResponse.IsSuccessStatusCode) {
        throw "Login failed with HTTP status $([int]$loginResponse.StatusCode)."
    }

    $runs = @()

    for ($index = 0; $index -lt ($WarmupRuns + $MeasuredRuns); $index++) {
        $shellStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        $response = $client.GetAsync(
            "$BaseUrl/dashboard",
            [System.Net.Http.HttpCompletionOption]::ResponseHeadersRead
        ).GetAwaiter().GetResult()
        $shellHeadersMs = $shellStopwatch.Elapsed.TotalMilliseconds
        $shellBody = $response.Content.ReadAsByteArrayAsync().GetAwaiter().GetResult()
        $shellTotalMs = $shellStopwatch.Elapsed.TotalMilliseconds
        $shellStopwatch.Stop()

        if (-not $response.IsSuccessStatusCode) {
            throw "Dashboard request failed with HTTP status $([int]$response.StatusCode)."
        }

        $contentRequest = [System.Net.Http.HttpRequestMessage]::new(
            [System.Net.Http.HttpMethod]::Get,
            "$BaseUrl/dashboard/content"
        )
        $contentRequest.Headers.Accept.ParseAdd('application/json')
        $contentRequest.Headers.Add('X-Requested-With', 'XMLHttpRequest')
        $contentStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        $contentResponse = $client.SendAsync(
            $contentRequest,
            [System.Net.Http.HttpCompletionOption]::ResponseHeadersRead
        ).GetAwaiter().GetResult()
        $contentHeadersMs = $contentStopwatch.Elapsed.TotalMilliseconds
        $contentBody = $contentResponse.Content.ReadAsByteArrayAsync().GetAwaiter().GetResult()
        $contentTotalMs = $contentStopwatch.Elapsed.TotalMilliseconds
        $contentStopwatch.Stop()
        $contentRequest.Dispose()

        if (-not $contentResponse.IsSuccessStatusCode) {
            throw "Dashboard content request failed with HTTP status $([int]$contentResponse.StatusCode)."
        }

        if ($index -ge $WarmupRuns) {
            $runs += [PSCustomObject]@{
                run = $index - $WarmupRuns + 1
                shell_ttfb_ms = [Math]::Round($shellHeadersMs, 3)
                shell_complete_ms = [Math]::Round($shellTotalMs, 3)
                content_ttfb_ms = [Math]::Round($contentHeadersMs, 3)
                content_complete_ms = [Math]::Round($contentTotalMs, 3)
                dashboard_complete_ms = [Math]::Round($shellTotalMs + $contentTotalMs, 3)
                status = [int]$response.StatusCode
                shell_response_bytes = $shellBody.Length
                content_response_bytes = $contentBody.Length
            }
        }
    }

    $shellTtfb = @($runs.shell_ttfb_ms | Sort-Object)
    $shellComplete = @($runs.shell_complete_ms | Sort-Object)
    $contentComplete = @($runs.content_complete_ms | Sort-Object)
    $dashboardComplete = @($runs.dashboard_complete_ms | Sort-Object)
    $middle = [Math]::Floor($MeasuredRuns / 2)

    [PSCustomObject]@{
        captured_at = [DateTimeOffset]::Now.ToString('o')
        base_url = $BaseUrl
        measured_runs = $MeasuredRuns
        conditions = [PSCustomObject]@{
            role = 'Municipality - Super Admin'
            year = $null
            filters = @()
            database_buffers = 'warm after login and one discarded warm-up request'
            browser_cache = 'not applicable to HTTP-only measurement'
        }
        summary = [PSCustomObject]@{
            shell_ttfb_ms_median = $shellTtfb[$middle]
            shell_complete_ms_median = $shellComplete[$middle]
            content_complete_ms_median = $contentComplete[$middle]
            dashboard_complete_ms_min = $dashboardComplete[0]
            dashboard_complete_ms_median = $dashboardComplete[$middle]
            dashboard_complete_ms_max = $dashboardComplete[-1]
        }
        runs = $runs
    } | ConvertTo-Json -Depth 6
}
finally {
    $client.Dispose()
    $handler.Dispose()
}
