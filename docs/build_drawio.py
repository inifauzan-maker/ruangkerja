from pathlib import Path
import xml.etree.ElementTree as ET
from xml.dom import minidom

OUT = Path(__file__).resolve().parent / "timmanager-documentation.drawio"
GREEN, AMBER, BLUE, GRAY, INK, WHITE = "153D36", "F2B84B", "2E74B5", "64748B", "0F172A", "FFFFFF"

class Diagram:
    def __init__(self):
        self.file = ET.Element("mxfile", {"host":"app.diagrams.net","modified":"2026-09-05T00:00:00.000Z","agent":"Codex","version":"24.7.17","type":"device"})
    def page(self, pid, name):
        d=ET.SubElement(self.file,"diagram",{"id":pid,"name":name})
        m=ET.SubElement(d,"mxGraphModel",{"dx":"1200","dy":"800","grid":"1","gridSize":"10","guides":"1","tooltips":"1","connect":"1","arrows":"1","fold":"1","page":"1","pageScale":"1","pageWidth":"1600","pageHeight":"1000","math":"0","shadow":"0"})
        r=ET.SubElement(m,"root"); ET.SubElement(r,"mxCell",{"id":"0"}); ET.SubElement(r,"mxCell",{"id":"1","parent":"0"}); return r
    def box(self,r,cid,label,x,y,w,h,fill=WHITE,stroke=GREEN,font=INK,bold=False,size=13):
        style=f"rounded=1;whiteSpace=wrap;html=1;fillColor=#{fill};strokeColor=#{stroke};fontColor=#{font};fontSize={size};fontStyle={1 if bold else 0};align=center;verticalAlign=middle;spacing=8;"
        c=ET.SubElement(r,"mxCell",{"id":cid,"value":label,"style":style,"vertex":"1","parent":"1"})
        g=ET.SubElement(c,"mxGeometry",{"x":str(x),"y":str(y),"width":str(w),"height":str(h)}); g.set("as","geometry")
    def edge(self,r,cid,s,t,label="",color=GRAY,dashed=False):
        style=f"edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#{color};endArrow=block;endFill=1;"+("dashed=1;" if dashed else "")
        c=ET.SubElement(r,"mxCell",{"id":cid,"value":label,"style":style,"edge":"1","parent":"1","source":s,"target":t})
        g=ET.SubElement(c,"mxGeometry",{"relative":"1"}); g.set("as","geometry")
    def title(self,r,cid,title,sub):
        self.box(r,cid,title,40,30,1520,55,GREEN,GREEN,WHITE,True,24)
        self.box(r,cid+"s",sub,40,90,1520,34,"EAF4F1","EAF4F1",GREEN,False,12)
    def save(self):
        raw=ET.tostring(self.file,encoding="utf-8")
        OUT.write_bytes(minidom.parseString(raw).toprettyxml(indent="  ",encoding="UTF-8"))

d=Diagram()

r=d.page("sitemap-v3","01 - Sitemap"); d.title(r,"t","SITEMAP RUANGKERJA","Navigasi publik, workspace, kolaborasi, integrasi, laporan, dan pusat superadmin")
nodes=[("visitor","Pengunjung",70,180),("login","Login",70,300),("register","Registrasi",70,390),("user","Pengguna Terautentikasi",380,180),("home","Beranda Tim & Proyek",360,300),("search","Pencarian Global",360,400),("reports","Dashboard Laporan",360,500),("profile","Profil & WhatsApp",360,600),("admin","Dashboard Superadmin",360,720),("team","Tim, Anggota & Undangan",700,300),("board","Papan Proyek",700,420),("kanban","Kanban & Filter",1040,230),("summary","Ringkasan",1040,330),("chat","Chat Grup",1040,430),("announce","Pengumuman",1040,530),("task","Detail Tugas",1040,650),("file","Preview & Versi File",1340,650)]
for cid,label,x,y in nodes:d.box(r,cid,label,x,y,220,62,"FFF7E6" if cid=="visitor" else "FFFFFF",AMBER if cid=="visitor" else GREEN,INK,cid=="user")
for i,(s,t) in enumerate([("visitor","login"),("visitor","register"),("login","user"),("user","home"),("user","search"),("user","reports"),("user","profile"),("user","admin"),("user","team"),("user","board"),("board","kanban"),("board","summary"),("board","chat"),("board","announce"),("board","task"),("task","file")]):d.edge(r,f"e{i}",s,t)

