<?php
//do some error checking
//method must be post
if (strtolower($_SERVER['REQUEST_METHOD']) != "post")
	die('You must POST to this page!');

//post params must include a 'cart' parameter
if (NULL == $_POST['cart'])
	die("You must post JSON in a form field named 'cart'!");

//try to decode the cart
$cart = json_decode(stripslashes($_POST['cart']));
if (NULL == $cart)
	die("Couldn't decode the JSON passed in the 'cart' field. This is what you passed: " . $_POST['cart']);

//cart must have name, address1, zip, phone, and items
if (NULL == $cart->name)
	die("Cart must have a 'name' property!");
if (NULL == $cart->address1)
	die("Cart must have an 'address1' property!");
if (NULL == $cart->zip)
	die("Cart must have a 'zip' property!");
if (NULL == $cart->phone)
	die("Cart must have a 'phone' property!");
if (NULL == $cart->items)
	die("Cart must have a 'items' property!");
if (0 == count($cart->items))
	die("No items passed in the 'items' property!");

//load our prices.json into an assoc array
$prices = json_decode(file_get_contents('prices.json'), true);
if (NULL == $prices)
	die('Unable to load the prices.json file on the server. Please report this to your instructor.');

//check each item
foreach ($cart->items as $item) {
	//must have a name
	if (NULL == $item->name)
		die ("All items must have a name property!");

	//type needs to be in prices list
	if (NULL == $prices[$item->type])
		die ("'$item->type' is not a valid item type! Must be 'pizza', 'drink', or 'dessert'.");

	//validate name against price list
	if (NULL == $prices[$item->type][$item->name])
		die ("'$item->name' is not a valid item name for the type $item->type!");

	//if size prop, make sure it is 'small', 'medium', or 'large'
	if ($item->size && 'small' != $item->size && 'medium' != $item->size && 'large' != $item->size)
		die ("Size '$item->size' specified for item '$item->name' is not valid! Must be 'small', 'medium', or 'large'.");
}

//running sub-total amount
$subtotal = 0;

?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Order Confirmation</title>
		<link rel="stylesheet" href="../lib/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/homework.css">
	</head>
	<body>
		<div class="container">
			<div class="page-header order-confirm-title">
				<h1>Order Confirmation</h1>
				<p class="lead">We'll have this out to you in about 45 minutes!</p>
			</div>

			<p><strong>Deliver To:</strong> <br>
				<?php echo htmlentities($cart->name) ?> <br>
				<?php echo htmlentities($cart->address1) ?> <br>
				<?php if ($cart->address2) echo htmlentities($cart->address2) . '<br>' ?>
				Seattle, WA <?php echo htmlentities($cart->zip) ?>
			</p>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Item</th>
						<th class="label-money">Price</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$pizzaPrices = NULL;
					$price = 0;
					foreach ($cart->items as $item) {
						echo '<tr>';
						echo '<td>' . htmlentities($item->name);
						if ($item->size)
							echo ' (' . htmlentities($item->size) . ')';
						echo '</td>';

						//get the price of the item
						//if it's a pizza, need to key on size
						if ('pizza' == $item->type) {
							$pizzaPrices = $prices['pizza'][$item->name];

							switch (strtolower($item->size)) {
								case 'small':
									$price = $pizzaPrices[0];
									break;
								case 'medium':
									$price = $pizzaPrices[1];
									break;
								case 'large':
									$price = $pizzaPrices[2];
									break;
							}
						} 
						else {
							$price = $prices[$item->type][$item->name];
						}

						//add to subtotal and display
						$subtotal += $price;

						echo '<td class="money">$' . number_format($price, 2) . '</td>';
						echo '</tr>';
					}
					?>		
					<tr>
						<td class="label-sub-total">Sub-Total:</td>
						<td class="money">$<?php echo number_format($subtotal, 2) ?></td>
					</tr>
					<tr>
						<td class="label-tax">Tax:</td>
						<td class="money">$<?php echo number_format(round($subtotal * 0.095, 2), 2) ?></td>
					</tr>
					<tr>
						<td class="label-total">Total:</td>
						<td class="money total">$<?php echo number_format(round($subtotal * 1.095, 2), 2) ?></td>
					</tr>
				</tbody>
			</table>



			<?php
			if (NULL != $cart->nextUrl) {
				$nextText = $cart->nextText;
				if (NULL == $nextText)
					$nextText = 'Back to Home Page';

				echo '<div class="next-url"><a class="btn btn-primary" href="' . $cart->nextUrl . '">';
				echo htmlentities($nextText) . '</a></div>';
			}
			?>
		</div> <!-- .container -->
	</body>
</html>
