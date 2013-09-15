<?php

function reportError($msg, $status = 400) {
	header('Content-Type: text/plain', true, $status);
	echo $msg;
	exit();
}

//do some error checking
//method must be post
if (strtolower($_SERVER['REQUEST_METHOD']) != "post")
	reportError('You must POST to this page!');

//post params must include a 'cart' parameter
if (NULL == $_POST['cart'])
	reportError("You must post JSON in a form field named 'cart'!");

//try to decode the cart
$cart = json_decode(stripslashes($_POST['cart']));
if (NULL == $cart)
	reportError("Couldn't parse JSON in 'cart' field. Check for syntax errors.\n\nHere is what you sent:\n" . stripslashes($_POST['cart']));

//cart must have name, address1, zip, phone, and items
if (NULL == $cart->name)
	reportError("Cart must have a 'name' property!");
if (NULL == $cart->address1)
	reportError("Cart must have an 'address1' property!");
if (NULL == $cart->zip)
	reportError("Cart must have a 'zip' property!");
if (NULL == $cart->phone)
	reportError("Cart must have a 'phone' property!");
if (NULL == $cart->items)
	reportError("Cart must have a 'items' property!");
if (0 == count($cart->items))
	reportError("No items passed in the 'items' property!");

//load our prices.json into an assoc array
$prices = json_decode(file_get_contents('prices.json'), true);
if (NULL == $prices)
	reportError('Unable to load the prices.json file on the server. Please report this to your instructor.');

//check each item
//also ensure that subtotal is at least $20
$subtotal = 0;
foreach ($cart->items as $item) {
	//must have a name
	if (NULL == $item->name)
		reportError("All items must have a name property!");

	//type needs to be in prices list
	if (NULL == $prices[$item->type])
		reportError ("'$item->type' is not a valid item type! Must be 'pizza', 'drink', or 'dessert'.");

	//if type is pizza, there must be a size prop
	if ('pizza' == $item->type && NULL == $item->size)
		reportError("You must specify a size for pizza '$item->name'!");

	//if size prop, make sure it is 'small', 'medium', or 'large'
	if ($item->size && 'small' != $item->size && 'medium' != $item->size && 'large' != $item->size)
		reportError("Size '$item->size' specified for item '$item->name' is not valid! Must be 'small', 'medium', or 'large'.");

	//validate name against price list
	$price = $prices[$item->type][$item->name];
	if (NULL == $price)
		reportError("'$item->name' is not a valid item name for the type $item->type!");

	//if item type was pizza, $price is an array
	//get right entry based on size
	if ('pizza' == $item->type) {

		switch (strtolower($item->size)) {
			case 'small':
				$price = $price[0];
				break;
			case 'medium':
				$price = $price[1];
				break;
			case 'large':
				$price = $price[2];
				break;
		}
	}

	//default quantity to 1
	if (NULL == $item->quantity || 0 == $item->quantity)
		$item->quantity = 1;

	//no fractional quantities
	$item->quantity = round($item->quantity, 0);

	//calc extended price
	$extendedPrice = $price * $item->quantity;

	//add extended price to subtotal
	$subtotal += $extendedPrice;

	//add extendedPrice to item so that we don't have to look it up again
	$item->price = $price;
	$item->extendedPrice = $extendedPrice;
}

if ($subtotal < 20)
	reportError("Minimum order amount is $20! Your subtotal is only $ $subtotal");

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
						<th class="label-qty">Qty</th>
						<th class="label-money">Ext Price</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach ($cart->items as $item) {
						echo '<tr>';
						echo '<td>' . htmlentities($item->name);
						if ($item->size)
							echo ' (' . htmlentities($item->size) . ')';
						echo '</td>';

						echo '<td class="money">$' . $item->price . '</td>';

						echo '<td class="qty">' . $item->quantity . '</td>';

						//we already looked up and stored the extended price of each item
						//when we validated above, so just use that stored price
						//we also calc'd the subtotal, so no need to recalc that again here

						echo '<td class="money">$' . number_format($item->extendedPrice, 2) . '</td>';
						echo '</tr>';
					}
					?>		
					<tr>
						<td colspan="3" class="label-sub-total">Sub-Total:</td>
						<td class="money">$<?php echo number_format($subtotal, 2) ?></td>
					</tr>
					<tr>
						<td colspan="3" class="label-tax">Tax:</td>
						<td class="money">$<?php echo number_format(round($subtotal * 0.095, 2), 2) ?></td>
					</tr>
					<tr>
						<td colspan="3" class="label-total">Total:</td>
						<td class="money total">$<?php echo number_format(round($subtotal * 1.095, 2), 2) ?></td>
					</tr>
				</tbody>
			</table>

			<?php
			if (NULL != $cart->nextUrl) {
				$nextCaption = $cart->nextCaption;
				if (NULL == $nextCaption)
					$nextCaption = 'Back to Home Page';

				echo '<div class="next-url"><a class="btn btn-primary" href="' . $cart->nextUrl . '">';
				echo htmlentities($nextCaption) . '</a></div>';
			}
			?>
		</div> <!-- .container -->
	</body>
</html>
