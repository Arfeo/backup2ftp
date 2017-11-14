<?

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'backup2ftp');
define('APP_TMP_DIR', '/var/tmp/');

require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_after.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/backup2ftp/src/func.php";

$APPLICATION->SetTitle("Журнал");

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$back = "?lang=ru";

//// Очистка журнала ////
if($request->get('clear_log') && $request->get('clear_log') == 'true') {

	if(file_put_contents(APP_TMP_DIR . "backup2ftp.log", "", LOCK_EX) !== false) {

		// Выставляем флаг с результатом выполнения операции
		setcookie("backup2ftp_status", "ok", time() + 3600);

	} else {

		// Выставляем флаг с результатом выполнения операции
		setcookie("backup2ftp_status", "error", time() + 3600);

	}

	header("Location: $back");

} else {

	if(isset($_COOKIE["backup2ftp_status"])) {

		switch($_COOKIE["backup2ftp_status"]) {

			case 'ok':
				
				CAdminMessage::showMessage(array(

		            "MESSAGE" => "Журнал успешно очищен.",
		            "TYPE" => "OK",

		        ));

				break;
			
			case 'error':
				
				CAdminMessage::showMessage("В процессе очистки журнала произошла ошибка. Повторите попытку.");

				break;

		}

		// Удаляем флаг с результатом выполнения операции
		setcookie("backup2ftp_status", "", time() - 3600);

	}

}

?>

<div style="background-color:#fff; min-height:300px; max-height:300px; overflow:auto; border:1px solid #aaa; margin-bottom:10px">
<table cellpadding="0" cellspacing="0" width="100%">

<?

$messages = array_reverse(explode("\n", file_get_contents(APP_TMP_DIR . "backup2ftp.log")));

$y = 0;

foreach($messages as $message) {

	if($message != null) {

		$y++;

		$m = explode(";", $message);

		echo '<tr style="background-color:#' . (($y % 2 == 0) ? 'f0f0f0' : 'fff') . ';"><td width="20%" style="padding:5px;">' . $m[0] . '</td><td width="80%" style="padding:5px;">' . $m[1] . '</td></tr>';

	}

}

?>

</table>
</div>

<input type="button" value="Очистить журнал" onClick="clearLogFile();" />

<script type="text/javascript">

	function clearLogFile() {

		if(confirm('Вы уверены, что хотите очистить журнал? Это действие нельзя отменить.')) {

			window.location = window.location.href + '&clear_log=true';

		}

		return false;

	}

</script>

<?

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';