from pathlib import Path
p=Path('d:/mobile_computing/event/frontend/lib/screens/hero_screen_3d.dart')
s=p.read_text()
cnt=0
line_no=1
for line in s.splitlines():
    for c in line:
        if c=='(':
            cnt+=1
        elif c==')':
            cnt-=1
        if cnt<0:
            print('NEGATIVE at line',line_no)
            raise SystemExit
    line_no+=1
print('FINAL',cnt)
# show the line around where open paren is max
cnt=0
maxcnt=0
maxline=1
line_no=1
for line in s.splitlines():
    for c in line:
        if c=='(':
            cnt+=1
        elif c==')':
            cnt-=1
    if cnt>maxcnt:
        maxcnt=cnt
        maxline=line_no
    line_no+=1
print('MAXCNT',maxcnt,'at line',maxline)
print('\n--- context around max line ---')
lines=s.splitlines()
start=max(0,maxline-5)
end=min(len(lines),maxline+5)
for i in range(start,end):
    print(f"{i+1}: {lines[i]}")
