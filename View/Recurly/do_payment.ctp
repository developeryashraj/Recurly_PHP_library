<?php echo $this->Html->script('payment'); ?>
<?php echo $this->Html->script('jquery.creditCardValidator'); ?>
<div class="padding-block plan-paymemt-detail orange">
    <div class="container">
        <div class="row">
            <div class="alert alert_payment alert-success alert-dismissible fade in <?php if(isset($errorMsg)) { ?> alert-danger <?php } ?>" role="alert" style="<?php if(isset($errorMsg)) { ?> display: block; <?php }else { ?> display: none; <?php } ?> ">
                <button type="button" class="close custom_close" aria-label="Close"><span aria-hidden="true">×</span></button>
                <span id="message-payment"><?php echo !empty($errorMsg) ? $errorMsg : '';  ?></span>
            </div>
            <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 col-lg-push-2 col-lg-pull-2 col-sm-push-0 col-sm-pull-0 col-md-push-1 col-md-pull-1">
                <form method="post" action="<?php echo SITE_URL; ?>recurly/raecurlySubscription">
                    <div class="row">
                        <div class="col-sm-7">
                            <h1 class="pagetitle block-title"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['header']; ?></h1>
                            <ul class="plan-selection-detail">
                                <li>
                                    <span><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['plan_detail_text']; ?> <strong><?php echo!empty($plan) ? ucfirst($plan) : ''; ?> plan</strong></span>
                                    <?php
                                    if (!empty($plan)) {
                                        ?>
                                        <a href="<?php echo SITE_URL . 'users/plans'; ?>" class="switch-plan-type1 <?php if ($plan == 'professional') { ?> hidden <?php } ?>" data-text="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['ProfessionalPlan']; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['switch_to']; ?> Professional</a>
                                        <a href="<?php echo SITE_URL . 'users/plans'; ?>" class="switch-plan-type1 <?php if ($plan == 'enterprise') { ?> hidden <?php } ?>" data-text="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['EnterprisePlan']; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['switch_to']; ?> Enterprise</a>
                                    <?php } ?>
                                </li>
                                <li>
                                    <span><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['paying_detail_text']; ?> <strong><?php echo!empty($billingInfo['plan']) ? $billingInfo['plan'] : ''; ?></strong></span>
                                    <a href="javascript:void(0);" class="switch-plan-term <?php if ($billingInfo['plan'] == $GLOBALS['LABELS']['MODULES']['BillingSummary']['Monthly']) { ?> hidden <?php } ?>" data-text="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Monthly']; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['switch_to']; ?> <?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Monthly']; ?></a>
                                    <a href="javascript:void(0);" class="switch-plan-term <?php if ($billingInfo['plan'] == $GLOBALS['LABELS']['MODULES']['BillingSummary']['Annually']) { ?> hidden <?php } ?>" data-text="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Annually']; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['switch_to']; ?> <?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Annually']; ?></a>
                                </li>
                                <li>
                                    <span><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['bucket_detail_text']; ?> <span class="bucket-quantity"><?php echo!empty($billingInfo['quantity']) ? $billingInfo['quantity'] : 0; ?></span> <?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['bucket_text']; ?></span>
                                    <a class="dc icon-signup btn-quantity-edit" href="javascript:void(0)">&nbsp;</a>

                                    <!--quantity-edit -->
                                    <div class="quantity-edit hidden">
                                        <!--quantity-box -->
                                        <div class="quantity-box">
                                            <button type="button" class="btn btn-quantity btn-down ripple-effect"></button>
                                            <input type="text" value="1" class="form-control" placeholder="" />
                                            <button type="button" class="btn btn-quantity btn-up  ripple-effect"></button>
                                        </div>
                                        <!--quantity-box -->

                                        <a href="javascript:void(0)" class="btn-quantity-done ripple-effect"><i class="dic-done dic"></i></a>
                                    </div>
                                    <!--quantity-edit -->

                                </li>
                                <li>
                                    <span><?php echo str_replace('#RATE', $billingInfo['plan_amount'], $GLOBALS['LABELS']['MODULES']['BillingSummary']['final_amount_text']); ?></span>
                                </li>
                            </ul>
                            <p class="renew-text"><?php //echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['renewal_text'];     ?></p>
                        </div>

                        <div class="col-sm-5">
                            <h1 class="pagetitle block-title"><?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['billing_detail']; ?> <a class="dc icon-signup" href="<?php echo SITE_URL . 'recurly/selectPlan'; ?>">&nbsp;</a></h1>
                            <address class="billing-address">
                                <strong class="buyer-name"><?php echo!empty($billingInfo['fullname']) ? ucwords($billingInfo['fullname']) : ''; ?></strong>
                                <span class="buyer-company"><?php echo!empty($billingInfo['company']) ? $billingInfo['company'] : ''; ?></span>

                                <p><?php echo!empty($billingInfo['address']) ? $billingInfo['address'] : ''; ?></p>
                            </address>
                        </div>
                    </div>

                    <!--payment page title -->
                    <h1 class="pagetitle block-title"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['header']; ?></h1>
                    <!--payment page title -->

                    <div class="card credit-card-form z-depth">
                        <div class="card-body">

                            <div class="form-group paper-input form-group-lg  field-card-name">
                                <label class="control-label" for="first-name"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['card_name']; ?></label>
                                <input type="text" class="form-control" name="card_name" value="<?php echo!empty($billingInfo['fullname']) ? ucwords($billingInfo['fullname']) : ''; ?>">
                            </div>

                            <div class="form-group paper-input form-group-lg  field-card-number">
                                <label class="control-label" for="first-name"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['card_number']; ?></label>
                                <input type="text" class="form-control" value="" name="card_number" data-recurly="number" id="number">
                            </div>

                            <div class="card-type" id="cardType">
                                <img class="master" src="<?php echo $webroot_img; ?>card-type-master.png">
                                <img class="visa" src="<?php echo $webroot_img; ?>card-type-visa.png">
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="form-group paper-input form-group-sm field-card-month">
                                <label class="control-label" for="first-name"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['expiration']; ?></label>
                                <select class="select-simple select-month" data-recurly="month" id="month" name="card_month">
                                    <option>MM</option>

                                    <?php
                                    $monthArray = array(01 => 'Jan', 02 => 'Feb', 03 => 'Mar', 04 => 'Apr', 05 => 'May', 06 => 'Jun', 07 => 'Jul', 08 => 'Aug', 09 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');
                                    foreach ($monthArray as $key => $value) {
                                        ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group paper-input form-group-sm  field-card-year">
                                <label class="control-label" for="first-name"></label>
                                <select class="select-simple select-year" data-recurly="year" id="year" name="card_year">
                                    <option>YYYY</option>
                                    <?php
                                    for ($i = date('Y'); $i <= $GLOBALS['LABELS']['MODULES']['PaymentDetails']['end_year_limit']; $i++) {
                                        ?>
                                        <option value="<?php echo substr($i, -2); ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group paper-input form-group-sm pull-right  field-card-cvv">
                                <label class="control-label" for="first-name"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['cvv']; ?> <a data-toggle="tooltip" data-placement="bottom" href="javascript:void(0);" class="title-help" data-original-title="The Card Verification Value (CVV*) is an extra code printed behind on your debit or credit card."><i class="dic-help dic dic-md"></i></a></label>
                                <input type="text" class="form-control" value="" id="cvv" name="card_cvv">
                            </div>
                        </div>
                    </div>

                    <?php
                    $firstName = '';
                    $lastName = '';
                    if (!empty($billingInfo['fullname'])) {
                        $name = explode(' ', $billingInfo['fullname']);
                    }
                    if (!empty($name)) {
                        $firstName = !empty($name[0]) ? $name[0] : '';
                        $lastName = !empty($name[1]) ? $name[1] : '';
                    }
                    ?>
                    <input type="hidden" data-recurly="first_name" id="first_name" name="first-name" value="<?php echo $firstName; ?>">
                    <input type="hidden" data-recurly="last_name" id="last_name" name="last-name" value="<?php echo $lastName; ?>">
                    <input type="hidden" id="country" name="country" data-recurly="country" value="<?php echo!empty($billingInfo['country']) ? $billingInfo['country'] : ''; ?>">
                    <input type="hidden" id="state" name="state" data-recurly="state" value="<?php echo!empty($billingInfo['state']) ? $billingInfo['state'] : ''; ?>">
                    <input type="hidden" data-recurly="city" id="city" name="city" value="<?php echo!empty($billingInfo['city']) ? $billingInfo['city'] : ''; ?>">
                    <input type="hidden" data-recurly="address1" id="address1" name="address1" value="<?php echo!empty($billingInfo['address']) ? $billingInfo['address'] : ''; ?>">
                    <input type="hidden" data-recurly="postal_code" id="postal_code" name="postal-code" value="<?php echo!empty($billingInfo['zipcode']) ? $billingInfo['zipcode'] : ''; ?>">
                    <input type="hidden" data-recurly="email" id="email" name="email" value="<?php echo!empty($billingInfo['email'][0]) ? $billingInfo['email'][0] : ''; ?>">
                    <input type="hidden" name="plan_code" value="<?php echo!empty($billingInfo['plan_code']) ? $billingInfo['plan_code'] : ''; ?>">
                    <input type="hidden" name="currency" value="<?php echo!empty($billingInfo['currency']) ? $billingInfo['currency'] : ''; ?>">
                    <input type="hidden" name="quantity" value="<?php echo!empty($billingInfo['quantity']) ? $billingInfo['quantity'] : 1; ?>">
                    <input type="hidden" name="planid" value="<?php echo!empty($billingInfo['plan_id']) ? $billingInfo['plan_id'] : 1; ?>">
                    <input type="hidden" name="company" value="<?php echo!empty($billingInfo['company']) ? $billingInfo['company'] : ''; ?>">
                    <input type="hidden" data-recurly="token" name="recurly-token">

                    <div class="text-center payment-action orange">
                        <a class="btn btn-lg btn-default ripple-effect" href="<?php echo SITE_URL . 'users/plans'; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['cancel_btn']; ?></a>
                        <button class="btn btn-lg btn-orange ripple-effect" id="subscribe"><?php echo $GLOBALS['LABELS']['MODULES']['PaymentDetails']['pay_btn']; ?></button>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://js.recurly.com/v3/recurly.js"></script>
<script>
    // Configure recurly.js
    recurly.configure('ewr1-FbAh5occLHzIz9githajTD');
    // On form submit, we stop submission to go get the token
    $('form').on('submit', function (event) {
        event.preventDefault();
        // Reset the errors display
        //$('#errors').text('');
        $('input').removeClass('error');
        // Disable the submit button
        $('button').prop('disabled', true);
        var form = this;
        // Now we call recurly.token with the form. It goes to Recurly servers
        // to tokenize the credit card information, then injects the token into the
        // data-recurly="token" field above
        recurly.token(form, function (err, token) {
            // send any errors to the error function below
            if (err)
                error(err);
            // Otherwise we continue with the form submission
            else
                form.submit();
        });
    });
    // A simple error handling function to expose errors to the customer
    function error(err) {
        //$('#errors').text('The following fields appear to be invalid: ' + err.fields.join(', '));
        $('button').prop('disabled', false);
        var alert_div = $("div.alert_payment");
        var message_class = "danger";
        alert_div.hide();
        alert_div.removeClass("alert-danger");
        alert_div.removeClass("alert-success");
        alert_div.addClass("alert-" + message_class);

        $("#message-payment").text('The following fields appear to be invalid: ' + err.fields.join(', '));
        alert_div.show();
        $('html, body').animate({
            scrollTop: alert_div.offset().top - 200
        }, 800);
        hide_alert_msg(alert_div);
        $.each(err.fields, function (i, field) {
            $('[data-recurly=' + field + ']').addClass('error');
        });
    }
    // runs some simple animations for the page
    $('body').addClass('show');
</script>