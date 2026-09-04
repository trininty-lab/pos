$ErrorActionPreference = 'Stop'

$composeFiles = @('-f', 'compose.yaml', '-f', 'compose.prod.yaml')
$applicationServices = @('app', 'web', 'queue', 'scheduler')
$applicationImages = @('point-of-sales:local', 'point-of-sales-web:local')

Write-Host 'Removing application containers only...'
docker compose @composeFiles rm --stop --force $applicationServices

Write-Host 'Removing application image tags only...'
foreach ($image in $applicationImages) {
    docker image rm $image 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Image tag not found: $image"
    }
}

Write-Host 'Building the PHP and Nginx application images...'
docker compose @composeFiles build --no-cache app web

Write-Host 'Starting the application services...'
docker compose @composeFiles up -d app web queue scheduler

Write-Host 'Application rebuild complete. Database, Redis, WhatsApp images, and volumes were preserved.'
