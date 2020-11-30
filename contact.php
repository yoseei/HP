<?php
session_start();
$mode = 'input';
$errmessage = array(); 
if( isset($_POST['back']) && $_POST['back'] ){
  //  何もしない
} else if( isset($_POST['confirm']) && $_POST['confirm'] ){
  // 確認画面
  if( !$_POST['your-name'] ) {
    $errmessage[] = "名前を入力してください";
  } else if( mb_strlen($_POST['your-name']) > 100 ){
    $errmessage[] = "名前は100文字以内にしてください";
  }
  $_SESSION['your-name'] = htmlspecialchars($_POST['your-name'], ENT_QUOTES);

  if( !$_POST['your-email'] ) {
    $errmessage[] = "Eメールを入力してください";
  } else if( mb_strlen($_POST['your-email']) > 200 ){
    $errmessage[] = "Eメールは200文字以内にしてください";
  } else if( !filter_var($_POST['your-email'], FILTER_VALIDATE_EMAIL) ){
    $errmessage[] = "メールアドレスが不正です";
  }
  $_SESSION['your-email'] = htmlspecialchars($_POST['your-email'], ENT_QUOTES);

  if( !$_POST['your-message'] ) {
    $errmessage[] = "お問い合わせ内容を入力してください";
  } else if( mb_strlen($_POST['your-message']) > 500 ){
    $errmessage[] = "お問い合わせ内容は500文字以内にしてください";
  }
  $_SESSION['your-message'] = htmlspecialchars($_POST['your-message'], ENT_QUOTES);

  if( $errmessage ){
    $mode = 'input';
  } else {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    $mode = 'confirm';
  }
} else if( isset($_POST['send']) && $_POST['send'] ){
  // 送信ボタンを押したとき
  if( !$_POST['token'] || !$_SESSION['token'] || !$_SESSION['your-email'] ){
    $errmessage[] = '不正な処理が行われました';
    $_SESSION     = array();
    $mode         = 'input';
  } else if( $_POST['token'] != $_SESSION['token'] ){
    $errmessage[] = '不正な処理が行われました';
    $_SESSION     = array();
    $mode         = 'input';
  } else {
    $message = "お問い合わせを受け付けました \r\n"
                . "名前: " . $_SESSION['your-name'] . "\r\n"
                . "email: " . $_SESSION['your-email'] . "\r\n"
                . "お問い合わせ内容:\r\n"
                . preg_replace("/\r\n|\r|\n/", "\r\n", $_SESSION['message']);
    mail($_SESSION['email'], 'お問い合わせありがとうございます', $message);
    mail('yoseei.drums@gmail.com', 'お問い合わせありがとうございます', $message);
    $_SESSION = array();
    $mode = 'send';
  }
} else { 
  $_SESSION['your-name'] = "";
  $_SESSION['your-email'] = "";
  $_SESSION['your-message'] = "";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>YLF Contact</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
  <link rel="stylesheet" href="css/styles.css">
  <style>
  
  </style>
</head>
<body>
<?php if( $mode == 'input' ){ ?>
  <!-- 入力画面 -->
  <div class="contact-wrapper">
    <header class="contact-header-container">
      <div class="contact-header-wrapper">
        <h1><a href="index.html">YLF</a></h1>
        <ul class="header-ul">
          <li class="header-li"><a href="profile.html">PROFILE</a></li>
          <li class="header-li"><a href="blog.html">BLOG</a></li>
          <li class="header-li"><a href="contact.php">CONTACT</a></li>
        </ul>
      </div>
    </header>
    
    <main class="contact-main">
      <h1>CONTACT</h1>
      <?php
        if( $errmessage ){
          echo '<div class="alert alert-danger" role="alert">';
          echo implode('<br>', $errmessage );
          echo '</div>';
        }
      ?>
      <form method="POST" action="./contact.php">
        <div class="contact-box">
          <label for="name">お名前</label>
          <input type="text" id="name" name="your-name" value="<?php echo $_SESSION['your-name'] ?>"><br>
        </div>
        <div class="contact-box">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="your-email" value="<?php echo $_SESSION['your-email'] ?>"><br>
        </div>
        <div class="contact-box">
          <label for="message">メッセージ</label>
          <textarea id="message" name="your-message"><?php echo $_SESSION['your-message'] ?></textarea>
        </div>
        <input type="submit" name="confirm" class="button" value="入力内容を確認する">
      </form>
    </main>
  </div>

  <footer>
    <div class="footer-wrapper">
      <div class="sns-icon">
        <a href="https://www.instagram.com/?hl=ja"><i class="fab fa-instagram"></i></a>
      </div>
      <p>©︎2020 Yoshitaka Kai</p>
    </div>
  </footer>
  
<?php } else if( $mode == 'confirm' ){ ?>
  <!-- 確認画面 -->
  <div class="confirm-wrapper">   
    <header class="contact-header-container">
      <div class="contact-header-wrapper">
        <h1><a href="index.html">YLF</a></h1>
        <ul class="header-ul">
          <li class="header-li"><a href="profile.html">PROFILE</a></li>
          <li class="header-li"><a href="blog.html">BLOG</a></li>
          <li class="header-li"><a href="contact.php">CONTACT</a></li>
        </ul>
      </div>
    </header> 

    <form method="POST" action="./contact.php">
      <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
      <h1 class="confirmation-title">お問い合わせ内容</h1>
      <div class="confirmation-about">
        <p>【お名前】</p>
        <?php echo $_SESSION['your-name'] ?><br>
        <p>【メールアドレス】</p>
        <?php echo $_SESSION['your-email'] ?><br>
        <p>【メッセージ】</p>
        <?php echo nl2br($_SESSION['your-message']) ?><br>
      </div>
      <input type="submit" name="back" class="button btn btn-primary" value="戻る">
      <input type="submit" name="send" class="button btn btn-primary" value="送信">
    </form>
  </div>

  <footer>
    <div class="footer-wrapper">
      <div class="sns-icon">
        <a href="https://www.instagram.com/?hl=ja"><i class="fab fa-instagram"></i></a>
      </div>
      <p>©︎2020 Yoshitaka Kai</p>
    </div>
  </footer>

<?php } else { ?>
  <!-- 完了画面 -->
  <div class="confirm-wrapper thanks-wrapper">
    <header class="contact-header-container">
      <div class="contact-header-wrapper">
        <h1><a href="index.html">YLF</a></h1>
        <ul class="header-ul">
          <li class="header-li"><a href="profile.html">PROFILE</a></li>
          <li class="header-li"><a href="blog.html">BLOG</a></li>
          <li class="header-li"><a href="contact.php">CONTACT</a></li>
        </ul>
      </div>
    </header>
    
    <div class="thanks">
      <p>送信しました。<br>お問い合わせありがとうございました。</p>
    </div>
    
    <footer>
      <div class="footer-wrapper">
        <div class="sns-icon">
          <a href="https://www.instagram.com/?hl=ja"><i class="fab fa-instagram"></i></a>
        </div>
        <p>©︎2020 Yoshitaka Kai</p>
      </div>
    </footer>
  </div>
<?php } ?>
</body>
</html>