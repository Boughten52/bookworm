<?php

  function empty_input($fname, $lname, $email, $pwd) {
    $result = false;

    if (empty($fname) || empty($lname) || empty($email) || empty($pwd)) {
      $result = true;
    }
    return $result;
  }

  function invalid_email($email) {
    $result = false;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $result = true;
    }
    return $result;
  }

  function fetch_user($conn, $uid, $email) {
    $sql = "SELECT * FROM user WHERE id = ? OR email = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
      header("location: ../pages/signup?error=STMT_FAILED");
      exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $uid, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($row = mysqli_fetch_assoc($result)) { return $row;}
    else { return false; }
  }

  function createUser($conn, $fname, $lname, $email, $pwd) {
    $sql = "INSERT INTO user (fname, lname, email, pwd_sha256, pwd_salt) VALUES (?, ?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
      header("location: ../pages/signup?error=STMT_FAILED");
      exit();
    }
    $salt = random_bytes(32);
    mysqli_stmt_bind_param($stmt, "sssss", $fname, $lname, $email, hash("sha256", $pwd . $salt), $salt);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../pages/signup?error=none");
    exit();
  }

  function loginUser($conn, $email, $pwd) {
    $userArr = fetch_user($conn, NULL, $email);

    if ($userArr == false) {
      header("location: ../pages/login?error=WRONG_LOGIN");
      exit();
    }

    $pwd_hashed = hash("sha256", $pwd . $userArr["pwd_salt"]);
    $checkPwd = password_verify($pwd_hashed, $userArr["pwd_sha256"]);

    if (strcmp($pwd_hashed, $userArr["pwd_sha256"])) {
      header("location: ../pages/login?error=WRONG_LOGIN");
      exit();
    }
    else {
      session_start();
      $_SESSION["id"] = $userArr["id"];
      header("location: /bookworm/index.php");
      $cookie_name = "cookie_loggedIn";
      $cookie_value = $email;
      setcookie($cookie_name, $cookie_value, 0);
      exit();
    }
  }

/*  function getProductsInCart($conn){
    //not sure if it works - dont have testing options avaliable
    $user = fetch_user($conn, $_COOKIE["cookie_loggedIn"]);
    $sql = "SELECT * FROM cart WHERE email = $user";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
      $row = fetch_assoc($result);
      return $row;
    }
    else {
      return NULL;
    }
  }*/

 ?>
