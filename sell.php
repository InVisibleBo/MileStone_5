<?php

	if (isset($_POST['data']) && isset($_POST['itemName']) && isset($_POST['itemPrice']) && isset($_POST['description']) && isset($_POST['category'])) {
		$mysqli = new mysqli("localhost","zshifour_zhongwu","307442570szw","ezcampus");
		if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}

		$itemName = $_POST['itemName'];
		$itemCategory = $_POST['category'];
		$itemPrice = $_POST['itemPrice'];
		$description = $_POST["description"];
		$seller = $_COOKIE['username'];
		$encoded_data = $_POST['data'];
		$id = uniqid();
		$type = "s";
		if (substr($encoded_data,11,3) == "png") {
        	$data = str_replace('data:image/png;base64,', '', $encoded_data);
        	$data = str_replace(' ', '+', $data);
			$decoded_data = base64_decode($data);
			$file = 'img/item/sell/' . $itemCategory . '/' . $id . '.png';
			$id = "png" . $id;
        } else if (substr($encoded_data,11,3) == "gif") {
        	$data = str_replace('data:image/gif;base64,', '', $encoded_data);
        	$data = str_replace(' ', '+', $data);
			$decoded_data = base64_decode($data);
			$file = 'img/item/sell/' . $itemCategory . '/' . $id . '.gif';
			$id = "gif" . $id;
        } else if (substr($encoded_data,11,4) == "jpeg") {
        	$data = str_replace('data:image/jpeg;base64,', '', $encoded_data);
        	$data = str_replace(' ', '+', $data);
			$decoded_data = base64_decode($data);
			$file = 'img/item/sell/' . $itemCategory . '/' . $id . '.jpg';
			$id = "jpg" . $id;
        }
		if ($stmt = $mysqli->prepare("INSERT INTO tradingItem 
			(name,category,seller,price,img,description,transact,put_date,type) VALUES 
			(?,?,?,?,?,?,0,now(),?)")) {
	        $stmt->bind_param("sssssss",$itemName,$itemCategory,$seller,$itemPrice,$id,$description,$type);
	        $stmt->execute();

			file_put_contents($file,$decoded_data);
	        exit();
	    } else {
	        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	        exit();
	    }
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome To Ezcampus!</title>
<link rel="stylesheet" type="text/css" href="css/sell.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</head>

<script>
	function readURL(input){
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload= function (e) {
				$("#itemImageDisplay")
					.attr("src", e.target.result)
					.width(150)
					.height(100);
				$("#itemImageCode").text(e.target.result);
			};

			reader.readAsDataURL(input.files[0]);
		}
	}

	function saveSellItem() {
		var name = $("[name=itemName]").val();
		var price = parseInt($("[name=itemPrice").val());
		var image= $("#itemImageCode").text();
		var des = $("#descriptionInput").val();
		var cate = $("#itemCategory option:selected").text();
	
		if (name == "") {
			alert("name is empty");
			return false;
		} else if (cate == "Please choose category...") {
			alert("Please choose a category!");
			return false;
		} else if (!price || price <= 0) {
			alert("Please put a valid price!");
			return false;
		} else if (des == "") {
			alert("description is empty!");
			return false;
		} else {
			$.ajax({
				url:"sell.php",
				type:"POST",
				data:{data:image,itemName:name,itemPrice:price,description:des,category:cate}
			}).done(function(msg) {
				window.location = "user.php";
			}).fail(function(msg) {
				
			});
		}
	}
</script>

<body>
    <div id="ezSellFormContainer">
    	<div id="itemAttributes">
    		<label id="nameLabel">Name:</label><input id="nameInput" type="text" name="itemName"> <br><br>
    		<label id="categoryLabel">Category:</label>
    		<select id="itemCategory" name="itemCategory">
                <option selected>Please choose category...</option>
                <option value="textbook">textbook</option>
                <option value="furniture">furniture</option>
                <option value="other">other</option>
            </select> <br><br>
        	<label id="priceLabel">Price:</label><label id="priceUnit">$</label><input id="priceInput" type="text" name="itemPrice"> <br><br>
        	<label id="imageLabel">Image:</label><input id="imageInput" type="file" name="itemImage" accept="image/gif, image/jpeg, image/png, image/jpg" onchange="readURL(this);"> <br><br>
        	<img id="itemImageDisplay" src="#" height="0" width="0" alt="displayedImage">
        	<input id="itemImageCode" type="hidden"> <br><br>
        	<label id="descriptionLabel">Description:</label><br>
        	<textarea id="descriptionInput" row="5" col="20"></textarea><br><br>
        	<button id="itemSubmit"  onclick="saveSellItem();">Sell</button>
        	<button id="itemCancel" onclick="window.location='user.php';">Cancel</button>
    	</div>
    </div>
</body>
<script>
	$("body").show();
</script>
</html>