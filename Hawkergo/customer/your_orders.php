<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if (empty($_SESSION['user_id'])) {
	header('Location: login.php');
	exit;
}

$receipt_notice = '';
if (!empty($_SESSION['receipt_email_notice'])) {
    $receipt_notice = $_SESSION['receipt_email_notice'];
    unset($_SESSION['receipt_email_notice']);
}

$review_notice = null;
if (!empty($_SESSION['review_notice'])) {
    $review_notice = $_SESSION['review_notice'];
    unset($_SESSION['review_notice']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="#">
	<title>My Orders</title>
	<link href="../css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/font-awesome.min.css" rel="stylesheet">
	<link href="../css/animsition.min.css" rel="stylesheet">
	<link href="../css/animate.css" rel="stylesheet">
	<link href="../css/style.css" rel="stylesheet">
	<link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/reviews.css" rel="stylesheet">
	<style type="text/css" rel="stylesheet">
		.indent-small {
			margin-left: 5px;
		}

		.form-group.internal {
			margin-bottom: 0;
		}

		.dialog-panel {
			margin: 10px;
		}

		.datepicker-dropdown {
			z-index: 200 !important;
		}

		.panel-body {
			background: #e5e5e5;
			/* Old browsers */
			background: -moz-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
			/* FF3.6+ */
			background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%, #e5e5e5), color-stop(100%, #ffffff));
			/* Chrome,Safari4+ */
			background: -webkit-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
			/* Chrome10+,Safari5.1+ */
			background: -o-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
			/* Opera 12+ */
			background: -ms-radial-gradient(center, ellipse cover, #e5e5e5 0%, #ffffff 100%);
			/* IE10+ */
			background: radial-gradient(ellipse at center, #e5e5e5 0%, #ffffff 100%);
			/* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#e5e5e5', endColorstr='#ffffff', GradientType=1);
			font: 600 15px "Open Sans", Arial, sans-serif;
		}

		label.control-label {
			font-weight: 600;
			color: #777;
		}

		/* 
table { 
	width: 750px; 
	border-collapse: collapse; 
	margin: auto;
	
	}

/* Zebra striping */
		/* tr:nth-of-type(odd) { 
	background: #eee; 
	}

th { 
	background: #404040; 
	color: white; 
	font-weight: bold; 
	
	}

td, th { 
	padding: 10px; 
	border: 1px solid #ccc; 
	text-align: left; 
	font-size: 14px;
	
	} */
		*/ @media only screen and (max-width: 760px),
		(min-device-width: 768px) and (max-device-width: 1024px) {

			/* table { 
		  width: 100%; 
	}

	
	table, thead, tbody, th, td, tr { 
		display: block; 
	} */


			/* thead tr { 
		position: absolute;
		top: -9999px;
		left: -9999px;
	}
	
	tr { border: 1px solid #ccc; } */

			/* td { 
		
		border: none;
		border-bottom: 1px solid #eee; 
		position: relative;
		padding-left: 50%; 
	}

	td:before { 
		
		position: absolute;
	
		top: 6px;
		left: 6px;
		width: 45%; 
		padding-right: 10px; 
		white-space: nowrap;
		
		content: attr(data-column);

		color: #000;
		font-weight: bold;
	} */

		}
	</style>

</head>

<body>


	<?php $nav_current = 'orders'; include __DIR__ . '/../includes/customer_nav.php'; ?>
	<div class="page-wrapper">



		<div class="inner-page-hero">
			<div class="container"> </div>

		</div>
		<div class="result-show">
			<div class="container">
				<div class="row">


				</div>
			</div>
		</div>

		<section class="hawkerstalls-page">
			<div class="container">
				<div class="row">
					<div class="col-xs-12">
					</div>
					<div class="col-xs-12">
						<div class="bg-gray">
							<div class="row">

<?php if ($review_notice) { ?>
                                            <div class="col-xs-12">
                                                <div class="alert alert-<?php echo $review_notice['type'] === 'success' ? 'success' : 'danger'; ?>" role="alert">
                                                    <?php echo htmlspecialchars($review_notice['message']); ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($receipt_notice !== '') { ?>
                                            <div class="col-xs-12">
                                                <div class="alert alert-success" role="alert">
                                                    <?php echo htmlspecialchars($receipt_notice); ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="col-xs-12 mb-3">
                                            <p class="text-muted small mb-0"><i class="fa fa-info-circle"></i> Each checkout gets one queue number at that hawker stall. Ordering from a <strong>different stall</strong> starts again at #1. Multiple items in the same order share the same queue number.</p>
                                        </div>

                                        <table class="table table-bordered table-hover orders-table">
									<thead>
										<tr>

											<th>Item</th>
                                            <th>Hawker Stall</th>
											<th>Quantity</th>
											<th>Price</th>
                                            <th>Queue</th>
                                            <th>Code</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Reviews</th>
											<th>Action</th>

										</tr>
									</thead>
									<tbody>


										<?php
										$stmt = $db->prepare(
                                            "SELECT o.*, r.title AS stall_name
                                             FROM users_orders o
                                             LEFT JOIN hawkerstalls r ON o.rs_id = r.rs_id
                                             WHERE o.u_id = ?
                                             ORDER BY o.o_id DESC"
                                        );
										$stmt->bind_param('i', $_SESSION['user_id']);
										$stmt->execute();
										$query_res = $stmt->get_result();

										if ($query_res->num_rows === 0) {
											echo '<tr><td colspan="10"><center>You have No orders Placed yet. </center></td></tr>';
										} else {
                                            $stall_review_shown = array();
											while ($row = $query_res->fetch_assoc()) {
                                                $o_id = intval($row['o_id']);
                                                $rs_id = intval($row['rs_id']);
                                                $batch_id = isset($row['order_batch_id']) ? $row['order_batch_id'] : '';
                                                $is_closed = hawkergo_order_is_reviewable($row['status']);
                                                $has_dish_review = hawkergo_user_has_dish_review($db, $_SESSION['user_id'], $o_id);
                                                $stall_key = $rs_id . '|' . $batch_id;
                                                $has_stall_review = ($batch_id !== '' && $rs_id > 0)
                                                    ? hawkergo_user_has_stall_review($db, $_SESSION['user_id'], $rs_id, $batch_id)
                                                    : false;
                                                $show_stall_btn = $is_closed && $batch_id !== '' && $rs_id > 0 && !$has_stall_review && empty($stall_review_shown[$stall_key]);
                                                if ($show_stall_btn) {
                                                    $stall_review_shown[$stall_key] = true;
                                                }
												?>
												<tr>
													<td data-column="Item"> <?php echo $row['title']; ?></td>
                                                    <td data-column="Hawker Stall"><?php echo !empty($row['stall_name']) ? htmlspecialchars($row['stall_name']) : '-'; ?></td>
													<td data-column="Quantity"> <?php echo $row['quantity']; ?></td>
													<td data-column="price">$<?php echo $row['price']; ?></td>
													<td data-column="Queue"><?php echo ($row['queue_number'] !== null && $row['queue_number'] !== '') ? '#' . intval($row['queue_number']) : '-'; ?></td>
                                            <td data-column="Code"><?php echo !empty($row['verification_code']) ? $row['verification_code'] : '-'; ?></td>
                                            <td data-column="status">
														<?php
														$status = $row['status'];
														if ($status == "" or $status == "NULL") {
															?>
															<button type="button" class="btn theme-btn"><span class="fa fa-bars"
																	aria-hidden="true"></span> Preparing</button>
														<?php
														}
														if ($status == "in process") { ?>
															<button type="button" class="btn btn-warning"><span
																	class="fa fa-cog fa-spin" aria-hidden="true"></span> Ready for
																Pickup</button>
															<?php
														}
														if ($status == "closed") {
															?>
															<button type="button" class="btn btn-success"><span
																	class="fa fa-check-circle" aria-hidden="true"></span> Completed
																/ Collected</button>
														<?php
														}
														?>
														<?php
														if ($status == "rejected") {
															?>
															<button type="button" class="btn btn-danger"> <i
																	class="fa fa-close"></i> Cancelled</button>
														<?php
														}
														?>






													</td>
													<td data-column="Date"> <?php echo $row['date']; ?></td>
                                                    <td data-column="Reviews" class="orders-review-actions">
                                                        <?php if ($is_closed) {
                                                            if ($has_dish_review) { ?>
                                                                <span class="hg-review-btn hg-review-btn--done"><i class="fa fa-check"></i> Dish</span>
                                                            <?php } else { ?>
                                                                <button type="button" class="hg-review-btn js-open-review"
                                                                    data-review-type="dish"
                                                                    data-o-id="<?php echo $o_id; ?>"
                                                                    data-label="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>">
                                                                    <i class="fa fa-star"></i> Dish
                                                                </button>
                                                            <?php }
                                                            if ($is_closed && $batch_id !== '' && $rs_id > 0) {
                                                                if ($has_stall_review && !empty($stall_review_shown[$stall_key . '_done'])) {
                                                                    // already shown
                                                                } elseif ($has_stall_review) { ?>
                                                                <span class="hg-review-btn hg-review-btn--done"><i class="fa fa-check"></i> Stall</span>
                                                            <?php   $stall_review_shown[$stall_key . '_done'] = true;
                                                                } elseif ($show_stall_btn) { ?>
                                                                <button type="button" class="hg-review-btn js-open-review"
                                                                    data-review-type="stall"
                                                                    data-o-id="<?php echo $o_id; ?>"
                                                                    data-label="<?php echo htmlspecialchars($row['stall_name'], ENT_QUOTES); ?>">
                                                                    <i class="fa fa-star"></i> Stall
                                                                </button>
                                                            <?php }
                                                            }
                                                        } else { ?>
                                                            <span class="text-muted small">After collection</span>
                                                        <?php } ?>
                                                    </td>
													<td data-column="Action"> <a
															href="delete_orders.php?order_del=<?php echo $row['o_id']; ?>"
															onclick="return confirm('Are you sure you want to cancel your order?');"
															class="btn btn-danger btn-flat btn-addon btn-xs m-b-10"><i
																class="fa fa-trash-o" style="font-size:16px"></i></a>
													</td>

												</tr>


												<?php
											}
										}
										$stmt->close();
										?>




									</tbody>
								</table>



							</div>

						</div>



					</div>



				</div>
			</div>
	</div>
	</section>


	<footer class="footer">
		<div class="row bottom-footer">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-3 payment-options color-gray">
						<h5>Payment Options</h5>
						<ul>
							<li>
								<a href="#"> <img src="../images/paypal.png" alt="Paypal"> </a>
							</li>
							<li>
								<a href="#"> <img src="../images/mastercard.png" alt="Mastercard"> </a>
							</li>
							<li>
								<a href="#"> <img src="../images/maestro.png" alt="Maestro"> </a>
							</li>
							<li>
								<a href="#"> <img src="../images/stripe.png" alt="Stripe"> </a>
							</li>
							<li>
								<a href="#"> <img src="../images/bitcoin.png" alt="Bitcoin"> </a>
							</li>
						</ul>
					</div>
					<div class="col-xs-12 col-sm-4 address color-gray">
						<h5>Address</h5>
						 <p> 9 woodlands avenue, singapore</p>
                        <h5>Phone: +65 89562321</a></h5>
					</div>
					<div class="col-xs-12 col-sm-5 additional-info color-gray">
						<h5>Addition informations</h5>
						<p>Join thousands of hawker stalls and customers ordering on HawkerGo.</p>
					</div>
				</div>
			</div>
		</div>

		</div>
	</footer>

    <div class="modal fade hg-review-modal" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="review_submit.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reviewModalTitle">Leave a review</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="review_type" id="reviewType" value="">
                        <input type="hidden" name="o_id" id="reviewOrderId" value="">
                        <p class="text-muted" id="reviewTargetLabel"></p>
                        <label class="font-weight-bold">Your rating</label>
                        <div class="hg-star-picker">
                            <?php for ($s = 5; $s >= 1; $s--) { ?>
                            <input type="radio" name="rating" id="reviewRating<?php echo $s; ?>" value="<?php echo $s; ?>" <?php echo $s === 5 ? 'checked' : ''; ?> required>
                            <label for="reviewRating<?php echo $s; ?>"><span><i class="fa fa-star"></i></span></label>
                            <?php } ?>
                        </div>
                        <div class="form-group mb-0">
                            <label for="reviewComment" class="font-weight-bold">Comment (optional)</label>
                            <textarea class="form-control" name="comment" id="reviewComment" rows="3" maxlength="500" placeholder="Share your experience..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-purple">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	</div>


	<script src="../js/jquery.min.js"></script>
	<script src="../js/tether.min.js"></script>
	<script src="../js/bootstrap.min.js"></script>
	<script src="../js/animsition.min.js"></script>
	<script src="../js/bootstrap-slider.min.js"></script>
	<script src="../js/jquery.isotope.min.js"></script>
	<script src="../js/headroom.js"></script>
	<script src="../js/foodpicky.min.js"></script>
    <script>
    (function ($) {
        $('.js-open-review').on('click', function () {
            var type = $(this).data('review-type');
            var oId = $(this).data('o-id');
            var label = $(this).data('label');
            $('#reviewType').val(type);
            $('#reviewOrderId').val(oId);
            $('#reviewTargetLabel').text(type === 'stall' ? 'Review hawker stall: ' + label : 'Review dish: ' + label);
            $('#reviewModalTitle').text(type === 'stall' ? 'Review Hawker Stall' : 'Review Dish');
            $('#reviewComment').val('');
            $('#reviewModal input[name="rating"][value="5"]').prop('checked', true);
            $('#reviewModal').modal('show');
        });
    })(jQuery);
    </script>
</body>

</html>