r=d.page("erd-core-v3","02 - ERD Core"); d.title(r,"t","ERD CORE","Identitas, akses global, tim, membership, proyek, list Kanban, dan tugas")
entities=[("users","USERS<br><b>PK id</b><br>name, email, password<br>global_role, is_active<br>profile & avatar",60,190),("teams","TEAMS<br><b>PK id</b><br><b>FK owner_id</b><br>name, slug, description",370,190),("members","TEAM_USER<br><b>FK team_id, user_id</b><br>role",220,510),("invites","TEAM_INVITATIONS<br>team_id, inviter_id<br>email, role, token_hash<br>expires_at, accepted_at",560,510),("boards","BOARDS<br><b>FK team_id</b><br>name, description<br>download_permission",700,190),("lists","BOARD_LISTS<br><b>FK board_id</b><br>title, color, position",1020,190),("tasks","TASKS<br>board_list_id, creator_id<br>title, description, priority<br>due_at, position",1300,190),("adminlogs","ADMIN_AUDIT_LOGS<br>actor_id, target_user_id<br>action, before, after<br>ip_address, user_agent",60,740)]
for cid,label,x,y in entities:d.box(r,cid,label,x,y,230,175,"FFFFFF",GREEN,INK,False,12)
for i,(s,t,l) in enumerate([("users","teams","owner"),("users","members","1:N"),("teams","members","1:N"),("teams","invites","1:N"),("users","invites","inviter"),("teams","boards","1:N"),("boards","lists","1:N"),("lists","tasks","1:N"),("users","tasks","creator"),("users","adminlogs","actor / target")]):d.edge(r,f"e{i}",s,t,l)

r=d.page("erd-collaboration-v3","03 - ERD Collaboration & File"); d.title(r,"t","ERD KOLABORASI FILE DAN NOTIFIKASI","Multi-assignee, checklist, komentar, activity, file berversi, Gmail, dan Fonnte")
items=[("task","TASKS",70,180,180,70),("tu","TASK_USER<br>task_id, user_id",320,160,220,95),("check","TASK_CHECKLIST_ITEMS<br>task_id, creator_id<br>title, is_completed<br>completed_at, position",320,310,250,140),("comment","TASK_COMMENTS<br>task_id, user_id, body",660,160,230,95),("mention","TASK_COMMENT_MENTIONS<br>comment_id, user_id",660,330,250,95),("activity","TASK_ACTIVITIES<br>task_id, actor_id<br>type, metadata JSON",1000,160,240,115),("attach","ATTACHMENTS<br>polymorphic attachable<br>root_attachment_id, version<br>is_current, scan_status",1000,350,270,145),("user","USERS",1330,180,180,70),("wa","WHATSAPP_CONNECTIONS<br>user_id, recipient_phone<br>preferences, consent<br>timezone, quiet hours",80,650,270,145),("walog","WHATSAPP_NOTIFICATION_LOGS<br>connection_id, task_id<br>event, status, schedule/error",450,650,290,140),("email","EMAIL_NOTIFICATION_LOGS<br>team_id, sender_id, recipient<br>event, status, send/error",850,650,290,140)]
for cid,label,x,y,w,h in items:d.box(r,cid,label,x,y,w,h,"FFFFFF",GREEN,INK,False,11)
for i,(s,t,l) in enumerate([("task","tu","1:N"),("task","check","1:N"),("task","comment","1:N"),("comment","mention","1:N"),("task","activity","1:N"),("task","attach","polymorphic"),("user","tu","assignee"),("user","mention","mentioned"),("user","activity","actor"),("user","attach","uploader"),("user","wa","1:1"),("wa","walog","1:N"),("task","walog","optional")]):d.edge(r,f"e{i}",s,t,l)
d.edge(r,"self","attach","attach","root/version",AMBER,True)

