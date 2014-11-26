<?php
    $mysqli = new mysqli("localhost","zshifour_zhongwu","307442570szw","ezcampus");
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
        $email = $_COOKIE['email'];
        $password = $_COOKIE['password'];

        if ($stmt = $mysqli->prepare("SELECT activated,name,school FROM users WHERE email=? and password=?")) {
            $stmt->bind_param("ss",$email,$password);
            $stmt->execute();
            mysqli_stmt_store_result($stmt);
            $check_count = $stmt->num_rows;
            $stmt->bind_result($activated,$username,$school);
            while ($stmt->fetch()) {}
            setcookie('username',$username,time()+24*60*60*3);
            mysqli_stmt_free_result($stmt);
            $stmt->close();
        } else {
            printf("prepare error");
        }
        if ($check_count == 1 && $activated == 1) {
            $append_url = "?name=".$username."&school=".$school;
            header('Location: user.php'.$append_url);
            exit();
        }
    }

    if (isset($_POST['email']) && isset($_POST['password'])) {
        $typed_email = $_POST['email'];
        $typed_password = $_POST['password'];

        if ($stmt = $mysqli->prepare("SELECT activated,name,school FROM users WHERE email=? and password=?")) {
            $stmt->bind_param("ss",$typed_email,$typed_password);
            $stmt->execute();
            mysqli_stmt_store_result($stmt);
            $check_count = $stmt->num_rows;
            $stmt->bind_result($activated,$username,$school);
            while ($stmt->fetch()) {}
            mysqli_stmt_free_result($stmt);
            $stmt->close();
        } else {
            printf("prepare error");
        }
        if ($check_count == 1 && $activated == 1) {
            setcookie('email',$typed_email,time()+24*60*60*3);
            setcookie('password',$typed_password,time()+24*60*60*3);
            setcookie('username',$username,time()+24*60*60*3);
            echo "?name=".$username."&school=".$school;
            exit();
        } else if ( $check_count == 1 && $activated == 0) {
            echo "fail1";
            exit();
        } else if ($check_count != 1) {
            echo "fail2";
            exit();
        }
    }
    mysqli_close($mysqli);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome To Ezcampus!</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>

<script>
    
    function login() {
        var e = $("[name=email]").val();
        var p = $("[name=password]").val();

        if (e.length == 0 || p.length == 0) {
            $("#requirementHint").css("color","red");
            $("#requirementHint").html("Please fill out all the form!");
        } else {
            $.ajax({
            type:"POST",
            url:"login.php",
            data: {email:e,password:p},
            dataType: "text"}).done(function(msg) {
                if (msg == "fail1") {
                    $("#requirementHint").css("color","red");
                    $("#requirementHint").html("Please go to " + e + " to activate your account!");
                } else if (msg == "fail2") {
                    $("#requirementHint").css("color","red");
                    $("#requirementHint").html("email and password does not match!");
                } else {
                    window.location = "user.php" + msg;
                }

            }).fail(function(msg) {
                alert("ERROR");
            });
        }
    }

</script>

<body>
    <div id="ezcontainer">
        <div id="ezwrapper">
            <div id="ezwrappertop">
                <div id="ezwrappertopleft">
                    <h3>Log In</h3>
                </div>
                <span id="requirementHint">
                </span>
                <div id="ezwrappertopright">
                    <h3 style="float:right;"><a href="index.php" id="loginButton">Sign Up &raquo;</a></h3>
                </div>
            </div>
            <div id="ezwrappermain">
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Email:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input style="height:35px;width:200px;" type="text" name="email" placeholder="Enter your email address.">
                        </div>
                    </div>
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Password:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input style="height:35px;width:200px;" type="password" name="password">
                        </div>
                    </div>
                    <div style="width:500px;height:50px;margin-left:75px;margin-top:20px;display:inline-block;">
                        <button style="height:50px;width:100px;margin-left:180px;" onclick="login()">Log In</button>
                    </div>
                    <!--<div style="width:500px;height:50px;margin-left:55px;margin-top:20px;display:inline-block;">
                        <a href="forgetpassword.php" style="height:50px;width:100px;margin-left:180px;text-decoration:none;">Forget your password?</a>
                    </div>!-->
            </div>
        </div>
    </div>
</body>
<script>
    $("body").show();
</script>
</html>