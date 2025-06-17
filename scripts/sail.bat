@echo off
setlocal

echo 🛠️  Initializing Laravel Sail environment...

:: 1. Copy .env if missing
IF NOT EXIST .env (
    IF EXIST .env.example (
        echo 📄 Copying .env from .env.example...
        copy .env.example .env > nul

        echo ⚙️  Adjusting .env for Sail...
        powershell -ExecutionPolicy Bypass -File scripts\patch-env-for-sail.ps1
    ) ELSE (
        echo ❌ .env.example not found. Aborting.
        exit /b 1
    )
)

:: 2. Composer install inside Docker
IF NOT EXIST vendor\bin\sail (
    echo 📦 Installing PHP dependencies via Docker...
    docker run --rm -u %USERNAME% ^
        -v %cd%:/var/www/html ^
        -w /var/www/html ^
        laravelsail/php84-composer:latest ^
        composer install
) ELSE (
    echo ✅ vendor\ already present.
)

:: 3. Start Sail
echo 🐳 Starting Sail containers...
vendor\bin\sail up -d

:: 4. Laravel key
echo 🔑 Generating app key...
vendor\bin\sail artisan key:generate

echo 🧪 Running migrations...
vendor\bin\sail artisan migrate

:: 5. Optional seeding
set /p SEED=🌱 Run database seeders? [y/N]:
IF /I "%SEED%"=="Y" (
    vendor\bin\sail artisan db:seed
)

:: 6. Frontend
IF EXIST package.json (
    echo 📦 Installing frontend dependencies...
    vendor\bin\sail npm install

    echo 🚧 Building frontend assets...
    vendor\bin\sail composer run dev
) ELSE (
    echo 📦 No package.json found. Skipping frontend setup.
)

echo ✅ Laravel Sail is ready! Visit: http://localhost