r=d.page("rbac-v3","04 - RBAC"); d.title(r,"t","ROLE BASED ACCESS CONTROL","Role global superadmin, role per tim, dan proteksi resource")
for cid,label,x,fill,font in [("owner","OWNER",80,GREEN,WHITE),("admin","ADMIN",380,AMBER,INK),("member","MEMBER",680,"E8EEF5",BLUE)]:d.box(r,cid,label,x,180,250,70,fill,fill,font,True,20)
d.box(r,"rules","ATURAN KEAMANAN<br><br>Board harus dapat diakses user<br>Task wajib berada pada board<br>Assignee/mention wajib anggota tim<br>File mengikuti download_permission<br>Resource lintas tim dikembalikan 404",80,340,820,350,"FFFDF5",AMBER,INK,False,14)
d.box(r,"global","SUPERADMIN GLOBAL<br><br>Akses /admin<br>Statistik seluruh platform<br>Filter dan kelola akun<br>Ubah global_role dan is_active<br>Audit setiap perubahan<br>Superadmin aktif terakhir dilindungi",80,730,820,190,"EEF2FF","6366F1",INK,False,14)
perms=[("p1","Kelola / hapus tim","owner"),("p2","Kelola anggota","owner,admin"),("p3","Promosikan admin","owner"),("p4","Kelola proyek","owner,admin"),("p5","Atur izin file","owner,admin"),("p6","Tugas, checklist, komentar","owner,admin,member"),("p7","Chat & pengumuman","owner,admin,member"),("p8","Lihat laporan","owner,admin,member")]
y=290
for cid,label,roles in perms:
 d.box(r,cid,label,1040,y,390,50,"FFFFFF","CBD5E1",INK,False,12)
 for role in roles.split(","):d.edge(r,cid+role,role,cid)
 y+=70

r=d.page("database-v2","05 - Database Catalog"); d.title(r,"t","KATALOG DATABASE","Tabel domain, kolaborasi, integrasi, dan infrastruktur Laravel")
groups=[("Identitas & Akses",["users","admin_audit_logs","teams","team_user","team_invitations"]),("Proyek & Tugas",["boards","board_lists","tasks","task_user"]),("Kolaborasi",["task_checklist_items","task_comments","task_comment_mentions","task_activities"]),("Konten & File",["board_messages","announcements","attachments"]),("Notifikasi",["whatsapp_connections","whatsapp_notification_logs","email_notification_logs"]),("Infrastruktur",["sessions","cache","cache_locks","jobs","job_batches","failed_jobs","password_reset_tokens"])]
coords=[(70,170),(570,170),(1070,170),(70,550),(570,550),(1070,550)]
for i,((title,names),(x,y)) in enumerate(zip(groups,coords)):
 d.box(r,f"g{i}","<b>"+title+"</b><br><br>"+"<br>".join("• "+n for n in names),x,y,400,290,["EAF4F1","FFF7E6","EEF2FF","FDF2F8","ECFDF5","F8FAFC"][i],[GREEN,AMBER,"6366F1","DB2777","059669",GRAY][i],INK,False,14)

r=d.page("task-flow-v2","06 - Task Flow"); d.title(r,"t","FLOWCHART TUGAS DAN KOLABORASI","Create/update task, assignee, checklist, komentar, mention, activity, dan notifikasi")
flow=[("start","Buka proyek",50,210),("form","Isi tugas, assignee,\ntenggat & file",300,200),("val","Validasi board, list,\nanggota & file",610,200),("ok","Valid?",920,210),("save","Transaksi task,\npivot, file, activity",1160,200),("error","Tampilkan error",920,390),("notify","Queue notifikasi",1320,390),("detail","Detail tugas",1160,570),("collab","Checklist • komentar\nmention • assignee",700,570),("log","Activity log",300,570)]
for cid,label,x,y in flow:d.box(r,cid,label,x,y,220,75,"FFFFFF",GREEN,INK,False,13)
for i,(s,t,l) in enumerate([("start","form",""),("form","val",""),("val","ok",""),("ok","save","Ya"),("ok","error","Tidak"),("save","notify",""),("save","detail",""),("detail","collab",""),("collab","log",""),("log","detail","refresh")]):d.edge(r,f"e{i}",s,t,l)

