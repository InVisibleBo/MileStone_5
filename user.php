<?php
	$tradeList = array();
	$recentList = array();
	if (isset($_POST['item_id']) && isset($_POST['uname']) && isset($_POST['sname'])) {
		$school = $_POST['sname'];
		$username = $_POST['uname'];
		$item_id = $_POST['item_id'];

		$file_path = "./users/" . $school . "/" . $username . "/" . "tradeList.txt";

		$myfile = fopen($file_path,'a+') or die("Unable to open file!");
		$temp = 0;
		if ($myfile) {
			while (($buffer = fgets($myfile, 4096)) !== false) {
		        if ($item_id === trim($buffer)) {
		        	$temp = 1;
		        	break;
		        }    
		    }
			if ($temp == 0) {
				fwrite($myfile,$item_id . PHP_EOL);
			}
			fclose($myfile);
		}

		$rv_path = "./users/" . $school . "/" . $username . "/" . "recentList.txt";

		$file = fopen($rv_path,'a+') or die("Unable to open file!");
		$temp = 0;
		if ($file) {
			while (($buffer = fgets($file, 4096)) !== false) {
		        if ($item_id === trim($buffer)) {
		        	$temp = 1;
		        	break;
		        }    
		    }
			if ($temp == 0) {
				fwrite($file,$item_id . PHP_EOL);
			}
			fclose($file);
		}

		exit();
	}

	if (isset($_POST['item_id_myTrade']) && isset($_POST['uname']) && isset($_POST['sname'])) {
		$school = $_POST['sname'];
		$username = $_POST['uname'];
		$item_id = $_POST['item_id_myTrade'];

		$file_path = "./users/" . $school . "/" . $username . "/" . "tradeList.txt";

		$myfile = fopen($file_path,'a+') or die("Unable to open file!");
		$temp = 0;
		if ($myfile) {
			while (($buffer = fgets($myfile, 4096)) !== false) {
		        if ($item_id === trim($buffer)) {
		        	$temp = 1;
		        	break;
		        }    
		    }
			if ($temp == 0) {
				fwrite($myfile,$item_id . PHP_EOL);
			}
			fclose($myfile);
		}

		exit();
	}

	if (isset($_POST['item_id_recent']) && isset($_POST['uname']) && isset($_POST['sname'])) {
		$school = $_POST['sname'];
		$username = $_POST['uname'];
		$item_id = $_POST['item_id_recent'];

		$rv_path = "./users/" . $school . "/" . $username . "/" . "recentList.txt";

		$file = fopen($rv_path,'a+') or die("Unable to open file!");
		$temp = 0;
		if ($file) {
			while (($buffer = fgets($file, 4096)) !== false) {
		        if ($item_id === trim($buffer)) {
		        	$temp = 1;
		        	break;
		        }    
		    }
			if ($temp == 0) {
				fwrite($file,$item_id . PHP_EOL);
			}
			fclose($file);
		}

		exit();
	}

	if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
		$email = $_COOKIE['email'];
        $password = $_COOKIE['password'];
        $mysqli = new mysqli("localhost","zshifour_zhongwu","307442570szw","ezcampus");
	    if (mysqli_connect_errno()) {
	        printf("Connect failed: %s\n", mysqli_connect_error());
	        exit();
	    }
        if ($stmt = $mysqli->prepare("SELECT activated,name,school FROM users WHERE email=? and password=?")) {
            $stmt->bind_param("ss",$email,$password);
            $stmt->execute();
            mysqli_stmt_store_result($stmt);
            $check_count = $stmt->num_rows;
            $stmt->bind_result($activated,$username,$school);
            while ($stmt->fetch()) {} // $activated,$username,$school are only valid after this
            setcookie('username',$username,time()+24*60*60*3);
            
            mysqli_stmt_free_result($stmt);
            $stmt->close();

        } else {
            printf("prepare error");
            exit();
        }
        if ($check_count == 0) {
            header("login.php");
            exit();
        } else if ($activated == 0) {
        	echo "Please go to your email to activate the account!<br />";
        	echo "Click <a href='process.php'>here</a> to go back to log in page.";
        	exit();
        } else {
        	if (!isset($_GET['school']) || !isset($_GET['name'])) {
        		$append_url = "?name=".$username."&school=".$school;
            	header('Location: user.php'.$append_url);
            	exit();
        	}

        	$username1 = str_replace(' ', '_', $username);
        	$school1 = str_replace(' ', '_', $school);

        	$file_path = "./users/" . $school1 . "/" . $username1 . "/" . "tradeList.txt";
        	if (file_exists($file_path)) {
        		addToTradeList($file_path,$mysqli,$tradeList);
        	}

        	$rv_path = "./users/" . $school1 . "/" . $username1 . "/" . "recentList.txt";
        	if (file_exists($rv_path)) {
        		addToRecentList($rv_path,$mysqli,$recentList);
        	}
        }

	} else {
		header("Location: login.php");
		exit();
	}

