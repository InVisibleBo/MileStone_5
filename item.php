<?php
	
	if (isset($_GET['id']) && isset($_GET['name'])) {
		$id = intval($_GET['id']);
		$name = $_GET['name'];
		
		$mysqli = new mysqli("localhost","zshifour_zhongwu","307442570szw","ezcampus");
	    if (mysqli_connect_errno()) {
	        printf("Connect failed: %s\n", mysqli_connect_error());
	        exit();
	    }
        if ($stmt = $mysqli->prepare("SELECT category,buyer,seller,price,img,description,put_date,type FROM tradingItem WHERE id = ? AND name= ?")) {
          
            $stmt->bind_param("is",$id,$name);
            $stmt->execute();
            mysqli_stmt_store_result($stmt);
            $check_count = $stmt->num_rows;
            $stmt->bind_result($category,$buyer,$seller,$price,$img,$description,$put_date,$type);
            while ($stmt->fetch()) {} // $activated,$username,$school are only valid after this
            
            mysqli_stmt_free_result($stmt);
            $stmt->close();

        } else {
            printf("prepare error");
            exit();
        }

        $img_type = substr($img,0,3);
        $img_id = substr($img,3,30);
        if ($type == "s") {
        	$img_path = "img/item/sell/" . $category . "/" . $img_id . "." . $img_type;
        	$user_type = "Seller";
        } else if ($type = "b") {
        	$img_path = "img/item/buy/" . $category . "/" . $img_id . "." . $img_type;
        	$user_type = "Buyer";
        }



	} else {

		header("Location: user.php");
		exit();
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome To Ezcampus!</title>
<link rel="stylesheet" type="text/css" href="css/item.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>

<script>
	$(function() {
		var img_path = <?php echo json_encode($img_path) ?>;
		$("#itemImage").attr("src",img_path);
		$("#itemImage").load(function() {
			var width = $(this).width();
			var height = $(this).height();
			if (width >= height) {
				$(this).width(300);
				$(this).height(300/width*height);
			} else {
				$(this).height(300);
				$(this).width(300/height*width);
			}
		});
	});
</script>
<body>
	<div class="background"></div>
	<div class="subbackground"></div>
	<div class="itemDisplay">
		<br>
		<h2><strong><?php echo $name ?></strong></h2><br>
		<img src="#" id="itemImage" /><br>
		<label><strong><?php echo $user_type; ?>:</strong> <?php echo $seller; ?></label><br>
		<label><strong>Category:</strong> <?php echo $category; ?></label><br>
		<label><strong>Price:</strong> <?php echo $price; ?></label><br><br>
		<label><strong>Put Date:</strong> <?php echo $put_date; ?></label><br>
		<label><strong>Description:</strong> <?php echo $description; ?></label>

	</div>
</body>
</html>