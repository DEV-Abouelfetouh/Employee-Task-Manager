<?php require 'db.php'; if(isset($_SESSION['logged_in'])) header("Location: ".$_SESSION['role'].".php"); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="theme-color" content="#007bff"><title>Task App</title><link rel="stylesheet" href="style.css"></head>
<body><div class="login-screen"><div class="login-box"><h1 class="login-title">Task Manager</h1><p id="errorMsg" class="error-msg"></p><form id="loginForm"><div class="form-group"><input type="text" id="mobile" name="mobile" required placeholder="Mobile Number"></div><div class="form-group"><input type="password" id="password" name="password" required placeholder="Password"></div><button type="submit" class="btn-primary">Login</button></form></div></div>
<script>
document.getElementById('loginForm').addEventListener('submit',function(e){
    e.preventDefault(); const f=new FormData(this); const m=document.getElementById('errorMsg');
    m.innerText='Verifying...'; m.style.color='#007bff';
    fetch('login_process.php',{method:'POST',body:f}).then(r=>r.text()).then(text=>{
        try { const d=JSON.parse(text); if(d.success){ m.style.color='#28a745'; m.innerText='Success!'; setTimeout(()=>window.location.href=d.role+'.php',500); } else { m.style.color='#dc3545'; m.innerText=d.message; } } 
        catch(e){ m.style.color='#dc3545'; m.innerText='Server Error: '+text; }
    }).catch(err=>{ m.style.color='#dc3545'; m.innerText='Network Error'; });
});
</script></body></html>
