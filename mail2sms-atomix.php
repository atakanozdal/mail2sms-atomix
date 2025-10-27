<?php
/**
 * Plugin Name: Mail → SMS (Atomix)
 * Description: WordPress’in gönderdiği e-postaların içinden telefon numarasını ayıklar ve Netgsm ile SMS gönderir.
 * Version: 1.0.0
 * Author: Atakan Özdal (Atomix)
 */

if (!defined('ABSPATH')) exit;

define('M2S_OPT', 'atomix_m2s');
define('M2S_API', 'https://api.netgsm.com.tr/sms/rest/v2/send');

function m2s_def(){ return ['usercode'=>'','password'=>'','msgheader'=>'','encoding'=>'TR','log'=>1]; }
function m2s_get(){ $o=get_option(M2S_OPT,[]); if(!is_array($o)) $o=[]; return array_merge(m2s_def(),$o); }
function m2s_upd($n){ $m=array_merge(m2s_get(),$n); update_option(M2S_OPT,$m); return $m; }

add_action('admin_menu', function(){
  add_options_page('Mail → SMS','Mail → SMS','manage_options','mail2sms-atomix', function(){
    if(!current_user_can('manage_options')) return; $s=m2s_get(); $ok=null;
    if(isset($_POST['save']) && check_admin_referer('save')){
      $usercode=sanitize_text_field($_POST['usercode']??'');
      $password=sanitize_text_field($_POST['password']??'');
      $msgheader=sanitize_text_field($_POST['msgheader']??'');
      $encoding=sanitize_text_field($_POST['encoding']??'TR');
      $log=isset($_POST['log'])?1:0;
      $s=m2s_upd(compact('usercode','password','msgheader','encoding','log')); $ok='Ayarlar kaydedildi.';
    }
    ?>
    <div class="wrap"><h1>Mail → SMS (Atomix)</h1>
      <?php if($ok): ?><div class="updated"><p><?php echo esc_html($ok);?></p></div><?php endif;?>
      <form method="post"><?php wp_nonce_field('save');?>
        <table class="form-table">
          <tr><th>Usercode</th><td><input name="usercode" class="regular-text" value="<?php echo esc_attr($s['usercode']);?>"></td></tr>
          <tr><th>Password</th><td><input name="password" class="regular-text" value="<?php echo esc_attr($s['password']);?>"></td></tr>
          <tr><th>Msgheader</th><td><input name="msgheader" class="regular-text" value="<?php echo esc_attr($s['msgheader']);?>"></td></tr>
          <tr><th>Encoding</th><td>
            <select name="encoding">
              <option value="TR" <?php selected($s['encoding'],'TR');?>>TR</option>
              <option value="TURKCE" <?php selected($s['encoding'],'TURKCE');?>>TURKCE</option>
              <option value="UTF-8" <?php selected($s['encoding'],'UTF-8');?>>UTF-8</option>
            </select>
          </td></tr>
          <tr><th>Debug log</th><td><label><input type="checkbox" name="log" <?php checked($s['log'],1);?>> Açık</label></td></tr>
        </table>
        <p><button class="button button-primary" name="save" value="1">Kaydet</button></p>
      </form>
      <p><strong>İpucu:</strong> Eğer e-postada telefon görünmüyorsa şablona şu satırı ekleyin: <code>&lt;!-- PHONE: {customer_phone} --&gt;</code></p>
    </div>
    <?php
  });
});

function m2s_norm($p){ $d=preg_replace('/\D+/','',(string)$p); if(strlen($d)==11 && $d[0]==='0') $d='90'.substr($d,1); if(strlen($d)==10) $d='90'.$d; return $d; }

function m2s_send($phone,$text){
  $s=m2s_get(); foreach(['usercode','password','msgheader'] as $k){ if(empty($s[$k])) return false; }
  $payload=['msgheader'=>$s['msgheader'],'messages'=>[['msg'=>$text,'no'=>m2s_norm($phone)]],'encoding'=>$s['encoding']];
  $r=wp_remote_post(M2S_API,[
    'headers'=>['Content-Type'=>'application/json','Authorization'=>'Basic '.base64_encode($s['usercode'].':'.$s['password'])],
    'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE),'timeout'=>30
  ]);
  if(is_wp_error($r)){ if($s['log']) error_log('[Mail2SMS] WP_Error: '.$r->get_error_message()); return false; }
  $code=(int)wp_remote_retrieve_response_code($r); $body=wp_remote_retrieve_body($r);
  $ok=($code===200 && ($j=json_decode($body,true)) && ($j['code']??'')==='00'); if($s['log']) error_log('[Mail2SMS] code='.$code.' body='.$body);
  return $ok;
}

/**
 * Mail → SMS köprüsü
 * wp_mail filtre zincirinde, gönderilen mailin konu+gövdesinden telefon yakalar.
 */
add_filter('wp_mail', function($args){
  $s=m2s_get();
  $raw = (string)($args['subject'] ?? '') . ' ' . (string)($args['message'] ?? '');
  if (preg_match('/(\+?90|0)?\s*5\d{2}\s*\d{3}\s*\d{2}\s*\d{2}/', $raw, $m)) {
    $digits = m2s_norm($m[0]);
    $plain  = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(($args['subject'] ?? '').' - '.($args['message'] ?? ''))));
    if (strlen($plain) > 900) $plain = substr($plain,0,900);
    $ok = m2s_send($digits, $plain);
    if (!empty($s['log'])) error_log('[Mail2SMS] trigger phone='.$digits.' ok=' . ($ok?'1':'0'));
  } else {
    if (!empty($s['log'])) error_log('[Mail2SMS] telefonsuz mail – SMS tetiklenmedi.');
  }
  return $args;
}, 99, 1);
