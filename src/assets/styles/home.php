<?php 
header("Content-type: text/css; charset: UTF-8");
require('../../config.php');
?>

/* PARALLAX */
.parallax {
	margin-top:100px;
	background-image: url(<?php echo BASE_URL; ?>assets/img/header/434949.jpg);
	height:200px;
	background-attachment: fixed;
	background-position: center;
	background-repeat: no-repeat;
	background-size:cover;
	color: white;
}

.parallax-container {
	height:100%;
	display:flex;
	flex:1;
	flex-direction:column;
	justify-content:space-around;
}

.parallax-title {
	font-weight:bold;
}

.parallax-title, 
.parallax-captions {
	display:flex;
	flex:1;
	align-items:center;
}

.parallax-captions {
	justify-content:space-around;
	font-size:30px;
	font-weight:lighter;
}

.parallax-title {
	justify-content:center;
}

body {
	background-image:none!important;
	background-color:#eee!important;
}


/* CENTRAL AREA */
#area-central {
	display:flex;
	flex:1;
	flex-direction:column;
	height:100%;
}


/* AREA */
.area {
	padding:30px 0;
}

.area-title {
	margin-bottom:30px;
}

/* BUNDLES EXPLORE */
.bundles-explore {
	display:flex;
	flex:1;
	flex-direction:column;
	height:100%;
}

.search-results {
	display: flex;
	flex:1;
	justify-content:space-around;
	flex-wrap:wrap;
}

.order-by-options {
	display: flex;
	justify-content:space-between;
	align-items:center;
}

.order-by-options .form-group {
	margin-bottom:0;	
}

#bundle-search-results {
	display:none;
}