?>

<?php 
	$buy_list = array();
	$sell_list = array();
	$buy_counter = 0;
	$sell_counter = 0;
    if ($stmt = $mysqli->prepare(
    	"SELECT tradingItem.name,category,buyer,price,img,description,put_date, email,tradingItem.id FROM tradingItem LEFT JOIN 
    	users ON tradingItem.buyer = users.name WHERE 
    	CHAR_LENGTH(buyer) > 0 AND
    	CHAR_LENGTH(seller) = 0 ORDER BY put_date DESC")) {
        $stmt->execute();
        mysqli_stmt_store_result($stmt);
        $check_count = $stmt->num_rows;
        $stmt->bind_result($name,$category,$buyer,$price,$img,$description,$put_date,$email,$item_id);

        while ($stmt -> fetch()) {
        	$buy_list[$buy_counter] = array($name,$category,$buyer,$price,substr($img,0,3),substr($img,3,30),$description,$put_date,$email,$item_id);
        	$buy_counter++;

        }
        mysqli_stmt_free_result($stmt);
        $stmt->close();

    } else {
    	die('prepare() failed: ' . htmlspecialchars($mysqli->error));
    }

    if ($stmt = $mysqli->prepare(
    	"SELECT tradingItem.name,category,seller,price,img,description,put_date, email,tradingItem.id FROM tradingItem
    	LEFT JOIN users ON tradingItem.seller = users.name WHERE 
    	CHAR_LENGTH(seller) > 0 AND
    	CHAR_LENGTH(buyer) = 0 ORDER BY put_date DESC")) {
        $stmt->execute();
        mysqli_stmt_store_result($stmt);
        $check_count = $stmt->num_rows;
        $stmt->bind_result($name,$category,$seller,$price,$img,$description,$put_date,$email,$item_id);

        while ($stmt -> fetch()) {
        	$sell_list[$sell_counter] = array($name,$category,$seller,$price,substr($img,0,3),substr($img,3,30),$description,$put_date,$email,$item_id);
        	$sell_counter++;
        }
        mysqli_stmt_free_result($stmt);
        $stmt->close();

    } else {
        printf("prepare error");
    }

?>

<?php

	function addToRecentList($file_path, $mysqli,$recentList) {
		global $recentList;
		$file = fopen($file_path,'r') or die("Unable to open file!");

		if ($file) {
			while (($line = fgets($file, 4096)) !== false) {
				if ($stmt = $mysqli->prepare("SELECT name FROM tradingItem WHERE id = ?")) {
			        $stmt->bind_param("s",intval(trim($line)));
			        $stmt->execute();
			        mysqli_stmt_store_result($stmt);
			        $check_count = $stmt->num_rows;
			        $stmt->bind_result($name);
			        while ($stmt->fetch()) {}
			        array_push($recentList,array($name,intval(trim($line))));    
			        mysqli_stmt_free_result($stmt);
			        $stmt->close();
			    } else {
			        printf("prepare error");
			    }
	        }    
	    }

		fclose($file);

	}

	function addToTradeList($file_path, $mysqli,$tradeList) {
		global $tradeList;
		$file = fopen($file_path,'r') or die("Unable to open file!");

		if ($file) {
			while (($line = fgets($file, 4096)) !== false) {
				if ($stmt = $mysqli->prepare("SELECT name FROM tradingItem WHERE id = ?")) {
			        $stmt->bind_param("s",intval(trim($line)));
			        $stmt->execute();
			        mysqli_stmt_store_result($stmt);
			        $check_count = $stmt->num_rows;
			        $stmt->bind_result($name);
			        while ($stmt->fetch()) {}
			        array_push($tradeList,array($name,intval(trim($line))));    
			        mysqli_stmt_free_result($stmt);
			        $stmt->close();
			    } else {
			        printf("prepare error");
			    }
	        }    
	    }

		fclose($file);
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome To Ezcampus!</title>
<link rel="stylesheet" type="text/css" href="css/user.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>

<script>
	$(document).ready(function(){
	  $(".ezCategory").click(function(){
	    if($(this).hasClass("chosen")) {
	    	$(".unchosen").slideToggle();
	    }
	    if ($(this).hasClass("unchosen")) {
	  		$(".chosen").before($(this));
		  	$(".chosen").removeClass("chosen").addClass("unchosen");
		  	$(this).removeClass("unchosen").addClass("chosen");
		  	$(".unchosen").css("display","none");
	  	}

	  });


	  var buy_num = "<?php echo $buy_counter; ?>";
	  var buy_item = <?php echo json_encode($buy_list); ?>;
	  for (var i = 0; i <buy_num; i++) {
	  	var count = i+1;
	  	var buy_source = "img/item/buy/";
	  	var buy_category = buy_item[i][1];
	  	var buy_image_name = buy_item[i][5];
	  	var buy_image_type = buy_item[i][4];
	  	var buy_item_id = buy_item[i][9];
	  	buy_source = buy_source + buy_category + "/" + buy_image_name + "." + buy_image_type;
	  	var buy_pic_num = "#buypic" + count;
	  	var temp = "<div name='b" + buy_item_id + "' class='ezPageMainMiddlePic buy' id='ezPageMainMiddlePicBuy" + count + "'></div>";
	  	$("#ezPageMainMiddleLeft").append(temp);
	  	temp = "<img id='buypic" + count + "' class='displayPic' src='" + buy_source + "' />";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  	temp = "<label id='buyprice" + count + "' class='priceTag'>$" + buy_item[i][3] + "</label>";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  	temp = "<label id='buycontact" + count + "' class='contactTag'>Sell&nbspit!</label>";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  	temp = "<label id='buydetail" + count + "' class='detailTag'>Detail...</label>";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  	temp = "<label id='buyemail" + count + "' class='emailTag' style='visibility:hidden'>" + buy_item[i][8] + "</label>";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  	temp = "<div id='buydes" + count + "' class='desTag' style='visibility:hidden'>"
	  			+ "<h4>Description: </h4><br><div><p>" + buy_item[0][6] + "</p></div></div>";
	  	$("#ezPageMainMiddlePicBuy" + count).append(temp);
	  }

	  var sell_num = "<?php echo $sell_counter; ?>";
	  var sell_item = <?php echo json_encode($sell_list); ?>;
	  for (var i = 0; i <sell_num; i++) {
	  	var count = i+1;
	  	var sell_source = "img/item/sell/";
	  	var sell_category = sell_item[i][1];
	  	var sell_image_name = sell_item[i][5];
	  	var sell_image_type = sell_item[i][4];
	  	var sell_item_id = sell_item[i][9];
	  	sell_source = sell_source + sell_category + "/" + sell_image_name + "." + sell_image_type;
	  	var sell_pic_num = "#sellpic" + count;
	  	var temp = "<div name='b" + sell_item_id + "' class='ezPageMainMiddlePic sell' id='ezPageMainMiddlePicSell" + count + "'></div>";
	  	$("#ezPageMainMiddleRight").append(temp);
	  	temp = "<img id='sellpic" + count + "' class='displayPic' src='" + sell_source + "' />";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  	temp = "<label id='sellprice" + count + "' class='priceTag'>$" + sell_item[i][3] + "</label>";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  	temp = "<label id='sellcontact" + count + "' class='contactTag'>Buy&nbspit!</label>";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  	temp = "<label id='selldetail" + count + "' class='detailTag'>Detail...</label>";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  	temp = "<label id='sellemail" + count + "' class='emailTag' style='visibility:hidden'>" + sell_item[i][8] + "</label>";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  	temp = "<div id='selldes" + count + "' class='desTag' style='visibility:hidden'>"
	  			+ "<h4>Description: </h4><br><div><p>" + sell_item[0][6] + "</p></div></div>";
	  	$("#ezPageMainMiddlePicSell" + count).append(temp);
	  }





	  var tradeList = <?php echo json_encode($tradeList); ?>;
	  var recentList = <?php echo json_encode($recentList); ?>;
	  for (var i = 0; i < tradeList.length; i++) {
	  	var url = "'item.php?id=" + tradeList[i][1] + "&name=" + tradeList[i][0] + "'";
	  	$("#myTradeList").append("<li><a href=" + url + ">" + tradeList[i][0] + '</a></li>');
	  }

	  for (var i = 0; i < recentList.length; i++) {
	  	var url = "'item.php?id=" + recentList[i][1] + "&name=" + recentList[i][0] + "'";
	  	$("#recentlyVisitedList").append("<li><a href=" + url + ">" + recentList[i][0] + '</a></li>');
	  }



	  


	  $(".contactTag").click(function() {
	  	var contactTag = $(this);
	  	var block = contactTag.parent();
	  	var item_id = block.attr("name").substring(1);
	  	var email = contactTag.next().next();
	  	if (email.css("visibility") == "hidden") {
	  		showContact(contactTag);
	  	} else {
	  		hideContact(contactTag);
	  	}

	  	var username = <?php echo json_encode($_GET['name']); ?>;
	  	var school = <?php echo json_encode($_GET['school']); ?>;
	  	username = username.split(' ').join('_');
	  	school = school.split(' ').join('_');
	  	$.ajax({
        type:"POST",
        url:"user.php",
        data: {item_id:item_id,uname:username,sname:school},
        dataType: "text"}).done(function(msg) {

        }).fail(function(msg) {
            alert("ERROR");
        });

	  });

	  $(".detailTag").click(function() {
	  	var detailTag = $(this);
	  	if (detailTag.css("visibility") == "visible") {
	  		showDetail(detailTag);
	  	}

	  	var block = detailTag.parent();
	  	var item_id_recent = block.attr("name").substring(1);
	  	var username = <?php echo json_encode($_GET['name']); ?>;
	  	var school = <?php echo json_encode($_GET['school']); ?>;
	  	username = username.split(' ').join('_');
	  	school = school.split(' ').join('_');
	  	$.ajax({
        type:"POST",
        url:"user.php",
        data: {item_id_recent:item_id_recent,uname:username,sname:school},
        dataType: "text"}).done(function(msg) {

        }).fail(function(msg) {
            alert("ERROR");
        });

	  });

	  $("body").on("click","#itemShowContact",function() {
	  	$("#itemEmail").slideToggle();
	  	var block = $("#itemShowContact").parent();
	  	var item_id_myTrade = block.attr("name").substring(1);
	  	var username = <?php echo json_encode($_GET['name']); ?>;
	  	var school = <?php echo json_encode($_GET['school']); ?>;
	  	username = username.split(' ').join('_');
	  	school = school.split(' ').join('_');
	  	$.ajax({
        type:"POST",
        url:"user.php",
        data: {item_id_myTrade:item_id_myTrade,uname:username,sname:school},
        dataType: "text"}).done(function(msg) {

        }).fail(function(msg) {
            alert("ERROR");
        });

	  });

	  $("body").on("click","#itemGoBack",function() {
	  	var itemGoBack = $(this);
	  	hideDetail(itemGoBack);  	
	  	
	  });

	  $("body").on("click","#myTrades",function() {
	  	$("#myTradeList").slideToggle();
	  });
	});

	function showDetail(detailTag) {
	  	var contactTag = detailTag.prev();
	  	var priceTag = contactTag.prev();
	  	var img = priceTag.prev();
	  	var block = detailTag.parent();
	  	var email = detailTag.next();
	  	var des = email.next();
	  	var buy_item = <?php echo json_encode($buy_list); ?>;
	  	var sell_item = <?php echo json_encode($sell_list); ?>;
	  	if (priceTag.attr("id") == "buyprice1") {
	  		var name = buy_item[0][0];
	  		var statue = "Sell it!";
	  	} else if (priceTag.attr("id") == "buyprice2") {
	  		var name = buy_item[1][0];
	  		var statue = "Sell it!";
	  	} else if (priceTag.attr("id") == "sellprice1") {
	  		var name = sell_item[0][0];
	  		var statue = "Buy it!";
	  	} else if (priceTag.attr("id") == "sellprice2") {
	  		var name = sell_item[1][0];
	  		var statue = "Buy it!";
	  	}
	  	$(".buy").css("display","none");
	  	$(".sell").css("display","none");
	  	$(detailTag).css("visibility","hidden");
	  	$(contactTag).css("visibility","hidden");
	  	$(priceTag).css("visibility","hidden");
	  	
	  	block.prepend("<h3 id='namePlusPrice'>" + name + " : " + priceTag.text() +  "</h3>");
	  	$("#ezPageMainMiddleLeft").css("border","none");
	  	img.css({"margin-left":"100px","margin-top":"20px"});
	  	
	  	des.css('visibility','visible');

	  	block.append("<label id='itemShowContact'>" + statue + "</label>")
	  	block.append("<label id='itemGoBack'>Go Back</label>");
	  	block.append("<label id='itemEmail' style='display:none;'>" + email.text() + "</label>");


	  	if ($(block).hasClass("sell")) {
	  		$(block).css("display","block");
		  	block.animate({
				width:"500px",
				height:"600px",
				marginLeft:"-250px"
			},500);


	  	} else if ($(block).hasClass("buy")) {
	  		$(block).css("display","block");
	  		block.animate({
	  		width:"500px",
			height:"600px",
			marginLeft:"80px"
	  		},500);
	  	}

	}

	function hideDetail(itemGoBack) {
		var block = itemGoBack.parent();
	  	block.animate({
  		width:"300px",
		height:"300px",
		marginLeft:"15px"
  		},500);
	  	$("#itemShowContact").remove();
	  	$("#itemGoBack").remove();
	  	$("#itemEmail").remove();
	  	$("#namePlusPrice").remove();
	  	$(".desTag").css("visibility","hidden");
	  	$("#ezPageMainMiddleLeft").css("border-right","dashed gray 2px");

	  	var img = block.children(":first");
	  	var priceTag = img.next();
	  	var contactTag = priceTag.next();
	  	var detailTag = contactTag.next();
	  	img.css({"margin-left":"0","margin-top":"0"});
	  	$(".buy").css("display","block");
	  	$(".sell").css("display","block");
	  	$(detailTag).css("visibility","visible");
	  	$(contactTag).css("visibility","visible");
	  	$(priceTag).css("visibility","visible");
	}

	function showContact(element) {
		var detailTag = element.next();
		var email = element.next().next();
		var block = element.parent();
		block.animate({
			height: "320px"
		},500);
		email.css({"visibility":"visible","float":"left","margin-left":"20px"});
	}

	function hideContact(element) {
		var detailTag = element.next();
		var email = element.next().next();
		var block = element.parent();
		block.animate({
			height: "300px"
		},500);
		email.css("visibility","hidden");
	}

	function loadsell() {
		$("#ezPageMainMiddleContainer").load("sell.php");
	}

	function loadbuy() {
		$("#ezPageMainMiddleContainer").load("buy.php");
	}

</script>

<body>
	<div id="ezPageTop1">
		<div id="ezPageTop1Container">
			<div id="ezPageTop1Logo"><img src="img/EZ.png" height="70" width="100" /></div>
			<div id="ezPageTop1Right">
				<ul id="ezPageTop1UL">
					<li class="ezPageTop1List" id="ezPageTop1Notification">
						<img src="lib/glyphicons/png/glyphicons_127_message_flag.png" />
					</li>
					<li class="ezPageTop1List" id="ezPageTop1Cart">
						<img src="lib/glyphicons/png/glyphicons_202_shopping_cart.png" />
					</li>
					<li class="ezPageTop1List" id="ezPageTop1Name">
						<?php echo $_COOKIE['username'] ?>
					</li>
					<li class="ezPageTop1List" id="ezPageTop1Logout">
						<button onclick="window.location='process.php';">Log out</button>
					</li>

				</ul>
			</div>
		</div>
	</div>
	<div id="ezPageTop2">
		<div id="ezPageTop2Container">
			<div id="ezPageTop2NavBar">
				<ul id="ezPageTop2UL">
					<li class="ezPageTop2List" id="ezPageTop2Trade">
						Trade
					</li>
					<li class="ezPageTop2List" id="ezPageTop2Courses">
						Courses
					</li>
					<li class="ezPageTop2List" id="ezPageTop2Events">
						Events
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="ezPageMain">
		<div id="ezPageMainSearchSell">
			<div id="ezPageMainCategoryButton">
				<ul>
					<li id="textbook" class="ezCategory chosen"><button>textbooks</button></li>
					<li id="furniture" class="ezCategory unchosen"><button>furnitures</button></li>
					<li id="electronics" class="ezCategory unchosen"><button>Electronics</button></li>
				</ul>
			</div>
			<div id="ezPageMainSearchBar">
				<div id="ezPageMainSearchInput">
					<textarea placeholder="anything you want"></textarea>
				</div>
				<div id="ezPageMainSearchButton">
					<button>Search</button>
				</div>
			</div>
			<div id="ezPageMainSellButton">
				<button onclick="loadsell();">Sell</button>
			</div>
			<div id="ezPageMainBuyButton">
				<button onclick="loadbuy();">Buy</button>
			</div>
		</div>
		<div id="ezPageMainContainer">
			<div id="ezPageMainLeftBar">
				<ul>
					<li><h3 id="myTrades">My Trades</h3><ul id="myTradeList">
						
					</ul></li>
					<li><h3 id="myCourses">My Courses</h3><ul id="myCourseList">
						
					</ul></li>
					<li><h3 id="myEvents">My Eventss</h3><ul id="myEventList">
						
					</ul></li>
					<li></li>
				</ul>
			</div>
			<div id="ezPageMainMiddleContainer">
				<div id="ezPageMainMiddleLeft">
					<div id="ezPageMainMiddleLeftTitle">
					<h3>What people want to buy...</h3>
					</div>

				
				</div>
				<div id="ezPageMainMiddleRight">
					<div id="ezPageMainMiddleRightTitle">
					<h3>What people want to sell...</h3>
					</div>


				</div>
			</div>
			<div id="ezPageMainRightBar">
				<h3>Recently visited</h3>
				<ul id="recentlyVisitedList">
					
				</ul>
			</div>
		</div>
	</div>
	<div id="ezPageButtom">
		
	</div>
</body>
<script>
	$("body").show();
</script>
</html>