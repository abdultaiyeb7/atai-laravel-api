# ✅ Laravel API Path
$projectPath = "D:\abdul doc\coding\laravel_php\atai-laravel-api-main"
$tempPath = "$env:TEMP\laravel-api-temp"
$zipPath = "$projectPath\laravel-api.zip"

# 🔄 Clean previous temp and zip files
if (Test-Path $tempPath) {
    Remove-Item -Path $tempPath -Recurse -Force -ErrorAction SilentlyContinue
}
if (Test-Path $zipPath) {
    Remove-Item -Path $zipPath -Force -ErrorAction SilentlyContinue
}

# 📁 Create temp folder
New-Item -ItemType Directory -Force -Path $tempPath | Out-Null

# 📂 Copy everything except vendor, node_modules, and existing zip
Get-ChildItem -Path $projectPath -Force | Where-Object {
    $_.Name -ne "vendor" -and $_.Name -ne "node_modules" -and $_.Name -ne "laravel-api.zip"
} | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $tempPath -Recurse -Force
}

# 📦 Create zip archive
Compress-Archive -Path "$tempPath\*" -DestinationPath $zipPath

# 🧹 Clean temp
Remove-Item -Path $tempPath -Recurse -Force -ErrorAction SilentlyContinue

# ✅ Done
Write-Host "`n✅ Laravel project zipped successfully at:"
Write-Host "$zipPath"
