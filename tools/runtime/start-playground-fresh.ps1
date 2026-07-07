param(
	[string]$ProjectRoot = (Get-Location).Path,
	[int]$Port = 9401
)

$nodeRoot = 'C:\CodexToolchain\node-v22.23.1-win-x64'
$env:PATH = "$nodeRoot;$env:PATH"

& "$nodeRoot\npx.cmd" -y @wp-playground/cli@latest start --skip-browser --reset --no-auto-mount --port $Port --path $ProjectRoot
