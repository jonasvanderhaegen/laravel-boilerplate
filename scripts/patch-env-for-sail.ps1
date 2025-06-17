# scripts/patch-env-for-sail.ps1
# Non-destructive .env patcher for Laravel Sail (Windows PowerShell)

Write-Host "⚙️  Non-destructive patching of .env for Laravel Sail..." -ForegroundColor Cyan

$envPath = ".env"
if (-not (Test-Path $envPath)) {
    Write-Error ".env file not found. Aborting."
    exit 1
}

# Define patch rules: KEY | ExpectedOldValue | NewValue
$patches = @(
    @{ Key = "APP_URL";             Old = "";              New = "http://localhost" }
    @{ Key = "DB_CONNECTION";       Old = "sqlite";        New = "pgsql" }
    @{ Key = "DB_HOST";             Old = "127.0.0.1";     New = "pgsql" }
    @{ Key = "DB_PORT";             Old = "3306";          New = "5432" }
    @{ Key = "DB_DATABASE";         Old = "";              New = "laravel" }
    @{ Key = "DB_USERNAME";         Old = "root";          New = "sail" }
    @{ Key = "DB_PASSWORD";         Old = "";              New = "password" }
    @{ Key = "SESSION_DRIVER";      Old = "database";      New = "redis" }
    @{ Key = "QUEUE_CONNECTION";    Old = "database";      New = "redis" }
    @{ Key = "CACHE_STORE";         Old = "database";      New = "redis" }
    @{ Key = "REDIS_HOST";          Old = "127.0.0.1";     New = "valkey" }
    @{ Key = "MAIL_MAILER";         Old = "log";           New = "smtp" }
    @{ Key = "MAIL_HOST";           Old = "127.0.0.1";     New = "mailpit" }
    @{ Key = "MAIL_PORT";           Old = "2525";          New = "1025" }
)

$content = Get-Content $envPath
$modified = $false

foreach ($patch in $patches) {
    $key = $patch.Key
    $old = $patch.Old
    $new = $patch.New
    $matchPattern = "^\s*${key}=(.*)"

    $matchedLine = $content | Where-Object { $_ -match $matchPattern }

    if ($matchedLine) {
        $currentValue = ($matchedLine -replace $matchPattern, '$1').Trim()

        if ($currentValue -eq $old -or $old -eq "") {
            Write-Host "🔁 Updating $key ($currentValue → $new)"
            $content = $content -replace $matchPattern, "$key=$new"
            $modified = $true
        } else {
            Write-Host "⏭  Skipping $key (custom value: $currentValue)"
        }
    } else {
        Write-Host "➕ Adding $key=$new"
        $content += "$key=$new"
        $modified = $true
    }
}

if ($modified) {
    $content | Set-Content $envPath -Encoding UTF8
    Write-Host "✅ .env patched for Sail." -ForegroundColor Green
} else {
    Write-Host "✅ No changes needed. .env already Sail-compatible." -ForegroundColor Green
}
