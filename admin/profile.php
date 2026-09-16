<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
$db=admin_db();$user=admin_require_auth();
if($_SERVER['REQUEST_METHOD']==='POST'){
    admin_verify_csrf();$action=(string)($_POST['action']??'profile');
    if($action==='profile'){
        $username=trim((string)($_POST['username']??''));$email=strtolower(trim((string)($_POST['email']??'')));$phone=trim((string)($_POST['recovery_phone']??''));
        if(!preg_match('/^[a-zA-Z0-9._-]{3,40}$/',$username)||!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^\+[1-9]\d{7,14}$/',$phone)){admin_flash('error','Check the username, email, and E.164 recovery phone format.');admin_redirect('profile.php');}
        try{$db->prepare('UPDATE admin_users SET username=?,email=?,recovery_phone=? WHERE id=?')->execute([$username,$email,$phone,(int)$user['id']]);admin_flash('success','Account details updated.');}catch(PDOException){admin_flash('error','That username or email is already in use.');}admin_redirect('profile.php');
    }
    if($action==='password'){
        $stmt=$db->prepare('SELECT password_hash FROM admin_users WHERE id=?');$stmt->execute([(int)$user['id']]);$hash=(string)$stmt->fetchColumn();$new=(string)($_POST['new_password']??'');
        if(!password_verify((string)($_POST['current_password']??''),$hash)){admin_flash('error','Current password is incorrect.');}elseif(strlen($new)<12){admin_flash('error','The new password must be at least 12 characters.');}elseif(!hash_equals($new,(string)($_POST['password_confirmation']??''))){admin_flash('error','The new passwords do not match.');}else{$db->prepare('UPDATE admin_users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),(int)$user['id']]);admin_flash('success','Password changed.');}admin_redirect('profile.php');
    }
}
admin_header('Account','profile');
?><div class="grid-2"><section class="panel" style="margin-top:0"><div class="panel-head"><h2>Owner profile</h2></div><form method="post"><input type="hidden" name="csrf_token" value="<?=admin_csrf()?>"><input type="hidden" name="action" value="profile"><div class="field"><label>Username</label><input name="username" required value="<?=admin_e((string)$user['username'])?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?=admin_e((string)$user['email'])?>"></div><div class="field"><label>OTP recovery phone</label><input name="recovery_phone" required value="<?=admin_e((string)$user['recovery_phone'])?>"><small>Use E.164 format, for example +9779845895222.</small></div><div class="actions"><button class="button acid">Save account</button></div></form></section><section class="panel" style="margin-top:0"><div class="panel-head"><h2>Change password</h2></div><form method="post"><input type="hidden" name="csrf_token" value="<?=admin_csrf()?>"><input type="hidden" name="action" value="password"><div class="field"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div><div class="field"><label>New password</label><input type="password" name="new_password" required minlength="12" autocomplete="new-password"></div><div class="field"><label>Confirm new password</label><input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></div><div class="actions"><button class="button acid">Change password</button></div></form></section></div><?php admin_footer();?>
