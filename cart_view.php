<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<h1 class="page-header">Votre panier</h1>
	        		<div class="box box-solid">
	        			<div class="box-body">
		        		<table class="table table-bordered">
		        			<thead>
		        				<th></th>
		        				<th>Photo</th>
		        				<th>Nom</th>
		        				<th>Prix</th>
		        				<th width="20%">Quantité</th>
		        				<th>Sous-total</th>
		        			</thead>
		        			<tbody id="tbody">
		        			</tbody>
		        		</table>
	        			</div>
	        		</div>
	        		<?php
	        			if(isset($_SESSION['user'])){
							echo "
								<form method='post'>
							";
							echo "
								<input type=\"submit\" name=\"buy\"  style=\"background: coral; border: 0; width: 100%; height: 50px; color: white; font-size: 25px;\" value=\"Acheter !\" />
							";
							echo "
								</form>
							";
	        			}
	        			else{
	        				echo "
	        					<h4><a href='login.php'>Connectez-vous</a> pour payer.</h4>
	        				";
	        			}
	        		?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  	<?php $pdo->close(); ?>
  	<?php include 'includes/footer.php'; ?>
</div>

<?php
	if(array_key_exists('buy', $_POST)) {
		$conn = $pdo->open();

		try{
			// $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id=:id");
			// $stmt->execute(['id'=>$_SESSION['user']]);
			// $id = $stmt->fetch();

			$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
			$stmt->execute(['user_id'=>$_SESSION['user'], 'pay_id'=>uniqid('pay_'), 'sales_date'=>date("Y-m-d")]);

			// njib product id o quantity mel cart
			$stmt = $conn->prepare("SELECT product_id FROM cart WHERE user_id = :user_id");
			$stmt->execute(['user_id'=>$_SESSION['user']]);
			$product = $stmt->fetch();

			$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = :user_id");
			$stmt->execute(['user_id'=>$_SESSION['user']]);
			$quantity = $stmt->fetch();

			$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
			$stmt->execute(['user_id'=>$_SESSION['user'], 'pay_id'=>uniqid('pay_'), 'sales_date'=>date("Y-m-d")]);
			$sale_id = $conn->lastInsertId();

			$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
			$stmt->execute(['sales_id'=>$sale_id, 'product_id'=>$product, 'quantity'=>$quantity]);

			$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
			$stmt->execute(['user_id'=>$_SESSION['user']]);

		}
		catch(PDOException $e){
			echo "Un problème de connexion est survenu: " . $e->getMessage();
		}

		$pdo->close();

		header('location: index.php');

		echo '<script>alert("Achat effectué avec succès")</script>';
	}
?>

<?php include 'includes/scripts.php'; ?>
<script>
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_delete.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		if(qty>1){
			qty--;
		}
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		qty++;
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	getDetails();
	getTotal();

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_details.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			total = response;
		}
	});
}
</script>
<!-- Paiment -->
<script>
payment: function(data, actions) {
	return actions.payment.create({
		payment: {
			transactions: [
				{
					//total purchase
					amount: { 
						total: total, 
						currency: '€' 
					}
				}
			]
		}
	});
},

onAuthorize: function(data, actions) {
	return actions.payment.execute().then(function(payment) {
		window.location = 'sales.php?pay='+payment.id;
	});
},
</script>
</body>
</html>