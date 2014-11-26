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
    else {
        setcookie('email',$email,time()-1000);
        setcookie('password',$password,time()-1000);
        setcookie('username',$username,time()-1000);
    }
}

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['school'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $school = $_POST['school'];
    $hash = md5(rand(0,1000));
    if ($stmt = $mysqli->prepare("INSERT INTO users (name,email,password,school,activated,hash) VALUES (?,?,?,?,0,?)")) {
        $stmt->bind_param("sssss",$username,$email,$password,$school,$hash);
        $stmt->execute();
        setcookie('email',$email,time()+24*60*60*3);
        setcookie('password',$password,time()+24*60*60*3);
        setcookie('username',$username,time()+24*60*60*3);

        echo "<p style='color:green;'>You are successfully registered! Please go to $email to activate your account!</p>";
        $to = $email;
        $subject = 'Signup | Verification';
        $message = "

        Thanks for signing up! Your account has been created.

        -------------------
        Username: $username
        Password: $password
        -------------------

        Please click this link to activate your account:
        http://www.ezcampus.org/confirmation.php?email=$email&password=$password&hash=$hash
        ";

        $header = 'From:nonreply@ezcampus.org' . "\r\n";
        mail($to,$subject,$message,$header);
        exit();
    } else {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit();
    }

}

