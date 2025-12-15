cd "D:\git\php\libuiBuilder\tools\formily-preview"
npm run build 2>&1 | Out-File -FilePath "build_output.txt" -Encoding UTF8
Get-Content "build_output.txt" | Select-Object -Last 50