r=d.page("file-flow-v2","07 - File Flow"); d.title(r,"t","FLOWCHART FILE, PREVIEW, DAN VERSIONING","Validasi berlapis, antivirus opsional, storage privat, izin download, dan versi aktif")
flow=[("upload","Pilih file",50,190),("val","≤5 file • ≤10 MB\nMIME + extension",290,180),("av","Antivirus aktif?",570,190),("scan","clamscan",830,190),("clean","Bersih?",1070,190),("store","Private storage\n+ metadata",1320,180),("reject","Tolak file",1070,370),("access","Preview / download",1320,520),("perm","Membership + permission",990,520),("preview","Image/PDF/Text inline",650,500),("download","Download format lain",650,680),("version","Upload versi baru\nextension sama",300,520),("tx","N+1 dan pindah\nis_current",50,520)]
for cid,label,x,y in flow:d.box(r,cid,label,x,y,210,78,"FFFFFF",GREEN,INK,False,12)
for i,(s,t,l) in enumerate([("upload","val",""),("val","av","valid"),("av","scan","Ya"),("av","store","Tidak"),("scan","clean",""),("clean","store","Ya"),("clean","reject","Tidak"),("store","access",""),("access","perm",""),("perm","preview","previewable"),("perm","download","lainnya"),("version","tx",""),("tx","store","")]):d.edge(r,f"e{i}",s,t,l)

r=d.page("notifications-v3","08 - Notification & Reports"); d.title(r,"t","NOTIFIKASI DAN LAPORAN","Gmail SMTP, API Fonnte global, queue dan log, dashboard, PDF, dan XLSX")
blocks=[("events","EVENT<br>Tugas • Chat • Pengumuman<br>Reminder tenggat",60,190,280,140),("gmail","Gmail SMTP<br>Undangan tim",470,170,240,100),("email","Email Logs<br>sent / failed",470,350,240,90),("prefs","Nomor penerima<br>consent, preferensi<br>dan quiet hours",820,150,260,120),("queue","Queue job<br>idempotency & retry",820,340,260,110),("api","Fonnte Send API<br>FONNTE_API_KEY global",1200,330,260,100),("walog","WA Logs<br>pending/sent/failed/skipped",1200,530,270,100),("agg","Report Aggregator<br>status • workload • activity • logs",450,660,320,130),("dash","Dashboard<br>filter proyek & periode",850,680,280,90),("export","PDF + XLSX",1260,680,210,75)]
for cid,label,x,y,w,h in blocks:d.box(r,cid,label,x,y,w,h,"FFFFFF",GREEN,INK,False,13)
for i,(s,t,l) in enumerate([("events","prefs","WA"),("prefs","queue","eligible"),("queue","api","send"),("api","walog","result"),("gmail","email","result"),("events","agg","activity"),("email","agg",""),("walog","agg",""),("agg","dash",""),("dash","export","")]):d.edge(r,f"e{i}",s,t,l)

