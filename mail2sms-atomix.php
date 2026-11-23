<?php
/**
 * Plugin Name: Mail → SMS (Atomix)
 * Description: WordPress’in gönderdiği e-postaların içinden telefon numarasını ayıklar ve Netgsm ile SMS gönderir.
 * Version: 1.0.1
 * Author: Atakan Özdal (Atomix)
 */

if (!defined('ABSPATH')) exit;

define('M2S_OPT', 'atomix_m2s');
define('M2S_API', 'https://api.netgsm.com.tr/sms/rest/v2/send');

function m2s_def(){ return ['usercode'=>'','password'=>'','msgheader'=>'','encoding'=>'TR','log'=>1]; }
function m2s_get(){ $o=get_option(M2S_OPT,[]); if(!is_array($o)) $o=[]; return array_merge(m2s_def(),$o); }
function m2s_upd($n){ $m=array_merge(m2s_get(),$n); update_option(M2S_OPT,$m); return $m; }
function m2s_encodings(){ return ['TR','TURKCE','UTF-8']; }

add_action('admin_menu', function(){
  add_options_page('Mail → SMS','Mail → SMS','manage_options','mail2sms-atomix', function(){
    if(!current_user_can('manage_options')) return; $s=m2s_get(); $ok=null;
    if(isset($_POST['save']) && check_admin_referer('mail2sms_atomix_save')){
      $usercode=sanitize_text_field(wp_unslash($_POST['usercode']??''));
      $password=trim((string)wp_unslash($_POST['password']??''));
      $msgheader=sanitize_text_field(wp_unslash($_POST['msgheader']??''));
      $encoding=sanitize_text_field(wp_unslash($_POST['encoding']??'TR'));
      if(!in_array($encoding,m2s_encodings(),true)) $encoding='TR';
      $log=isset($_POST['log'])?1:0;
      $new=compact('usercode','msgheader','encoding','log');
      if($password!=='') $new['password']=$password; // boş gelirse kayıtlı şifre korunur
      $s=m2s_upd($new); $ok='Ayarlar kaydedildi.';
    }
    ?>
    <div class="wrap"><h1>Mail → SMS (Atomix)</h1>
      <?php if($ok): ?><div class="updated"><p><?php echo esc_html($ok);?></p></div><?php endif;?>
      <form method="post"><?php wp_nonce_field('mail2sms_atomix_save');?>
        <table class="form-table">
          <tr><th>Usercode</th><td><input name="usercode" class="regular-text" value="<?php echo esc_attr($s['usercode']);?>"></td></tr>
          <tr><th>Password</th><td><input type="password" name="password" class="regular-text" autocomplete="new-password" value="" placeholder="<?php echo $s['password']!=='' ? esc_attr('(kayıtlı)') : '';?>"></td></tr>
          <tr><th>Msgheader</th><td><input name="msgheader" class="regular-text" value="<?php echo esc_attr($s['msgheader']);?>"></td></tr>
          <tr><th>Encoding</th><td>
            <select name="encoding">
              <option value="TR" <?php selected($s['encoding'],'TR');?>>TR</option>
              <option value="TURKCE" <?php selected($s['encoding'],'TURKCE');?>>TURKCE</option>
              <option value="UTF-8" <?php selected($s['encoding'],'UTF-8');?>>UTF-8</option>
            </select>
          </td></tr>
          <tr><th>Debug log</th><td><label><input type="checkbox" name="log" <?php checked($s['log'],1);?>> Açık</label> <span class="description">(yalnızca WP_DEBUG açıkken yazar)</span></td></tr>
        </table>
        <p><button class="button button-primary" name="save" value="1">Kaydet</button></p>
      </form>
      <p><strong>İpucu:</strong> Eğer e-postada telefon görünmüyorsa şablona şu satırı ekleyin: <code>&lt;!-- PHONE: {customer_phone} --&gt;</code></p>
    </div>
    <?php
  });
});

function m2s_norm($p){ $d=preg_replace('/\D+/','',(string)$p); if(strlen($d)>2 && substr($d,0,2)==='00') $d=substr($d,2); if(strlen($d)==13 && substr($d,0,3)==='900') $d='90'.substr($d,3); if(strlen($d)==11 && $d[0]==='0') $d='90'.substr($d,1); if(strlen($d)==10) $d='90'.$d; return $d; }

/** Metinde Türkiye cep numarası arar, bulursa 905XXXXXXXXX döner, bulamazsa ''. */
function m2s_find_phone($text){
  $sep='[ \t.\-]{0,3}';
  $re='/(?<![\d+])(?:(?:\+|00)?90'.$sep.')?\(?0?'.$sep.'5\d{2}\)?'.$sep.'\d{3}'.$sep.'\d{2}'.$sep.'\d{2}(?!\d)/';
  if(!preg_match($re,(string)$text,$m)) return '';
  $d=m2s_norm($m[0]);
  return preg_match('/^905\d{9}$/',$d) ? $d : '';
}

