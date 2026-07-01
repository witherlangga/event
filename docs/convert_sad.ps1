# PowerShell script to convert docs/SAD.md to DOCX and PDF using pandoc
# Requires: pandoc installed and available in PATH. For PDF, requires a PDF engine (wkhtmltopdf or LaTeX)

$md = "docs/SAD.md"
$docx = "docs/SAD.docx"
$pdf = "docs/SAD.pdf"

if (-not (Get-Command pandoc -ErrorAction SilentlyContinue)) {
    Write-Error "pandoc not found. Install pandoc: https://pandoc.org/installing.html"
    exit 1
}

# Convert to DOCX
pandoc $md -o $docx
if ($LASTEXITCODE -ne 0) { Write-Error "Failed to convert to DOCX"; exit 1 }
Write-Host "Created $docx"

# Convert to PDF (may require LaTeX)
pandoc $md -o $pdf
if ($LASTEXITCODE -ne 0) { Write-Warning "PDF conversion failed. Install LaTeX or use wkhtmltopdf." } else { Write-Host "Created $pdf" }