r=d.page("guide-v3","09 - User Guide"); d.title(r,"t","PANDUAN PENGGUNA","Langkah utama dari registrasi hingga ekspor laporan")
steps=[("1","Registrasi / Login","Buat akun dan masuk"),("2","Lengkapi Profil","Profil, avatar, password"),("3","Buat / Gabung Tim","Undangan Gmail dan role"),("4","Buat Proyek","Owner/Admin membuat papan"),("5","Buat Tugas","Assignee, tenggat, file"),("6","Berkolaborasi","Checklist, komentar, chat"),("7","Aktifkan WhatsApp","Nomor, consent, preferensi"),("8","Cari & Pantau","Search, filter, ringkasan"),("9","Buka Laporan","Workload, activity, PDF/XLSX")]
coords=[(70,180),(570,180),(1070,180),(70,400),(570,400),(1070,400),(70,620),(570,620),(1070,620)]
for (n,title,desc),(x,y) in zip(steps,coords):d.box(r,"u"+n,f"<b>{n}. {title}</b><br><br>{desc}",x,y,400,150,"FFFFFF",GREEN,INK,False,15)
for i in range(1,9):d.edge(r,"e"+str(i),"u"+str(i),"u"+str(i+1))

r=d.page("deployment-v3","10 - Deployment"); d.title(r,"t","DEPLOYMENT DAN OPERASI","PHP 8.4, .env, storage, queue, scheduler, Fonnte, dan quality gate")
blocks=[("browser","Browser",60,210,180,60),("web","Laravel Web<br>PHP 8.4 + Blade",340,190,260,100),("db","MySQL",740,160,220,70),("storage","Private Storage<br>public/storage link",740,290,220,80),("queue","Cron tiap menit<br>queue:work database<br>--stop-when-empty",340,470,260,100),("schedule","Cron tiap menit<br>schedule:run",340,640,260,80),("gmail","smtp.gmail.com<br>App Password",740,500,220,80),("meta","Fonnte Send API<br>API key global",740,650,220,80),("env","RAHASIA .ENV<br>APP_KEY • DB • Gmail<br>FONNTE_API_KEY<br>ClamAV config",1100,180,360,190),("quality","QUALITY GATE<br>migrate:status<br>107 tests / 425 assertions<br>view:cache • npm run build<br>Pint",1100,520,360,180)]
for cid,label,x,y,w,h in blocks:d.box(r,cid,label,x,y,w,h,"FFFFFF",GREEN,INK,False,14)
for i,(s,t,l) in enumerate([("browser","web","HTTPS"),("web","db","Eloquent"),("web","storage","authorized I/O"),("web","queue","dispatch"),("schedule","queue","enqueue"),("queue","gmail","SMTP"),("queue","meta","HTTPS"),("env","web","config"),("env","queue","config")]):d.edge(r,f"e{i}",s,t,l)

r=d.page("admin-guide-v1","11 - Superadmin Guide"); d.title(r,"t","PANDUAN SUPERADMIN","Aktivasi akun, dashboard responsif, pengelolaan user, dan audit akses")
steps=[("a1","1. Promosikan akun","users:promote-superadmin<br>email --force",70,180),("a2","2. Login ulang","Masuk dengan akun aktif<br>dan buka /admin",390,180),("a3","3. Gunakan sidebar","Collapse pada desktop<br>drawer pada mobile",710,180),("a4","4. Pantau statistik","User aktif, superadmin,<br>tim, proyek, tugas",1030,180),("a5","5. Filter pengguna","Nama/email, role,<br>dan status akun",70,480),("a6","6. Ubah akses","Pilih global_role<br>dan status aktif",390,480),("a7","7. Simpan perubahan","Validasi melindungi<br>superadmin aktif terakhir",710,480),("a8","8. Periksa audit","Actor, target, before/after,<br>IP dan waktu",1030,480)]
for cid,label,desc,x,y in steps:d.box(r,cid,f"<b>{label}</b><br><br>{desc}",x,y,260,145,"FFFFFF",GREEN,INK,False,14)
for i in range(1,8):d.edge(r,"ae"+str(i),"a"+str(i),"a"+str(i+1))
d.box(r,"adminnote","Catatan: akun nonaktif tidak dapat login. Perubahan role dan status dicatat. Jangan menurunkan atau menonaktifkan satu-satunya superadmin aktif.",230,750,1120,100,"FFF7E6",AMBER,INK,True,13)

d.save()
print(OUT)
