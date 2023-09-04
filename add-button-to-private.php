<?php

/*
Plugin Name: 売約ボタンを追加するプラグイン
Description: 売約ボタンを追加するプラグイン
Version: 1.0
Author: a.kobayashi
*/

//=================================================
// 管理画面にメニューを追加登録する
//=================================================
add_action('admin_menu', function () {

    add_submenu_page('options-general.php', '売約ボタン設定', '売約ボタン設定', 'manage_options', 'abtp_settings', 'abtp_add_menu_page', 9);

});


//=================================================
// メインメニューページ内容の表示・更新処理
//=================================================
function abtp_add_menu_page()
{

    //---------------------------------
    // ユーザーが必要な権限を持つか確認
    //---------------------------------
    if (!current_user_can('manage_options')) {
        wp_die(__('この設定ページのアクセス権限がありません'));
    }


    //---------------------------------
    // 初期化
    //---------------------------------
    $opt_name_to = 'abtp_send_to_addr'; //オプション名の変数
    $opt_name_cc = 'abtp_send_cc_addr'; //オプション名の変数
    $opt_name_content = 'abtp_send_content'; //オプション名の変数
    $opt_val_to = get_option($opt_name_to); // 既に保存してある値があれば取得
    $opt_val_cc = get_option($opt_name_cc); // 既に保存してある値があれば取得
    $opt_val_content = get_option($opt_name_content); // 既に保存してある値があれば取得
    $opt_val_old_to = $opt_val_to;
    $opt_val_old_cc = $opt_val_cc;
    $opt_val_old_content = $opt_val_content;
    $message_html = "";


    //---------------------------------
    // 更新されたときの処理
    //---------------------------------
    if (isset($_POST[$opt_name_to])) {

        // POST されたデータを取得
        $opt_val_to = $_POST[$opt_name_to];
        // POST された値を$opt_name=$opt_valでデータベースに保存(wp_options テーブル内に保存)
        update_option($opt_name_to, $opt_val_to);

        if (isset($_POST[$opt_name_cc])) {
            // POST されたデータを取得
            $opt_val_cc = $_POST[$opt_name_cc];
            // POST された値を$opt_name=$opt_valでデータベースに保存(wp_options テーブル内に保存)
            update_option($opt_name_cc, $opt_val_cc);
        }

        // 画面にメッセージを表示
        $message_html = <<<EOF
			
<div class="notice notice-success is-dismissible">
	<p>
		保存しました<br>
		to:{$opt_val_old_to}→{$opt_val_to}<br>
		cc:{$opt_val_old_cc}→{$opt_val_cc}<br>
	</p>
</div>
			
EOF;

    }


    if (isset($_POST[$opt_name_content])) {

        // POST されたデータを取得
        $opt_val_content = $_POST[$opt_name_content];
        // POST された値を$opt_name=$opt_valでデータベースに保存(wp_options テーブル内に保存)
        update_option($opt_name_content, $opt_val_content);


        // 画面にメッセージを表示
        $message_html = <<<EOF
			
<div class="notice notice-success is-dismissible">
	<p>
		本文を保存しました
	</p>
</div>
			
EOF;

    }


    //---------------------------------
    // HTML表示
    //---------------------------------
    echo $html = <<<EOF

{$message_html}

<div class="wrap">
	<h2>売約ボタン設定</h2>
    <p>
    売約ボタンが押されたときの送信先メールアドレスを設定してください
    </p>	
	<form name="form1" method="post" action="">
		<p>
		    to(必須/ひとつしか入りません):<br>
			<input type="email" name="{$opt_name_to}" value="{$opt_val_to}" size="32" placeholder="メールアドレス" required>
		</p>
		<p>
		    cc(ひとつしか入りません):<br>
			<input type="email" name="{$opt_name_cc}" value="{$opt_val_cc}" size="32" placeholder="メールアドレス">
		</p>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="アドレスを保存" />
		</p>
	</form>
	<hr>	
	<form name="form1" method="post" action="">
		<p>
		    メール文:<br>
			<textarea name="{$opt_name_content}" rows="5" cols="33">{$opt_val_content}</textarea>
		</p>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="本文を保存" />
		</p>
	</form>
</div>

EOF;

}


//=================================================
// 売約ボタンの処理
//=================================================

function shortcode_abtp_button($atts, $content, $tag)
{
    $this_post_id = get_the_ID();
    if(!isset($this_post_id)){
        return;
    }
    if(get_post_status($this_post_id) !== "publish"){
        return;
    }

    ?>
    <form method="post" id="abtpForm_<?php echo $this_post_id; ?>" action="">
        <input type="hidden" name="abtp_postid" value="<?php echo $this_post_id ?>">
        <input type="hidden" name="abtp_posttitle" value="<?php echo get_the_title( $this_post_id ); ?>">
        <input type="hidden" name="abtp_name" id="abtp_name_<?php echo $this_post_id; ?>" value="">
        <button type="submit" id="submitButton_<?php echo $this_post_id; ?>" style="display: none;">売約実行</button>
    </form>
    <button onclick="promptForName(<?php echo $this_post_id; ?>)">売約</button>
    <script>
        function promptForName(postID) {
            var name = prompt("名前を入れてください");
            if (name != null) {
                document.getElementById("abtp_name_"+postID).value = name;
                document.getElementById("submitButton_"+postID).click();
            }
        }
    </script>
    <?php
}

function check_abtp_form_submission()
{
    $opt_name_to = 'abtp_send_to_addr'; //オプション名の変数
    $opt_name_cc = 'abtp_send_cc_addr'; //オプション名の変数
    $opt_name_content = 'abtp_send_content'; //オプション名の変数

// Check if form is submitted
    if (isset($_POST['abtp_name']) && isset($_POST['abtp_postid']) && isset($_POST['abtp_posttitle'])) {
        $name = sanitize_text_field($_POST['abtp_name']);
        $post_id = sanitize_text_field($_POST['abtp_postid']);
        $post_title = sanitize_text_field($_POST['abtp_posttitle']);

        //投稿ステータスを非公開に変更
        $my_post = array('ID' => $post_id, 'post_status' => 'private');
        wp_update_post($my_post);

        //メール送信
        $to = get_option($opt_name_to); // Retrieve the email recipient from the options table
        $subject = '【M-Store】' . $post_title . ' - 売約実行されました';

        $raw_message = get_option($opt_name_content);
        $message_body = str_replace(array('{name}', '{title}'), array($name, $post_title), $raw_message);

        $headers = array('Content-Type: text/html; charset=UTF-8');

        // if a CC address is set in the option, add it to the headers
        if (get_option($opt_name_cc)) {
            $headers[] = 'Cc: ' . get_option($opt_name_cc);
        }


        if(wp_mail( $to, $subject, $message_body, $headers )):  ?>
            <script>
                // Save the current page without any URL parameters
                var clean_url = window.location.protocol + "//" + window.location.host + window.location.pathname;

                // Redirect to the clean URL - This acts similarly to a page reload, but without the POST data
                // which prevents the Email from being sent again on reload
                window.location = clean_url;
            </script>
        <?php endif;
    }
}

add_action( 'init', 'check_abtp_form_submission' );

function abtp_shortcodes_init(){
    add_shortcode('abtp_button', 'shortcode_abtp_button');
}
add_action('init', 'abtp_shortcodes_init');