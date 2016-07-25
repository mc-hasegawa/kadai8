<?php
header('Content-Type: text/html; charset=UTF-8');
require "mail_sender.php";
$file = fopen("lesson.bbs", "r+");
$unix_time = time()+(7 * 60 * 60);
$show_time = date('Y/m/d H:i:s',$unix_time);
if (isset($_POST["posting_content"]))
{
	$user_name = $_POST["posting_user_name"];
	$user_mail = $_POST["posting_user_mail"];
	$content = $_POST["posting_content"];
	if ($user_name == "")
	{
		$user_name = "匿名希望";
	}
	fseek($file,0,SEEK_END);
	fwrite($file,"\n***>>");
	fwrite($file,htmlspecialchars($user_name."\n"));
	fwrite($file,"date>>");
	fwrite($file,htmlspecialchars($show_time."\n"));
	fwrite($file,"cont>>\n");
	fwrite($file,htmlspecialchars($content));
	fwrite($file,"\ncontend>>");
	rewind($file);

	if ($user_mail != "")
	{
		if (!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $user_mail))
		{
			echo "不正なメールアドレスです";
		}
		else
		{
			if (date('a',$unix_time) == 'am')
			{
				$ampm = '午前';
			}
			else
			{
				$ampm = '午後';
			}
			$page_url = (empty($_SERVER["HTTPS"])?"http://":"https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			// 件名
			$subject = 'PC用BBS 投稿のお知らせ';
			// 本文
			$mailbody = date('Y年n月d日',$unix_time).$ampm.date('g時i分s秒',$unix_time)."に ".$user_name."さんから 書き込みがありました。\n".$content."\n--------------------------------------\n\n ■BBSへのURLはこちら↓■\n".$page_url;
			$from_text = 'BBS管理局';
			$from = 'From:'.mb_encode_mimeheader($from_text).MAIL_SENDER;
			mb_language("ja");
			mb_internal_encoding("UTF-8");
			if (!mb_send_mail($user_mail,$subject,$mailbody,$from))
			{
				echo "メール送信失敗";
			}
			else
			{
				echo "メール送信成功";
			}
		}
	}
}


$file_line_data = fopen("lesson.bbs", "r");
$response_count = 0;
$file_line_num = 0;
define("RESPONSE_SHOW_LIMIT",10);

while (($file_buffer = fgets($file_line_data, 4096)) !== false)
{
	if (preg_match("/contend>>/",$file_buffer))
	{
		$response_count++;
	}
	$file_line_num++;
}
rewind($file_line_data);
if (RESPONSE_SHOW_LIMIT < $response_count)	//ログ削除処理
{
	$edit_file = file("lesson.bbs");
	$delete_count = 0;
	while (($file_buffer = fgets($file_line_data, 4096)) !== false)
	{
		if (preg_match("/contend>>/",$file_buffer))
		{
			$response_count--;
		}
		unset($edit_file[$delete_count]);
		$delete_count++;
		$file_line_num--;
		if ($response_count == RESPONSE_SHOW_LIMIT)
		{
			break;
		}
	}
	file_put_contents("lesson.bbs", $edit_file);
}
$response_count = 0;
$file_line_count = 1;

?>
<html>
<head>
<title>PHP課題8_2</title>
</head>
<script>
function posting_check()
{
	if(document.posting_form.posting_content.value == "")
	{
		document.posting_form.posting_button.disabled = "true";
	}
	else
	{
		document.posting_form.posting_button.disabled = "";
	}
}
</script>
<body>
	<p>PHP課題8_2</p>
	<p>
		======================================<br>
		<?php
		if ($file)
		{
			while (($buffer = fgets($file, 4096)) !== false)
			{
				if (preg_match("/\*\*\*>>/",$buffer))
				{
					$response_count++;
					echo $response_count.", ";
					echo str_replace("***>>","&nbsp",$buffer);
				}
				elseif (preg_match("/date>>/",$buffer))
				{
					echo "&nbsp(投稿日時:&nbsp";
					echo str_replace("date>>","",$buffer);
					echo ")<br>";
				}
				elseif (preg_match("/cont>>/",$buffer))
				{
					echo str_replace("cont>>","",$buffer);
				}
				elseif (preg_match("/contend>>/",$buffer))
				{
					if ($file_line_count < $file_line_num)
					{
						echo "<br>--------------------------------------<br>";
					}
					else
					{
						echo "<br>";
					}
				}
				else
				{
					echo $buffer;
					echo "<br>";
				}
				$file_line_count++;
			}
			if (!feof($file))
			{
				echo "Error: unexpected fgets() fail\n";
			}
		}
		?>
		======================================<br>
		<form action="" method="post" name="posting_form">
			名前
			<p><input type="text" name="posting_user_name" value=""></p>
			メールアドレス
			<p><input type="text" name="posting_user_mail" value=""></p>
			書き込む内容
			<p><textarea name="posting_content" rows="6" cols="40" wrap="hard" onChange="posting_check()"></textarea></p>
			<input name="posting_button" type="submit" value="書き込む" disabled>
		</form>
	</p>
</body>
</html>
<?php
fclose($file);
fclose($file_line_data);
?>