if (isset($_POST['username'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    if ($stmt = $mysqli->prepare("SELECT id FROM users WHERE name=?")) {
        $stmt->bind_param("s",$username);
        $stmt->execute();
        mysqli_stmt_store_result($stmt);
        $user_count = $stmt->num_rows;
        mysqli_stmt_free_result($stmt);
        $stmt->close();
    } else {
        printf("prepare error 1");
    }

    if ($user_count == 1) {

        echo "<p style='color:red;'>username is taken!</p>";
        exit();
    } else {
        echo "<p style='color:green;'>good!</p>";
        exit();
    }

}

if (isset($_POST['email'])) {
    $email = $mysqli->real_escape_string($_POST['email']);
    if ($stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?")) {
        $stmt->bind_param("s",$email);
        $stmt->execute();
        mysqli_stmt_store_result($stmt);
        $email_count = $stmt->num_rows;
        mysqli_stmt_free_result($stmt);
        $stmt->close();
    } else {
        printf("prepare error 1");
    }

    if ($email_count == 1) {

        echo "<p style='color:red;'>email is taken!</p>";
        exit();
    } else {
        echo "<p style='color:green;'>good!</p>";
        exit();
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome To Ezcampus!</title>
<link rel="stylesheet" type="text/css" href="lib/css/normalize.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>
<script>

function checkUsername() {
    var u = $("[name=username]").val();
    $("#usernameHint").html("Checking...");
    var username_format=/^[a-zA-Z]{1}[a-zA-Z0-9]{2,14}$/;
    if (u.length == 0) {
        $("#usernameHint").css("color","red");
        $("#usernameHint").html("username is empty!");
    } else if (u.length < 3 || u.length > 15) {
        $("#usernameHint").css("color","red");
        $("#usernameHint").html("username can only be 5-15 characters!");
    } else if (!u.match(username_format)) {
        $("#usernameHint").css("color","red");
        $("#usernameHint").html("starts with a character; characters and digits only!");
    } else {
        $.ajax({
        type:"POST",
        url:"index.php",
        data: {username:u},
        dataType: "text"}).done(function(msg) {
            $("#usernameHint").html(msg);

        }).fail(function(msg) {
            alert("ERROR");
        });
    }
}

function checkEmail() {
    var e = $("[name=email").val();
    $("#emailHint").html("Checking...");
    var UR_email = /.+@.+\.rochester+\.edu$/;
    var NYU_email = /.+@.+\.nyu+\.edu$/;
    if (e.length == 0) {
        $("#emailHint").css("color","red");
        $("#emailHint").html("email field is empty!");
    } //else if (!e.match(UR_email) && !e.match(NYU_email)) {
        //$("#emailHint").css("color","red");
        //$("#emailHint").html("Please enter a valid school email address.");
    //} 
        else {
        $.ajax({
        type:"POST",
        url:"index.php",
        data: {email:e},
        dataType: "text"}).done(function(msg) {
            $("#emailHint").html(msg);

        }).fail(function(msg) {
            alert("ERROR");
        });
    }
}

function checkPassword() {
    var p1 = $("[name=password1]").val();
    var p2 = $("[name=password2]").val();
    $("#password1Hint").html("Checking...");
    if (p1.length == 0) {
        $("#password1Hint").css("color","red");
        $("#password1Hint").html("password field is empty!");
    } else if (p1.length < 8) {
        $("#password1Hint").css("color","red");
        $("#password1Hint").html("password should be at least 8 characters!");
    } else {
        $("#password1Hint").css("color","green");
        $("#password1Hint").html("good!");
        if (p2.length > 0 && p1 != p2) {
            $("#password2Hint").css("color","red");
            $("#password2Hint").html("two passwords does not match!");
        } else if (p1 == p2) {
            $("#password2Hint").css("color","green");
            $("#password2Hint").html("good!");
        } 
    }
}

function confirmPassword() {
    var p1 = $("[name=password1]").val();
    var p2 = $("[name=password2").val();
    if (p2.length == 0) {
        $("#password2Hint").css("color","red");
        $("#password2Hint").html("this field is empty!");
    }else if (p2 == p1) {
        $("#password2Hint").css("color","green");
        $("#password2Hint").html("good!");
    } else {
        $("#password2Hint").css("color","red");
        $("#password2Hint").html("two passwords does not match!");
    }
}

function schoolCheck() {
    var s = $("#school_list option:selected").text();
    if (s == "Choose your school...") {
        $("#schoolHint").css("color","red");
        $("#schoolHint").html("Please choose your school!");
    } else {
        $("#schoolHint").css("color","green");
        $("#schoolHint").html("good!");
    }
}

function signup() {
    var uHint = $("#usernameHint").text();
    var eHint = $("#emailHint").text();
    var p1Hint = $("#password1Hint").text();
    var p2Hint = $("#password2Hint").text();
    var sHint= $("#schoolHint").text();

    var u = $("[name=username]").val();
    var e = $("[name=email]").val();
    var p1 = $("[name=password1]").val();
    var p2 = $("[name=password2]").val();
    var s = $("[name=school]").val();
    if (uHint != "good!" || eHint != "good!" || p1Hint != "good!" || p2Hint != "good!" || sHint !="good!") {
        $("#requirementHint").css("color","red");
        $("#requirementHint").html("Please correct all the unqualified fields!");
        if (u.length == 0) {
            $("#usernameHint").css("color","red");
            $("#usernameHint").html("username is empty!");
        }
        if (e.length == 0) {
            $("#emailHint").css("color","red");
            $("#emailHint").html("email is empty!");
        }
        if (p1.length == 0) {
            $("#password1Hint").css("color","red");
            $("#password1Hint").html("password is empty!");
        }
        if (p2.length == 0) {
            $("#password2Hint").css("color","red");
            $("#password2Hint").html("this is empty!");
        }

        return false;
    } else {
        $.ajax({
        type:"POST",
        url:"index.php",
        data: {username:u,email:e,password:p1,school:s},
        dataType: "text"}).done(function(msg) {
            $("#requirementHint").html(msg);

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
                    <h3>Sign Up</h3>
                </div>
                <span id="requirementHint">
                </span>
                <div id="ezwrappertopright">
                    <h3 style="float:right;"><a href="login.php" id="loginButton">Log In &raquo;</a></h3>
                </div>
            </div>
            <div id="ezwrappermain">

                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Username:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input onblur="checkUsername()" style="height:35px;width:200px;" type="text" name="username" placeholder="Type your name here.">
                        </div>
                        <span class="ezfieldcomment" id="usernameHint"></span>
                    </div>
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Email:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input onblur="checkEmail()" style="height:35px;width:200px;" type="text" name="email" placeholder="Enter your email address.">
                        </div>
                        <span class="ezfieldcomment" id="emailHint"></span>
                    </div>
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Password:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input onblur="checkPassword()" style="height:35px;width:200px;" type="password" name="password1">
                        </div>
                        <span class="ezfieldcomment" id="password1Hint"></span>
                    </div>
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">Password again:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <input onblur="confirmPassword()" style="height:35px;width:200px;" type="password" name="password2">
                        </div>
                        <span class="ezfieldcomment" id="password2Hint"></span>
                    </div>
                    <div class="ezfield">
                        <div class="ezfieldleft">
                            <h3 style="float:right;">School:</h3> 
                        </div>
                        <div class="ezfieldright">
                            <select onblur="schoolCheck()" style="height:35px;width:200px;" id="school_list" name="school">
                                <option selected>Choose your school...</option>
                                <option value="University of Rochester">University of Rochester</option>
                                <option value="New York University">New York University</option>
                            </select>
                        </div>
                        <span class="ezfieldcomment" id="schoolHint"></span>
                    </div>
                    <div style="width:500px;height:50px;margin-left:75px;margin-top:20px;display:inline-block;">
                        <button style="height:50px;width:100px;margin-left:180px;" onclick="signup()">Submit</button>
                    </div>
            </div>
        </div>
    </div>
</body>
<script>
    $("body").show();
</script>
</html>