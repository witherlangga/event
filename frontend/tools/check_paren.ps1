$cnt=0
$line=1
$maxcnt=0
$maxline=0
Get-Content 'd:/mobile_computing/event/frontend/lib/screens/hero_screen_3d.dart' | ForEach-Object {
  $l = $_
  foreach($c in $l.ToCharArray()) {
    if ($c -eq '(') { $cnt++ }
    elseif ($c -eq ')') { $cnt-- }
    if ($cnt -lt 0) { Write-Output "NEGATIVE at line $line"; exit 1 }
  }
  if ($cnt -gt $maxcnt) { $maxcnt=$cnt; $maxline=$line }
  $line++
}
Write-Output "FINAL $cnt"
Write-Output "MAXCNT $maxcnt at line $maxline"
Write-Output "--- context around max line ---"
$start=[math]::Max(1,$maxline-5)
$end=$maxline+5
Get-Content 'd:/mobile_computing/event/frontend/lib/screens/hero_screen_3d.dart' | Select-Object -Index ($start-1)..($end-1) | ForEach-Object { $i = $start; Write-Output "$i: $_"; $start++ }