/** UTF-8 güvenli kesme; mbstring yoksa regex ile, o da olmazsa bayt bazlı. */
function m2s_cut($t,$n){
  $t=(string)$t;
  if(function_exists('mb_substr')) return mb_substr($t,0,$n,'UTF-8');
  if(preg_match('/^.{0,'.(int)$n.'}/us',$t,$m)) return $m[0];
  return substr($t,0,$n);
}

function m2s_mask($d){ $d=(string)$d; $l=strlen($d); return $l>2 ? str_repeat('*',$l-2).substr($d,-2) : str_repeat('*',$l); }

function m2s_log($msg){
  $s=m2s_get();
  if(!empty($s['log']) && defined('WP_DEBUG') && WP_DEBUG) error_log('[Mail2SMS] '.$msg);
}

/**
 * Şifre sıfırlama ve hesap güvenliği mailleri.
 * Çekirdek bu filtreleri wp_mail'den hemen önce çağırır; konuyu not alıp o maili atlarız.
 */
function m2s_security_subjects($add=null){
  static $list=[];
  if($add!==null && $add!=='') $list[]=(string)$add;
  return $list;
}
add_filter('retrieve_password_title', function($title){ m2s_security_subjects($title); return $title; }, 9999, 1);
foreach(['retrieve_password_notification_email','password_change_email','email_change_email','wp_password_change_notification_email','wp_new_user_notification_email'] as $m2s_f){
  add_filter($m2s_f, function($email){ if(is_array($email) && isset($email['subject'])) m2s_security_subjects($email['subject']); return $email; }, 9999, 1);
}
unset($m2s_f);

function m2s_is_security_mail($subject,$message){
  if(in_array((string)$subject, m2s_security_subjects(), true)) return true;
  $body=(string)$message;
  if(stripos($body,'wp-login.php?action=rp')!==false) return true;
  if(preg_match('/[?&;]action=(?:rp|resetpass|confirmaction)(?![a-z])/i',$body)) return true;
  return false;
}

function m2s_send($phone,$text){
  $s=m2s_get(); foreach(['usercode','password','msgheader'] as $k){ if(empty($s[$k])) return false; }
  $enc=in_array($s['encoding'],m2s_encodings(),true) ? $s['encoding'] : 'TR';
  $no=m2s_norm($phone);
  $payload=['msgheader'=>$s['msgheader'],'messages'=>[['msg'=>$text,'no'=>$no]],'encoding'=>$enc];
  $r=wp_remote_post(M2S_API,[
    'headers'=>['Content-Type'=>'application/json','Authorization'=>'Basic '.base64_encode($s['usercode'].':'.$s['password'])],
    'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE),'timeout'=>30
  ]);
  if(is_wp_error($r)){ m2s_log('WP_Error phone='.m2s_mask($no).' '.$r->get_error_message()); return false; }
  $code=(int)wp_remote_retrieve_response_code($r); $body=wp_remote_retrieve_body($r);
  $j=json_decode($body,true); $st=(is_array($j) && isset($j['code'])) ? (string)$j['code'] : '-';
  $ok=($code===200 && $st==='00');
  m2s_log('phone='.m2s_mask($no).' http='.$code.' netgsm='.$st);
  return $ok;
}

/**
 * Mail → SMS köprüsü
 * wp_mail filtre zincirinde, gönderilen mailin konu+gövdesinden telefon yakalar.
 * Şifre sıfırlama / hesap güvenliği mailleri gönderilmez.
 * Site sahibi 'mail2sms_atomix_should_send' filtresiyle (bool $send, array $args) karar verebilir.
 */
add_filter('wp_mail', function($args){
  if(!is_array($args)) return $args;
  $subject=(string)($args['subject'] ?? ''); $message=(string)($args['message'] ?? '');
  $send = !m2s_is_security_mail($subject,$message);
  $send = (bool)apply_filters('mail2sms_atomix_should_send', $send, $args);
  if(!$send){ m2s_log('SMS atlandı (güvenlik maili ya da filtre).'); return $args; }
  $digits = m2s_find_phone($subject.' '.$message);
  if ($digits !== '') {
    $plain  = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($subject.' - '.$message)));
    $plain  = m2s_cut($plain, 900);
    $ok = m2s_send($digits, $plain);
    m2s_log('trigger phone='.m2s_mask($digits).' ok=' . ($ok?'1':'0'));
  } else {
    m2s_log('telefonsuz mail – SMS tetiklenmedi.');
  }
  return $args;
}, 99, 1);
